/* global Cypress */
export default class Login {
  constructor(cy) {
    this.cy = cy;
  }

  login() {
    this.cy
      .visit(`${Cypress.env('DEMO_URL')}/wp-admin`)
      .get('#user_login', { timeout: 30000 })
      .should('be.visible')
      .then($loginInput => {
          $loginInput[0].value = Cypress.env('ADMIN_USER');
      })
      .get('#user_pass')
      .should('be.visible')
      .type(Cypress.env('ADMIN_PASSWORD'))
      .get('#wp-submit', { timeout: 30000 })
      .should('be.visible')
      .click();
  }

  logout() {
    this.cy
      .visit(`${Cypress.env('DEMO_URL')}/wp-admin`)
      .get('#wp-admin-bar-logout > a:nth-child(1)')
      .click({force: true})
      .get('.message')
      .should('be.visible')
      .contains('You are now logged out.');
  }
}
