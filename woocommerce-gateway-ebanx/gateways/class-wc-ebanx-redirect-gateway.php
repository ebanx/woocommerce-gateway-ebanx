<?php

if (!defined('ABSPATH')) {
	exit;
}

abstract class WC_EBANX_Redirect_Gateway extends WC_EBANX_Gateway
{
	protected $redirect_url;

	/**
	 * Process the response of request from EBANX API
	 *
	 * @param  Object $request The result of request
	 * @param  WC_Order $order   The order created
	 * @return void
	 */
	protected function process_response($request, $order)
	{
		if ($request->status == 'ERROR') {
			return $this->process_response_error($request, $order);
		}
		$redirect = $request->redirect_url;
		if (!$redirect && !isset($request->payment->redirect_url)) {
			return $this->process_response_error($request, $order);
		}
		$redirect = $request->payment->redirect_url;

		parent::process_response($request, $order);

		update_post_meta($order->id, '_ebanx_payment_hash', $request->payment->hash);

		$this->redirect_url = $redirect;
	}

	/**
	 * Dispatch an array to request, always dispatch success
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
