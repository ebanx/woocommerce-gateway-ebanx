<?php

define('ABSPATH', realpath('.'));
define('WC_EBANX_VENDOR_DIR', realpath('.') . '/vendor/');
define('IS_TEST', true);


require __DIR__ . '/includes/wc_class.php';
require WC_EBANX_VENDOR_DIR . '/autoload.php';

require_once realpath('.') . '/gateways/class-wc-ebanx-global-gateway.php';

function autoload_services($class_name) {
	$base_dir = realpath('.')  . '/services/';
	$relative_class = 'class-' . str_replace('_', '-', strtolower($class_name));
	$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
	if (file_exists($file)) {
		require_once $file;
	}
}

function autoload_builders($class_name) {
	$base_dir = __DIR__ . '/helpers/builders/';
	$relative_class = $class_name;
	$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
	if (file_exists($file)) {
		require $file;
	}
}

spl_autoload_register('autoload_builders');
spl_autoload_register('autoload_services');
