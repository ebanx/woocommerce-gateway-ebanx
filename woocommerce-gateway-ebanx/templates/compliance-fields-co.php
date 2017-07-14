<?php
	$order_id = get_query_var('order-pay');

	if ($order_id) {
		$order = wc_get_order($order_id);
		$document = $order ? get_user_meta($order->get_user_id(), '_ebanx_document', true) : false;
		$address = $order->get_address();

		$fields = array(
			'ebanx_billing_colombia_document' => array(
				'label' => 'DNI',
				'value' => $document
			),
			'billing_postcode' => array(
				'label' => __('Zipcode', 'woocommerce'),
				'value' => $address['postcode']
			),
			'billing_address_1' => array(
				'label' => 'Address',
				'value' => $address['address_1']
			),
			'billing_city' => array(
				'label' => 'City',
				'value' => $address['city']
			),
			'billing_state' => array(
				'label' => 'State',
				'value' => $address['state']
			),
			'billing_country' => array(
				'label' => 'Country',
				'value' => $address['country'],
				'type' => 'hidden'
			)
		);
	}
?>

<?php if ($order_id): ?>
	<div class="ebanx-compliance-fields ebanx-compliance-fiels-co">
		<?php foreach ($fields as $name => $field): ?>
			<?php if (isset($field['type']) && $field['type'] === 'hidden'): ?>
				<input
					type="hidden"
					name="<?php echo "{$id}[{$name}]" ?>"
					value="<?php echo isset($field['value']) ? $field['value'] : null  ?>"
					class="input-text"
				/>
			<?php else: ?>
				<label>
					<?php echo $field['label'] ?>
					<input
						type="<?php echo isset($field['type']) ? $field['type'] : 'text'  ?>"
						name="<?php echo "{$id}[{$name}]" ?>"
						value="<?php echo isset($field['value']) ? $field['value'] : null  ?>"
						class="input-text"
					/>
				</label>
			<?php endif ?>
		<?php endforeach ?>
	</div>
<?php endif ?>