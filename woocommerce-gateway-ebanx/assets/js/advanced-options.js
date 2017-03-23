;(function($){
  // Cookie management
  var createCookie = function(name, value) {
    document.cookie = name + "=" + value + "; path=/";
  };

  var getCookie = function(name) {
    if (document.cookie.length == 0) {
      return "";
    }

    var start = document.cookie.indexOf(name + "=");
    if (start == -1) {
      return "";
    }

    var start = start + name.length + 1;
    var end = document.cookie.indexOf(";", start);
    if (end == -1) {
      end = document.cookie.length;
    }

    return unescape(document.cookie.substring(start, end));
  };

  // Checkout manager managed fields
  var modesField = $('#woocommerce_ebanx-global_brazil_taxes_options');
  var fields = $('.ebanx-checkout-manager-field');
  var fieldsToggler = $('#woocommerce_ebanx-global_checkout_manager_enabled');
  var fieldBrazilTaxes = $('.brazil-taxes');
  var countryPayments = {
    brazil: $('#woocommerce_ebanx-global_brazil_payment_methods'),
    chile: $('#woocommerce_ebanx-global_chile_payment_methods')
  };
  

  var disableFields = function(jqElementList){
    jqElementList.closest('tr').hide();
  };

  var enableFields = function(jqElementList){
    jqElementList.closest('tr').show();
  };

  var updateFields = function(){
    var modes = modesField.val();
    var brazil_val = countryPayments.brazil.val();
    var chile_val = countryPayments.chile.val();
    disableFields(fields);
    disableFields(fieldBrazilTaxes);

    if (brazil_val != null && brazil_val.length > 0) {
      enableFields(fieldBrazilTaxes);
    }

    if (fieldsToggler[0].checked) {
      enableFields(fields.filter('.always-visible'));
      if (brazil_val != null && brazil_val.length > 0) {
        for (var i in modes) {
          enableFields(fields.filter('.' + modes[i]));
        }
        if (modes.length == 2) {
          enableFields(fields.filter('.cpf_cnpj'));
        }
      }
      if (chile_val != null && chile_val.length > 0) {
        enableFields(fields.filter('.chile'));
      }
    }
  };

  fieldsToggler
    .click(function(){
      updateFields();
    });

  modesField.change(function(){
    updateFields();
  });

  for (var i in countryPayments) {
    countryPayments[i].change(function(){
      updateFields();
    });
  }

  // Advanced options toggler
  var optionsToggler = $('#woocommerce_ebanx-global_advanced_options_title');

  var toggleElements = function() {
    var wasClosed = optionsToggler.hasClass('closed');
    optionsToggler.toggleClass('closed');
    $('.ebanx-advanced-option')
      .add($('.ebanx-advanced-option').closest('.form-table'))
      .slideToggle('fast');

    //Extra call to update checkout manager stuff on open
    if(wasClosed) {
      updateFields();
    }

    createCookie('ebanx_advanced_options_toggle', wasClosed?"open":"closed");
  };
  optionsToggler
    .addClass('togglable')
    .click(toggleElements);

    if(getCookie('ebanx_advanced_options_toggle') != 'open'){
      toggleElements();
    } else {
      //Extra call to update checkout manager stuff if it's already open
      updateFields();
    }
})(jQuery);
