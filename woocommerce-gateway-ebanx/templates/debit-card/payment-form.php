<?php

if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="ebanx-debit-cart-form" class="ebanx-payment-container ebanx-language-<?php echo $language ?>">
	<div id="ebanx-container-new-debit-card">
		<section class="ebanx-form-row">
			<label for="ebanx-debit-card-holder-name"><?php _e('Titular de la tarjeta', 'woocommerce-gateway-ebanx') ?> <span class="required">*</span></label>
			<input id="ebanx-debit-card-holder-name" class="wc-credit-card-form-card-name input-text" type="text" autocomplete="off" />
		</section>
		<section class="ebanx-form-row">
			<label for="ebanx-debit-card-number"><?php _e('Número de la tarjeta', 'woocommerce-gateway-ebanx') ?> <span class="required">*</span></label>
			<input id="ebanx-debit-card-number" class="input-text wc-credit-card-form-card-number" type="tel" maxlength="20" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" />
		</section>
		<div class="clear"></div>
		<section class="ebanx-form-row ebanx-form-row-first">
			<label for="ebanx-debit-card-expiry"><?php _e('Fecha de expiración (MM / AA)', 'woocommerce-gateway-ebanx') ?> <span class="required">*</span></label>
			<input id="ebanx-debit-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="tel" autocomplete="off" placeholder="<?php _e('MM / AA', 'woocommerce-gateway-ebanx');?>" maxlength="7" />
		</section>
		<section class="ebanx-form-row ebanx-form-row-last">
			<label for="ebanx-debit-card-cvv"><?php _e('Código de verificación', 'woocommerce-gateway-ebanx') ?> <span class="required">*</span></label>
			<input id="ebanx-debit-card-cvv" class="input-text wc-credit-card-form-card-cvc" type="tel" autocomplete="off" placeholder="<?php _e('CVV', 'woocommerce-gateway-ebanx');?>" />
		</section>

		<div class="clear"></div>
	</div>
</div>
