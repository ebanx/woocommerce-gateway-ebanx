<?php
/**
 * EFT - Checkout form.
 *
 * @author  Ebanx.com
 * @package WooCommerce_Ebanx/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ebanx-eft-payment">
  <p>Which bank to pay?</p>

	<?php foreach(WC_Ebanx_Gateway_Utils::$BANKS_EFT_ALLOWED[WC_Ebanx_Gateway_Utils::COUNTRY_COLOMBIA] as $bank): ?>
		<div class="form-row">
			<label class="ebanx-label">
				<input type="radio" name="eft" value="<?=$bank;?>" checked> <?=$bank;?>
			</label>
		</div>
	<? endforeach; ?>
</div>
