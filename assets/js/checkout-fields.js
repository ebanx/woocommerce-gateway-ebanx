jQuery (function ($) {
  EBANX.errors.summary.pt_BR['BP-DR-57'] = 'A data do cartão de crédito deve estar no formato MM/AA';
  EBANX.errors.summary.es['BP-DR-57'] = 'Por favor, escribe la fecha en el formato MM/AA';
	EBANX.errors.summary.pt_BR['BP-DR-101'] = 'Ops! Esse cartão não está liberado para fazer compras na internet. Entre em contato com o seu banco para mais informações.';
	EBANX.errors.summary.es['BP-DR-101'] = '¡Lo sentimos!, vuelva a intentarlo con otra tarjeta.';
  // Custom select fields
  if ('select2' in $.fn) {
		$( 'select.ebanx-select-field' ).select2();
		$( '.ebanx-select-field > select' ).select2();
  }

  // Masks
  $(document).find('.ebanx_billing_brazil_document input').mask('000.000.000-00');
  $(document).find('.ebanx_billing_brazil_cnpj input').mask('00.000.000/0000-00');

  $(document).find('input[name*="brazil_document"]').mask('000.000.000-00');
  $(document).find('input[name*="brazil_cnpj"]').mask('00.000.000/0000-00');

  var getBillingFields = function (filter) {
    filter = filter || '';

    switch (filter) {
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
      case 'co':
        filter = 'colombia_';
        break;
      case 'pe':
        filter = 'peru_';
        break;
			case 'ar':
				filter = 'argentina_';
				break;
      default:
        // Filter is some other country, let's give it an empty set
        return $([]);
    }

    return $('.woocommerce-checkout').find('p').filter(function(index){
      return this.className.match(new RegExp('.*ebanx_billing_' + filter + '.*$', 'i'));
    });
  };

  var disableFields = function (billingFields) {
    billingFields.each(function() {
      $(this).hide().removeAttr('required');
    });
  };

  var enableFields = function (billingFields) {
    billingFields.each(function() {
      $(this).show().attr('required', true);
    });
  };

  // Select to choose individuals or companies
  var taxes = $('.ebanx_billing_brazil_selector').find('select');

  taxes
    .on('change', function () {
      disableFields($('.ebanx_billing_brazil_selector_option'));
      enableFields($('.ebanx_billing_brazil_' + this.value));
    });

  $('#billing_country')
    .on('change',function() {
      var country = this.value.toLowerCase();

      disableFields(getBillingFields());

      if (country) {
        enableFields(getBillingFields(country));
      }

      if (country === 'br') {
        taxes.change();
      }
    })
    .change();

	var hideDocument = function ( gatewayId ) {
		switch ( $( '#billing_country' ).val() ) {
			case 'CO':
				var ebanxColombianDocumentField = $( '#ebanx_billing_colombia_document_field' );
				if ( gatewayId !== 'ebanx-credit-card-co' ) {
					disableFields( ebanxColombianDocumentField );
					break;
				}
				enableFields( ebanxColombianDocumentField );
				break;
			case "CL":
				var ebanxChileanDocumentField = $( '#ebanx_billing_chile_document_field' );
				if ( gatewayId !== 'ebanx-webpay' ) {
					disableFields( ebanxChileanDocumentField );
					break;
				}
				enableFields( ebanxChileanDocumentField );
				break;
			default:
				break;
		}
	};

	$( 'body' ).on( 'updated_checkout', function () {
		var paymentMethods = $( '.wc_payment_methods.payment_methods.methods > li > input' );

		if (wc_ebanx_checkout_params.is_sandbox) {
			var messages = wc_ebanx_checkout_params.sandbox_tag_messages;
			var localizedMessage = $( '#billing_country' ).val() === 'BR' ? messages['pt-br'] : messages['es'];
			var methodsLabels = $( '.wc_payment_methods.payment_methods.methods > li > label' );
			var ebanxMethodsLabels = methodsLabels.filter(function (index, elm) {
				return /ebanx/.test( $( elm ).attr( 'for' ) );
			});
			$( ebanxMethodsLabels ).find( 'img' ).before( '<span id="sandbox-alert-tag">' + localizedMessage + '</span>' );
		}

		hideDocument( $( 'input[name=payment_method]:checked' ).val() );
		paymentMethods.on( 'change', function( e ) { hideDocument( e.target.value ); } );
	});
});
