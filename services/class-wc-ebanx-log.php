<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_EBANX_Log {
    static private function get_common_data() {
        $environment = new WC_EBANX_Environment();
        return array(
            'platform' => array(
                'name' => 'WORDPRESS',
                'version' => $environment->platform->version,
                'theme' => self::get_theme_data(),
                'plugins' => self::get_plugins_data(),
            ),
            'server' => array(
                'language' => $environment->interpreter,
                'web_server' => $environment->web_server,
                'database_server' => $environment->database_server,
                'os' => $environment->operating_system,
            ),
        );
    }

    /**
     * @return array
     */
    private static function get_plugins_data()
    {
        return array();
    }

    /**
     * @return array
     */
    private static function get_theme_data()
    {
        return array();
    }
}
