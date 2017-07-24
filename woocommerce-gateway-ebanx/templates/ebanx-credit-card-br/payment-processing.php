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

<p><strong><?php _e(sprintf('Seu pagamento foi confirmado, %s.', $customer_name), 'woocommerce-gateway-ebanx') ?></strong></p>
<p><?php _e('Valor a pagar com ' .
            ($this->configs->get_setting_or_default('add_iof_to_local_amount_enabled', 'yes') === 'yes' ? 'IOF (0.38%)' : 'em Reais')
            . ':', 'woocommerce-gateway-ebanx') ?> <?php echo $total ?></p>
<?php if ($instalments_number > 1) : ?>
    <p class="ebanx-payment-type"><?php echo $instalments_number ?> <?php _e('parcelas de', 'woocommerce-gateway-ebanx') ?> <?php echo $instalments_amount ?></p>
<?php else : ?>
    <p class="ebanx-payment-type"><?php _e('Pagamento à vista', 'woocommerce-gateway-ebanx') ?></p>
<?php endif; ?>
<p><?php _e(sprintf('Pago com Cartão %s:', ucwords($card_brand_name)), 'woocommerce-gateway-ebanx') ?> <?php echo $masked_card ?></p>
<p><?php _e('Obrigado por ter comprado conosco.', 'woocommerce-gateway-ebanx') ?></p>
