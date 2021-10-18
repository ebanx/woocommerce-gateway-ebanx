<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_EBANX_Wallet_Paypal_Gateway
 */
class WC_EBANX_Wallet_Paypal_Gateway extends WC_EBANX_Wallet_Gateway {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id           = 'ebanx-wallet-paypal';
		$this->method_title = __( 'EBANX - Wallet PayPal', 'woocommerce-gateway-ebanx' );
		$this->api_name     = 'paypal';
		$this->title        = 'PayPal';
		$this->description  = 'Você será redirecionado para o ambiente da PayPal.';

		parent::__construct();

		$this->ebanx_gateway = $this->ebanx->walletPaypal();

		$this->enabled = is_array( $this->configs->settings['brazil_payment_methods'] )
						 && in_array( $this->id, $this->configs->settings['brazil_payment_methods'] )
			? 'yes'
			: false;

		$this->debug_log_if_available('Constructing ' . $this->id . ' gateway');
		$this->debug_log_if_available($this->id . ($this->enabled ? ' is ' : ' is not ') . 'enabled');
		$this->debug_log_if_available($this->id . ' supports ' . implode(', ', $this->supports));
	}

	/**
	 * The page of order received, we call them as "Thank you pages"
	 *
	 * @param WC_Order $order The order created.
	 *
	 * @return void
	 */
	public static function thankyou_page( $order ) {
		$data = [
			'data'         => [
				'wallet_name'   => get_post_meta( $order->get_id(), '_ebanx_wallet_paypal', true ),
				'customer_name' => get_post_meta( $order->get_id(), '_billing_first_name', true ),
			],
			'order_status' => $order->get_status(),
			'method'       => 'paypal',
		];

		parent::thankyou_page( $data );
	}
}
