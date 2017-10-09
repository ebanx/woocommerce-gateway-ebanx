<!-- Installments -->
<?php
$currency = $currency ?: get_woocommerce_currency();
$currency_rate = $currency_rate ?: 1;

if ($currency === WC_EBANX_Constants::CURRENCY_CODE_BRL) {
	$currency_rate *= 1 + WC_EBANX_Constants::BRAZIL_TAX;
}

if ( count($instalments_terms) > 1 ) : ?>
	<section class="ebanx-form-row">
		<label for="ebanx-card-installments"><?php echo $instalments; ?> <span class="required">*</span></label>
		<select
			data-country="<?php echo $country ?>"
			data-amount="<?php echo $cart_total ?>"
			data-currency="<?php echo $currency ?>"
			data-order-id="<?php echo get_query_var('order-pay') ?>"
			class="ebanx-instalments ebanx-select-field"
			name="ebanx-credit-card-installments"
		>
			<?php foreach ($instalments_terms as $instalment): ?>
				<option value="<?php echo $instalment['number'] ?>">
					<?php printf( __( '%1$dx of %2$s', 'woocommerce-gateway-ebanx' ), absint( $instalment['number'] ), esc_html( strip_tags( wc_price( $instalment['price'] * $currency_rate, array('currency' => $currency) ) ) ) ); ?>
					<?= $instalment['has_interest'] ? __( 'with interest', 'woocommerce-gateway-ebanx' ) : '' ?>
				</option>
			<?php endforeach; ?>
		</select>
	</section>
	<div class="clear"></div>
<?php endif; ?>
