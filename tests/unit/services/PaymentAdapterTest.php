<?php

use Ebanx\Benjamin\Models\Person;
use EBANX\Tests\Helpers\Builders\CheckoutRequestBuilder;
use EBANX\Tests\Helpers\Builders\GlobalConfigBuilder;
use EBANX\Plugin\Services\WC_EBANX_Payment_Adapter;
use PHPUnit\Framework\TestCase;

class PaymentAdapterTest extends TestCase {

	/* @var array */
	private $names;

	/* @var GlobalConfigBuilder */
	private $global_config_builder;

	/* @var CheckoutRequestBuilder */
	private $checkout_request_builder;

	protected function setUp(): void {
		parent::setUp();

		$this->global_config_builder = new GlobalConfigBuilder();
		$this->checkout_request_builder = new CheckoutRequestBuilder();

		$this->names = [
			'ebanx_billing_brazil_cnpj' => 'ebanx_billing_brazil_cnpj',
			'ebanx_billing_brazil_document' => 'ebanx_billing_brazil_document',
			'ebanx_billing_brazil_person_type' => 'ebanx_billing_brazil_person_type',
			'ebanx_billing_argentina_document' => 'ebanx_billing_argentina_document',
			'ebanx_billing_chile_document' => 'ebanx_billing_chile_document',
			'ebanx_billing_colombia_document' => 'ebanx_billing_colombia_document',
			'ebanx_billing_peru_document' => 'ebanx_billing_peru_document'
		];

		// define('ABSPATH', __DIR__);
	}

	public function getPersonTypeCasesData() {
		return [
			[[], NULL, Person::TYPE_PERSONAL],
			[['cnpj'], NULL, Person::TYPE_BUSINESS],
			[['cpf', 'cnpj'], NULL, Person::TYPE_PERSONAL],
			[['cpf', 'cnpj'], 'cnpj', Person::TYPE_BUSINESS],
		];
	}

	public function getDocumentByCountryCasesData() {
		return [
			['12-34567890-1', 'get_argentinian_document', 'ebanx_billing_argentina_document'],
			['1234567890', 'get_chilean_document', 'ebanx_billing_chile_document'],
			['1245678901', 'get_colombian_document', 'ebanx_billing_colombia_document'],
			['1234678901', 'get_peruvian_document', 'ebanx_billing_peru_document'],
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

	public function testGetBrazilianDocument() {
		$expected_document = '123.456.789-90';
		$configs = $this->global_config_builder
			->with_brazil_taxes_options(['cpf'])
			->build();

		$this->checkout_request_builder
			->with_ebanx_billing_document('ebanx_billing_brazil_document', $expected_document)
			->build();

		$document = WC_EBANX_Payment_Adapter::get_brazilian_document($configs, $this->names, NULL);

		$this->assertEquals($expected_document, $document);
	}

	/**
	 * @dataProvider getDocumentByCountryCasesData()
	 *
	 * @param string $expected_document
	 * @param string $adapter_country_function
	 * @param string $document_type
	 *
	 * @throws Exception Shouldn't be thrown.
	 */
	public function testGetDocument($expected_document, $adapter_country_function, $document_type) {
		$this->checkout_request_builder
			->with_ebanx_billing_document($document_type, $expected_document)
			->build();

		$return_document = WC_EBANX_Payment_Adapter::$adapter_country_function($this->names, NULL);

		$this->assertEquals($expected_document, $return_document);
	}
}
