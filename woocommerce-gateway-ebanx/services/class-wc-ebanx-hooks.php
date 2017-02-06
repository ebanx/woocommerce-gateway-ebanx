<?php

class WC_EBANX_Hooks {
    /**
     * Initiliazer
     *
     * @return void
     */
    public static function init() {
        add_action( 'init', __CLASS__ . '::payment_status_hook_action' );
    }

    /**
     * Check if the url has the response type
     *
     * @return boolean
     */
    private static function is_url_response() {
        $urlResponse = ( isset( $_REQUEST['hash'] ) && isset( $_REQUEST['merchant_payment_code'] ) && isset( $_REQUEST['payment_type_code'] ) );

        if ( $urlResponse ) {
            $_REQUEST['notification_type'] = 'UPDATE';
        }

        return $urlResponse;
    }

    /**
     * Process future hooks for cash payments like TEF, OXXO, etc
     *
     * @return boolean
     */
    public static function payment_status_hook_action() {
        ob_start();

        // $myfile = fopen("/var/www/checkout-woocommerce/test.txt", "a") or die("Unable to open file!");
        // fwrite($myfile, json_encode(array('get' => $_GET, 'post' => $_REQUEST, 'request' => $_REQUEST)));

        if ( ( isset( $_REQUEST['operation'] ) && $_REQUEST['operation'] == 'payment_status_change'
                && isset( $_REQUEST['notification_type'] ) && ( isset( $_REQUEST['hash_codes'] )||isset( $_REQUEST['codes'] ) ) )
            || self::is_url_response()
        ) {
            $codes = array();

            if ( isset( $_REQUEST['hash_codes'] ) ) {
                $codes['hash'] = $_REQUEST['hash_codes'];
            }

            if ( isset( $_REQUEST['hash'] ) ) {
                $codes['hash'] = $_REQUEST['hash'];
            }

            if ( isset( $_REQUEST['codes'] ) ) {
                $codes['merchant_payment_code'] = $_REQUEST['codes'];
            }

            if ( isset( $_REQUEST['merchant_payment_code'] ) ) {
                $codes['merchant_payment_code'] = $_REQUEST['merchant_payment_code'];
            }

            $ebanx = new WC_EBANX_Tef_Gateway();
            $order = $ebanx->process_hook( $codes, $_REQUEST['notification_type'] );

            if ( self::is_url_response() ) {
                wp_redirect( $order->get_checkout_order_received_url() );
                exit;
            }
        }

        ob_end_clean();

        return true;
    }
}

WC_EBANX_Hooks::init();
