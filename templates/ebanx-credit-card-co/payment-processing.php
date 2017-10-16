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

<div class="ebanx-thank-you-page ebanx-thank-you-page--co ebanx-thank-you-page--cc-co">
	<?php if ($instalments_number > 1) : ?>
		<p><strong><?= $customer_name ?> tu pago de <?= $total ?>, dividido en <?= $instalments_number ?> meses de <?= $instalments_amount ?>, fue aprobado</strong></p>
	<?php else: ?>
		<p><strong><?= $customer_name ?> tu pago de <?= $total ?>, en una sola exibición, fue aprobado o/</strong></p>
	<?php endif ?>

	<p>Se tienes alguna duda en relación a tu pago, ingresa a la Cuenta EBANX con el email <?= $customer_email ?></p>
</div>
