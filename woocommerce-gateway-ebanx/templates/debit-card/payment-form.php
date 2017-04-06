<?php

if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="ebanx-debit-cart-form" class="ebanx-payment-container ebanx-language-es">
    <section class="ebanx-form-row">
    	<?php if (!empty($cards)): ?>
    		<?php foreach ($cards as $k => $card): ?>
                <div class="ebanx-debit-card-option">
                    <label class="ebanx-debit-card-label">
        				<input type="radio" <?php if ($k===0): ?>checked="checked"<?php endif; ?> class="input-radio <?php echo trim($card->brand . "-" . $card->masked_number); ?>" value="<?php echo $card->token; ?>" name="ebanx-debit-card-use" />
        				<span class="ebanx-debit-card-brand">
                            <img src="<?php echo PLUGIN_DIR_URL . "assets/images/icons/$card->brand.png" ?>" height="20" style="height: 20px; margin-left: 0; margin-right: 7px; float: none;" alt="<?php echo $card->brand ?>">
                        </span>
                        <span class="ebanx-debit-card-bin">&bull;&bull;&bull;&bull; <?php echo substr($card->masked_number, -4) ?></span>
        			</label>
                    <div class="clear"></div>
        			<div class="ebanx-container-debit-card" style="<?php if ($k!==0): ?>display: none;<?php endif; ?>">
        				<section class="ebanx-form-row">
        					<section class="ebanx-form-row">
        					    <label for="ebanx-card-cvv"><?php _e('Código de verificación', 'woocommerce-gateway-ebanx') ?> <span class="required">*</span></label>

            					<input class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="<?php _e('CVV', 'woocommerce-gateway-ebanx');?>" style="float: none;" />
            					<input type="hidden" autocomplete="off" value="<?php echo $card->brand; ?>" class="ebanx-card-brand-use" />
            					<input type="hidden" autocomplete="off" value="<?php echo $card->masked_number; ?>" class="ebanx-card-masked-number-use" />
        					</section>
        				</section>
        			</div>
                </div>
    		<?php endforeach;?>

            <div class="ebanx-debit-card-option">
                <label class="ebanx-debit-card-label">
        			<input type="radio" class="input-radio" value="new" <?php if (empty($cards)): ?>checked="checked"<?php endif; ?> name="ebanx-debit-card-use"> <?php _e('Otra tarjeta de crédito', 'woocommerce-gateway-ebanx'); ?>
                </label>
    			<div class="ebanx-container-debit-card" id="ebanx-container-new-debit-card" style="<?php if (!empty($cards)): ?>display: none;<?php endif; ?>">
    				<?php include_once 'card-template.php';?>
    			</div>
            </div>
    	<?php else: ?>
            <div id="ebanx-container-new-debit-card">
        		<?php include_once 'card-template.php';?>
            </div>
    	<?php endif;?>
    </section>
</div>

<script>
	// Custom select fields
	if ('jQuery' in window && 'select2' in jQuery.fn) {
		jQuery('select.ebanx-select-field').select2();
	}
</script>
