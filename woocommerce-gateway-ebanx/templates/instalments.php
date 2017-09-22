<!-- Installments -->
<?php if ( count($instalments_terms) > 1 ) : ?>
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
					<?php printf( __( '%1$dx of %2$s', 'woocommerce-gateway-ebanx' ), absint( $instalment['number'] ), esc_html( strip_tags( wc_price( $instalment['price'] ) ) ) ); ?>
					<?= $instalment['has_interest'] ? __( 'with interest', 'woocommerce-gateway-ebanx' ) : '' ?>
				</option>
			<?php endforeach; ?>
		</select>
	</section>
	<div class="clear"></div>
<?php endif; ?>
