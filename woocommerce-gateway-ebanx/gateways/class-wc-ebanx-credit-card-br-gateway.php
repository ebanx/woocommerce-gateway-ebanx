<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Credit_Card_BR_Gateway extends WC_EBANX_Credit_Card_Gateway
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->id = 'ebanx-credit-card-br';
		$this->method_title = __('EBANX - Credit Card Brazil', 'woocommerce-gateway-ebanx');

		$this->title = __('Cartão de Crédito', 'woocommerce-gateway-ebanx');
		$this->description = __('Pague com cartão de crédito.', 'woocommerce-gateway-ebanx');

		parent::__construct();

		$this->enabled = is_array($this->configs->settings['brazil_payment_methods']) ? in_array($this->id, $this->configs->settings['brazil_payment_methods']) ? 'yes' : false : false;
	}

	/**
	 * Check if the method is available to show to the users
	 *
	 * @return boolean
	 */
	public function is_available()
	{
		return parent::is_available() && $this->getTransactionAddress('country') == WC_EBANX_Constants::COUNTRY_BRAZIL;
	}

	/**
	 * Check if the currency is processed by EBANX
	 *
	 * @param  string $currency Possible currencies: BRL
	 * @return boolean          Return true if EBANX process the currency
	 */
	public function ebanx_process_merchant_currency($currency) {
		return $currency === WC_EBANX_Constants::CURRENCY_CODE_BRL;
	}

	/**
	 * Action to capture the payment
	 *
	 * @param  WC_Order $order WooCommerce Order
	 * @return void
	 */
	public function capture_payment_action($order)
	{
		if ($order->get_status() != 'pending' || $order->payment_method != $this->id) {
			return;
		}

		\Ebanx\Config::set([
			'integrationKey' => $this->private_key,
			'testMode' => $this->is_sandbox_mode,
		]);

		$request = \Ebanx\Ebanx::doCapture(['hash' => get_post_meta($order->id, '_ebanx_payment_hash')]);

		if ($request->status != 'SUCCESS') {
			return;
		}

		if ($request->payment->status == 'CO') {
			$order->payment_complete();
			$order->update_status('processing');
			$order->add_order_note(sprintf(__('EBANX: Transaction captured by %s', 'woocommerce-gateway-ebanx'), wp_get_current_user()->data->user_email));
		}
	}

	/**
	 * The HTML structure on checkout page
	 */
	public function payment_fields() {
		parent::payment_fields();

		parent::checkout_rate_conversion(WC_EBANX_Constants::CURRENCY_CODE_BRL);
	}
}
