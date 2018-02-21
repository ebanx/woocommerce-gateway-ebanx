<?php
if ( ! $is_sandbox_mode) {
	return;
}
?>
<div class="sandbox-alert-box">
	<img class="sandbox-alert-icon" src="<?php echo WC_EBANX_PLUGIN_DIR_URL ?>assets/images/icons/warning-icon.svg" />
	<div class="sandbox-alert-message"><?= 'Ainda estamos testando esse tipo de pagamento. Por isso, a sua compra não será cobrada nem enviada.' ?></div>
</div>
