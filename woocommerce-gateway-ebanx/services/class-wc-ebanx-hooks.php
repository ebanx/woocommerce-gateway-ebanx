<?php

class WC_EBANX_Hooks
{
    /**
     * Initiliazer
     *
     * @return void
     */
    public static function init()
    {
        add_action('init', __CLASS__ . '::paymentStatusHookAction');
    }

    /**
     * Process future hooks for cash payments like TEF, OXXO, etc
     *
     * @return boolean
     */
    public static function paymentStatusHookAction()
    {
        if (
            isset($_REQUEST['operation']) &&
            $_REQUEST['operation'] == 'payment_status_change' &&
            isset($_REQUEST['notification_type']) &&
            (isset($_REQUEST['hash_codes']) ||
            isset($_REQUEST['codes']))
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
