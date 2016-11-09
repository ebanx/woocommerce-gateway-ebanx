<?php

class WC_Ebanx_Hooks {

	public static function init(){
		add_action('init', __CLASS__.'::paymentStatusHookAction');
	}

	public static function paymentStatusHookAction(){
        if (
            isset($_GET['ebanx']) &&
            isset($_GET['payment_type_code']) &&
            isset($_GET['hash']) && WC_Ebanx_Gateway_Utils::isTef($_GET['payment_type_code'])
        ) {
            $ebanx = new WC_Ebanx_Tef_Gateway();
            $ebanx->process_hook($_GET['hash']);
        }
    }
}

WC_Ebanx_Hooks::init();
