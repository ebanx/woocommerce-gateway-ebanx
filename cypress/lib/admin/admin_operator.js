const adminOperator = (function (test) {
  const _private = {
    pages: {
      login: require('./pages/login')(test),
      settings: require('./pages/settings')(test),
    }
  };

  const { login, settings } = _private.pages;

  const $public = {
    openAdmin: function () {
      login
        .open()
        .login();

      return this;
    },

    openEbanxSettings: function () {
      settings
        .open();

      return this;
    },

    fillKeys: function (keys) {
      settings
        .fillSandboxKeys(keys);

      return this;
    },

    saveSettings: function () {
      settings
        .saveSettings();

      return this;
    }
  };

  return $public;
});

module.exports = adminOperator;