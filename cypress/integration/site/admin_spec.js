const defaults = require('../../lib/defaults');

global.ebanx = {};
let admin = {};

describe('Admin', () => {

  context('Settings', () => {
    before(function () {
      admin = require('../../lib/admin/admin_operator')(cy);
    });

    it('Fill sandbox keys and save settings', () => {
      admin
        .openAdmin()
        .openEbanxSettings()
        .fillKeys({
          integration_key: defaults.api.integration_key,
          public_key: defaults.api.public_key
        })
        .saveSettings();
    });

    it('Fill instalments number and save settings', () => {
      admin
        .openAdmin()
        .openEbanxSettings()
        .fillInstalments(defaults.admin.instalments)
        .saveSettings();
    });
  });

});