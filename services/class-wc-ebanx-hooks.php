<?php
class WC_Ebanx_Hooks {

	public static function init(){
		add_action('init', __CLASS__.'::tefHookAction');
	}

	public static function tefHookAction(){
		// é hook tef url
		// tef gateway is enable
		if (
      $_SERVER['REDIRECT_URL']==='/ebanx/webhooks/notifications'
      && isset($_GET['payment_type_code'])
      && isset($_GET['hash'])
      && WC_Ebanx_Gateway_Utils::isTef($_GET['payment_type_code'])
    )
    {
      $ebanxTef = new WC_Ebanx_Tef_Gateway();
      $ebanxTef->process_hook($_GET['hash']);
    }
	}
}
WC_Ebanx_Hooks::init();
?>
