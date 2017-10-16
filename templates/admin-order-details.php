<div class="form-field form-field-wide">
	<h3><?php _e('EBANX Order Details', 'woocommerce-gateway-ebanx') ?></h3>
	<p>
		<?php _e('Dashboard Payment Link', 'woocommerce-gateway-ebanx') ?>
		<br>
		<a href="<?php echo $dashboard_link ?>" class="ebanx-text-overflow" target="_blank"><?php echo $dashboard_link ?></a>
	</p>
	<p>
		<?php _e('Payment Hash', 'woocommerce-gateway-ebanx') ?>
		<br>
		<input type="text" value="<?php echo $payment_hash ?>" onfocus="this.select();" onmouseup="return false;" readonly>
	</p>
	<?php if ($order->status === 'pending' && $payment_checkout_url): ?>
		<p>
			<strong><?php _e('Customer Payment Link', 'woocommerce-gateway-ebanx') ?></strong>
			<br>
			<input type="text" value="<?php echo $payment_checkout_url ?>" onfocus="this.select();" onmouseup="return false;" readonly>
		</p>
	<?php endif ?>
</div>


<style>
	.ebanx-text-overflow {
		text-overflow: ellipsis;
		white-space: nowrap;
		width: 100%;
		overflow: hidden;
		display: block;
	}
</style>
