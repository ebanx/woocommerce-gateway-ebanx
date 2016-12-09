<?php
/**
 * Admin options screen.
 *
 * @package WooCommerce_Ebanx/Admin/Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<h3><?php echo esc_html( $this->method_title ); ?></h3>

<?php
if ( 'yes' === $this->get_option( 'enabled' ) ) {
	// TODO: IMPORTANT!!! See
//	if ( ! $this->api->using_supported_currency() && ! class_exists( 'woocommerce_wpml' ) ) {
//		include dirname( __FILE__ ) . '/html-notice-currency-not-supported.php';
//	}
}
?>

<?php echo wp_kses_post( wpautop( $this->method_description ) ); ?>

<table class="form-table">
	<?php $this->generate_settings_html(); ?>
</table>
