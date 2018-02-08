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
	<?php if ( $instalments_number > 1 ) : ?>
		<p><strong><?php echo $customer_name; ?> seu pagamento de <?php echo $total; ?>, parcelado em <?php echo $instalments_number; ?>x de <?php echo $instalments_amount; ?>, foi aprovado o/</strong></p>
	<?php else : ?>
		<p><strong><?php echo $customer_name; ?> seu pagamento de <?php echo $total; ?>, à vista, foi aprovado o/</strong></p>
	<?php endif ?>

	<p>Se tiver alguma dúvida em relação ao seu pagamento, acesse a Conta EBANX com o email <strong><?php echo $customer_email; ?></strong>.</p>

	<?php require WC_EBANX::get_templates_path() . 'apps_br.php'; ?>
</div>
