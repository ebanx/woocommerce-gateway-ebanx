<?php
/**
 * Debit Card - Payment processed.
 *
 * @author  EBANX.com
 * @package WooCommerce_EBANX/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<p><strong><?php echo esc_html( sprintf( 'Pago aprobado con éxito, %s.', $customer_name ) ); ?></strong></p>
<p><strong>Resumo de la compra:</strong></p>
<p>Valor: <?php echo wp_kses_post( wc_price( $order_amount ) ); ?></p>
<p>Pago realizado en una sola exhibición'</p>
<p>Gracias por haber comprado con nosotros.'</p>
