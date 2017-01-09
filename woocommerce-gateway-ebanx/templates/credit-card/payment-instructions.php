<?php
/**
 * Credit Card - Payment instructions.
 *
 * @author  EBANX
 * @package WooCommerce_Pagarme/Templates
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

?>
<!-- TODO:Create a better design to do this -->

<p><?php printf(wp_kses(__('Payment successfully made using %1$s credit card in %2$s.', 'woocommerce-gateway-ebanx'), array('strong' => array())), '<strong>' . esc_html(ucwords($card_brand)) . '</strong>', '<strong>' . intval($instalments) . 'x</strong>');?></p>
