<!-- Installments -->
<?php if ( $max_installment > 0 ) : ?>
    <p class="form-row form-row-wide">
        <label for="ebanx-card-installments"><?php esc_html_e( 'Installments', 'woocommerce-ebanx' ); ?> <span class="required">*</span></label>
        <select name="ebanx_billing_installments" id="ebanx-installments">
            <?php for ($number = 1; $number <= $max_installment; ++$number):
                $installment_amount = strip_tags( wc_price( $cart_total / ($number) ) );
                ?>
                <option value="<?php echo $number ?>"><?php printf( esc_html__( '%1$dx of %2$s', 'woocommerce-ebanx' ), absint( $number ), esc_html( $installment_amount )); ?></option>
            <?php endfor; ?>
        </select>
    </p>
<?php endif; ?>