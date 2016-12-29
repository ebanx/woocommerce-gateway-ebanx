<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $product;
$i = 0;

?>

<?php if ($cards): ?>
    <div class="clear"></div>
    <div class="ebanx-one-click-container">
        <input type="hidden" name="ebanx_one_click" id="ebanx-one-click" value />

        <div class="ebanx-one-click-button-container">
            <button id="ebanx-one-click-button" class="single_add_to_cart_button ebanx-one-click-button button" type="button"><?php _e('One-Click Purchase', 'woocommerce-gateway-ebanx') ?></button>

            <div class="ebanx-one-click-tooltip">
                <button class="ebanx-one-click-close-button"></button>

                <h3><?php echo $t['title'] ?></h3>
                <div class="ebanx-one-click-cards">
                    <?php foreach ($cards as $key => $card): ?>
                        <label class="ebanx-one-click-card">
                            <input type="radio" class="ebanx-one-click-card-radio" name="ebanx_one_click_token" value="<?php echo $card->token ?>" <?php echo $i === 0 ? 'checked="checked"' : '' ?> />
                            <img src="<?php echo PLUGIN_DIR_URL . "assets/images/icons/$card->brand.png" ?>" height="20" />
                            <span>&bull;&bull;&bull;&bull; <?php echo substr($card->masked_number, -4) ?></span>
                        </label>
                    <?php $i++; endforeach; ?>
                </div>

                <div class="ebanx-one-click-cvv">
                    <label><?php echo $t['cvv'] ?> *</label>
                    <input type="text" maxlength="4" minlength="3" class="ebanx-one-click-cvv-input" id="ebanx-one-click-cvv-input" name="ebanx_one_click_cvv" placeholder="CVV">
                </div>

                <div class="ebanx-one-click-installments">
                    <?php include 'installments.php';?>
                </div>

                <button class="ebanx-one-click-pay button" type="button"><?php echo $t['pay'] ?></button>
            </div>
        </div>
    </div>
<?php endif ?>
