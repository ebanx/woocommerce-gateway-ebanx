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
<div class="ebanx-payment-converted-amount" style="overflow: hidden;">
    <p style="float: left;"><?php echo $message; ?></p>
    <img
        class="ebanx-spinner" 
        src="/wp-admin/images/spinner-2x.gif" 
        width="20"
        height="20"
        style="display: none; margin-left: 10px; float: left;"
    />
</div>