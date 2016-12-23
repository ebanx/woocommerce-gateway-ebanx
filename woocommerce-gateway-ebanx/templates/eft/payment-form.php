<?php
/**
 * EFT - Checkout form.
 *
 * @author  EBANX.com
 * @package WooCommerce_EBANX/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

asort($banks);
?>

<div id="ebanx-eft-payment" class="ebanx-payment-container ebanx-language-<?php echo $language ?>">
    <select name="eft">
        <?php foreach($banks as $key => $bank): ?>
        	<option value="<?php echo $key ?>"><?php echo $bank ?></option>
        <?php endforeach ?>
    </select>
</div>
