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

<fieldset id="ebanx-credit-cart-form">
	<?php if (!empty($cards)): ?>
		<?php foreach($cards as $card) : ?>
			<div class="form-row">
				<input type="radio" class="input-radio" value="<?php echo $card->token; ?>" name="ebanx-credit-card-use" /> <?php echo $card->brand ." ". $card->masked_number; ?>
				<div class="ebanx-container-credit-card" style="display: none;">
					<div class="form-row">
						<label for="ebanx-card-cvc"><?php esc_html_e( 'Card Code', 'woocommerce-ebanx' ); ?> <span class="required">*</span></label>
						<input id="ebanx-card-cvc-use" class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="<?php esc_html_e( 'CVC', 'woocommerce-ebanx' ); ?>" style="font-size: 1.5em; padding: 8px;" />
						<input type="hidden" autocomplete="off" value="<?php echo $card->brand; ?>" id="ebanx-card-brand-use" />
						<input type="hidden" autocomplete="off" value="<?php echo $card->masked_number; ?>" id="ebanx-card-masked-number-use" />
						<?php include_once('installments.php'); ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
		<div class="form-row">
			<input type="radio" class="input-radio" value="new" checked="checked" name="ebanx-credit-card-use"> <?php esc_html_e( 'Use new', 'woocommerce-ebanx' ); ?>
			<div class="ebanx-container-credit-card">
				<?php include_once('card-template.php'); ?>
			</div>
		</div>
	<?php else: ?>
		<?php include_once('card-template.php'); ?>
	<?php endif; ?>
</fieldset>
