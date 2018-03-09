<?php
/**
 * Credit Card - Payment processed.
 *
 * @author  EBANX.com
 * @package WooCommerce_EBANX/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="ebanx-thank-you-page ebanx-thank-you-page--br ebanx-thank-you-page--cc-br">
	<?php if ($instalments_number > 1) : ?>
		<p><strong><?php echo esc_html( $customer_name ); ?> seu pagamento de <?php echo esc_html( $total ); ?>, parcelado em <span id="ebanx-instalment-number"><?php echo esc_html( $instalments_number ); ?></span>x de <?php echo esc_html( $instalments_amount ); ?>, foi aprovado o/</strong></p>
	<?php else: ?>
		<p><strong><?= $customer_name ?> seu pagamento de <?= $total ?>, à vista, foi aprovado o/</strong></p>
	<?php endif ?>

	<p>Se tiver alguma dúvida em relação ao seu pagamento, acesse a Conta EBANX com o email <strong><?= $customer_email ?></strong>.</p>

	<?php include WC_EBANX::get_templates_path() . 'apps_br.php' ?>
	<input type="hidden" id="ebanx-payment-hash" data-doraemon-hash="<?php echo esc_html( $hash ); ?>">
</div>
