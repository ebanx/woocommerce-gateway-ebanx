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
    <button type="submit" class="ebanx-one-click-button button"><span class="button-label"><?php echo $label ?></span></button>

<!--    --><?php //do_action( 'ebanx_after_one_click_button' ); ?>

</div>