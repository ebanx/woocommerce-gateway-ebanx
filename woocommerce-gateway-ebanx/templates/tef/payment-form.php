<?php
/**
 * TEF - Checkout form.
 *
 * @author  EBANX.com
 * @package WooCommerce_EBANX/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ebanx-tef-payment">
	<p>
		<label class="ebanx-label">
			<input type="radio" name="tef" value="itau" checked> Itaú
		</label>
	</p>
	<p>
		<label class="ebanx-label">
			<input type="radio" name="tef" value="bradesco"> Bradesco
		</label>
	</p>
	<p>
		<label class="ebanx-label">
			<input type="radio" name="tef" value="bancodobrasil"> Banco do Brasil
		</label>
	</p>
	<p>
		<label class="ebanx-label">
			<input type="radio" name="tef" value="banrisul"> Banrisul
		</label>
	</p>
</div>
