;(function($){
  // Interest rates fields
  var maxInstalmentsFieldAr = $('#woocommerce_ebanx-global_ar_credit_card_instalments');
  var maxInstalmentsFieldBr = $('#woocommerce_ebanx-global_br_credit_card_instalments');
  var maxInstalmentsFieldCo = $('#woocommerce_ebanx-global_co_credit_card_instalments');
  var maxInstalmentsFieldMx = $('#woocommerce_ebanx-global_mx_credit_card_instalments');
  var fieldsAr = $('.interest-rates-fields.interest-ar');
  var fieldsBr = $('.interest-rates-fields.interest-br');
  var fieldsCo = $('.interest-rates-fields.interest-co');
  var fieldsMx = $('.interest-rates-fields.interest-mx');
  var fieldsTogglerAr = $('#woocommerce_ebanx-global_ar_interest_rates_enabled');
  var fieldsTogglerBr = $('#woocommerce_ebanx-global_br_interest_rates_enabled');
  var fieldsTogglerCo = $('#woocommerce_ebanx-global_co_interest_rates_enabled');
  var fieldsTogglerMx = $('#woocommerce_ebanx-global_mx_interest_rates_enabled');
  var fieldsDueDate = $('#woocommerce_ebanx-global_due_date_days');

  var disableFields = function(jqElementList){
    jqElementList.closest('tr').hide();
  };

  var enableFields = function(jqElementList){
    jqElementList.closest('tr').show();
  };

  var updateFieldsAr = function () {
    var maxInstalments = maxInstalmentsFieldAr.val();
    disableFields(fieldsAr);

    if (fieldsTogglerAr.length == 1 && fieldsTogglerAr[0].checked) {
      fieldsAr.each(function() {
        var $this = $(this);
        var idnum = parseInt($this.attr('id').substr(-2));
        if (idnum <= maxInstalments) {
          enableFields($this);
        }
      });
    }
  };

  var updateFieldsBr = function () {
    var maxInstalments = maxInstalmentsFieldBr.val();
    disableFields(fieldsBr);

    if (fieldsTogglerBr.length == 1 && fieldsTogglerBr[0].checked) {
      fieldsBr.each(function() {
        var $this = $(this);
        var idnum = parseInt($this.attr('id').substr(-2));
        if (idnum <= maxInstalments) {
          enableFields($this);
        }
      });
    }
  };

  var updateFieldsCo = function () {
    var maxInstalments = maxInstalmentsFieldCo.val();
    disableFields(fieldsCo);

    if (fieldsTogglerCo.length == 1 && fieldsTogglerCo[0].checked) {
      fieldsCo.each(function() {
        var $this = $(this);
        var idnum = parseInt($this.attr('id').substr(-2));
        if (idnum <= maxInstalments) {
          enableFields($this);
        }
      });
    }
  };

  var updateFieldsMx = function () {
    var maxInstalments = maxInstalmentsFieldMx.val();
    disableFields(fieldsMx);

    if (fieldsTogglerMx.length == 1 && fieldsTogglerMx[0].checked) {
      fieldsMx.each(function() {
        var $this = $(this);
        var idnum = parseInt($this.attr('id').substr(-2));
        if (idnum <= maxInstalments) {
          enableFields($this);
        }
      });
    }
  };

  fieldsTogglerAr
    .click(function () {
      updateFieldsAr();
    });

  maxInstalmentsFieldAr.change(function () {
    updateFieldsAr();
  });

  fieldsTogglerBr
    .click(function () {
      updateFieldsBr();
    });

  maxInstalmentsFieldBr.change(function () {
    updateFieldsBr();
  });

  fieldsTogglerCo
    .click(function () {
      updateFieldsCo();
    });

  maxInstalmentsFieldCo.change(function () {
    updateFieldsCo();
  });

  fieldsTogglerMx
    .click(function () {
      updateFieldsMx();
    });

  maxInstalmentsFieldMx.change(function () {
    updateFieldsMx();
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
      updateFieldsAr();
      updateFieldsBr();
      updateFieldsCo();
      updateFieldsMx();
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
    updateFieldsAr();
    updateFieldsBr();
    updateFieldsCo();
    updateFieldsMx();
  }
})(jQuery);
