<?php
/**
 * Payment completed.
 *
 * @author  EBANX.com
 * @package WooCommerce_EBANX/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<p><?php echo __('Obrigado! O pagamento foi realizado com sucesso', 'woocommerce-gateway-ebanx'); ?></p>
<p><?php echo sprintf(__('Um comprovante foi enviado para o email %s', 'woocommerce-gateway-ebanx'), $customer_email); ?></p>
