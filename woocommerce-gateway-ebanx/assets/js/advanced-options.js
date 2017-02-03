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

  var disableFields = function(jqElementList){
    jqElementList.removeAttr('required').closest('tr').hide();
  };

  var enableFields = function(jqElementList){
    jqElementList.attr('required', '').closest('tr').show();
  };

  var updateFields = function(){
    var modes = modesField.val();
    disableFields(fields);

    if (fieldsToggler[0].checked) {
      for (var i in modes) {
        enableFields(fields.filter('.' + modes[i]));
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

  // Advanced options toggler
  var selector = '.ebanx-advanced-option, .form-table, p:not(.submit)';

  var optionsToggler = $('#woocommerce_ebanx-global_advanced_options_title');
  optionsToggler
    .addClass('togglable')
    .click(function(){
      var wasClosed = $(this).hasClass('closed');
      $(this).toggleClass('closed')
        .nextAll(selector).slideToggle('fast');

      //Extra call to update checkout manager stuff on open
      if(wasClosed) {
        updateFields();
      }

      createCookie('ebanx_advanced_options_toggle', wasClosed?"open":"closed");
    });

    if(getCookie('ebanx_advanced_options_toggle') != 'open'){
      optionsToggler.addClass('closed').nextAll(selector).slideUp('fast');
    } else {
      //Extra call to update checkout manager stuff if it's already open
      updateFields();
    }
})(jQuery);
