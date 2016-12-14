<?php
/**
 * Credit Card - Checkout form.
 *
 * @author  Ebanx.com
 * @package WooCommerce_Ebanx/Templates
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<fieldset id="ebanx-credit-cart-form">
	<?php if (!empty($cards)): ?>
		<?php foreach ($cards as $card): ?>
			<div class="form-row">
                <label class="ebanx-credit-card-label">
					<input type="radio" class="input-radio <?php echo trim($card->brand . "-" . $card->masked_number); ?>" value="<?php echo $card->token; ?>" name="ebanx-credit-card-use" />
					<?php echo trim($card->brand . " " . $card->masked_number); ?>
				</label>
				<div class="ebanx-container-credit-card" style="display: none;">
					<div class="form-row">
						<label for="ebanx-card-cvv"><?php esc_html_e('Card Code', 'woocommerce-gateway-ebanx');?> <span class="required">*</span></label>

						<input class="input-text wc-credit-card-form-card-cvv" type="text" autocomplete="off" placeholder="<?php esc_html_e('CVC', 'woocommerce-gateway-ebanx');?>" style="font-size: 1.5em; padding: 8px;" />
						<input type="hidden" autocomplete="off" value="<?php echo $card->brand; ?>" class="ebanx-card-brand-use" />
						<input type="hidden" autocomplete="off" value="<?php echo $card->masked_number; ?>" class="ebanx-card-masked-number-use" />

                        <?php include 'installments.php';?>
					</div>
				</div>
			</div>
		<?php endforeach;?>
		<div class="form-row">
            <label class="ebanx-credit-card-label">
    			<input type="radio" class="input-radio" value="new" checked="checked" name="ebanx-credit-card-use"> <?php esc_html_e('Use new', 'woocommerce-gateway-ebanx');?>
            </label>
			<div class="ebanx-container-credit-card" id="ebanx-container-new-credit-card">
				<?php include_once 'card-template.php';?>
			</div>
		</div>
	<?php else: ?>
        <div id="ebanx-container-new-credit-card">
    		<?php include_once 'card-template.php';?>
        </div>
	<?php endif;?>
</fieldset>
