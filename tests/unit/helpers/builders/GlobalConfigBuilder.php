<?php

namespace EBANX\Tests\Helpers\Builders;

class GlobalConfigBuilder {

	/* @var array */
	public $settings;

	public function __construct() {
		$this->settings = array();
	}

	public function with_brazil_taxes_options($document_types) {
		$this->settings['brazil_taxes_options'] = $document_types;
		return $this;
	}

	public function build() {
		return $this;
	}
}
