<?php
/**
 * Plugin Name: WooCommerce EBANX.com
 * Plugin URI: http://github.com/ebanx/woocommerce
 * Description: Gateway de pagamento ebanx.com para WooCommerce.
 * Author: EBANX
 * Author URI: https://ebanx.com/
 * Version: 1.0.0
 * License: GPLv2 or later
 * Text Domain: woocommerce-ebanx
 * Domain Path: /languages
 *
 * @package WooCommerce_EBANX
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WC_EBANX_MIN_PHP_VER', '5.3.0');
define('WC_EBANX_MIN_WC_VER', '2.5.0');
define('INCLUDES_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR);
define('SERVICES_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR);

if (!class_exists('WC_EBANX')) {

    /**
     * WooCommerce WC_EBANX main class.
     */
    class WC_EBANX
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

        private static $endpoint = 'ebanx-credit-cards';

        private static $menu_name = 'EBANX - Credit Cards';

        /**
         * Initialize the plugin public actions.
         */
        private function __construct()
        {
            add_action('admin_init', array($this, 'check_environment'));
            add_action('admin_notices', array($this, 'admin_notices'), 15);
            add_action('plugins_loaded', array($this, 'init'));

            // My Account
            add_action('init', array($this, 'my_account_endpoint'));
            add_filter('query_vars', array($this, 'my_account_query_vars'), 0);
            register_activation_hook(self::DIR, array($this, 'my_account_endpoint'));
            register_deactivation_hook(self::DIR, array($this, 'my_account_endpoint'));

            add_filter('woocommerce_account_menu_items', array($this, 'my_account_menus'));
            add_filter('the_title', array($this, 'my_account_menus_title'));
            add_action('woocommerce_account_' . self::$endpoint . '_endpoint', array($this, 'my_account_template'));

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

        public function my_account_template()
        {
            if (isset($_POST['credit-card-delete']) && is_account_page()) {
                // Find credit cards saved and delete the selected
                $cards = get_user_meta(get_current_user_id(), '__ebanx_credit_card_token', true);

                foreach ($cards as $k => $cd) {
                    if (in_array($cd->masked_number, $_POST['credit-card-delete'])) {
                        unset($cards[$k]);
                    }
                }

                update_user_meta(get_current_user_id(), '__ebanx_credit_card_token', $cards);
            }

            $cards = array_filter((array) get_user_meta(get_current_user_id(), '__ebanx_credit_card_token', true), function ($card) {
                return !empty($card->brand) && !empty($card->token) && !empty($card->masked_number); // TODO: Implement token due date
            });

            wc_get_template(
                'my-account/ebanx-credit-cards.php',
                array(
                    'cards' => (array) $cards,
                ),
                'woocommerce/ebanx/',
                WC_EBANX::get_templates_path()
            );
        }

        public function my_account_query_vars($vars)
        {
            $vars[] = self::$endpoint;

            return $vars;
        }

        public function my_account_endpoint()
        {
            add_rewrite_endpoint(self::$endpoint, EP_ROOT | EP_PAGES);
            flush_rewrite_rules();
        }

        public function my_account_menus_title($title)
        {
            global $wp_query;

            $is_endpoint = isset($wp_query->query_vars[self::$endpoint]);

            if ($is_endpoint && !is_admin() && is_main_query() && in_the_loop() && is_account_page()) {
                $title = __(self::$menu_name, 'woocommerce-gateway-ebanx');
                remove_filter('the_title', array($this, 'my_account_menus_title'));
            }

            return $title;
        }

        public function my_account_menus($menu)
        {
            // Remove the logout menu item.
            $logout = $menu['customer-logout'];
            unset($menu['customer-logout']);

            $menu[self::$endpoint] = __(self::$menu_name, 'woocommerce-gateway-ebanx');

            // Insert back the logout item.
            $menu['customer-logout'] = $logout;

            return $menu;
        }

        public function admin_notices()
        {
            foreach ((array) $this->notices as $notice_key => $notice) {
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

        public static function get_environment_warning()
        {
            if (version_compare(phpversion(), WC_EBANX_MIN_PHP_VER, '<')) {
                $message = __('WooCommerce EBANX - The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'woocommerce-gateway-ebanx', 'woocommerce-gateway-ebanx');
                return sprintf($message, WC_EBANX_MIN_PHP_VER, phpversion());
            }
            if (!defined('WC_VERSION')) {
                return __('WooCommerce EBANX requires WooCommerce to be activated to work.', 'woocommerce-gateway-ebanx');
            }
            if (version_compare(WC_VERSION, WC_EBANX_MIN_WC_VER, '<')) {
                $message = __('WooCommerce EBANX - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'woocommerce-gateway-ebanx', 'woocommerce-gateway-ebanx');
                return sprintf($message, WC_EBANX_MIN_WC_VER, WC_VERSION);
            }
            if (!function_exists('curl_init')) {
                return __('WooCommerce EBANX - cURL is not installed.', 'woocommerce-gateway-ebanx');
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
            include_once(INCLUDES_DIR . 'class-wc-ebanx-gateway-utils.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-gateway.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-redirect-gateway.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-pagoefectivo-gateway.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-account-gateway.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-safetypay-gateway.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-my-account.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-banking-ticket-gateway.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-global-gateway.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-credit-card-gateway.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-debit-card-gateway.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-oxxo-gateway.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-servipag-gateway.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-tef-gateway.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-eft-gateway.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-one-click.php');
            include_once(SERVICES_DIR . 'class-wc-ebanx-hooks.php');
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
            $methods[] = 'WC_EBANX_Global_Gateway';
            $methods[] = 'WC_EBANX_Banking_Ticket_Gateway';
            $methods[] = 'WC_EBANX_Credit_Card_Gateway';
            $methods[] = 'WC_EBANX_Debit_Card_Gateway';
            $methods[] = 'WC_EBANX_Oxxo_Gateway';
            $methods[] = 'WC_EBANX_Servipag_Gateway';
            $methods[] = 'WC_EBANX_Tef_Gateway';
            $methods[] = 'WC_EBANX_Pagoefectivo_Gateway';
            $methods[] = 'WC_EBANX_Safetypay_Gateway';
            $methods[] = 'WC_EBANX_Eft_Gateway';
            $methods[] = 'WC_EBANX_Account_Gateway';

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

            $ebanx_global = 'ebanx-global';

            $plugin_links[] = '<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=' . $ebanx_global)) . '">' . __('EBANX Settings', 'woocommerce-gateway-ebanx') . '</a>';

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
            if (empty(self::$log)) self::$log = new WC_Logger();

            self::$log->add('woocommerce-gateway-ebanx', $message);

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log($message);
            }
        }
    }

    add_action('plugins_loaded', array('WC_EBANX', 'get_instance'));
}
