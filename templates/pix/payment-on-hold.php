<?php
/**
 * EBANX Pix - Payment instructions.
 *
 * @author  EBANX
 * @package WooCommerce_EBANX/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ebanx-thank-you-page ebanx-thank-you-page--br ebanx-thank-you-page--cc-br">
	<p><strong><?php echo esc_html( $customer_name ); ?>, seu PIX foi gerado :)</strong></p>

	<p>Enviamos uma cópia para o email <strong><?php echo esc_html( $customer_email ); ?></strong></p>

	<p>Você pode efetuar o pagamento apenas escaneando o QRCode com seu celular!</p>

	<div>
		<div class="pipx_qrcode">
			<div id="qrcode"></div>
			<button type="button" class="button ebanx-button--copy" data-clipboard-text="<?php echo esc_attr( $qrcode ); ?>">Copiar</button>
		</div>
	</div>

	<br>

	<?php // phpcs:disable ?>
	<script type="text/javascript" src="<?= plugins_url( 'assets/js/qrcode.js', WC_EBANX::DIR ) ?>"></script>
	<script type="text/javascript">
		new QRCode(document.getElementById("qrcode"), "<?= esc_attr( $qrcode );?>");
	</script>

	<?php require WC_EBANX::get_templates_path() . 'apps-br.php'; ?>
	<input type="hidden" id="ebanx-payment-hash" data-doraemon-hash="<?php echo esc_html( $pix_hash ); ?>">
</div>
