<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Flash {
	public static function display_flash_messages() {
		$flash_messages = self::get_flash_messages();
		$notices = new WC_EBANX_Notices_Notice();
		foreach ($flash_messages as $flash_message) {
			$notices
				->with_message($flash_message['message'])
				->with_type($flash_message['type']);
			if ($flash_message['dismissible']) {
				$notices->dismissible();
			}
			$notices->display();
		}
    }

    public static function enqueue_flash_message($message, $type = 'error', $dismissible = false) {
    	$flash_messages = maybe_unserialize(get_option('wp_flash_messages', array()));
    	$flash_messages[] = [
    		'message' => $message,
    		'type' => $type,
    		'dismissible' => $dismissible
    	];
    	update_option('wp_flash_messages', $flash_messages);
    }

    public static function get_flash_messages(){
    	$flash_messages = maybe_unserialize(get_option('wp_flash_messages', array()));
    	delete_option('wp_flash_messages');
    	return $flash_messages;
    }
}
