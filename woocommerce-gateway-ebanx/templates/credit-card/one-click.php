<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $product;

?>

<div class="clear"></div>
<div class="ebanx-one-click-wrapper">

<!--    --><?php //do_action( 'ebanx_before_one_click_button' ); ?>

    <input type="hidden" name="ebanx_one_click" value />
    <input type="hidden" name="ebanx_one_click_token" value />
    <button type="submit" class="ebanx-one-click-button button"><span class="button-label"><?php echo $label ?></span></button>

<!--    --><?php //do_action( 'ebanx_after_one_click_button' ); ?>

</div>

<div class="modal" id="ebanx-one-click-modal" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Choose card</h4>
            </div>
            <div class="modal-body">
                <?php foreach ($cards as $card) : ?>
                    <input type="radio" class="input-radio ebanx-card-use" value="<?php echo $card->token; ?>" /> <?php echo $card->brand . " " . $card->masked_number; ?>
                <?php endforeach; ?>
                <input class="input-text" maxlength="3" name="ebanx_one_click_cvv" type="text" autocomplete="off" placeholder="CVV" required="required"/>
                <?php include 'installments.php';?>
            </div>
            <div class="modal-footer">
<!--                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>-->
                <button type="button" class="btn btn-primary" id="ebanx-one-click-pay">Pay</button>
            </div>
        </div>
    </div>
</div>