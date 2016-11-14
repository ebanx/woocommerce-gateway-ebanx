
<?php

require 'ebanx-php/src/autoload.php';

if (!defined('ABSPATH')) {
    exit;
}

abstract class WC_Ebanx_Gateway extends WC_Payment_Gateway
{

    public function __construct()
    {
        $this->init_settings();
    }

    public function admin_options()
    {
        include dirname(__FILE__) . '/admin/views/html-admin-page.php';
    }

    public function is_available()
    {
        return parent::is_available() && !empty($this->api_key) && !empty($this->encryption_key);
    }

    protected function request_data($order)
    {
        $data = array(
            'mode'      => 'full',
            'operation' => 'request',
            'payment'   => array(
                'country'               => $_POST['billing_country'], // TODO: Dynamic from config or this?
                'currency_code'         => WC_Ebanx_Gateway_Utils::CURRENCY_CODE_USD, // TODO: Dynamic
                "name"                  => $_POST['billing_first_name'] . " " . $_POST['billing_last_name'],
                "email"                 => $_POST['billing_email'],
                "phone_number"          => $_POST['billing_phone'],
                'amount_total'          => $order->get_total(),
                'order_number'          => $order->id,
                'merchant_payment_code' => $order->id . '-' . md5(rand(123123, 9999999)),
            ),
        );

        if (trim(strtolower(WC()->customer->get_shipping_country())) === WC_Ebanx_Gateway_Utils::COUNTRY_BRAZIL) {
            $data['payment'] = array_merge($data['payment'], array(
                'document'      => '07834442902', // TODO get from ?
                'birth_date'    => '03/11/1992', // TODO get from ?
                'zipcode'       => $_POST['billing_postcode'],
                'address'       => $_POST['billing_address_1'],
                'street_number' => 123, // TODO get from ?
                'city'          => $_POST['billing_city'],
                'state'         => $_POST['billing_state'],
            ));
        }

        return $data;
    }

    protected function getTransactionAddress($attr = '')
    {
        if (empty($_POST['billing_country']) && empty(WC()->customer->get_shipping_country())) {
            throw new Exception("Missing address country.");
        }

        $this->address[country] = trim(strtolower($_POST['billing_country']));

        if (empty($this->address[country])) {
            $this->address[country] = trim(strtolower(WC()->customer->get_shipping_country()));
        }

        if ($attr !== '' && !empty($this->address[$attr])) {
            return $this->address[$attr];
        }

        return $this->address;
    }

    public function process_payment($order_id)
    {
        try {
            $order = wc_get_order($order_id);

            if ($order->get_total() > 0) {
                $data = $this->request_data($order);

                $config = [
                    'integrationKey' => $this->settings['api_key'],
                    'testMode'       => ('yes' === $this->settings['testmode']),
                ];

                \Ebanx\Config::set($config);
                \Ebanx\Config::setDirectMode(true);

                $request = \Ebanx\Ebanx::doRequest($data);

                // TODO: Remove this
                echo json_encode(array(
                    'config'  => $config,
                    'request' => $request,
                    'post'    => $_POST,
                    'order'   => $order,
                    'data'    => $data,
                ));

                $this->process_response($request, $order); // TODO: What make when response_Error called?
            } else {
                $order->payment_complete();
            }

            return $this->dispatch(array(
                'result'   => 'success',
                'redirect' => $this->get_return_url($order),
            ));
        } catch (Exception $e) {
            // TODO: How make this ?
            wc_add_notice($e->getMessage(), 'error');
            WC()->session->set('refresh_totals', true);
            WC_EBANX::log(sprintf(__('Error: %s', 'woocommerce-ebanx'), $e->getMessage()));
            return;
        }
    }

    protected function dispatch($data)
    {
        WC()->cart->empty_cart();

        return $data;
    }

    protected function save_order_meta_fields($order, $request)
    {
        // General
        // TODO: Hash, payment_type_code if possible
        update_post_meta($order->id, '_ebanx_payment_hash', $request->payment->hash);
        update_post_meta($order->id, 'Payment\'s Hash', $request->payment->hash);
    }

    protected function process_response_error($request, $order)
    {
        throw new Exception($request->status_message);
        // TODO: What make here?
        //        $message = $request->status_message;
        //        $order->update_status( 'failed', __( 'Pagar.me: The transaction was rejected by the card company or by fraud.', 'woocommerce-pagarme' ) );
        //
        //        $transaction_id  = get_post_meta( $order->id, '_wc_pagarme_transaction_id', true );
        //        $transaction_url = '<a href="https://dashboard.pagar.me/#/transactions/' . intval( $transaction_id ) . '">https://dashboard.pagar.me/#/transactions/' . intval( $transaction_id ) . '</a>';
        //
        //        $this->send_email(
        //            sprintf( esc_html__( 'The transaction for order %s was rejected by the card company or by fraud', 'woocommerce-pagarme' ), $order->get_order_number() ),
        //            esc_html__( 'Transaction failed', 'woocommerce-pagarme' ),
        //            sprintf( esc_html__( 'Order %1$s has been marked as failed, because the transaction was rejected by the card company or by fraud, for more details, see %2$s.', 'woocommerce-pagarme' ), $order->get_order_number(), $transaction_url )
        //        );
    }

    protected function process_response($request, $order)
    {
        WC_EBANX::log("Processing response: " . print_r($request, true));

        if ($request->status == 'ERROR') {
            return $this->process_response_error($request, $order);
        }

        $message = 'Compra aprovada. Hash: ' . $request->payment->hash;

        WC_EBANX::log($message);

        if ($request->payment->pre_approved) {
            $order->add_order_note(__('EBANX: Transaction paid.', 'woocommerce-ebanx'));
            $order->payment_complete($request->hash);
        }

        $this->save_order_meta_fields($order, $request);
    }

    final public function process_hook($hash)
    {
        $config = [
            'integrationKey' => $this->settings['api_key'],
            'testMode'       => ('yes' === $this->settings['testmode']),
        ];

        \Ebanx\Config::set($config);

        $data = \Ebanx\Ebanx::doQuery(array(
            'hash' => $hash,
        ));

        $order = reset(get_posts(array(
            'meta_query' => array(
                array(
                    'key'   => '_ebanx_payment_hash',
                    'value' => $hash,
                ),
            ),
            'post_type'  => 'shop_order',
        )));

        $order = new WC_Order($order->ID);

        // TODO: if (empty($order)) {}
        // TODO: if ($data->status != "SUCCESS")

        switch (strtoupper($data->payment->status)) {
            case 'CO':
                $order->update_status('completed');
                break;
            case 'CA':
                $order->update_status('cancelled');
                break;
            case 'PE':
                $order->update_status('pending');
                break;
            case 'OP':
                $order->update_status('processing');
                break;
        }

        // TODO: How to call process response to finish the transaction and save meta fields?
    }
}
