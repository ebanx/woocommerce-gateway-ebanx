<?php
/**
 * Safetypay - Checkout form.
 *
 * @author  EBANX.com
 * @package WooCommerce_EBANX/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ebanx-safetypay-payment">
  <p>Which bank to pay?</p>

	<div class="form-row">
		<label class="ebanx-label">
			<input type="radio" name="safetypay" value="cash" checked> Cash
		</label>
	</div>
	<div class="form-row">
		<label class="ebanx-label">
			<input type="radio" name="safetypay" value="online"> Online
		</label>
	</div>
</div>
