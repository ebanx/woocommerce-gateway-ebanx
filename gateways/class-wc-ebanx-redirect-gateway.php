<?php

if (!defined('ABSPATH')) {
	exit;
}

abstract class WC_EBANX_Redirect_Gateway extends WC_EBANX_New_Gateway
{
	protected $redirect_url;

	/**
	 * @param array $response
	 * @param WC_Order $order
	 *
	 * @throws Exception
	 * @throws WC_EBANX_Payment_Exception
	 */
	protected function process_response( $response, $order)
	{
		if ( $response['status'] == 'ERROR') {
			$this->process_response_error( $response, $order);
		}
		$redirect = $response['redirect_url'];
		if (!$redirect && !isset( $response['payment']['redirect_url'])) {
			$this->process_response_error( $response, $order);
		}
		$redirect = $response['payment']['redirect_url'];

		parent::process_response( $response, $order);

		update_post_meta($order->id, '_ebanx_payment_hash', $response['payment']['hash']);

		$this->redirect_url = $redirect;
	}

	/**
	 * Dispatch an array to request, always dispatch success
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	final protected function dispatch($data)
	{
		return parent::dispatch(array(
			'result'   => 'success',
			'redirect' => $this->redirect_url,
		));
	}
}
