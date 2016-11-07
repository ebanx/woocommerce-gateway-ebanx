<?php
/**
 * TEF - Checkout form.
 *
 * @author  Ebanx.com
 * @package WooCommerce_Ebanx/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ebanx-tef-payment">
  <p>Which bank to pay?</p>

	<div class="form-row">
		<label class="ebanx-label">
			<input type="radio" name="tef" value="itau" checked> Itaú
		</label>
	</div>
	<div class="form-row">
		<label class="ebanx-label">
			<input type="radio" name="tef" value="bradesco"> Bradesco
		</label>
	</div>
	<div class="form-row">
		<label class="ebanx-label">
			<input type="radio" name="tef" value="bancodobrasil"> Banco do Brasil
		</label>
	</div>
	<div class="form-row">
		<label class="ebanx-label">
			<input type="radio" name="tef" value="banrisul"> Banrisul
		</label>
	</div>
</div>
