<?php

require 'ebanx-php/src/autoload.php';

if (!defined('ABSPATH')) {
    exit;
}

abstract class WC_Ebanx_Gateway extends WC_Payment_Gateway
{

    public function __construct()
    {
        $this->userId = get_current_user_id();

        $this->configs = new WC_Ebanx_Global_Gateway();

        $this->enabled = $this->configs->settings[$this->id];

        $this->is_sandbox = $this->configs->settings['sandbox_enabled'] === 'yes';

        $this->private_key = $this->is_sandbox ? $this->configs->settings['sandbox_private_key'] : $this->configs->settings['production_private_key'];

        $this->public_key = $this->is_sandbox ? $this->configs->settings['sandbox_public_key'] : $this->configs->settings['production_public_key'];

        if ($this->configs->settings['debug_enabled'] === 'yes') {
            $this->log = new WC_Logger();
        }

        add_action('wp_enqueue_scripts', array($this, 'checkout_scripts'));

        add_filter('woocommerce_checkout_fields', function ($fields) {
            $fields['billing']['ebanx_billing_brazil_street_number'] = array(
                'type'     => 'text',
                'label'    => 'Street Number',
                'required' => true,
            );
            $fields['billing']['ebanx_billing_brazil_birth_date'] = array(
                'type'     => 'text',
                'label'    => 'Birth Date',
                'required' => true,
            );
            $fields['billing']['ebanx_billing_brazil_document'] = array(
                'type'     => 'text',
                'label'    => 'Document',
                'required' => true,
            );
            return $fields;
        });

        $this->supports = array(
            // 'subscriptions',
            'refunds'
        );

        $this->icon = $this->show_icon();
    }

    public function show_icon()
    {
        return plugins_url('/assets/images/' . $this->id . '.png', plugin_basename(dirname(__FILE__)));
    }

    public function checkout_scripts()
    {
        if (is_checkout()) {
            wp_enqueue_script('woocommerce_ebanx_checkout_fields', plugins_url('assets/js/checkout-fields.js', WC_Ebanx::DIR));
        }
    }

    public function admin_options()
    {
        include dirname(__FILE__) . '/admin/views/html-admin-page.php';
    }

    public function is_available()
    {
        return parent::is_available() && !empty($this->public_key) && !empty($this->private_key);
    }

    public function process_refund($order_id, $amount = null, $reason = '')
    {
        $order = wc_get_order($order_id);

        $hash = get_post_meta($order->id, '_ebanx_payment_hash', true);

        if (!$order || is_null($amount) || !$hash) {
            return false;
        }

        $data = array(
            'hash' => $hash,
            'amount' => $amount,
            'operation' => 'request',
            'description' => $reason
        );

        $config = [
            'integrationKey' => $this->private_key,
            'testMode'       => $this->is_sandbox,
        ];

        \Ebanx\Config::set($config);

        $request = \Ebanx\Ebanx::doRefund($data);

        if ($request->status !== 'SUCCESS') {
            return false;
        }

        $order->add_order_note(sprintf('Refund requested to EBANX %s - Refund ID: %s - Reason: %s', wc_price($amount), $request->refund->id, $reason));

        $refunds = current(get_post_meta((int) $order_id, "_ebanx_payment_refunds"));

        $request->refund->wc_refund = current($order->get_refunds());

        $refunds[] = $request->refund;

        update_post_meta($order->id, "_ebanx_payment_refunds", $refunds);

        return true;
    }

    protected function request_data($order)
    {
        $data = array(
            'mode'      => 'full',
            'operation' => 'request',
            'payment'   => array(
                'country'               => $order->get_address()['country'],
                'currency_code'         => WC_Ebanx_Gateway_Utils::CURRENCY_CODE_USD, // TODO: Dynamic
                "name"                  => $order->get_address()['first_name'] . " " . $order->get_address()['last_name'],
                "email"                 => $order->get_address()['email'],
                "phone_number"          => $order->get_address()['phone'],
                'amount_total'          => $order->get_total(),
                'order_number'          => $order->id,
                'merchant_payment_code' => $order->id . '-' . md5(rand(123123, 9999999)),
            ),
        );

        if (trim(strtolower(WC()->customer->get_shipping_country())) === WC_Ebanx_Gateway_Utils::COUNTRY_BRAZIL) {
            if (empty($_POST['ebanx_billing_brazil_document']) ||
                empty($_POST['billing_postcode']) ||
                empty($_POST['billing_address_1']) ||
                empty($_POST['billing_city']) ||
                empty($_POST['billing_state'])
            ) {
                throw new Exception("Missing fields to checkout.");
            }

            $data['payment'] = array_merge($data['payment'], array(
                'document'      => $_POST['ebanx_billing_brazil_document'],
                'zipcode'       => $order->get_address()['postcode'],
                'birth_date'    => '10/10/2010',
                'street_number' => '123',
                'address'       => $order->get_address()['address_1'],
                'city'          => $order->get_address()['city'],
                'state'         => $order->get_address()['state'],
            ));
        }

        return $data;
    }

    protected function getTransactionAddress($attr = '')
    {
        if (empty(WC()->customer) || is_admin()) {
            return false;
        }

        if (empty($_POST['billing_country']) && empty(WC()->customer->get_shipping_country())) {
            throw new Exception("Missing address country.");
        }

        if (!empty($_POST['billing_country'])) {
            $this->address['country'] = trim(strtolower($_POST['billing_country']));
        } else {
            $this->address['country'] = trim(strtolower(WC()->customer->get_shipping_country()));
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
                    'integrationKey' => $this->private_key,
                    'testMode'       => $this->is_sandbox,
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

        $this->save_user_meta_fields($order);
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
            $order->payment_complete($request->payment->hash);
        }

        $this->save_order_meta_fields($order, $request);
    }

    protected function save_user_meta_fields($order)
    {
        if ($this->userId) {
            if (trim(strtolower($order->get_address()['country'])) === WC_Ebanx_Gateway_Utils::COUNTRY_BRAZIL) {
                update_user_meta($this->userId, '__ebanx_billing_brazil_document', $_POST['ebanx_billing_brazil_document']);
            }
        }
    }

    final public function process_hook($hash, $notificationType)
    {
        $config = [
            'integrationKey' => $this->private_key,
            'testMode'       => $this->is_sandbox,
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

        switch (strtoupper($notificationType)) {
            case 'REFUND':
                $refunds = current(get_post_meta($order->id, "_ebanx_payment_refunds"));

                foreach ($refunds as $k => $ref) {
                    foreach ($data->payment->refunds as $refund) {
                        if ($ref->id == $refund->id) {
                            if ($refund->status == 'CO' && $refunds[$k]->status != 'CO') {
                                $order->add_order_note(sprintf('Refund confirmed to EBANX - Refund ID: %s', $refund->id));
                            }
                            if ($refund->status == 'CA' && $refunds[$k]->status != 'CA') {
                                $order->add_order_note(sprintf('Refund canceled to EBANX - Refund ID: %s', $refund->id));
                            }

                            $refunds[$k]->status       = $refund->status; // status == co save note
                            $refunds[$k]->cancel_date  = $refund->cancel_date;
                            $refunds[$k]->request_date = $refund->request_date;
                            $refunds[$k]->pending_date = $refund->pending_date;
                            $refunds[$k]->confirm_date = $refund->confirm_date;
                        }
                    }
                }

                update_post_meta($order->id, "_ebanx_payment_refunds", $refunds);
            break;
            case 'UPDATE':
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
            break;
        };
    }
}
