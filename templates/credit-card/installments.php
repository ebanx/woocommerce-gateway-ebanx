<!-- Installments -->
<?php if ($max_installment > 0) : ?>
    <p class="form-row form-row-first">
        <label for="ebanx-card-installments"><?php esc_html_e( 'Installments', 'woocommerce-gateway-ebanx' ); ?> <span class="required">*</span></label>
        <select class="ebanx-instalments">
            <?php for ($number = 1; $number <= $max_installment; ++$number):
                $installment_amount = strip_tags( wc_price( $cart_total / ($number) ) );
                ?>
                <option value="<?php echo $number ?>"><?php printf( esc_html__( '%1$dx of %2$s', 'woocommerce-gateway-ebanx' ), absint( $number ), esc_html( $installment_amount )); ?></option>
            <?php endfor; ?>
        </select>
    </p>
<?php endif; ?>
