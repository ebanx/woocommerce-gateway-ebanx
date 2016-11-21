<p class="form-row form-row-first">
    <label for="ebanx-card-holder-name"><?php echo $_[$__]['card_name']; ?> <span class="required">*</span></label>
    <input id="ebanx-card-holder-name" class="input-text" type="text" autocomplete="off" style="font-size: 1.5em; padding: 8px;" />
</p>
<p class="form-row form-row-last">
    <label for="ebanx-card-number"><?php echo $_[$__]['card_number']; ?> <span class="required">*</span></label>
    <input id="ebanx-card-number" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" style="font-size: 1.5em; padding: 8px;" />
</p>
<div class="clear"></div>
<p class="form-row form-row-first">
    <label for="ebanx-card-expiry"><?php echo $_[$__]['card_expiry']; ?> <span class="required">*</span></label>
    <input id="ebanx-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="<?php esc_html_e( 'MM / YY', 'woocommerce-ebanx' ); ?>" style="font-size: 1.5em; padding: 8px;" />
</p>
<p class="form-row form-row-last">
    <label for="ebanx-card-cvc"><?php echo $_[$__]['card_code']; ?> <span class="required">*</span></label>
    <input id="ebanx-card-cvc" class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="<?php esc_html_e( 'CVC', 'woocommerce-ebanx' ); ?>" style="font-size: 1.5em; padding: 8px;" />
</p>
<div class="clear"></div>

<!-- Installments -->
<?php if ( $max_installment > 0 ) : ?>
    <p class="form-row form-row-wide">
        <label for="ebanx-card-installments"><?php echo $_[$__]['card_installments']; ?> <span class="required">*</span></label>
        <select name="ebanx_billing_installments" id="ebanx-installments">
            <?php for ($number = 1; $number <= $max_installment; ++$number):
                $installment_amount = strip_tags( wc_price( $cart_total / ($number) ) );
                ?>
                <option value="<?php echo $number ?>"><?php printf( esc_html__( '%1$dx of %2$s', 'woocommerce-ebanx' ), absint( $number ), esc_html( $installment_amount )); ?></option>
            <?php endfor; ?>
        </select>
    </p>
<?php endif; ?>