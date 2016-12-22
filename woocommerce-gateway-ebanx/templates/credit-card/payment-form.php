<?php
/**
 * Credit Card - Checkout form.
 *
 * @author  EBANX.com
 * @package WooCommerce_EBANX/Templates
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="ebanx-credit-cart-form">
    <section class="form-row">
    	<?php if (!empty($cards)): ?>
    		<?php foreach ($cards as $card): ?>
                <div class="ebanx-credit-card-option">
                    <label class="ebanx-credit-card-label">
        				<input type="radio" class="input-radio <?php echo trim($card->brand . "-" . $card->masked_number); ?>" value="<?php echo $card->token; ?>" name="ebanx-credit-card-use" />
        				<span class="ebanx-credit-card-brand"><img src="<?php echo PLUGIN_DIR_URL . "assets/images/icons/$card->brand.png" ?>" height="20" style="height: 20px; margin-left: 0; margin-right: 7px;" alt="<?php echo $card->brand ?>"></span>
                        <span class="ebanx-credit-card-bin">&bull;&bull;&bull;&bull; <?php echo substr($card->masked_number, -4) ?></span>
        			</label>
        			<div class="ebanx-container-credit-card" style="display: none;">
        				<div class="form-row">
        					<section class="form-row">
        					    <label for="ebanx-card-cvv"><?php _e('Card Code', 'woocommerce-gateway-ebanx');?> <span class="required">*</span></label>

            					<input class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="<?php _e('CVV', 'woocommerce-gateway-ebanx');?>" />
            					<input type="hidden" autocomplete="off" value="<?php echo $card->brand; ?>" class="ebanx-card-brand-use" />
            					<input type="hidden" autocomplete="off" value="<?php echo $card->masked_number; ?>" class="ebanx-card-masked-number-use" />
        					</section>

                            <?php include 'installments.php';?>
        				</div>
        			</div>
                </div>
    		<?php endforeach;?>

            <div class="ebanx-credit-card-option">
                <label class="ebanx-credit-card-label">
        			<input type="radio" class="input-radio" value="new" checked="checked" name="ebanx-credit-card-use"> <?php _e('Another credit card', 'woocommerce-gateway-ebanx');?>
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
    </section>
</div>
