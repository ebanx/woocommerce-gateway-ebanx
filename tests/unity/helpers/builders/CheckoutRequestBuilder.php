<?php

namespace EBANX\Tests\Helpers\Builders;

class CheckoutRequestBuilder {
	private $ebanx_billing_brazil_person_type;
	private $ebanx_billing_brazil_document;
	private $ebanx_billing_argentina_document;

	public function __construct() {
		$_REQUEST = array();
	}

	public function with_ebanx_billing_brazil_person_type($person_type) {
		$this->ebanx_billing_brazil_person_type = $person_type;
		return $this;
	}

	public function with_ebanx_billing_brazil_document($document) {
		$this->ebanx_billing_brazil_document = $document;
		return $this;
	}

	public function with_ebanx_billing_argentina_document($document) {
		$this->ebanx_billing_argentina_document = $document;
		return $this;
	}

	public function build() {
		foreach (get_object_vars($this) as $attribute => $value) {
			if (!is_null($value)) $_REQUEST[$attribute] = $value;
		}
	}
}
