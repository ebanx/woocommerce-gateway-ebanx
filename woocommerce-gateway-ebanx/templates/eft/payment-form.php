<?php
/**
 * EFT - Checkout form.
 *
 * @author  EBANX.com
 * @package WooCommerce_EBANX/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

asort($banks);
?>

<div id="ebanx-eft-payment" class="ebanx-payment-container ebanx-language-es">
	<?php include WC_EBANX::get_templates_path() . 'compliance-fields-co.php' ?>

    <select name="eft" class="ebanx-select-field">
        <?php foreach($banks as $key => $bank): ?>
        	<option value="<?php echo $key ?>"><?php echo $bank ?></option>
        <?php endforeach ?>
    </select>

	<script>
		// Custom select fields
		if ('jQuery' in window && 'select2' in jQuery.fn) {
			jQuery('select.ebanx-select-field').select2();
		}
	</script>
</div>
