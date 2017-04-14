<?php

class WC_EBANX_Assets {
	/**
	 * Renders the static assets needed to change admin panel to desired behavior
	 *
	 * @return void
	 */
	public static function render() {
		self::adjust_dynamic_admin_options_sections();
		self::resize_settings_menu_icon();
		self::disable_ebanx_gateways();
	}

	/**
	 * The EBANX logo SVG base64 encoded with data:image protocol
	 *
	 * @return string
	 */
	public static function get_small_logo() {
		return <<<SVG
data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c3ZnIHdpZHRoPSIxNnB4IiBoZWlnaHQ9IjIwcHgiIHZpZXdCb3g9IjAgMCAxNiAyMCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj4gICAgICAgIDx0aXRsZT5lYmFueC1zdmc8L3RpdGxlPiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4gICAgPGRlZnM+PC9kZWZzPiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4gICAgICAgIDxnIGlkPSJlYmFueC1zdmciPiAgICAgICAgICAgIDxwb2x5Z29uIGlkPSJTaGFwZSIgZmlsbD0iIzFDNDE3OCIgcG9pbnRzPSIwLjExMTYyNzkwNyAwLjA5MDkwOTA5MDkgMTIuNTM5NTM0OSAxMCAwLjExMTYyNzkwNyAxOS45MDkwOTA5Ij48L3BvbHlnb24+ICAgICAgICAgICAgPHBvbHlnb24gaWQ9IlNoYXBlIiBmaWxsPSIjREFEQkRCIiBwb2ludHM9IjkuMTM0ODgzNzIgMTIuNzA5MDkwOSAwLjExMTYyNzkwNyAxOS45MDkwOTA5IDE1Ljk2Mjc5MDcgMTkuODkwOTA5MSI+PC9wb2x5Z29uPiAgICAgICAgICAgIDxwb2x5Z29uIGlkPSJTaGFwZSIgZmlsbD0iI0RBREJEQiIgcG9pbnRzPSIwLjExMTYyNzkwNyAwLjA5MDkwOTA5MDkgOS4xMzQ4ODM3MiA3LjI5MDkwOTA5IDE1Ljk2Mjc5MDcgMC4wOTA5MDkwOTA5Ij48L3BvbHlnb24+ICAgICAgICAgICAgPHBvbHlnb24gaWQ9IlNoYXBlIiBmaWxsPSIjMDA5M0QwIiBwb2ludHM9IjAuMTExNjI3OTA3IDE5LjkwOTA5MDkgOS4xMzQ4ODM3MiAxMi43MDkwOTA5IDYuNzUzNDg4MzcgMTAgMC4xMTE2Mjc5MDcgMTcuMiI+PC9wb2x5Z29uPiAgICAgICAgICAgIDxwb2x5Z29uIGlkPSJTaGFwZSIgZmlsbD0iIzAwQkNFNCIgcG9pbnRzPSIwLjExMTYyNzkwNyAyLjggMC4xMTE2Mjc5MDcgMTcuMiA2Ljc1MzQ4ODM3IDEwIj48L3BvbHlnb24+ICAgICAgICAgICAgPHBvbHlnb24gaWQ9IlNoYXBlIiBmaWxsPSIjMDA5M0QwIiBwb2ludHM9IjAuMTExNjI3OTA3IDAuMDkwOTA5MDkwOSA5LjEzNDg4MzcyIDcuMjkwOTA5MDkgNi43NTM0ODgzNyAxMCAwLjExMTYyNzkwNyAyLjgiPjwvcG9seWdvbj4gICAgICAgIDwvZz4gICAgPC9nPjwvc3ZnPg==
SVG;
	}

