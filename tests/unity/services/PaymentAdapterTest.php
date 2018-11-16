<?php

use Ebanx\Benjamin\Models\Person;
use EBANX\Tests\Helpers\Builders\GlobalConfigBuilder;
use PHPUnit\Framework\TestCase;

class PaymentAdapterTest extends TestCase {
	/* @var array */
	private $names;

	/* @var GlobalConfigBuilder */
	private $global_config_builder;

	protected function setUp() {
		parent::setUp();

		$this->global_config_builder = new GlobalConfigBuilder();
		$this->names = [
			'ebanx_billing_brazil_cnpj' => 'ebanx_billing_brazil_cnpj',
			'ebanx_billing_brazil_document' => 'ebanx_billing_brazil_document',
			'ebanx_billing_brazil_person_type' => 'ebanx_billing_brazil_person_type',
		];
	}

	public function testGetPersonType_WithCnpj_ShouldReturnBusiness() {
		$configs = $this->global_config_builder
			->with_brazil_taxes_options(['cnpj'])
			->build();

		$person_type = WC_EBANX_Payment_Adapter::get_person_type($configs, $this->names);

		$this->assertEquals(Person::TYPE_BUSINESS, $person_type);
	}

	public function testGetPersonType_WithEmptyConfigs_ShouldReturnPersonal() {
		$configs = $this->global_config_builder->build();

		$person_type = WC_EBANX_Payment_Adapter::get_person_type($configs, $this->names);

		$this->assertEquals(Person::TYPE_PERSONAL, $person_type);
	}

	public function testGetPersonType_WithCpfAndCnpjAndNoRequest_ShouldReturnCpf() {
		$configs = $this->global_config_builder
			->with_brazil_taxes_options(['cpf', 'cnpj'])
			->build();

		$person_type = WC_EBANX_Payment_Adapter::get_person_type($configs, $this->names);

		$this->assertEquals(Person::TYPE_PERSONAL, $person_type);
	}

	public function testGetPersonType_WithCpfAndCnpjAndCnpjOnRequest_ShouldReturnCnpj() {
		$_REQUEST['ebanx_billing_brazil_person_type'] = 'cnpj';

		$configs = $this->global_config_builder
			->with_brazil_taxes_options(['cpf', 'cnpj'])
			->build();

		$person_type = WC_EBANX_Payment_Adapter::get_person_type($configs, $this->names);

		$this->assertEquals(Person::TYPE_BUSINESS, $person_type);
	}
}
