<?php

require 'ebanx-php/src/autoload.php';

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Ebanx_Gateway class.
 *
 * @extends WC_Payment_Gateway
 */
abstract class WC_Ebanx_Gateway extends WC_Payment_Gateway {

    /**
     * Constructor for the gateway.
     */
    public function __construct() {
        // TODO make debug option
        /*$this->debug = $this->get_option( 'debug' );
        if ( 'yes' === $this->debug ) {
            $this->log = new WC_Logger();
        }*/
    }

    /**
     * Admin page.
     */
    public function admin_options() {
        include dirname( __FILE__ ) . '/admin/views/html-admin-page.php';
    }

    /**
     * Check if the gateway is available to take payments.
     *
     * @return bool
     */
    public function is_available() {
        /*TODO: Make this by country rule ? and .. && $this->api->using_supported_currency()*/
        return parent::is_available() && ! empty( $this->api_key ) && ! empty( $this->encryption_key );
    }

    protected function request_data($order) {
        $data = [
            'mode' => 'full',
            'operation' => 'request',
            'payment' => array(
                'country' => $_POST['billing_country'], // TODO: Dynamic from config or this?
                'currency_code' => 'USD', // TODO: Dynamic
                "name" => $_POST['billing_first_name']." ".$_POST['billing_last_name'],
		        "email" => $_POST['billing_email'],
                "phone_number" => $_POST['billing_phone'],
                'amount_total' => $order->get_total() / 100,
                'order_number' => $order->id,
                'merchant_payment_code' => $order->id . '-' . md5(rand(123123, 9999999))
            )
        ];

        if(trim(strtolower($_POST['billing_country']))==WC_Ebanx_Gateway_Utils::COUNTRY_BRAZIL) {
            $data['payment'] = array_merge($data['payment'], array(
                'document' => '519.168.571-75', // TODO get from ?
                'birth_date' => '03/11/1992', // TODO get from ?
                'zipcode' => $_POST['billing_postcode'],
                'address' => $_POST['billing_address_1'],
                'street_number' => 123, // TODO get from ?
                'city' => $_POST['billing_city'],
                'state' => $_POST['billing_state']
            ));
        }

        return $data;
    }

    /**
     * Process the payment.
     *
     * @param int $order_id Order ID.
     *
     * @return array Redirect data.
     */
    public function process_payment( $order_id ) {
        try {
            $order = wc_get_order( $order_id );

            if ( $order->get_total() > 0 ) {
                $data = $this->request_data($order);

                $options = get_option( 'woocommerce_ebanx_settings' );
                // TODO: $integrationKey = 'yes' === $options['testmode'] ? $options['test_secret_key'] : $options['secret_key'];

                \Ebanx\Config::set([
                    'integrationKey' => $this->get_option( 'api_key' ),
                    'testMode'       => true // TODO 'yes' === $options['testmode']
                ]);

                \Ebanx\Config::setDirectMode(true); //TODO: Option on admin ?

                $request = \Ebanx\Ebanx::doRequest($data);

                echo json_encode(array(
                    'request'=>$request,
                    'post'=>$_POST,
                    'order'=>$order,
                    'data'=>$data
                ));
                $this->process_response($request, $order); // TODO: What make when response_Error called?
            } else {
                $order->payment_complete();
            }

            WC()->cart->empty_cart();

            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order )
            );
        } catch (Exception $e) {
            // TODO: How make this ?
            wc_add_notice( $e->getMessage(), 'error' );
            WC()->session->set( 'refresh_totals', true );
            WC_EBANX::log( sprintf( __( 'Error: %s', 'woocommerce-ebanx' ), $e->getMessage() ) );
            return;
        }
    }

    protected function save_order_meta_fields($id, $request) {} // TODO: abstract

    protected function process_response_error($request, $order) {
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

    protected function process_response($request, $order) {
        WC_EBANX::log( "Processing response: " . print_r( $request, true ) );

        if ($request->status == 'ERROR') {
            return $this->process_response_error($request, $order);
        }

        $message = 'Compra aprovada. Hash: ' . $request->payment->hash;

        WC_EBANX::log( $message );

        update_post_meta( $order->id, '_ebanx_payment_hash', $request->payment->hash );

        if ($request->payment->pre_approved) {
            $order->add_order_note( __( 'EBANX: Transaction paid.', 'woocommerce-ebanx' ) );
            $order->payment_complete( $request->hash );
        }

        $this->save_order_meta_fields($order->id, $request);
    }

//    TODO: abstract ? public function thankyou_page( $order_id ) {}

//    TODO: abstract ? public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {

    /**
     * IPN handler.
     */
    public function ipn_handler() {
//        $this->api->ipn_handler();
    }
}