	/**
	 * The EBANX logo with text SVG base64 encoded with data:image protocol
	 */
	public static function get_logo() {
		return <<<SVG
data:image/svg+xml;base64,DQo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgdmlld0JveD0iMCAwIDI3OS4xNSA1Mi42MyI+PGRlZnM+PHN0eWxlPi5jbHMtMXtmaWxsOiMyNjk5ZDY7fS5jbHMtMntmaWxsOiMyZjRhNzQ7fTwvc3R5bGU+PC9kZWZzPjx0aXRsZT5Bc3NldCAxPC90aXRsZT48ZyBpZD0iTGF5ZXJfMiIgZGF0YS1uYW1lPSJMYXllciAyIj48ZyBpZD0iQ2FtYWRhXzEiIGRhdGEtbmFtZT0iQ2FtYWRhIDEiPjxwYXRoIGNsYXNzPSJjbHMtMSIgZD0iTS40NywwLDI0LjM1LDE5LjUsNDEuNzUuNjhhLjQxLjQxLDAsMCwwLS4zLS42OFoiLz48cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0uNDcsNTIuNjNsMjMuODgtMTkuNSwxNy40LDE4LjgxYS40MS40MSwwLDAsMS0uMy42OFoiLz48cGF0aCBjbGFzcz0iY2xzLTIiIGQ9Ik0uNzMuMSwzMi40NSwyNmEuNDUuNDUsMCwwLDEsMCwuNjlMLjczLDUyLjUzQS40NS40NSwwLDAsMSwwLDUyLjE4Vi40NUEuNDUuNDUsMCwwLDEsLjczLjFaIi8+PHBhdGggY2xhc3M9ImNscy0yIiBkPSJNMTMxLjkyLDM3LjM1YzAsOS03LjI5LDE1LjI1LTE2LjM4LDE1LjI1SDkzLjY1YS40My40MywwLDAsMS0uNDMtLjQzVi40M0EuNDIuNDIsMCwwLDEsOTMuNjUsMEgxMTRjOC44NywwLDE2LDYsMTYsMTQuOGExMywxMywwLDAsMS01LDEwLjUyQTEzLjMzLDEzLjMzLDAsMCwxLDEzMS45MiwzNy4zNVpNMTAzLjU5LDEwLjA2VjIwLjgzYS40NC40NCwwLDAsMCwuNDQuNDRIMTE0YTUuNjIsNS42MiwwLDAsMCw1LjYxLTYuMzcsNS44MSw1LjgxLDAsMCwwLTUuODQtNS4yaC05Ljc4QS4zNS4zNSwwLDAsMCwxMDMuNTksMTAuMDZabTE4LDI2LjEzYTYuMTgsNi4xOCwwLDAsMC02LjIzLTUuNjdIMTAzLjU5djEyLjRoMTEuOTVBNiw2LDAsMCwwLDEyMS42MSwzNi4xOVoiLz48cGF0aCBjbGFzcz0iY2xzLTIiIGQ9Ik0xNjkuMDYsNDMuMTRoLTIwLjRhLjM5LjM5LDAsMCwwLS4zNy4yN2wtMyw4LjkzYS4zOS4zOSwwLDAsMS0uMzcuMjdIMTM0LjU4YS4zOS4zOSwwLDAsMS0uMzctLjUyTDE1Mi4zNC4yN2EuMzkuMzksMCwwLDEsLjM3LS4yNkgxNjVhLjM5LjM5LDAsMCwxLC4zNy4yNmwxOC4yMSw1MS44MWEuMzkuMzksMCwwLDEtLjM3LjUySDE3Mi43OGEuMzkuMzksMCwwLDEtLjM3LS4yN2wtMy04LjkzQS4zOS4zOSwwLDAsMCwxNjkuMDYsNDMuMTRabS0zLjEyLTEwLjIxLTYuNjctMTkuODdhLjM5LjM5LDAsMCwwLS43NCwwbC02LjY3LDE5Ljg3YS4zOS4zOSwwLDAsMCwuMzcuNTJoMTMuMzRBLjM5LjM5LDAsMCwwLDE2NS45MywzMi45M1oiLz48cGF0aCBjbGFzcz0iY2xzLTIiIGQ9Ik0yMjkuNjcuNDNWNTIuMThhLjQyLjQyLDAsMCwxLS40Mi40MkgyMjJhLjQyLjQyLDAsMCwxLS4zNS0uMThsLTIyLjQxLTMyVjUyLjE4YS40Mi40MiwwLDAsMS0uNDIuNDJIMTg5LjNhLjQyLjQyLDAsMCwxLS40Mi0uNDJWLjQzQS40Mi40MiwwLDAsMSwxODkuMywwaDcuMjVhLjQyLjQyLDAsMCwxLC4zNS4xOGwyMi40MSwzMS45Vi40M2EuNDIuNDIsMCwwLDEsLjQyLS40Mmg5LjUyQS40Mi40MiwwLDAsMSwyMjkuNjcuNDNaIi8+PHBhdGggY2xhc3M9ImNscy0yIiBkPSJNMjY3LjU2LDUyLjM5bC05LjcxLTE2LjI4YS40NC40NCwwLDAsMC0uNzYsMGwtOS43MSwxNi4yOGEuNDQuNDQsMCwwLDEtLjM4LjIxaC0xMC43YS40NC40NCwwLDAsMS0uMzgtLjY3bDE1LjU1LTI2LjA4YS40NC40NCwwLDAsMCwwLS40NUwyMzYuNzUuNjdhLjQ0LjQ0LDAsMCwxLC4zOC0uNjZoMTAuN2EuNDQuNDQsMCwwLDEsLjM4LjIxbDguODksMTQuOTNhLjQ0LjQ0LDAsMCwwLC43NiwwTDI2Ni43My4yMmEuNDQuNDQsMCwwLDEsLjM4LS4yMWgxMC43YS40NC40NCwwLDAsMSwuMzguNjdMMjYzLjQ3LDI1LjMzYS40NC40NCwwLDAsMCwwLC40NWwxNS42MiwyNi4xNmEuNDQuNDQsMCwwLDEtLjM4LjY3SDI2Ny45NEEuNDQuNDQsMCwwLDEsMjY3LjU2LDUyLjM5WiIvPjxwYXRoIGNsYXNzPSJjbHMtMiIgZD0iTTY2LjE4LDQyLjY2YTIuNzUsMi43NSwwLDAsMS0yLjQtMi43MlYzMS4zYS40MS40MSwwLDAsMSwuNDEtLjQxaDE5LjFhLjQxLjQxLDAsMCwwLC40MS0uNDF2LTlhLjQxLjQxLDAsMCwwLS40MS0uNDFINjQuMThhLjQxLjQxLDAsMCwxLS40MS0uNDFWMTAuMzNhLjQxLjQxLDAsMCwxLC40MS0uNDFoMjFhLjQxLjQxLDAsMCwwLC40MS0uNDFWLjQyQS40MS40MSwwLDAsMCw4NS4xNiwwSDUzLjgxYS40MS40MSwwLDAsMC0uNDEuNDFWNDIuNzlhOS44Myw5LjgzLDAsMCwwLDkuODMsOS44M2gyMi43di0xMFoiLz48L2c+PC9nPjwvc3ZnPg==
SVG;
	}

