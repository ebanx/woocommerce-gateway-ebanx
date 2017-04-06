<?php
/**
 * Plugin Name: EBANX Payment Gateway for WooCommerce
 * Plugin URI: https://www.ebanx.com/business/en/developers/integrations/extensions-and-plugins/woocommerce-plugin
 * Description: Offer Latin American local payment methods & increase your conversion rates with the solution used by AliExpress, AirBnB and Spotify in Brazil.
 * Author: EBANX
 * Author URI: https://www.ebanx.com/business/en
 * Version: 1.10.1
 * License: MIT
 * Text Domain: woocommerce-gateway-ebanx
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
define('GATEWAYS_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'gateways' . DIRECTORY_SEPARATOR);
define('SERVICES_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR);
define('LANGUAGES_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR);
define('TEMPLATES_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR);
define('VENDOR_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR);
define('ASSETS_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR);

if (!class_exists('WC_EBANX')) {
	/**
	 * Hooks
	 */
	register_activation_hook(__FILE__, array('WC_EBANX', 'active_plugin'));
	register_deactivation_hook(__FILE__, array('WC_EBANX', 'deactivate_plugin'));

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
		const VERSION = '1.10.1';

		const DIR = __FILE__;

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		private static $log;

		private static $endpoint = 'ebanx-cards';

		private static $menu_name = 'EBANX - Cards';

		/**
		 * Initialize the plugin public actions.
		 */
		private function __construct()
		{
			include_once SERVICES_DIR . 'class-wc-ebanx-notice.php';

			$this->notices = new WC_EBANX_Notice();

			if (!class_exists('WC_Payment_Gateway')) {
				$this->notices
					->with_view('missing-woocommerce')
					->enqueue();
				return;
			}
			/**
			 * i18n
			 */
			$this->enable_i18n();

			/**
			 * Includes
			 */
			$this->includes();

			/**
			 * Actions
			 */
			add_action('plugins_loaded', array($this, 'plugins_loaded'));

			add_action('init', array($this, 'my_account_endpoint'));
			add_action('init', array($this, 'ebanx_router'));
			add_action('admin_footer', array($this, 'render_static_assets'), 0);
			add_action('admin_init', array($this, 'ebanx_sidebar_shortcut'));
			add_action('admin_init', array('WC_EBANX_Flash', 'enqueue_admin_messages'));

			if ( empty( $_POST ) ) {
				add_action('admin_init', array($this, 'setup_configs'), 10);
				add_action('admin_init', array($this, 'checker'), 30);
			}

			add_action('woocommerce_account_' . self::$endpoint . '_endpoint', array($this, 'my_account_template'));
			add_action('woocommerce_settings_saved', array($this, 'setup_configs'), 10);
			add_action('woocommerce_settings_saved', array($this, 'on_save_settings'), 10);
			add_action('woocommerce_settings_saved', array($this, 'update_lead'), 20);
			add_action('woocommerce_settings_saved', array($this, 'checker'), 20);

			add_action('woocommerce_admin_order_data_after_order_details', array($this, 'ebanx_admin_order_details'), 10, 1);

			/**
			 * Filters
			 */
			add_filter('query_vars', array($this, 'my_account_query_vars'), 0);
			add_filter('woocommerce_account_menu_items', array($this, 'my_account_menus'));
			add_filter('the_title', array($this, 'my_account_menus_title'));
			add_filter('woocommerce_payment_gateways', array($this, 'add_gateway'));
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
		}

		/**
		* Sets up the configuration object
		*
		* @return void
		*/
		public function setup_configs() {
			/**
			 * Configs
			 */
			$this->configs = new WC_EBANX_Global_Gateway();
			$this->is_sandbox_mode = $this->configs->settings['sandbox_mode_enabled'] === 'yes';
			$this->private_key = $this->is_sandbox_mode ? $this->configs->settings['sandbox_private_key'] : $this->configs->settings['live_private_key'];
			$this->public_key = $this->is_sandbox_mode ? $this->configs->settings['sandbox_public_key'] : $this->configs->settings['live_public_key'];
		}

		/**
		* Performs checks on some system status
		*
		* @return void
		*/
		public function checker() {
			WC_EBANX_Checker::check_sandbox_mode($this);
			WC_EBANX_Checker::check_merchant_api_keys($this);
			WC_EBANX_Checker::check_environment($this);
			WC_EBANX_Checker::check_currency($this);
		}

		/**
		 * Call when the plugins are loaded
		 *
		 * @return void
		 */
		public function plugins_loaded()
		{
			if ($this->get_environment_warning()) {
				return;
			}
		}

		/**
		 * Check if it is receiving a third-party request and routes it
		 *
		 * @return void
		 */
		public function ebanx_router()
		{
			if (isset($_GET['ebanx'])) {
				$action = $_GET['ebanx'];
				if ($action === 'order-received' && isset($_GET['hash'])) {
					$hash = $_GET['hash'];
					$payment_type = isset($_GET['payment_type']) ? $_GET['payment_type'] : null;
					$this->ebanx_order_received($hash, $payment_type);
					return;
				}
				if ($action === 'dashboard-check') {
					$this->ebanx_dashboard_check();
					return;
				}
			}
		}

		/**
		 * Gets the banking ticket HTML by cUrl with url fopen fallback
		 *
		 * @return void
		 */
		private function ebanx_order_received($hash, $payment_type)
		{
			$this->setup_configs();
			$subdomain = $this->is_sandbox_mode ? 'sandbox' : 'print';
			$url = "https://{$subdomain}.ebanx.com/";
			if (!isset($payment_type) || $payment_type !== 'cip') {
				$url .= 'print/';
			}
			if (isset($payment_type) && $payment_type !== 'boleto') {
				$url .= "{$payment_type}/";
			}
			$url .= "?hash={$hash}";
			if (!isset($payment_type) || $payment_type !== 'baloto') {
				$url .= '&format=basic#';
			}
			if (in_array('curl', get_loaded_extensions())) {
				$curl = curl_init($url);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				$html = curl_exec($curl);

				if (!curl_error($curl)) {
					curl_close($curl);
					echo $html;
					exit;
				}
			}

			echo file_get_contents($url);
			exit;
		}

		/**
		 * Responds that the plugin is installed
		 *
		 * @return void
		 */
		private function ebanx_dashboard_check()
		{
			$json = json_encode(array(
				'ebanx' => true,
				'version' => self::VERSION
			));
			echo $json;
			exit;
		}

		/**
		 * It enables the i18n of the plugin using the languages folders and the domain 'woocommerce-gateway-ebanx'
		 *
		 * @return void
		 */
		public function enable_i18n()
		{
			load_plugin_textdomain('woocommerce-gateway-ebanx', false, dirname( plugin_basename(__FILE__) ) . '/languages/');
		}

		/**
		 * It enables the my account page for logged users
		 *
		 * @return void
		 */
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
			
			if (isset($_POST['debit-card-delete']) && is_account_page()) {
				// Find debit cards saved and delete the selected
				$cards = get_user_meta(get_current_user_id(), '_ebanx_debit_card_token', true);

				foreach ($cards as $k => $cd) {
					if ($cd && in_array($cd->masked_number, $_POST['debit-card-delete'])) {
						unset($cards[$k]);
					}
				}

				update_user_meta(get_current_user_id(), '_ebanx_credit_card_token', $cards);
			}

			$credit_cards = array_filter((array) get_user_meta(get_current_user_id(), '_ebanx_credit_card_token', true), function ($card) {
				return !empty($card->brand) && !empty($card->token) && !empty($card->masked_number); // TODO: Implement token due date
			});

			$debit_cards = array_filter((array) get_user_meta(get_current_user_id(), '_ebanx_debit_card_token', true), function ($card) {
				return !empty($card->brand) && !empty($card->token) && !empty($card->masked_number); // TODO: Implement token due date
			});

			wc_get_template(
				'my-account/ebanx-cards.php',
				array(
					'credit_cards' => (array) $credit_cards,
					'debit_cards' => (array) $debit_cards
				),
				'woocommerce/ebanx/',
				WC_EBANX::get_templates_path()
			);
		}

		/**
		 * Mount query vars on my account for credit cards
		 *
		 * @param  array $vars
		 * @return void
		 */
		public function my_account_query_vars($vars)
		{
			$vars[] = self::$endpoint;

			return $vars;
		}

		/**
		 * It creates a endpoint to my account
		 *
		 * @return void
		 */
		public function my_account_endpoint()
		{
			// My account endpoint
			add_rewrite_endpoint(self::$endpoint, EP_ROOT | EP_PAGES);

			add_option('woocommerce_ebanx-global_settings', WC_EBANX_Global_Gateway::$defaults);

			flush_rewrite_rules();
		}

		/**
		 * Save some informations from merchant and send to EBANX servers
		 *
		 * @return void
		 */
		public static function save_merchant_infos() {
			// Prevent fatal error if WooCommerce isn't installed
			if ( !defined('WC_VERSION') ) {
				return;
			}

			// Save merchant informations
			$user = get_userdata(get_current_user_id());
			if (!$user || is_wp_error($user)) {
				return;
			}

			$url = 'https://www.ebanx.com/business/en/dashboard/api/lead';
			$args = array(
				'body' => array(
					'lead' => array(
						'user_email' => $user->user_email,
						'user_display_name' => $user->display_name,
						'user_last_name' => $user->last_name,
						'user_first_name' => $user->first_name,
						'site_email' => get_bloginfo('admin_email'),
						'site_url' => get_bloginfo('url'),
						'site_name' => get_bloginfo('name'),
						'site_language' => get_bloginfo('language'),
						'wordpress_version' => get_bloginfo('version'),
						'woocommerce_version' => WC()->version
					)
				)
			);

			// Call EBANX API to save a lead
			$request = wp_remote_post($url, $args);

			if (isset($request['body'])) {
				$data = json_decode($request['body']);

				// Update merchant
				update_option('_ebanx_lead_id', $data->id, false);
			}
		}

		/**
		 * A method that will be called every time settings are saved
		 *
		 * @return void
		 */
		public function on_save_settings() {
			// Delete flag that check if the api is ok
			delete_option('_ebanx_api_was_checked');
		}

		/**
		 * Update and inegrate the lead to the merchant using the merchant's integration key
		 *
		 * @return void
		 */
		public function update_lead() {
			$url = 'https://www.ebanx.com/business/en/dashboard/api/lead';
			$lead_id = get_option('_ebanx_lead_id');

			$args = array(
				'body' => array(
					'lead' => array(
						'id' => $lead_id,
						'integration_key' => $this->private_key
					)
				)
			);

			// Call EBANX API to save a lead
			wp_remote_post($url, $args);
		}

		/**
		 * Method that will be called when plugin is activated
		 *
		 * @return void
		 */
		public static function active_plugin() {
			self::save_merchant_infos();

			flush_rewrite_rules();
		}

		/**
		 * Method that will be called when plugin is deactivated
		 *
		 * @return void
		 */
		public static function deactivate_plugin() {
			flush_rewrite_rules();
		}

		/**
		 * It enables a tab on WooCommerce My Account page
		 *
		 * @param  string $title
		 * @return string Return the title to show on tab
		 */
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

		/**
		 * It enalbes the menu as a tab on My Account page
		 *
		 * @param  array $menu The all menus supported by WooCoomerce
		 * @return array       The new menu
		 */
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

		/**
		 * Check if the merchant's environment meets the requirements
		 *
		 * @return boolean|string
		 */
		public function get_environment_warning()
		{
			if (version_compare(phpversion(), WC_EBANX_MIN_PHP_VER, '<')) {
				$message = __('EBANX Payment Gateway for WooCommerce - The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'woocommerce-gateway-ebanx', 'woocommerce-gateway-ebanx');

				return sprintf($message, WC_EBANX_MIN_PHP_VER, phpversion());
			}

			if (!defined('WC_VERSION')) {
				return __('EBANX Payment Gateway for WooCommerce - It requires WooCommerce to be activated to work.', 'woocommerce-gateway-ebanx');
			}

			if (version_compare(WC_VERSION, WC_EBANX_MIN_WC_VER, '<')) {
				$message = __('EBANX Payment Gateway for WooCommerce - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'woocommerce-gateway-ebanx', 'woocommerce-gateway-ebanx');

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
			// Custom Order
			include_once SERVICES_DIR . 'class-wc-ebanx-custom-order.php';

			// Utils
			include_once SERVICES_DIR . 'class-wc-ebanx-constants.php';
			include_once SERVICES_DIR . 'class-wc-ebanx-notice.php';
			include_once SERVICES_DIR . 'class-wc-ebanx-hooks.php';
			include_once SERVICES_DIR . 'class-wc-ebanx-checker.php';
			include_once(SERVICES_DIR . 'class-wc-ebanx-flash.php');

			// Gateways
			include_once GATEWAYS_DIR . 'class-wc-ebanx-gateway.php';
			include_once GATEWAYS_DIR . 'class-wc-ebanx-redirect-gateway.php';
			include_once GATEWAYS_DIR . 'class-wc-ebanx-global-gateway.php';
			include_once GATEWAYS_DIR . 'class-wc-ebanx-credit-card-gateway.php';

			// Chile Gateways
			include_once GATEWAYS_DIR . 'class-wc-ebanx-servipag-gateway.php';
			include_once GATEWAYS_DIR . 'class-wc-ebanx-sencillito-gateway.php';

			// Brazil Gateways
			include_once GATEWAYS_DIR . 'class-wc-ebanx-banking-ticket-gateway.php';
			include_once GATEWAYS_DIR . 'class-wc-ebanx-credit-card-br-gateway.php';
			include_once GATEWAYS_DIR . 'class-wc-ebanx-account-gateway.php';
			include_once GATEWAYS_DIR . 'class-wc-ebanx-tef-gateway.php';

			// Mexico Gateways
			include_once GATEWAYS_DIR . 'class-wc-ebanx-credit-card-mx-gateway.php';
			include_once GATEWAYS_DIR . 'class-wc-ebanx-debit-card-gateway.php';
			include_once GATEWAYS_DIR . 'class-wc-ebanx-oxxo-gateway.php';

			// Colombia Gateways
			include_once GATEWAYS_DIR . 'class-wc-ebanx-baloto-gateway.php';
			include_once GATEWAYS_DIR . 'class-wc-ebanx-eft-gateway.php';

			// Peru Gateways
			include_once GATEWAYS_DIR . 'class-wc-ebanx-pagoefectivo-gateway.php';
			include_once GATEWAYS_DIR . 'class-wc-ebanx-safetypay-gateway.php';

			// Hooks/Actions
			include_once(SERVICES_DIR . 'class-wc-ebanx-my-account.php');
			include_once(SERVICES_DIR . 'class-wc-ebanx-one-click.php');
		}

		/**
		 * Get templates path.
		 *
		 * @return string
		 */
		public static function get_templates_path()
		{
			return TEMPLATES_DIR;
		}

		/**
		 * Add the gateways to WooCommerce.
		 *
		 * @param  array $methods WooCommerce payment methods.
		 *
		 * @return array
		 */
		public function add_gateway($methods)
		{
			// Global
			$methods[] = 'WC_EBANX_Global_Gateway';

			// Brazil
			$methods[] = 'WC_EBANX_Banking_Ticket_Gateway';
			$methods[] = 'WC_EBANX_Credit_Card_BR_Gateway';
			$methods[] = 'WC_EBANX_Tef_Gateway';
			$methods[] = 'WC_EBANX_Account_Gateway';

			// Mexico
			$methods[] = 'WC_EBANX_Credit_Card_MX_Gateway';
			$methods[] = 'WC_EBANX_Debit_Card_Gateway';
			$methods[] = 'WC_EBANX_Oxxo_Gateway';

			// Chile
			$methods[] = 'WC_EBANX_Sencillito_Gateway';
			$methods[] = 'WC_EBANX_Servipag_Gateway';

			// Colombia
			$methods[] = 'WC_EBANX_Baloto_Gateway';
			$methods[] = 'WC_EBANX_Eft_Gateway';

			// Peru
			$methods[] = 'WC_EBANX_Pagoefectivo_Gateway';
			$methods[] = 'WC_EBANX_Safetypay_Gateway';

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
			include_once TEMPLATES_DIR . 'views/html-notice-missing-woocommerce.php';
		}

		/**
		 * Log messages
		 *
		 * @param  string $message The log message
		 * @return void
		 */
		public static function log($message)
		{
			if (empty(self::$log)) self::$log = new WC_Logger();

			self::$log->add('woocommerce-gateway-ebanx', $message);

			if (defined('WP_DEBUG') && WP_DEBUG) {
				error_log($message);
			}
		}

		/**
		 * Renders the static assets needed to change admin panel to desired behavior
		 *
		 * @return void
		 */
		public function render_static_assets() {
			$this->adjust_dynamic_admin_options_sections();
			$this->resize_settings_menu_icon();
			$this->disable_ebanx_gateways();
		}

		/**
		 * Renders the script to manage the admin options script part of ebanx gateway configuration
		 *
		 * @return void
		 */
		public function adjust_dynamic_admin_options_sections() {
			if (!isset($_GET['section']) || $_GET['section'] !== 'ebanx-global') {
				return;
			}

			echo "
			<style>
				.wc-settings-sub-title.togglable {
					cursor: pointer;
					font-size: larger;
				}
				.wc-settings-sub-title.togglable:after {
					content: ' ▲';
				}
				.wc-settings-sub-title.togglable.closed:after {
					content: ' ▼';
				}
			</style>";

			wp_enqueue_script(
				'woocommerce_ebanx_payments_options',
				plugins_url('assets/js/payments-options.js', WC_EBANX::DIR),
				array('jquery'),
				WC_EBANX::VERSION,
				true
			);
			wp_enqueue_script(
				'woocommerce_ebanx_advanced_options',
				plugins_url('assets/js/advanced-options.js', WC_EBANX::DIR),
				array('jquery'),
				WC_EBANX::VERSION,
				true
			);
		}

		/**
		 * Renders the style tag to resize the menu icon to the correct size
		 *
		 * @return void
		 */
		public function resize_settings_menu_icon() {
			echo "<style> #adminmenu div.wp-menu-image.svg { background-size: auto 18px !important; } </style>";
		}

		/**
		 * Disabled all other EBANX gateways
		 *
		 * @return void
		 */
		public function disable_ebanx_gateways()
		{
			echo "
				<style>
					.woocommerce_page_wc-settings .subsubsub > li { display: none; }
					.woocommerce_page_wc-settings .woocommerce .form-table th { width: 250px !important; }
				</style>

				<script>
					var woocommerceSettings = jQuery('.woocommerce_page_wc-settings');

					if (woocommerceSettings.length) {
						var subsub = jQuery('.subsubsub > li');

						for (var i = 0, t = subsub.length; i < t; ++i) {
							var s = jQuery(subsub[i]);
							var sub = jQuery(s).find('a');

							if (sub.text().indexOf('EBANX -') === -1) {
								s.css({
									display: 'inline-block'
								});
							}
						}

						var last = subsub.filter(function () { return jQuery(this).css('display') === 'inline-block' }).last();

						last.html(last.html().replace(/ \| ?/g, ''));

						jQuery('.ebanx-select').select2();
					}
				</script>
			";
		}

		/**
		 * It inserts a EBANX Settings shortcut on Wordpress sidebar
		 *
		 * @return void
		 */
		public function ebanx_sidebar_shortcut()
		{
			add_menu_page(
				'EBANX Settings',
				'EBANX Settings',
				'administrator',

				// TODO: Create a dynamic url
				'admin.php?page=wc-settings&tab=checkout&section=ebanx-global',
				'',
				'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c3ZnIHdpZHRoPSIxNnB4IiBoZWlnaHQ9IjIwcHgiIHZpZXdCb3g9IjAgMCAxNiAyMCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj4gICAgICAgIDx0aXRsZT5lYmFueC1zdmc8L3RpdGxlPiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4gICAgPGRlZnM+PC9kZWZzPiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4gICAgICAgIDxnIGlkPSJlYmFueC1zdmciPiAgICAgICAgICAgIDxwb2x5Z29uIGlkPSJTaGFwZSIgZmlsbD0iIzFDNDE3OCIgcG9pbnRzPSIwLjExMTYyNzkwNyAwLjA5MDkwOTA5MDkgMTIuNTM5NTM0OSAxMCAwLjExMTYyNzkwNyAxOS45MDkwOTA5Ij48L3BvbHlnb24+ICAgICAgICAgICAgPHBvbHlnb24gaWQ9IlNoYXBlIiBmaWxsPSIjREFEQkRCIiBwb2ludHM9IjkuMTM0ODgzNzIgMTIuNzA5MDkwOSAwLjExMTYyNzkwNyAxOS45MDkwOTA5IDE1Ljk2Mjc5MDcgMTkuODkwOTA5MSI+PC9wb2x5Z29uPiAgICAgICAgICAgIDxwb2x5Z29uIGlkPSJTaGFwZSIgZmlsbD0iI0RBREJEQiIgcG9pbnRzPSIwLjExMTYyNzkwNyAwLjA5MDkwOTA5MDkgOS4xMzQ4ODM3MiA3LjI5MDkwOTA5IDE1Ljk2Mjc5MDcgMC4wOTA5MDkwOTA5Ij48L3BvbHlnb24+ICAgICAgICAgICAgPHBvbHlnb24gaWQ9IlNoYXBlIiBmaWxsPSIjMDA5M0QwIiBwb2ludHM9IjAuMTExNjI3OTA3IDE5LjkwOTA5MDkgOS4xMzQ4ODM3MiAxMi43MDkwOTA5IDYuNzUzNDg4MzcgMTAgMC4xMTE2Mjc5MDcgMTcuMiI+PC9wb2x5Z29uPiAgICAgICAgICAgIDxwb2x5Z29uIGlkPSJTaGFwZSIgZmlsbD0iIzAwQkNFNCIgcG9pbnRzPSIwLjExMTYyNzkwNyAyLjggMC4xMTE2Mjc5MDcgMTcuMiA2Ljc1MzQ4ODM3IDEwIj48L3BvbHlnb24+ICAgICAgICAgICAgPHBvbHlnb24gaWQ9IlNoYXBlIiBmaWxsPSIjMDA5M0QwIiBwb2ludHM9IjAuMTExNjI3OTA3IDAuMDkwOTA5MDkwOSA5LjEzNDg4MzcyIDcuMjkwOTA5MDkgNi43NTM0ODgzNyAxMCAwLjExMTYyNzkwNyAyLjgiPjwvcG9seWdvbj4gICAgICAgIDwvZz4gICAgPC9nPjwvc3ZnPg==',
				21
			);
		}

		/**
		 * It inserts informations about the order on admin order details
		 *
		 * @param  WC_Object $order The WC order object
		 * @return void
		 */
		public function ebanx_admin_order_details ($order) {
			if (in_array($order->payment_method, WC_EBANX_Constants::flatten(WC_EBANX_Constants::$EBANX_GATEWAYS_BY_COUNTRY))) {
				$payment_hash = get_post_meta($order->id, '_ebanx_payment_hash', true);

				wc_get_template(
					'admin-order-details.php',
					array(
						'order' => $order,
						'payment_hash' => $payment_hash,
						'payment_checkout_url' => get_post_meta($order->id, '_ebanx_checkout_url', true),
						'is_sandbox_mode' => $this->is_sandbox_mode,
						'dashboard_link' => "http://dashboard.ebanx.com/" . ($this->is_sandbox_mode ? 'test/' : '') . "payments/?hash=$payment_hash"
					),
					'woocommerce/ebanx/',
					WC_EBANX::get_templates_path()
				);
			}
		}
	}

	add_action('plugins_loaded', array('WC_EBANX', 'get_instance'));
}
