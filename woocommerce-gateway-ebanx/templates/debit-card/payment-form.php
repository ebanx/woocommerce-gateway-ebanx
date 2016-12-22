<?php

if (!defined('ABSPATH')) {
    exit;
}
?>

<fieldset id="ebanx-debit-cart-form">
	<div id="ebanx-container-new-debit-card">
		<section class="form-row">
			<label for="ebanx-debit-card-holder-name"><?php _e('Card Holder Name', 'woocommerce-gateway-ebanx');?><span class="required">*</span></label>
			<input id="ebanx-debit-card-holder-name" class="input-text" type="text" autocomplete="off" style="font-size: 1.5em; padding: 8px;" />
		</section>
		<section class="form-row">
			<label for="ebanx-debit-card-number"><?php _e('Card Number', 'woocommerce-gateway-ebanx');?> <span class="required">*</span></label>
			<input id="ebanx-debit-card-number" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" style="font-size: 1.5em; padding: 8px;" />
		</section>
		<div class="clear"></div>
		<section class="form-row form-row-first">
			<label for="ebanx-debit-card-expiry"><?php _e('Expiry (MM/YY)', 'woocommerce-gateway-ebanx');?> <span class="required">*</span></label>
			<input id="ebanx-debit-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="<?php _e('MM / YY', 'woocommerce-gateway-ebanx');?>" style="font-size: 1.5em; padding: 8px;" />
		</section>
		<section class="form-row form-row-last">
			<label for="ebanx-debit-card-cvv"><?php _e('Card Code', 'woocommerce-gateway-ebanx');?> <span class="required">*</span></label>
			<input id="ebanx-debit-card-cvv" class="input-text wc-credit-card-form-card-cvv" type="text" autocomplete="off" placeholder="<?php _e('CVV', 'woocommerce-gateway-ebanx');?>" style="font-size: 1.5em; padding: 8px;" />
		</section>

		<div class="clear"></div>
	</div>
</fieldset>
