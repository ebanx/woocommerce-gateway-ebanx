<?php
/**
 * PagosNet - Checkout form.
 *
 * @author  EBANX.com
 * @package WooCommerce_EBANX/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="ebanx-pagosnet-payment" class="ebanx-payment-container ebanx-language-es">
	<?php require WC_EBANX::get_templates_path() . 'compliance-fields-bo.php'; ?>
</div>
