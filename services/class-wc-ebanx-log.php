<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_EBANX_Log {
    public static function get_platform_info() {
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

    private static function get_plugins_data()
    {
        return array_map(function ($plugin) {
            return get_file_data(
                WC_EBANX_DIR.'../'.$plugin,
                array(
                    'version' => 'version',
                    'Plugin Name' => 'Plugin Name',
                    'Description' => 'Description',
                    'Plugin URI' => 'Plugin URI',
                    'Author' => 'Author',
                    'License' => 'License',
                    'Author URI' => 'Author URI',
                )
            );
        }, get_option('active_plugins'));
    }

    private static function get_theme_data()
    {
        $wp_theme = wp_get_theme();

        return [
            'Name' => $wp_theme->get('Name'),
            'ThemeURI' => $wp_theme->get('ThemeURI'),
            'Description' => $wp_theme->get('Description'),
            'Author' => $wp_theme->get('Author'),
            'AuthorURI' => $wp_theme->get('AuthorURI'),
            'Version' => $wp_theme->get('Version'),
            'Template' => $wp_theme->get('Template'),
            'Status' => $wp_theme->get('Status'),
            'Tags' => $wp_theme->get('Tags'),
            'TextDomain' => $wp_theme->get('TextDomain'),
            'DomainPath' => $wp_theme->get('DomainPath'),
        ];
    }
}
