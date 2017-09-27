<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $product;
$i = 0;

?>

<?php if ($should_show_button) : ?>
	<form class="ebanx-one-click-form" id="ebanx-one-click-form" method="post" action="<?php echo $permalink ?>">
		<input type="hidden" name="ebanx-action" value="<?php echo $action ?>">
		<input type="hidden" name="ebanx-nonce" value="<?php echo $nonce ?>">
		<input type="hidden" name="ebanx-cart-total" value="<?php echo $cart_total ?>">
		<input type="hidden" name="ebanx-product-id" value="<?php echo $product_id ?>">
	
		<div class="clear"></div>
		<div class="ebanx-one-click-container">
			<div class="ebanx-one-click-button-container">
				<button id="ebanx-one-click-button" class="single_add_to_cart_button ebanx-one-click-button button" type="button"><?php _e('One-Click Purchase', 'woocommerce-gateway-ebanx') ?></button>

				<div class="ebanx-one-click-tooltip form-row">
					<button class="ebanx-one-click-close-button"></button>

					<h3><?php _e('Choose Card', 'woocommerce-gateway-ebanx') ?></h3>
					<div class="ebanx-one-click-cards">
						<?php foreach ($cards as $key => $card): ?>
							<label class="ebanx-one-click-card">
								<input type="radio" class="ebanx-one-click-card-radio" name="ebanx-one-click-token" value="<?php echo $card->token ?>" <?php echo $i === 0 ? 'checked="checked"' : '' ?> />
								<img src="<?php echo WC_EBANX_PLUGIN_DIR_URL . "assets/images/icons/$card->brand.png" ?>" height="20" />
								<span>&bull;&bull;&bull;&bull; <?php echo substr($card->masked_number, -4) ?></span>
							</label>
						<?php $i++; endforeach; ?>
					</div>

					<div class="ebanx-one-click-cvv">
						<label><?php _e('Card Code', 'woocommerce-gateway-ebanx') ?></label>
						<input type="text" maxlength="4" minlength="3" autocomplete="off" class="ebanx-one-click-cvv-input input-text" id="ebanx-one-click-cvv-input" name="ebanx-one-click-cvv" placeholder="CVV" required>
					</div>

					<div class="ebanx-one-click-installments">
						<?php include WC_EBANX::get_templates_path() . 'instalments.php'; ?>
					</div>

					<button class="single_add_to_cart_button ebanx-one-click-pay button" data-processing-label="<?php _e('Processing...', 'woocommerce-gateway-ebanx') ?>" type="submit"><?php _e('Pay Now', 'woocommerce-gateway-ebanx') ?></button>
				</div>
			</div>
		</div>
	</form>
<?php endif ?>
