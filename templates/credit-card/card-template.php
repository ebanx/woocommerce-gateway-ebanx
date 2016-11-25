<p class="form-row form-row-first">
    <label for="ebanx-card-holder-name"><?php esc_html_e( 'Card Holder Name', 'woocommerce-ebanx' ); ?><span class="required">*</span></label>
    <input id="ebanx-card-holder-name" class="input-text" type="text" autocomplete="off" style="font-size: 1.5em; padding: 8px;" />
</p>
<p class="form-row form-row-last">
    <label for="ebanx-card-number"><?php esc_html_e( 'Card Number', 'woocommerce-ebanx' ); ?> <span class="required">*</span></label>
    <input id="ebanx-card-number" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" style="font-size: 1.5em; padding: 8px;" />
</p>
<div class="clear"></div>
<p class="form-row form-row-first">
    <label for="ebanx-card-expiry"><?php esc_html_e( 'Expiry (MM/YY)', 'woocommerce-pagarme' ); ?> <span class="required">*</span></label>
    <input id="ebanx-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="<?php esc_html_e( 'MM / YY', 'woocommerce-ebanx' ); ?>" style="font-size: 1.5em; padding: 8px;" />
</p>
<p class="form-row form-row-last">
    <label for="ebanx-card-cvc"><?php esc_html_e( 'Card Code', 'woocommerce-ebanx' ); ?> <span class="required">*</span></label>
    <input id="ebanx-card-cvc" class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="<?php esc_html_e( 'CVC', 'woocommerce-ebanx' ); ?>" style="font-size: 1.5em; padding: 8px;" />
</p>
<div class="clear"></div>

<?php include_once('installments.php'); ?>