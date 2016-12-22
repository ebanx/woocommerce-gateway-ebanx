<div class="ebanx-credit-card-template">
    <?php if ($country != 'br') :?>
        <section class="form-row">
            <label for="ebanx-card-holder-name"><?php _e('Card Holder Name', 'woocommerce-gateway-ebanx');?><span class="required">*</span></label>
            <input id="ebanx-card-holder-name" class="wc-credit-card-form-card-number input-text" type="text" autocomplete="off" />
        </section>
    <?php endif; ?>
    <section class="form-row">
        <label for="ebanx-card-number"><?php _e('Card Number', 'woocommerce-gateway-ebanx');?> <span class="required">*</span></label>
        <input id="ebanx-card-number" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" />
    </section>
    <div class="clear"></div>
    <section class="form-row form-row-first">
        <label for="ebanx-card-expiry"><?php _e('Expiry (MM/YY)', 'woocommerce-gateway-ebanx');?> <span class="required">*</span></label>
        <input id="ebanx-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="<?php _e('MM / YY', 'woocommerce-gateway-ebanx');?>" />
    </section>
    <section class="form-row form-row-last">
        <label for="ebanx-card-cvv"><?php _e('Card Code', 'woocommerce-gateway-ebanx');?> <span class="required">*</span></label>
        <input id="ebanx-card-cvv" class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="<?php _e('CVV', 'woocommerce-gateway-ebanx');?>" />
    </section>
    <div class="clear"></div>

    <?php include 'installments.php';?>

    <?php if ($place_order_enabled) : ?>
        <section class="form-row">
            <label for="ebanx-save-credit-card">
                <input id="ebanx-save-credit-card" name="ebanx-save-credit-card" class="input-text wc-credit-card-form-save" type="checkbox" style="width: auto; display: inline-block;" value="yes" checked />
                <?php _e('Save this card for a future order', 'woocommerce-gateway-ebanx');?>
            </label>
        </section>
    <?php endif; ?>
    <div class="clear"></div>
</div>
