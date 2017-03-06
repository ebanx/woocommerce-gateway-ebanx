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

  // Interest rates fields
  var maxInstalmentsField = $('#woocommerce_ebanx-global_credit_card_instalments');
  var fields = $('.interest-rates-fields');
  var fieldsToggler = $('#woocommerce_ebanx-global_interest_rates_enabled');

  var disableFields = function(jqElementList){
    jqElementList.closest('tr').hide();
  };

  var enableFields = function(jqElementList){
    jqElementList.closest('tr').show();
  };

  var updateFields = function(){
    var maxInstalments = maxInstalmentsField.val();
    disableFields(fields);

    if (fieldsToggler[0].checked) {
      fields.each(function() {
        var $this = $(this);
        var idnum = parseInt($this.attr('id').substr(-2));
        if (idnum <= maxInstalments) {
          enableFields($this);
        }
      });
    }
  };

  fieldsToggler
    .click(function(){
      updateFields();
    });

  maxInstalmentsField.change(function(){
    updateFields();
  });

  // Payments options toggler 
  var optionsToggler = $('#woocommerce_ebanx-global_payments_options_title');

  var toggleElements = function() {
    var wasClosed = optionsToggler.hasClass('closed');
    optionsToggler.toggleClass('closed');
    $('.ebanx-payments-option')
      .add($('.ebanx-payments-option').closest('.form-table'))
      .slideToggle('fast');

    //Extra call to update checkout manager stuff on open
    if(wasClosed) {
      updateFields();
    }

    createCookie('ebanx_payments_options_toggle', wasClosed?"open":"closed");
  };

  optionsToggler
    .addClass('togglable')
    .click(toggleElements);

  if(getCookie('ebanx_payments_options_toggle') != 'open'){
    toggleElements();
  } else {
    //Extra call to update checkout manager stuff if it's already open
    updateFields();
  }
})(jQuery);
