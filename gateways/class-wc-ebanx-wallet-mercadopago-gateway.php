<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_EBANX_Wallet_MercadoPago_Gateway
 */
class WC_EBANX_Wallet_MercadoPago_Gateway extends WC_EBANX_Wallet_Gateway {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id           = 'ebanx-wallet-mercadopago';
		$this->method_title = __( 'EBANX - Wallet MercadoPago', 'woocommerce-gateway-ebanx' );
		$this->api_name     = 'mercadopago';
		$this->title        = 'MercadoPago';
		$this->description  = 'Você será redirecionado para o ambiente da MercadoPago.';

		parent::__construct();

		$this->ebanx_gateway = $this->ebanx->walletMercadoPago();

		$this->enabled = false;

		foreach (
			[
				'argentina_payment_methods',
				'brazil_payment_methods',
				'mexico_payment_methods',
			] as $country_payment_methods
		) {
			if ( ! is_array( $this->configs->settings[ $country_payment_methods ] ) ) {
				continue;
			}

			if ( in_array( $this->id, $this->configs->settings[ $country_payment_methods ], true ) ) {
				$this->enabled = 'yes';
				break;
			}
		}
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
				'wallet_name'   => get_post_meta( $order->get_id(), '_ebanx_wallet_mercadopago', true ),
				'customer_name' => get_post_meta( $order->get_id(), '_billing_first_name', true ),
			],
			'order_status' => $order->get_status(),
			'method'       => 'mercadopago',
		];

		parent::thankyou_page( $data );
	}
}
