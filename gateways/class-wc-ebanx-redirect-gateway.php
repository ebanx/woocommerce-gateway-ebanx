<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_EBANX_Redirect_Gateway
 */
abstract class WC_EBANX_Redirect_Gateway extends WC_EBANX_Gateway {

	/**
	 *
	 * @var string
	 */
	protected $redirect_url;

	/**
	 *
	 * @param array    $response
	 * @param WC_Order $order
	 *
	 * @throws Exception Throw parameter missing exception.
	 * @throws WC_EBANX_Payment_Exception Throws error message.
	 */
	protected function process_response( $response, $order ) {
		if ( 'ERROR' === $response['status'] ) {
			$this->process_response_error( $response, $order );
		}
		if ( ! $response['redirect_url'] && ! isset( $response['payment']['redirect_url'] ) ) {
			$this->process_response_error( $response, $order );
		}

		parent::process_response( $response, $order );

		update_post_meta( $order->id, '_ebanx_payment_hash', $response['payment']['hash'] );

		$this->redirect_url = $response['payment']['redirect_url'];
	}

	/**
	 * Dispatch an array to request, always dispatch success
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	final protected function dispatch( $data ) {
		return parent::dispatch(
			array(
				'result'   => 'success',
				'redirect' => $this->redirect_url,
			)
		);
	}
}
