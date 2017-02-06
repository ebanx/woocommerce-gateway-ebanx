/* global wc_ebanx_params */
EBANX.config.setMode(wc_ebanx_params.mode);
EBANX.config.setPublishableKey(wc_ebanx_params.key);

jQuery( function($) {
	/**
	 * Object to handle EBANX payment forms.
	 */
	var wc_ebanx_form = {

		/**
		 * Initialize event handlers and UI state.
		 */
		init: function( form ) {
			this.form = form;

			$(this.form)
				.on('click', '#place_order', this.onSubmit)
        .on('submit checkout_place_order_ebanx-credit-card-br')
				.on('submit checkout_place_order_ebanx-credit-card-mx');

			$(document)
				.on(
					'change',
					'#wc-ebanx-cc-form :input',
					this.onCCFormChange
				)
				.on(
					'ebanxErrorCreditCard',
					this.onError
				);
		},

		isEBANXPaymentMethod: function () {
			return $('input[value*=ebanx-credit-card]').is(':checked') && (!$('input[name="wc-ebanx-payment-token"]:checked').length || 'new' === $('input[name="wc-ebanx-payment-token"]:checked').val());
		},

		hasToken: function () {
			return 0 < $('input#ebanx_token').length;
		},

		hasDeviceFingerprint: function () {
			return 0 < $('input#ebanx_device_fingerprint').length;
		},

		block: function () {
			wc_ebanx_form.form.block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
		},

		unblock: function () {
			wc_ebanx_form.form.unblock();
		},

		onError: function (e, res) {
      wc_ebanx_form.removeErrors();

			$('#ebanx-credit-cart-form').prepend('<p class="woocommerce-error">' + (res.response.error.err.message || 'Some error happened. Please, verify the data of your credit card and try again.') + '</p>');

			$('body, html').animate({
				scrollTop: $('#ebanx-credit-cart-form').find('.woocommerce-error').offset().top - 20
			});

			wc_ebanx_form.unblock();
		},

    removeErrors: function () {
      $('.woocommerce-error, .ebanx_token').remove();
    },

		onSubmit: function (e) {
      wc_ebanx_form.removeHiddenInputs();

			if (wc_ebanx_form.isEBANXPaymentMethod()) {
				e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

				wc_ebanx_form.block();

				var card      = $('#ebanx-card-number').val();
				var cvv       = $('#ebanx-card-cvv').val();
				var expires   = $('#ebanx-card-expiry').payment('cardExpiryVal');
				var card_name = $('#ebanx-card-holder-name').val() || ($('#billing_first_name').val() + ' ' + $('#billing_last_name').val());
				var country   = $('#billing_country').val().toLowerCase();
        var instalments = $('#ebanx-container-new-credit-card').find('.ebanx-instalments').val();

				EBANX.config.setCountry(country);

				var cardUse = $('input[name="ebanx-credit-card-use"]:checked');

				var creditcard = {
					"card_number": parseInt(card.replace(/ /g,'')),
					"card_name": card_name,
					"card_due_date": (parseInt( expires['month'] ) || 0) + '/' + (parseInt( expires['year'] ) || 0),
					"card_cvv": cvv,
          "instalments": instalments
				};

				if (cardUse && cardUse.val() && cardUse.val() !== 'new') {
					creditcard.token = cardUse.val();
					creditcard.card_cvv = $(cardUse).parents('.ebanx-credit-card-option').find('.wc-credit-card-form-card-cvc').val();
					creditcard.brand = $(cardUse).parents('.ebanx-credit-card-option').find('.ebanx-card-brand-use').val();
					creditcard.masked_number = $(cardUse).parents('.ebanx-credit-card-option').find('.ebanx-card-masked-number-use').val();
          creditcard.instalments = $(cardUse).parents('.ebanx-form-row').find('.ebanx-instalments').val();

					var response = {
						data: {
							status: 'SUCCESS',
							token: creditcard.token,
							card_cvv: creditcard.card_cvv,
							payment_type_code: creditcard.brand,
							masked_card_number: creditcard.masked_number,
              instalments: creditcard.instalments
						}
					};

          wc_ebanx_form.renderInstalments(creditcard.instalments);
          wc_ebanx_form.renderCvv(creditcard.card_cvv);

          EBANX.deviceFingerprint.setup(function (deviceId) {
            response.data.deviceId = deviceId;

            wc_ebanx_form.onEBANXReponse(response);
          });
				} else {
          wc_ebanx_form.renderInstalments(creditcard.instalments);
          wc_ebanx_form.renderCvv(creditcard.card_cvv);

					EBANX.card.createToken(creditcard, wc_ebanx_form.onEBANXReponse);
				}
			}
		},

		onCCFormChange: function () {
			$('.woocommerce-error, .ebanx_token').remove();
		},

		toggleCardUse: function () {
			$(document).on('click', 'li[class*="payment_method_ebanx-credit-card"] .ebanx-credit-card-label', function () {
				$('.ebanx-container-credit-card').hide();
				$(this).siblings('.ebanx-container-credit-card').show();
			});
		},

		onEBANXReponse: function (response) {
			if ( response.data && (response.data.status == 'ERROR' || !response.data.token)) {
				$( document ).trigger('ebanxErrorCreditCard', { response: response } );

        wc_ebanx_form.removeHiddenInputs();
			} else {
				wc_ebanx_form.form.append('<input type="hidden" name="ebanx_token" id="ebanx_token" value="' + response.data.token + '"/>');
				wc_ebanx_form.form.append('<input type="hidden" name="ebanx_brand" id="ebanx_brand" value="' + response.data.payment_type_code + '"/>');
				wc_ebanx_form.form.append('<input type="hidden" name="ebanx_masked_card_number" id="ebanx_masked_card_number" value="' + response.data.masked_card_number + '"/>');
				wc_ebanx_form.form.append('<input type="hidden" name="ebanx_device_fingerprint" id="ebanx_device_fingerprint" value="' + response.data.deviceId + '">');

				wc_ebanx_form.form.submit();
			}
		},

    renderInstalments: function (instalments) {
      wc_ebanx_form.form.append('<input type="hidden" name="ebanx_billing_instalments" id="ebanx_billing_instalments" value="' + instalments + '">');
    },

    renderCvv: function (cvv) {
      wc_ebanx_form.form.append('<input type="hidden" name="ebanx_billing_cvv" id="ebanx_billing_cvv" value="' + cvv + '">');
    },

    removeHiddenInputs: function () {
      $('#ebanx_token').remove();
      $('#ebanx_brand').remove();
      $('#ebanx_masked_card_number').remove();
      $('#ebanx_device_fingerprint').remove();
      $('#ebanx_billing_instalments').remove();
      $('#ebanx_billing_cvv').remove();
    }
	};

	wc_ebanx_form.init( $( "form.checkout, form#order_review, form#add_payment_method, form.woocommerce-checkout" ) );

	wc_ebanx_form.toggleCardUse();
} );
