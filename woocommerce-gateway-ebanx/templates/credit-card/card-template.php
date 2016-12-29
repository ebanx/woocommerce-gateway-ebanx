<div class="ebanx-credit-card-template">
    <?php if ($country != 'br') :?>
        <section class="ebanx-form-row">
            <label for="ebanx-card-holder-name"><?php echo $t['name'] ?><span class="required">*</span></label>
            <input id="ebanx-card-holder-name" class="input-text" type="text" autocomplete="off" />
        </section>
        <div class="clear"></div>
    <?php endif; ?>
    <section class="ebanx-form-row">
        <label for="ebanx-card-number"><?php echo $t['number'] ?> <span class="required">*</span></label>
        <input id="ebanx-card-number" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" />
    </section>
    <div class="clear"></div>
    <section class="ebanx-form-row ebanx-form-row-first">
        <label for="ebanx-card-expiry"><?php echo $t['expiry']; ?> <span class="required">*</span></label>
        <input id="ebanx-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="<?php _e('MM / YY', 'woocommerce-gateway-ebanx');?>" />
    </section>
    <section class="ebanx-form-row ebanx-form-row-last">
        <label for="ebanx-card-cvv"><?php echo $t['cvv']; ?> <span class="required">*</span></label>
        <input id="ebanx-card-cvv" class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="<?php _e('CVV', 'woocommerce-gateway-ebanx');?>" />
    </section>

    <?php include 'installments.php';?>

    <?php if ($place_order_enabled) : ?>
        <section class="ebanx-form-row">
            <label for="ebanx-save-credit-card">
                <input id="ebanx-save-credit-card" name="ebanx-save-credit-card" class="input-text wc-credit-card-form-save" type="checkbox" style="width: auto; display: inline-block;" value="yes" checked />
                <?php echo $t['save_card']; ?>
            </label>
        </section>
        <div class="clear"></div>
    <?php endif; ?>
</div>
