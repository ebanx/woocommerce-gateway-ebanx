<?php

class WC_Ebanx_Hooks
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
            && isset($_REQUEST['notification_type']) && isset($_REQUEST['hash_codes'])
        ) {
            $ebanx = new WC_Ebanx_Tef_Gateway();
            $ebanx->process_hook($_REQUEST['hash_codes'], $_REQUEST['notification_type']);
        }

        return true;
    }
}

WC_Ebanx_Hooks::init();
