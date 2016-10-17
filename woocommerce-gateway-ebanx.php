<?php
/*
 * Plugin Name: WooCommerce EBANX Gateway
 * Plugin URI: https://wordpress.org/plugins/woocommerce-gateway-ebanx/
 * Description: Take credit card payments on your store using EBANX.
 * Author: Automattic
 * Author URI: http://woothemes.com/
 * Version: 3.0.2
 * Text Domain: woocommerce-gateway-ebanx
 * Domain Path: /languages
 *
 * Copyright (c) 2016 Automattic
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Required minimums and constants
 */
define( 'WC_EBANX_VERSION', '3.0.2' );
define( 'WC_EBANX_MIN_PHP_VER', '5.3.0' );
define( 'WC_EBANX_MIN_WC_VER', '2.5.0' );
define( 'WC_EBANX_MAIN_FILE', __FILE__ );
define( 'WC_EBANX_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

if ( ! class_exists( 'WC_EBANX' ) ) {

    class WC_EBANX {

        /**
         * @var Singleton The reference the *Singleton* instance of this class
         */
        private static $instance;

        /**
         * Returns the *Singleton* instance of this class.
         *
         * @return Singleton The *Singleton* instance.
         */
        public static function get_instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Private clone method to prevent cloning of the instance of the
         * *Singleton* instance.
         *
         * @return void
         */
        private function __clone() {}

        /**
         * Private unserialize method to prevent unserializing of the *Singleton*
         * instance.
         *
         * @return void
         */
        private function __wakeup() {}

        /**
         * Notices (array)
         * @var array
         */
        public $notices = array();

        /**
         * Protected constructor to prevent creating a new instance of the
         * *Singleton* via the `new` operator from outside of this class.
         */
        protected function __construct() {}
    }

    $GLOBALS['wc_ebanx'] = WC_EBANX::get_instance();

}

