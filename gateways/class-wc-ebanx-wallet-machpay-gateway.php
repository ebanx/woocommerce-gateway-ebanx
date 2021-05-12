<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_EBANX_Wallet_MACHPay_Gateway
 */
class WC_EBANX_Wallet_MACHPay_Gateway extends WC_EBANX_Wallet_Gateway {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id           = 'ebanx-wallet-machpay';
		$this->method_title = __( 'EBANX - Wallet MACH Pay', 'woocommerce-gateway-ebanx' );
		$this->api_name     = 'machpay';
		$this->title        = 'MACH Pay';
		$this->description  = 'Você será redirecionado para o ambiente da MACH Pay.';

		parent::__construct();

		$this->ebanx_gateway = $this->ebanx->walletMACHPay();

		$this->enabled = is_array( $this->configs->settings['chile_payment_methods'] )
						 && in_array( $this->id, $this->configs->settings['chile_payment_methods'] )
			? 'yes'
			: false;
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
				'wallet_name'   => get_post_meta( $order->get_id(), '_ebanx_wallet_machpay', true ),
				'customer_name' => get_post_meta( $order->get_id(), '_billing_first_name', true ),
			],
			'order_status' => $order->get_status(),
			'method'       => 'machpay',
		];

		parent::thankyou_page( $data );
	}
}
