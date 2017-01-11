<?php
/**
 * Plugin Name: WooCommerce EBANX Gateway
 * Plugin URI: https://www.ebanx.com/business/en/developers/integrations/extensions-and-plugins/woocommerce-plugin
 * Description: Accept credit card and cash payments in Brazil and Latin America using EBANX.
 * Author: EBANX
 * Author URI: https://www.ebanx.com/business/
 * Version: 1.0.0
 * License: MIT
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
define('PLUGIN_DIR_URL', plugin_dir_url(__FILE__) . DIRECTORY_SEPARATOR);
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
            add_action('current_screen', array($this, 'check_status_change_notification_url_configured'));
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

            // i18n
            $this->enable_i18n();

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

        public function enable_i18n()
        {
            load_plugin_textdomain( 'woocommerce-gateway-ebanx', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
        }

        public function my_account_template()
        {
            if (isset($_POST['credit-card-delete']) && is_account_page()) {
                // Find credit cards saved and delete the selected
                $cards = get_user_meta(get_current_user_id(), '_ebanx_credit_card_token', true);

                foreach ($cards as $k => $cd) {
                    if ($cd && in_array($cd->masked_number, $_POST['credit-card-delete'])) {
                        unset($cards[$k]);
                    }
                }

                update_user_meta(get_current_user_id(), '_ebanx_credit_card_token', $cards);
            }

            $cards = array_filter((array) get_user_meta(get_current_user_id(), '_ebanx_credit_card_token', true), function ($card) {
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

            add_option('woocommerce_ebanx-global_settings', WC_EBANX_Global_Gateway::$defaults);
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

        public function check_status_change_notification_url_configured()
        {
            $screen = get_current_screen();
            if ($screen->id != 'woocommerce_page_wc-settings') return;

            if (!isset($_GET['tab']) || !isset($_GET['section']) || $_GET['tab'] != 'checkout' || $_GET['section'] != 'ebanx-global') return;

            $this->configs = new WC_EBANX_Global_Gateway();
            $is_sandbox = ($this->configs->settings['sandbox_mode_enabled'] == 'yes');

            if (empty($this->get_notification_url($is_sandbox))) {
                $home_url = get_home_url();
                $live_url = "https://www.ebanx.com/business/en/dashboard/settings#settings-integration";
                $sandbox_url = "https://www.ebanx.com/business/en/dashboard/test/settings#settings-integration";
                $merchant_area_url = ($is_sandbox) ? $sandbox_url : $live_url;

                $message = "There was a problem with your notification settings. Please go to <a href='${merchant_area_url}'>Settings in your Dashboard</a> and copy & paste “${home_url}” into the “Status Change Notification URL” field under Services URLs. This will allow WooCommerce to receive payment confirmations automatically.";

                $this->add_admin_notice('configure_status_change', 'error', $message);
            }
        }

        private function get_notification_url($is_sandbox)
        {
            $private_key = $is_sandbox ? $this->configs->settings['sandbox_private_key'] : $this->configs->settings['live_private_key'];

            \Ebanx\Config::set(array('integrationKey' => $private_key, 'testMode' => $is_sandbox));

            try {
                $res = \Ebanx\Ebanx::getMerchantIntegrationProperties(array('integrationKey' => '1231000'));

                if (empty($res->body->url_status_change_notification)) {
                    throw new Exception('CONNECTION-ERROR');
                }

                return $res->body->url_status_change_notification;
            } catch (Exception $e) {
                $api_url = 'https://api.ebanx.com';

                $message = sprintf('Could not connect to EBANX servers. Please check if your server can reach your API: <a href="%1$s">%1$s</a>', $api_url);
                $this->add_admin_notice('connection_error', 'error', $message);
            }
        }

        public static function get_environment_warning()
        {
            if (version_compare(phpversion(), WC_EBANX_MIN_PHP_VER, '<')) {
                $message = __('WooCommerce EBANX Gateway - The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'woocommerce-gateway-ebanx', 'woocommerce-gateway-ebanx');
                return sprintf($message, WC_EBANX_MIN_PHP_VER, phpversion());
            }
            if (!defined('WC_VERSION')) {
                return __('WooCommerce EBANX Gateway requires WooCommerce to be activated to work.', 'woocommerce-gateway-ebanx');
            }
            if (version_compare(WC_VERSION, WC_EBANX_MIN_WC_VER, '<')) {
                $message = __('WooCommerce EBANX Gateway - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'woocommerce-gateway-ebanx', 'woocommerce-gateway-ebanx');
                return sprintf($message, WC_EBANX_MIN_WC_VER, WC_VERSION);
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
            include_once(INCLUDES_DIR . 'class-wc-ebanx-custom-order.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-gateway-utils.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-gateway.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-redirect-gateway.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-pagoefectivo-gateway.php');
            include_once(INCLUDES_DIR . 'class-wc-ebanx-sencillito-gateway.php');
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
            $methods[] = 'WC_EBANX_Sencillito_Gateway';
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

            $plugin_links[] = '<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=' . $ebanx_global)) . '">' . __('Settings', 'woocommerce-gateway-ebanx') . '</a>';

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
