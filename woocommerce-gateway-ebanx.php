<?php
/**
 * Plugin Name: WooCommerce Ebanx.com
 * Plugin URI: http://github.com/ebanx/woocommerce
 * Description: Gateway de pagamento Ebanx.com para WooCommerce.
 * Author: Woocommercer, Cristopher
 * Author URI: https://ebanx.com/
 * Version: 1.0.0
 * License: GPLv2 or later
 * Text Domain: woocommerce-ebanx
 * Domain Path: /languages/
 *
 * @package WooCommerce_Ebanx
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('WC_Ebanx')) :

    /**
     * WooCommerce WC_Ebanx main class.
     */
    class WC_Ebanx {

        /**
         * Plugin version.
         *
         * @var string
         */
        const VERSION = '1.0.0';

        const DIR = __FILE__;

        /**
         * Instance of this class.
         *
         * @var object
         */
        protected static $instance = null;

        private static $log;

        /**
         * Initialize the plugin public actions.
         */
        private function __construct() {
            // Load plugin text domain.
            add_action('init', array($this, 'load_plugin_textdomain'));

            // Checks with WooCommerce is installed.
            if (class_exists('WC_Payment_Gateway')) {
                $this->upgrade();
                $this->includes();

                add_filter('woocommerce_payment_gateways', array($this, 'add_gateway'));
                add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
            } else {
                add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            }
        }

        /**
         * Return an instance of this class.
         *
         * @return object A single instance of this class.
         */
        public static function get_instance() {
            // If the single instance hasn't been set, set it now.
            if (null === self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }

        /**
         * Includes.
         */
        private function includes() {
            include_once dirname(__FILE__) . '/services/class-wc-ebanx-hooks.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-gateway-utils.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-redirect-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-pagoefectivo-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-safetypay-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-my-account.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-banking-ticket-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-credit-card-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-oxxo-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-servipag-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-tef-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-eft-gateway.php';
        }

        /**
         * Load the plugin text domain for translation.
         */
        public function load_plugin_textdomain() {
            load_plugin_textdomain('woocommerce-ebanx', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }

        /**
         * Get templates path.
         *
         * @return string
         */
        public static function get_templates_path() {
            return plugin_dir_path(__FILE__) . 'templates/';
        }

        /**
         * Add the gateway to WooCommerce.
         *
         * @param  array $methods WooCommerce payment methods.
         *
         * @return array
         */
        public function add_gateway($methods) {
            $methods[] = 'WC_Ebanx_Banking_Ticket_Gateway';
            $methods[] = 'WC_Ebanx_Credit_Card_Gateway';
            $methods[] = 'WC_Ebanx_Oxxo_Gateway';
            $methods[] = 'WC_Ebanx_Servipag_Gateway';
            $methods[] = 'WC_Ebanx_Tef_Gateway';
            $methods[] = 'WC_Ebanx_Pagoefectivo_Gateway';
            $methods[] = 'WC_Ebanx_Safetypay_Gateway';
            $methods[] = 'WC_Ebanx_Eft_Gateway';

            return $methods;
        }

        /**
         * Action links.
         *
         * @param  array $links Plugin links.
         *
         * @return array
         */
        public function plugin_action_links($links) {
            $plugin_links = array();

            $banking_ticket = 'wc_ebanx_banking_ticket_gateway';
            $credit_card    = 'wc_ebanx_credit_card_gateway';
            $oxxo           = 'wc_ebanx_oxxo_gateway';
            $servipag       = 'wc_ebanx_servipag_gateway';
            $tef            = 'wc_ebanx_tef_gateway';
            $eft            = 'wc_ebanx_eft_gateway';
            $pagoefectivo   = 'wc_ebanx_pagoefectivo_gateway';
            $safetypay      = 'wc_ebanx_safetypay_gateway';

            $plugin_links[] = '<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=' . $banking_ticket)) . '">' . __('Bank Slip Settings', 'woocommerce-ebanx') . '</a>';

            $plugin_links[] = '<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=' . $credit_card)) . '">' . __('Credit Card Settings', 'woocommerce-ebanx') . '</a>';

            $plugin_links[] = '<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=' . $oxxo)) . '">' . __('Oxxo Settings', 'woocommerce-ebanx') . '</a>';

            $plugin_links[] = '<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=' . $servipag)) . '">' . __('Servipag Settings', 'woocommerce-ebanx') . '</a>';

            $plugin_links[] = '<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=' . $tef)) . '">' . __('TEF Settings', 'woocommerce-ebanx') . '</a>';

            $plugin_links[] = '<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=' . $pagoefectivo)) . '">' . __('Pagoefectivo Settings', 'woocommerce-ebanx') . '</a>';

            $plugin_links[] = '<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=' . $safetypay)) . '">' . __('Safetypay Settings', 'woocommerce-ebanx') . '</a>';

            $plugin_links[] = '<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=' . $eft)) . '">' . __('EFT Settings', 'woocommerce-ebanx') . '</a>';

            return array_merge($plugin_links, $links);
        }

        /**
         * WooCommerce fallback notice.
         */
        public function woocommerce_missing_notice() {
            // TODO: Others notice here
            include dirname(__FILE__) . '/includes/admin/views/html-notice-missing-woocommerce.php';
        }

        /**
         * Upgrade.
         *
         * @since 2.0.0
         */
        private function upgrade() {
            if (is_admin()) {
                if ($old_options = get_option('woocommerce_ebanx_settings')) {
                    // Banking ticket options.
                    $banking_ticket = array(
                        'enabled'        => $old_options['enabled'],
                        'title'          => 'Boleto bancário',
                        'description'    => '',
                        'api_key'        => $old_options['api_key'],
                        'encryption_key' => $old_options['encryption_key'],
                        'debug'          => $old_options['debug'],);

                    // Oxxo options.
                    $oxxo = array(
                        'enabled'        => $old_options['enabled'],
                        'title'          => 'Oxxo',
                        'description'    => '',
                        'api_key'        => $old_options['api_key'],
                        'encryption_key' => $old_options['encryption_key'],
                        'debug'          => $old_options['debug'],);

                    // Servipag options.
                    $servipag = array(
                        'enabled'        => $old_options['enabled'],
                        'title'          => 'Servipag',
                        'description'    => '',
                        'api_key'        => $old_options['api_key'],
                        'encryption_key' => $old_options['encryption_key'],
                        'debug'          => $old_options['debug'],);

                    // Credit card options.
                    $credit_card = array(
                        'enabled'              => $old_options['enabled'],
                        'title'                => 'Cartão de crédito',
                        'description'          => '',
                        'api_key'              => $old_options['api_key'],
                        'encryption_key'       => $old_options['encryption_key'],
                        'checkout'             => 'no',
                        'max_installment'      => $old_options['max_installment'],
                        'smallest_installment' => $old_options['smallest_installment'],
                        'interest_rate'        => $old_options['interest_rate'],
                        'free_installments'    => $old_options['free_installments'],
                        'debug'                => $old_options['debug'],);

                    // Tef options.
                    $tef = array(
                        'enabled'        => $old_options['enabled'],
                        'title'          => 'TEF',
                        'description'    => '',
                        'api_key'        => $old_options['api_key'],
                        'encryption_key' => $old_options['encryption_key'],
                        'debug'          => $old_options['debug'],);

                    // Pagoefectivo options.
                    $pagoefectivo = array(
                        'enabled'        => $old_options['enabled'],
                        'title'          => 'Pagoefectivo',
                        'description'    => '',
                        'api_key'        => $old_options['api_key'],
                        'encryption_key' => $old_options['encryption_key'],
                        'debug'          => $old_options['debug'],);

                    // Safetypay options.
                    $safetypay = array(
                        'enabled'        => $old_options['enabled'],
                        'title'          => 'Safetypay',
                        'description'    => '',
                        'api_key'        => $old_options['api_key'],
                        'encryption_key' => $old_options['encryption_key'],
                        'debug'          => $old_options['debug'],);

                    // EFT options.
                    $eft = array(
                        'enabled'        => $old_options['enabled'],
                        'title'          => 'EFT',
                        'description'    => '',
                        'api_key'        => $old_options['api_key'],
                        'encryption_key' => $old_options['encryption_key'],
                        'debug'          => $old_options['debug'],);

                    update_option('woocommerce_ebanx-banking-ticket_settings', $banking_ticket);
                    update_option('woocommerce_ebanx-credit-card_settings', $credit_card);
                    update_option('woocommerce_ebanx-oxxo_settings', $oxxo);
                    update_option('woocommerce_ebanx-servipag_settings', $servipag);
                    update_option('woocommerce_ebanx-tef_settings', $tef);
                    update_option('woocommerce_ebanx-eft_settings', $eft);
                    update_option('woocommerce_ebanx-pagoefectivo_settings', $pagoefectivo);
                    update_option('woocommerce_ebanx-safetypay_settings', $safetypay);

                    delete_option('woocommerce_ebanx_settings');
                }
            }
        }

        public static function log($message) {
            if (empty(self::$log)) {
                self::$log = new WC_Logger();
            }

            self::$log->add('woocommerce-ebanx', $message);

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log($message);
            }
        }
    }

    add_action('plugins_loaded', array('WC_Ebanx', 'get_instance'));
endif;
