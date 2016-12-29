<?php

class WC_EBANX_Hooks
{

    public static function init()
    {
        add_action('init', __CLASS__ . '::paymentStatusHookAction');
    }

    public static function paymentStatusHookAction()
    {
//        $myfile = fopen("/var/www/checkout-woocommerce/test.txt", "a") or die("Unable to open file!");
//        fwrite($myfile, json_encode(array('get' => $_GET, 'post' => $_REQUEST, 'request' => $_REQUEST)));

        if (isset($_REQUEST['operation']) && $_REQUEST['operation'] == 'payment_status_change'
            && isset($_REQUEST['notification_type']) && (isset($_REQUEST['hash_codes'])||isset($_REQUEST['codes']))
        ) {
            $codes = array();

            if (isset($_REQUEST['hash_codes'])) {
                $codes['hash'] = $_REQUEST['hash_codes'];
            }

            if (isset($_REQUEST['codes'])) {
                $codes['merchant_payment_code'] = $_REQUEST['codes'];
            }

            $ebanx = new WC_EBANX_Tef_Gateway();
            $ebanx->process_hook($codes, $_REQUEST['notification_type']);
        }

        return true;
    }
}

WC_EBANX_Hooks::init();
