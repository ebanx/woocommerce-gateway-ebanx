<?php
/**
 * Pagoefectivo - Payment instructions.
 *
 * @author  EBANX.com
 * @package WooCommerce_EBANX/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<?php var_dump( get_defined_vars() ); ?>
<div class="ebanx-order__desc">
    <p>Acabamos de confirmar la operación y procesaremos tu orden. Imprime tu cupón y acércate a cualquier centro autorizado para realizar tu pago.</p>
    <p>Una copia del cupón fue enviada al correo electrónico:</p>
    <p><strong><?=$customer_email ?></strong></p>
    <p>Si tienes dudas, por favor escribe a <a href="mailto:soporte@ebanx.com">soporte@ebanx.com</a>.</p>
</div>

