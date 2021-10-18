<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_EBANX_Wallet_Picpay_Gateway
 */
class WC_EBANX_Wallet_Picpay_Gateway extends WC_EBANX_Wallet_Gateway {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id           = 'ebanx-wallet-picpay';
		$this->method_title = __( 'EBANX - Wallet MACH Pay', 'woocommerce-gateway-ebanx' );
		$this->api_name     = 'picpay';
		$this->title        = 'Picpay';
		$this->description  = 'Você será redirecionado para o ambiente da Picpay.';

		parent::__construct();

		$this->ebanx_gateway = $this->ebanx->walletPicpay();

		$this->enabled = is_array( $this->configs->settings['brazil_payment_methods'] ) && in_array( $this->id, $this->configs->settings['brazil_payment_methods'], true )
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
				'wallet_name'   => get_post_meta( $order->get_id(), '_ebanx_wallet_picpay', true ),
				'customer_name' => get_post_meta( $order->get_id(), '_billing_first_name', true ),
			],
			'order_status' => $order->get_status(),
			'method'       => 'picpay',
		];

		parent::thankyou_page( $data );
	}
}
