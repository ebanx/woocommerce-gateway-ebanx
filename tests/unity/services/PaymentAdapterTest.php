<?php

use Ebanx\Benjamin\Models\Person;
use EBANX\Tests\Helpers\Builders\CheckoutRequestBuilder;
use EBANX\Tests\Helpers\Builders\GlobalConfigBuilder;
use PHPUnit\Framework\TestCase;

class PaymentAdapterTest extends TestCase {

	/* @var array */
	private $names;

	/* @var GlobalConfigBuilder */
	private $global_config_builder;

	/* @var CheckoutRequestBuilder */
	private $checkout_request_builder;

	protected function setUp() {
		parent::setUp();

		$this->global_config_builder = new GlobalConfigBuilder();
		$this->checkout_request_builder = new CheckoutRequestBuilder();

		$this->names = [
			'ebanx_billing_brazil_cnpj' => 'ebanx_billing_brazil_cnpj',
			'ebanx_billing_brazil_document' => 'ebanx_billing_brazil_document',
			'ebanx_billing_brazil_person_type' => 'ebanx_billing_brazil_person_type',
		];
	}

	public function getPersonTypeCasesData() {
		return [
			[[], NULL, Person::TYPE_PERSONAL],
			[['cnpj'], NULL, Person::TYPE_BUSINESS],
			[['cpf', 'cnpj'], NULL, Person::TYPE_PERSONAL],
			[['cpf', 'cnpj'], 'cnpj', Person::TYPE_BUSINESS],
		];
	}

	/**
	 * @dataProvider getPersonTypeCasesData()
	 *
	 * @param array $possible_document_types
	 * @param string $used_document_type
	 * @param string $expected_person_type
	 *
	 * @throws Exception Shouldn't be thrown.
	 */
	public function testGetPersonType($possible_document_types, $used_document_type, $expected_person_type) {
		$configs = $this->global_config_builder
			->with_brazil_taxes_options($possible_document_types)
			->build();

		$this->checkout_request_builder
			->with_ebanx_billing_brazil_person_type($used_document_type)
			->build();

		$person_type = WC_EBANX_Payment_Adapter::get_person_type($configs, $this->names);

		$this->assertEquals($expected_person_type, $person_type);
	}
}
