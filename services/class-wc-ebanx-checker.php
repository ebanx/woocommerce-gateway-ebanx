<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Checker {

	/**
	* Checks if sandbox mode is enabled and warns the user if it is.
	*
	* @return void
	*/
	public static function check_sandbox_mode($context) {

		if (!$context->is_sandbox_mode || WC_EBANX_Request::has('action') ) {
			return;
		}

		$info_message = __('EBANX - The Sandbox Mode option is enabled, in this mode, none of your transactions will be processed.', 'woocommerce-gateway-ebanx');
		$context->notices
			->with_message($info_message)
			->with_type('info')
			->persistent()
			->enqueue();
	}

	/**
	 * Check if the merchant's integration keys are valid
	 *
	 * @return void
	 */
	public static function check_merchant_api_keys($context)
	{
		if (get_option('_ebanx_api_was_checked') === 'success') {
			return;
		}

		if (!self::check_api_key_pair($context)) {
			return;
		}

		update_option('_ebanx_api_was_checked', 'success');
	}

	/**
	 * Check if both integration keys
	 *
	 * @param  WC_EBANX_Gateway $context The gateway context
	 * @return bool
	 */
	public static function check_api_key_pair($context) {
		if (!self::check_api_key_pair_empty($context)) {
			return false;
		}

		$private_check = self::check_private_key($context);
		if ($private_check === null) {
			return false;
		}

		$public_check = self::check_public_key($context);
		if ($public_check === null) {
			return false;
		}

		if (!$private_check || !$public_check) {
			return false;
		}

		return true;
	}

	/**
	 * Check empty keys
	 *
	 * @return bool
	 */
	public static function check_api_key_pair_empty($context) {
		if (!empty($context->public_key) || !empty($context->private_key)) {
			return true;
		}

		$context->notices
			->with_message(sprintf(__('EBANX - We are almost there. To start selling, <a href="%s">set your integration keys.</a>', 'woocommerce-gateway-ebanx'), admin_url( WC_EBANX_Constants::SETTINGS_URL )))
			->with_type('warning')
			->persistent();

		if ( WC_EBANX_Request::is_post_empty() ) {
			$context->notices->enqueue();
			return false;
		}

		$context->notices->display();
		return false;
	}

	/**
	 * Check private key
	 *
	 * @return bool
	 */
	public static function check_private_key($context) {
		\Ebanx\Config::set(array('integrationKey' => $context->private_key, 'testMode' => $context->is_sandbox_mode));
		try {
			$res = \Ebanx\Ebanx::getMerchantIntegrationProperties(array('integrationKey' => $context->private_key));
			if ($res->status === 'SUCCESS') {
				return true;
			}

			$message = sprintf(
				__('EBANX - Your <strong>%s Integration Key</strong> is invalid, please <a href="%s">adjust your settings</a>.', 'woocommerce-gateway-ebanx'),
				$context->is_sandbox_mode ? "Sandbox" : "Live",
				WC_EBANX_Constants::SETTINGS_URL
			);
			$context->notices
				->with_message($message)
				->with_type('error')
				->persistent();

			if ( WC_EBANX_Request::is_post_empty() ) {
				$context->notices->enqueue();
				return false;
			}

			$context->notices->display();
		}
		catch (RuntimeException $e) {
			self::connection_error($context);
		}
		return null;
	}

	/**
	 * Check public key
	 *
	 * @return bool
	 */
	public static function check_public_key($context) {
		\Ebanx\Config::set(array('integrationKey' => $context->private_key, 'testMode' => $context->is_sandbox_mode));
		try {
			$res = \Ebanx\Ebanx::getMerchantIntegrationPublicProperties(array('public_integration_key' => $context->public_key));
			if ($res->status === 'SUCCESS') {
				return true;
			}

			$message = sprintf(
				__('EBANX - Your <strong>%s Public Integration Key</strong> is invalid, please <a href="%s">adjust your settings</a>.', 'woocommerce-gateway-ebanx'),
				$context->is_sandbox_mode ? "Sandbox" : "Live",
				WC_EBANX_Constants::SETTINGS_URL
			);
			$context->notices
				->with_message($message)
				->with_type('error')
				->persistent();

			if ( WC_EBANX_Request::is_post_empty() ) {
				$context->notices->enqueue();
				return false;
			}

			$context->notices->display();
		}
		catch (RuntimeException $e) {
			self::connection_error($context);
		}
		return null;
	}

	private static function connection_error($context) {
		$api_url = 'https://api.ebanx.com';

		$message = sprintf(
			__('EBANX - Could not connect to our servers. Please check if your server can reach our API (<a href="%1$s">%1$s</a>).', 'woocommerce-gateway-ebanx'),
			$api_url);
		$context->notices
			->with_message($message)
			->with_type('error')
			->persistent();

		if ( WC_EBANX_Request::is_post_empty() ) {
			$context->notices->enqueue();
			return;
		}

		$context->notices->display();
	}

	/**
	 * Check if the merchant's environment meets the requirements
	 *
	 * @return void
	 */
	public static function check_environment($context)
	{
		$notice = $context->notices;

		$notice->with_type('error')->persistent();

		if (version_compare(phpversion(), WC_EBANX_MIN_PHP_VER, '<')) {
			$message = __('EBANX - The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'woocommerce-gateway-ebanx', 'woocommerce-gateway-ebanx');

			$message = sprintf($message, WC_EBANX_MIN_PHP_VER, phpversion());

			$notice->with_message($message)->enqueue();
		}

		if (!defined('WC_VERSION')) {
			$message = __('EBANX - It requires WooCommerce to be activated to work.', 'woocommerce-gateway-ebanx');

			$notice->with_message($message)->enqueue();
		}

		if (version_compare(WC_VERSION, WC_EBANX_MIN_WC_VER, '<')) {
			$message = __('EBANX - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'woocommerce-gateway-ebanx', 'woocommerce-gateway-ebanx');

			$message = sprintf($message, WC_EBANX_MIN_WC_VER, WC_VERSION);

			$notice->with_message($message)->enqueue();
		}

		if (version_compare(get_bloginfo('version'), WC_EBANX_MIN_WP_VER, '<')) {
			$message = __('EBANX - The minimum WordPress version required for this plugin is %1$s. You are running %2$s.', 'woocommerce-gateway-ebanx', 'woocommerce-gateway-ebanx');

			$message = sprintf($message, WC_EBANX_MIN_WP_VER, get_bloginfo('version'));

			$notice->with_message($message)->enqueue();
		}
	}

	/**
	 * Check if the currency is suported
	 *
	 * @return void
	 */
	public static function check_currency($context)
	{
		if (!in_array(get_woocommerce_currency(), WC_EBANX_Constants::$CURRENCIES_CODES_ALLOWED)) {
			$message = __('EBANX Gateway - Does not support the Currency you have set on the WooCommerce settings. To process with the EBANX plugin choose one of the following: %1$s.', 'woocommerce-gateway-ebanx');
			$message = sprintf($message, implode(', ', WC_EBANX_Constants::$CURRENCIES_CODES_ALLOWED));

			$context->notices
				->with_message($message)
				->with_type('warning')
				->persistent()
				->enqueue();

		}
	}

	/**
	 * Check if the protocol is not HTTPS
	 *
	 * @return void
	 */
	public static function check_https_protocol($context) {
		if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
			$message = __('EBANX - To improve the site security, we recommend the use of HTTPS protocol on site pages.', 'woocommerce-gateway-ebanx');

			$context->notices
				->with_message($message)
				->with_type('info')
				->persistent()
				->enqueue();
		}
	}
}
