<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Flash {
	const KEY = '_ebanx_wp_flash_messages';

	public static function display_messages() {
		$flash_messages = self::get_messages();
		$notices = new WC_EBANX_Notice();
		foreach ($flash_messages as $flash_message) {
			$notices
				->with_message($flash_message['message'])
				->with_type($flash_message['type']);
			if ($flash_message['dismissible']) {
				$notices->dismissible();
			}
			$notices->enqueue();
		}
    }

    public static function enqueue_message($message, $type = 'error', $dismissible = false) {
    	$flash_messages = maybe_unserialize(get_option(self::KEY, array()));
    	$flash_messages[] = [
    		'message' => $message,
    		'type' => $type,
    		'dismissible' => $dismissible
    	];
    	update_option(self::KEY, $flash_messages);
    }

    public static function get_messages(){
    	$flash_messages = maybe_unserialize(get_option(self::KEY, array()));
    	delete_option(self::KEY);
    	return $flash_messages;
    }
}
