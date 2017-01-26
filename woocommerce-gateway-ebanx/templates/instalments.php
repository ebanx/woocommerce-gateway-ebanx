<!-- Installments -->
<?php if ($max_installment > 1) : ?>
    <section class="ebanx-form-row">
        <label for="ebanx-card-installments"><?php echo $instalments; ?> <span class="required">*</span></label>
        <select class="ebanx-instalments" name="ebanx-credit-card-installments">
            <?php for ($number = 1; $number <= $max_installment; ++$number):
                $installment_amount = strip_tags( wc_price( $cart_total / ($number) ) );
                ?>
                <option value="<?php echo $number ?>"><?php printf( __( '%1$dx of %2$s', 'woocommerce-gateway-ebanx' ), absint( $number ), esc_html( $installment_amount )); ?></option>
            <?php endfor; ?>
        </select>
    </section>
    <div class="clear"></div>
<?php endif; ?>
