<?php
/**
 * Efectivo - Checkout form.
 *
 * @author  EBANX.com
 * @package WooCommerce_EBANX/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="ebanx-efectivo-payment" class="ebanx-payment-container ebanx-language-es">
	<?php include WC_EBANX::get_templates_path() . 'compliance-fields-pe.php' ?>

	<div class="ebanx-form-row">
		<label class="ebanx-label">
			<input type="radio" name="efectivo" value="rapipago" checked> Rapipago <img src="<?php echo WC_EBANX_PLUGIN_DIR_URL ?>assets/images/ebanx-rapipago.png" height="20" />
		</label>
	</div>
	<div class="ebanx-form-row">
		<label class="ebanx-label">
			<input type="radio" name="efectivo" value="pagofacil"> Pagofacil <img src="<?php echo WC_EBANX_PLUGIN_DIR_URL ?>assets/images/ebanx-pagofacil.png" height="20" />
		</label>
	</div>
	<div class="ebanx-form-row">
		<label class="ebanx-label">
			<input type="radio" name="efectivo" value="cupon"> Otros Cupones <img src="<?php echo WC_EBANX_PLUGIN_DIR_URL ?>assets/images/ebanx-cupon.png" height="20" />
		</label>
	</div>
</div>
