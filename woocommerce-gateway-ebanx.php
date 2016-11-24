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

if (!defined('ABSPATH')) {
    exit;
}

define('WC_EBANX_MIN_PHP_VER', '5.3.0');
define('WC_EBANX_MIN_WC_VER', '2.5.0');

if (!class_exists('WC_Ebanx')) {

    /**
     * WooCommerce WC_Ebanx main class.
     */
    class WC_Ebanx
    {

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

        public $notices = array();

        /**
         * Initialize the plugin public actions.
         */
        private function __construct()
        {
            add_action('admin_init', array($this, 'check_environment'));
            add_action('admin_notices', array($this, 'admin_notices'), 15);
            add_action('plugins_loaded', array($this, 'init'));

            if (class_exists('WC_Payment_Gateway')) {
                $this->includes();

                add_filter('woocommerce_payment_gateways', array($this, 'add_gateway'));
                add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
            } else {
                add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            }
        }

        public function init()
        {
            if (self::get_environment_warning()) {
                return;
            }
        }

        public function admin_notices()
        {
            foreach((array) $this->notices as $notice_key => $notice) {
                echo "<div class='" . esc_attr($notice['class']) . "'><p>";
                echo wp_kses($notice['message'], array('a' => array('href' => array())));
                echo "</p></div>";
            }
        }

        protected function add_admin_notice($slug, $class, $message)
        {
            $this->notices[$slug] = array(
                'class'   => $class,
                'message' => $message);
        }

        public function check_environment()
        {
            $environment_warning = self::get_environment_warning();
            if ($environment_warning && is_plugin_active(plugin_basename(__FILE__))) {
                $this->add_admin_notice('bad_environment', 'error', $environment_warning);
            }
        }

        public static function get_environment_warning() {
            if (version_compare(phpversion(), WC_EBANX_MIN_PHP_VER, '<')) {
                $message = __('WooCommerce Ebanx - The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'woocommerce-gateway-ebanx', 'woocommerce-gateway-ebanx');
                return sprintf($message, WC_EBANX_MIN_PHP_VER, phpversion());
            }
            if (!defined('WC_VERSION')) {
                return __('WooCommerce Ebanx requires WooCommerce to be activated to work.', 'woocommerce-gateway-ebanx');
            }
            if (version_compare(WC_VERSION, WC_EBANX_MIN_WC_VER, '<')) {
                $message = __('WooCommerce Ebanx - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'woocommerce-gateway-ebanx', 'woocommerce-gateway-ebanx');
                return sprintf($message, WC_EBANX_MIN_WC_VER, WC_VERSION);
            }
            if (!function_exists('curl_init')) {
                return __('WooCommerce Ebanx - cURL is not installed.', 'woocommerce-gateway-ebanx');
            }
            return false;
        }

        /**
         * Return an instance of this class.
         *
         * @return object A single instance of this class.
         */
        public static function get_instance()
        {
            // If the single instance hasn't been set, set it now.
            if (null === self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }

        /**
         * Includes.
         */
        private function includes()
        {
            include_once dirname(__FILE__) . '/services/class-wc-ebanx-hooks.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-gateway-utils.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-redirect-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-pagoefectivo-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-safetypay-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-my-account.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-banking-ticket-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-global-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-credit-card-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-oxxo-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-servipag-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-tef-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-wc-ebanx-eft-gateway.php';
        }

        /**
         * Get templates path.
         *
         * @return string
         */
        public static function get_templates_path()
        {
            return plugin_dir_path(__FILE__) . 'templates/';
        }

        /**
         * Add the gateway to WooCommerce.
         *
         * @param  array $methods WooCommerce payment methods.
         *
         * @return array
         */
        public function add_gateway($methods)
        {
            $methods[] = 'WC_Ebanx_Global_Gateway';
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
        public function plugin_action_links($links)
        {
            $plugin_links = array();

            $ebanx_global   = 'ebanx-global';

            $plugin_links[] = '<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=' . $ebanx_global)) . '">' . __('Ebanx Settings', 'woocommerce-gateway-ebanx') . '</a>';

            return array_merge($plugin_links, $links);
        }

        /**
         * WooCommerce fallback notice.
         */
        public function woocommerce_missing_notice()
        {
            // TODO: Others notice here
            include dirname(__FILE__) . '/includes/admin/views/html-notice-missing-woocommerce.php';
        }

        public static function log($message)
        {
            if (empty(self::$log)) {
                self::$log = new WC_Logger();
            }

            self::$log->add('woocommerce-gateway-ebanx', $message);

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log($message);
            }
        }
    }

    add_action('plugins_loaded', array('WC_Ebanx', 'get_instance'));
}
