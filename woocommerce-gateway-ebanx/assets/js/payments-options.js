;(function($){
  // Interest rates fields
  var maxInstalmentsField = $('#woocommerce_ebanx-global_credit_card_instalments');
  var fields = $('.interest-rates-fields');
  var fieldsToggler = $('#woocommerce_ebanx-global_interest_rates_enabled');
  var fieldsDueDate = $('#woocommerce_ebanx-global_due_date_days');

  var disableFields = function(jqElementList){
    jqElementList.closest('tr').hide();
  };

  var enableFields = function(jqElementList){
    jqElementList.closest('tr').show();
  };

  var updateFields = function () {
    var maxInstalments = maxInstalmentsField.val();
    disableFields(fields);

    if (fieldsToggler.length == 1 && fieldsToggler[0].checked) {
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
    .click(function () {
      updateFields();
    });

  maxInstalmentsField.change(function () {
    updateFields();
  });

  // Fields due date
  fieldsDueDate.attr('min', '1');

  // Payments options toggler
  var optionsToggler = $('#woocommerce_ebanx-global_payments_options_title');

  var toggleElements = function() {
    var wasClosed = optionsToggler.hasClass('closed');
    optionsToggler.toggleClass('closed');
    $('.ebanx-payments-option')
      .add($('.ebanx-payments-option').closest('.form-table'))
      .slideToggle('fast');

    //Extra call to update checkout manager stuff on open
    if (wasClosed) {
      updateFields();
    }

    localStorage.setItem('ebanx_payments_options_toggle', wasClosed ? 'open' : 'closed');
  };

  optionsToggler
    .addClass('togglable')
    .click(toggleElements);

  if (localStorage.getItem('ebanx_payments_options_toggle') != 'open'){
    toggleElements();
  } else {
    //Extra call to update checkout manager stuff if it's already open
    updateFields();
  }
})(jQuery);