	// PRIVATE

	/**
	 * Renders the script to manage the admin options script part of ebanx gateway configuration
	 *
	 * @return void
	 */
	private static function adjust_dynamic_admin_options_sections() {
		if ( ! self::is_in_ebanx_settings() ) {
			return;
		}

		self::render_stylesheet('settings-modal');
		self::render_stylesheet('toggleable-options');

		self::render_script('settings-modal', array('jquery'));
		self::render_script('fetch-keys', array('jquery'));
		self::render_script('payments-options', array('jquery'));
		self::render_script('advanced-options', array('jquery'));
	}

	/**
	 * Renders the style tag to resize the menu icon to the correct size
	 *
	 * @return void
	 */
	private static function resize_settings_menu_icon() {
		self::render_stylesheet('settings-menu-icon');
	}

	/**
	 * Disable all EBANX gateways so only our global settings one is displayed
	 *
	 * @return void
	 */
	private static function disable_ebanx_gateways() {
		self::render_stylesheet('disable-ebanx-gateways');
		self::render_inline_script('disable-ebanx-gateways');
	}

	/**
	 * When ebanx settings page is open
	 *
	 * @return boolean
	 */
	private static function is_in_ebanx_settings() {
		return WC_EBANX_Request::has('section')
			&& WC_EBANX_Request::read('section') === 'ebanx-global';
	}

	/**
	 * Prints a style tag with a css content directly to the page body
	 * Files must be under assets/css/
	 *
	 * @param  string $filename Filename without extension
	 * @return void
	 */
	private static function render_stylesheet($filename) {
		$file_path = plugins_url('assets/css/'.$filename.'.css', WC_EBANX::DIR);
		$contents = file_get_contents($file_path);
		echo '<style>'.$contents.'</style>';
	}

	/**
	 * Enqueues a script for rendering at the bottom of page body
	 * Files must be under assets/js/
	 *
	 * @param  string $filename     Filename without extension
	 * @param  array  $dependencies An array with wp script names of dependencies
	 * @return void
	 */
	private static function render_script($filename, $dependencies = array()) {
		$script_name = 'woocommerce_ebanx_'.str_replace('-', '_', $filename);
		$file_path = plugins_url('assets/js/'.$filename.'.js', WC_EBANX::DIR);

		wp_enqueue_script(
			$script_name,
			$file_path,
			$dependencies,
			WC_EBANX::get_plugin_version(),
			true
		);
	}

	private static function render_inline_script($filename) {
		$file_path = plugins_url('assets/js/'.$filename.'.js', WC_EBANX::DIR);
		$contents = file_get_contents($file_path);
		echo '<script>'.$contents.'</script>';
	}
}
