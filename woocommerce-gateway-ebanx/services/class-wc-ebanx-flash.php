<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_EBANX_Flash {
	/**
	 * The key we are using on wp_option
	 */
	const KEY = '_ebanx_wp_flash_messages';

	/**
	 * Enqueue every enqueued flash message to the admin_notices hook
	 *
	 * @return void
	 */
	public static function enqueue_admin_messages() {
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

	/**
	 * Enqueue a message to the next admin_init
	 *
	 * @param  string  $message     The message to enqueue
	 * @param  string  $type        The notice type
	 * @param  boolean $dismissible If the notice will be dismissible
	 * @return void
	 */
	public static function add_message($message, $type = 'error', $dismissible = false) {
		$flash_messages = maybe_unserialize(get_option(self::KEY, array()));
		$flash_messages[] = array(
			'message' => $message,
			'type' => $type,
			'dismissible' => $dismissible
		);
		update_option(self::KEY, $flash_messages);
	}

	/**
	 * Returns all the unqueued flash messages in an array
	 *
	 * @return array All the enqueued flash messages
	 */
	public static function get_messages(){
		$flash_messages = maybe_unserialize(get_option(self::KEY, array()));
		delete_option(self::KEY);
		return $flash_messages;
	}
}
