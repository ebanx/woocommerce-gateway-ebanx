<?php
/**
 * Credit Card - Checkout form.
 *
 * @author  Ebanx.com
 * @package WooCommerce_Ebanx/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- finger print, see: https://github.com/ebanx/device-fingerprint -->
<input type="hidden" name="ebanx_device_fingerprint" id="ebanx_device_fingerprint">

<fieldset id="ebanx-credit-cart-form">
	<p class="form-row form-row-first">
		<label for="ebanx-card-holder-name"><?php esc_html_e( 'Card Holder Name', 'woocommerce-ebanx' ); ?><span class="required">*</span></label>
		<input id="ebanx-card-holder-name" class="input-text" type="text" autocomplete="off" style="font-size: 1.5em; padding: 8px;" />
	</p>
	<p class="form-row form-row-last">
		<label for="ebanx-card-number"><?php esc_html_e( 'Card Number', 'woocommerce-ebanx' ); ?> <span class="required">*</span></label>
		<input id="ebanx-card-number" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" style="font-size: 1.5em; padding: 8px;" />
	</p>
	<div class="clear"></div>
	<p class="form-row form-row-first">
		<label for="ebanx-card-expiry"><?php esc_html_e( 'Expiry (MM/YY)', 'woocommerce-pagarme' ); ?> <span class="required">*</span></label>
		<input id="ebanx-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="<?php esc_html_e( 'MM / YY', 'woocommerce-ebanx' ); ?>" style="font-size: 1.5em; padding: 8px;" />
	</p>
	<p class="form-row form-row-last">
		<label for="ebanx-card-cvc"><?php esc_html_e( 'Card Code', 'woocommerce-ebanx' ); ?> <span class="required">*</span></label>
		<input id="ebanx-card-cvc" class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="<?php esc_html_e( 'CVC', 'woocommerce-ebanx' ); ?>" style="font-size: 1.5em; padding: 8px;" />
	</p>
	<div class="clear"></div>
	<?php if ( apply_filters( 'wc_ebanx_allow_credit_card_installments', 1 < $max_installment ) ) : ?>
		<p class="form-row form-row-wide">
			<label for="ebanx-card-installments"><?php esc_html_e( 'Installments', 'woocommerce-ebanx' ); ?> <span class="required">*</span></label>
			<select name="ebanx_installments" id="ebanx-installments" style="font-size: 1.5em; padding: 8px; width: 100%;">
				<?php
				foreach ( $installments as $number => $installment ) :
					if ( 1 !== $number && $smallest_installment > $installment['installment_amount'] ) {
						break;
					}

					$interest           = ( ( $cart_total * 100 ) < $installment['amount'] ) ? sprintf( __( '(total of %s)', 'woocommerce-ebanx' ), strip_tags( wc_price( $installment['amount'] / 100 ) ) ) : __( '(interest-free)', 'woocommerce-ebanx' );
					$installment_amount = strip_tags( wc_price( $installment['installment_amount'] / 100 ) );
				?>
				<option value="<?php echo absint( $installment['installment'] ); ?>"><?php printf( esc_html__( '%1$dx of %2$s %3$s', 'woocommerce-ebanx' ), absint( $installment['installment'] ), esc_html( $installment_amount ), esc_html( $interest ) ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
	<?php endif; ?>
</fieldset>
