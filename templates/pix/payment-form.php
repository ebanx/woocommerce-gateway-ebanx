<?php
/**
 * pix - Checkout form.
 *
 * @author  EBANX.com
 * @package WooCommerce_EBANX/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="ebanx-pix-payment" class="ebanx-payment-container ebanx-language-br">
	<?php require WC_EBANX::get_templates_path() . 'compliance-fields-br.php'; ?>
</div>
