jQuery( function($) {
  $(document).find(".ebanx_billing_chile_birth_date input").mask('00/00/0000');
  $(document).find(".ebanx_billing_chile_document input").mask('00.000.000-0');
  $(document).find(".ebanx_billing_brazil_birth_date input").mask('00/00/0000');
  $(document).find(".ebanx_billing_brazil_document input").mask('000.000.000-00');

  function getBillingFields(filter) {
    filter = filter || '';

    switch(filter) {
      case '':
        break;
      case 'br':
        filter = 'brazil_';
        break;
      case 'cl':
        filter = 'chile_';
        break;
      case 'mx':
        filter = 'mexico_';
        break;
      default:
        // Filter is some other country, let's give it an empty set
        return $([]);
    }

    return $('.woocommerce-checkout').find('p').filter(function(index){
      return this.className.match(new RegExp('.*ebanx_billing_' + filter + '.*$', 'i'));
    });
  }

  function disableFields(billingFields) {
    billingFields.each(function() {
      $(this).hide().removeAttr("required");
    });
  }
  function enableFields(billingFields) {
    billingFields.each(function() {
      $(this).show().attr("required", true);
    });
  }

  $('#billing_country')
    .on('change',function() {
      disableFields(getBillingFields());
      enableFields(getBillingFields(this.value.toLowerCase()));
    })
    .change();
});
