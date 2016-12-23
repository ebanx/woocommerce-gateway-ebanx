<?php
/**
 * Bank Slip - Payment instructions.
 *
 * @author  EBANX
 * @package WooCommerce_Pagarme/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="ebanx-bank-slip-instructions" class="ebanx-payment-container ebanx-language-<?php echo $language ?>">
	<p><?php esc_html_e( 'After clicking "Place order" you will have access to banking banking ticket which you can print and pay in your internet banking or in a lottery retailer.', 'woocommerce-gateway-ebanx' ); ?><br /><?php esc_html_e( 'Note: The order will be confirmed only after the payment approval.', 'woocommerce-gateway-ebanx' ); ?></p>
</div>
