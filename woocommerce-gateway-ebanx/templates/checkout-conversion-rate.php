<?php
/**
 * Checkout Conversion Rate.
 *
 * @author  EBANX.com
 * @package WooCommerce_EBANX/Templates
 */

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="ebanx-payment-converted-amount">
    <p><?php printf( __( 'Converted amount for %1$s: <strong>%2$s</strong>', 'woocommerce-gateway-ebanx' ), get_woocommerce_currencies()[$currency], wc_price($amountConverted, array('currency' => $currency))); ?></p>
</div>