<!-- Installments -->
<?php if ($max_installment > 1) : ?>
    <section class="ebanx-form-row">
        <label for="ebanx-card-installments"><?php echo $instalments; ?> <span class="required">*</span></label>
        <select class="ebanx-instalments ebanx-select-field" name="ebanx-credit-card-installments">
            <?php for ($number = 1; $number <= $max_installment; ++$number):
            	$has_tax = false;
            	$price_total = $cart_total;
            	if (isset($installment_taxes) && array_key_exists($number, $installment_taxes)) {
            		$price_total += $price_total * $installment_taxes[$number];
            		if ($installment_taxes[$number] > 0) {
            			$has_tax = true;
            		}
            	}
            	$installment_price = $price_total / $number;
                $installment_amount = strip_tags( wc_price( $installment_price ) );
                ?>
                <option value="<?php echo $number ?>">
                	<?php printf( __( '%1$dx of %2$s', 'woocommerce-gateway-ebanx' ), absint( $number ), esc_html( $installment_amount )); ?>
                	<?= $has_tax ? __( 'with interest', 'woocommerce-gateway-ebanx' ) : '' ?>
                </option>
            <?php endfor; ?>
        </select>
    </section>
    <div class="clear"></div>
<?php endif; ?>
