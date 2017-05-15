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

<p><strong><?php _e(sprintf('Pago aprobado con éxito, %s.', $customer_name), 'woocommerce-gateway-ebanx') ?></strong></p>
<p><strong><?php _e('Resumo de la compra:', 'woocommerce-gateway-ebanx') ?></strong></p>
<p><?php _e('Valor:', 'woocommerce-gateway-ebanx') ?> <?php echo $total ?></p>
<?php if ($instalments_number > 1) : ?>
    <p><?php echo $instalments_number ?> <?php _e('meses sen intereses de', 'woocommerce-gateway-ebanx') ?> <?php echo $instalments_amount ?></p>
<?php else : ?>
    <p><?php _e('Pago realizado en una sola exhibición', 'woocommerce-gateway-ebanx') ?></p>
<?php endif; ?>
<p><?php _e(sprintf('Pago con tarjeta %s:', ucwords($card_brand_name)), 'woocommerce-gateway-ebanx') ?> <?php echo $masked_card ?></p>
<p><?php _e('Gracias por haber comprado con nosotros.', 'woocommerce-gateway-ebanx') ?></p>
