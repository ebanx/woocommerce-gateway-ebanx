const Faker = require('faker');
const CPF = require('cpf_cnpj').CPF;
const CNPJ = require('cpf_cnpj').CNPJ;
let site = {};
let mock = {};

describe('Site', () => {

  context('Payment', () => {
    beforeEach(function () {
      Faker.locale = 'pt_BR';

      site = require('../../lib/site/site_operator')(cy);
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
    });

    it('Make a Boleto Payment', () => {
      mock.country = 'Brazil';
      mock.state = 'Paraná';
      mock.brazilDocument = CPF.generate(true);
      mock.brazilBirthdate = '01/01/1970';
      mock.postcode = '80010010';

      site.makePaymentBoleto(mock);
    });

    it('Make a Credit Card Payment using Visa', () => {

    });

  });

});