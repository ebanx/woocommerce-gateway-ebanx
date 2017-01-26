<div class="ebanx-credit-card-template ebanx-language-<?php echo $language ?>">
    <section class="ebanx-form-row">
        <label for="ebanx-card-holder-name"><?php _e('Titular de la tarjeta', 'woocommerce-gateway-ebanx'); ?><span class="required">*</span></label>
        <input id="ebanx-card-holder-name" class="wc-credit-card-form-card-name input-text" type="text" autocomplete="off" />
    </section>
    <div class="clear"></div>
    <section class="ebanx-form-row">
        <label for="ebanx-card-number"><?php _e('Número de la tarjeta', 'woocommerce-gateway-ebanx'); ?> <span class="required">*</span></label>
        <input id="ebanx-card-number" class="input-text wc-credit-card-form-card-number" type="tel" maxlength="20" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" />
    </section>
    <div class="clear"></div>
    <section class="ebanx-form-row ebanx-form-row-first">
        <label for="ebanx-card-expiry"><?php _e('Fecha de expiración (MM / AA)', 'woocommerce-gateway-ebanx');  ?> <span class="required">*</span></label>
        <input id="ebanx-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="tel" autocomplete="off" placeholder="<?php _e('MM / AA', 'woocommerce-gateway-ebanx'); ?>" maxlength="7" />
    </section>
    <section class="ebanx-form-row ebanx-form-row-last">
        <label for="ebanx-card-cvv"><?php _e('Código de verificación', 'woocommerce-gateway-ebanx');  ?> <span class="required">*</span></label>
        <input id="ebanx-card-cvv" class="input-text wc-credit-card-form-card-cvc" type="tel" autocomplete="off" placeholder="<?php _e('CVV', 'woocommerce-gateway-ebanx');?>" />
    </section>

    <?php include WC_EBANX::get_templates_path() . '/instalments.php'; ?>

    <?php if ($place_order_enabled) : ?>
        <section class="ebanx-form-row">
            <label for="ebanx-save-credit-card">
                <input id="ebanx-save-credit-card" name="ebanx-save-credit-card" class="wc-credit-card-form-save" type="checkbox" style="width: auto; display: inline-block;" value="yes" checked />
                <?php echo _e('Guarda esta tarjeta para compras futuras.', 'woocommerce-gateway-ebanx');  ?>
            </label>
        </section>
        <div class="clear"></div>
    <?php endif; ?>
</div>
