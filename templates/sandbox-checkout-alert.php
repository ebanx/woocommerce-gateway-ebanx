<?php
if ( ! $is_sandbox_mode) {
	return;
}

$messages = array(
	'pt-br' => 'Ainda estamos testando esse tipo de pagamento. Por isso, a sua compra não será cobrada nem enviada.',
	'es' => 'Todavia estamos probando este método de pago. Por eso su compra no sera cobrada ni enviada.',
);

?>
<div class="sandbox-alert-box">
	<img class="sandbox-alert-icon" src="<?php echo WC_EBANX_PLUGIN_DIR_URL ?>assets/images/icons/warning-icon.svg" />
	<div class="sandbox-alert-message"><?= $messages[$language] ?></div>
</div>
