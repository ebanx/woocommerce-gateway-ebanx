<?php
/**
 * Plugin Name: EBANX Payment Gateway for WooCommerce
 * Plugin URI: https://www.ebanx.com/business/en/developers/integrations/extensions-and-plugins/woocommerce-plugin
 * Description: Offer Latin American local payment methods & increase your conversion rates with the solution used by AliExpress, AirBnB and Spotify in Brazil.
 * Author: EBANX
 * Author URI: https://www.ebanx.com/business/en
 * Version: 1.7.1
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
define('INCLUDES_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR);
define('SERVICES_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR);

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
		const VERSION = '1.7.1';

		const DIR = __FILE__;

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		private static $log;

		private static $endpoint = 'ebanx-credit-cards';

		private static $menu_name = 'EBANX - Credit Cards';

		/**
		 * Initialize the plugin public actions.
		 */
		private function __construct()
		{
			include_once(INCLUDES_DIR . 'notices/class-wc-ebanx-notices-notice.php');
			$this->notices = new WC_EBANX_Notices_Notice();

			if (!class_exists('WC_Payment_Gateway')) {
				$this->notices
					->with_view('missing-woocommerce')
					->enqueue();
				return;
			}
			/**
			 * Actions
			 */
			add_action('plugins_loaded', array($this, 'plugins_loaded'));

			add_action('init', array($this, 'my_account_endpoint'));
			add_action('init', array($this, 'ebanx_router'));

			add_action('admin_footer', array($this, 'render_static_assets'), 0);

			add_action('admin_init', array($this, 'check_environment'));
			add_action('admin_init', array($this, 'ebanx_sidebar_shortcut'));

			if ( empty( $_POST ) ) {
				add_action('admin_init', array($this, 'setup_configs'), 10);
				add_action('admin_init', array($this, 'check_merchant_api_keys'), 20);
			}

			add_action('woocommerce_account_' . self::$endpoint . '_endpoint', array($this, 'my_account_template'));

			/**
			 * Filters
			 */
			add_filter('query_vars', array($this, 'my_account_query_vars'), 0);
			add_filter('woocommerce_account_menu_items', array($this, 'my_account_menus'));
			add_filter('the_title', array($this, 'my_account_menus_title'));

			/**
			 * i18n
			 */
			$this->enable_i18n();

			$this->includes();

			add_filter('woocommerce_payment_gateways', array($this, 'add_gateway'));
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));

			add_action('woocommerce_settings_saved', array($this, 'setup_configs'), 10);
			add_action('woocommerce_settings_saved', array($this, 'update_lead'), 20);
			add_action('woocommerce_settings_saved', array($this, 'check_merchant_api_keys'), 20);
		}

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
		 * Call when the plugins are loaded
		 *
		 * @return void
		 */
		public function plugins_loaded()
		{
			if (self::get_environment_warning()) {
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
				'ebanx' => true
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
		 * Check if the merchant environment
		 *
		 * @return void
		 */
		public function check_environment()
		{
			$environment_warning = self::get_environment_warning();

			if ($environment_warning && is_plugin_active(plugin_basename(__FILE__))) {
				$this->notices
					->with_message($environment_warning)
					->with_type('error')
					->persistent()
					->enqueue();
			}
		}

		/**
		 * Check if the merchant's integration keys are valid
		 *
		 * @return boolean
		 */
		public function check_merchant_api_keys()
		{
			\Ebanx\Config::set(array('integrationKey' => $this->private_key, 'testMode' => $this->is_sandbox_mode));

			try {
				$res = \Ebanx\Ebanx::getMerchantIntegrationProperties(array('integrationKey' => $this->private_key));
				$res_public = \Ebanx\Ebanx::getMerchantIntegrationPublicProperties(array('public_integration_key' => $this->public_key));

				if ( $res->status !== 'SUCCESS' || $res_public->status !== 'SUCCESS' ) {
					throw new Exception('CONNECTION-ERROR');
				}
			} catch (Exception $e) {
				$api_url = 'https://api.ebanx.com';

				$message = sprintf('Could not connect to EBANX servers. Please check if your server can reach our API (<a href="%1$s">%1$s</a>) and your integrations keys are correct.', $api_url);
				$this->notices
					->with_message($message)
					->with_type('error')
					->persistent();
				if (empty($_POST)) {
					$this->notices->enqueue();
					return;
				}
				$this->notices->display();
			}
		}


		/**
		 * Check if the merchant's environment meets the requirements
		 *
		 * @return boolean|string
		 */
		public static function get_environment_warning()
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
			include_once(INCLUDES_DIR . 'class-wc-ebanx-credit-card-br-gateway.php');
			include_once(INCLUDES_DIR . 'class-wc-ebanx-credit-card-mx-gateway.php');
			include_once(INCLUDES_DIR . 'class-wc-ebanx-debit-card-gateway.php');
			include_once(INCLUDES_DIR . 'class-wc-ebanx-oxxo-gateway.php');
			include_once(INCLUDES_DIR . 'class-wc-ebanx-servipag-gateway.php');
			include_once(INCLUDES_DIR . 'class-wc-ebanx-tef-gateway.php');
			include_once(INCLUDES_DIR . 'class-wc-ebanx-eft-gateway.php');
			include_once(INCLUDES_DIR . 'class-wc-ebanx-baloto-gateway.php');
			include_once(INCLUDES_DIR . 'class-wc-ebanx-one-click.php');
			include_once(SERVICES_DIR . 'class-wc-ebanx-hooks.php');
			include_once(INCLUDES_DIR . 'notices/class-wc-ebanx-notices-notice.php');
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
		 * Add the gateways to WooCommerce.
		 *
		 * @param  array $methods WooCommerce payment methods.
		 *
		 * @return array
		 */
		public function add_gateway($methods)
		{
			$methods[] = 'WC_EBANX_Global_Gateway';
			$methods[] = 'WC_EBANX_Banking_Ticket_Gateway';
			$methods[] = 'WC_EBANX_Credit_Card_BR_Gateway';
			$methods[] = 'WC_EBANX_Credit_Card_MX_Gateway';
			$methods[] = 'WC_EBANX_Debit_Card_Gateway';
			$methods[] = 'WC_EBANX_Oxxo_Gateway';
			$methods[] = 'WC_EBANX_Sencillito_Gateway';
			$methods[] = 'WC_EBANX_Servipag_Gateway';
			$methods[] = 'WC_EBANX_Tef_Gateway';
			$methods[] = 'WC_EBANX_Pagoefectivo_Gateway';
			$methods[] = 'WC_EBANX_Safetypay_Gateway';
			$methods[] = 'WC_EBANX_Eft_Gateway';
			$methods[] = 'WC_EBANX_Baloto_Gateway';
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
			include dirname(self::DIR) . '/includes/admin/views/html-notice-missing-woocommerce.php';
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
			$this->hide_advanced_options_section();
			$this->resize_settings_menu_icon();
			$this->disable_ebanx_gateways();
		}

		/**
		 * Renders the script to manage the advanced options script part of ebanx gateway configuration
		 *
		 * @return void
		 */
		public function hide_advanced_options_section() {
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
	}

	add_action('plugins_loaded', array('WC_EBANX', 'get_instance'));
}
