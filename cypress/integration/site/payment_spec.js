const Faker = require('faker');
const CPF = require('cpf_cnpj').CPF;
const CNPJ = require('cpf_cnpj').CNPJ;
const defaults = require('../../lib/defaults');
let site = {};
let api = {};
let mock = {};

global.ebanx = {};

describe('Site', () => {

  context('Brazil Payments', () => {
    beforeEach(function () {
      Faker.locale = 'pt_BR';

      site = require('../../lib/site/site_operator')(cy);
      api = require('../../lib/pay/api_operator')(cy);

      mock = {
        firstName: Faker.name.firstName(),
        lastName: Faker.name.lastName(),
        company: Faker.company.companyName(),
        email: Faker.internet.email(),
        phone: Faker.phone.phoneNumber(),
        country: Faker.address.country(),
        address: Faker.address.streetAddress(),
        state: Faker.address.state(),
        postcode: Faker.address.zipCode(),
        city: Faker.address.city()
      };

      mock.country = 'Brazil';
      mock.state = 'Paraná';
      mock.brazilDocument = CPF.generate(true);
      mock.brazilBirthdate = '01/01/1970';
      mock.postcode = '80010010';
    });

    it('Make a Boleto Payment', () => {
      site.makePaymentBoleto(mock, hash => {
        api.assertPaymentStatus(hash, 'PE');
      });
    });

    it('Make a Credit Card Payment using Visa', () => {
      let cc_data = {
        cvv: Faker.random.number({ min: 100, max: 999 }).toString(),
        due_date: '02 / 25',
        card_name: `${Faker.name.firstName} ${Faker.name.lastName}`,
        number: defaults.site.payments.credit_card.visa
      };

      site.makePaymentCreditCardToBrazil(mock, cc_data, hash => {
        api.assertPaymentStatus(hash, 'CO');
      });
    });

    it('Make a Credit Card Payment using Visa with Instalments', () => {
      let cc_data = {
        cvv: Faker.random.number({ min: 100, max: 999 }).toString(),
        due_date: `02 / ${Faker.random.number({ min: 20, max: 30 }) }`,
        card_name: `${Faker.name.firstName} ${Faker.name.lastName}`,
        number: defaults.site.payments.credit_card.visa,
        instalments: Faker.random.number({ min: 2, max: 12 }).toString()
      };

      site.makePaymentCreditCardToBrazil(mock, cc_data, hash => {
        api.assertPaymentStatus(hash, 'CO');
      });
    });

  });

});