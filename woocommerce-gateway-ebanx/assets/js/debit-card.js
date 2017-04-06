/* global wc_ebanx_params */
EBANX.config.setMode(wc_ebanx_params.mode);
EBANX.config.setPublishableKey(wc_ebanx_params.key);

// TODO: Create abstract card js to use on debit and debit ?

jQuery( function($) {
	var wc_ebanx_form = {
		init: function( form ) {
			this.form = form;

			$(this.form)
				.on('click', '#place_order', this.onSubmit)
				.on('submit checkout_place_order_ebanx-debit-card');

			$(document)
				.on(
					'ebanxErrorDebitCard',
					this.onError
				);
		},

		isEBANXPaymentMethod: function () {
			return $('input[value=ebanx-debit-card]').is(':checked');
		},

		hasToken: function () {
			return 0 < $('input#ebanx_debit_token').length;
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

			$('#ebanx-debit-cart-form').prepend('<p class="woocommerce-error">' + (res.response.error.err.message || 'Some error happened. Please, verify the data of your debit card and try again.') + '</p>');

			$('body, html').animate({
				scrollTop: $('#ebanx-debit-cart-form').find('.woocommerce-error').offset().top - 20
			});

			wc_ebanx_form.unblock();
		},

		removeErrors: function () {
		  $('.woocommerce-error, .ebanx_debit_token').remove();
		},

		onSubmit: function (e) {
      		wc_ebanx_form.removeHiddenInputs();

			if (wc_ebanx_form.isEBANXPaymentMethod()) {
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();

				wc_ebanx_form.block();

				var card      = $('#ebanx-debit-card-number').val();
				var cvv       = $('#ebanx-debit-card-cvv').val();
				var expires   = $('#ebanx-debit-card-expiry').payment('cardExpiryVal');
				var card_name = $('#ebanx-debit-card-holder-name').val();
				var country   = $('#billing_country').val().toLowerCase();

				EBANX.config.setCountry(country);

				var cardUse = $('input[name="ebanx-debit-card-use"]:checked');

				var debitcard = {
					"card_number": parseInt(card.replace(/ /g,'')),
					"card_name": card_name,
					"card_due_date": (parseInt( expires['month'] ) || 0) + '/' + (parseInt( expires['year'] ) || 0),
					"card_cvv": cvv
				};

				if (cardUse && cardUse.val() && cardUse.val() !== 'new') {
					debitcard.token = cardUse.val();
					debitcard.card_cvv = $(cardUse).parents('.ebanx-debit-card-option').find('.wc-debit-card-form-card-cvc').val();
					debitcard.brand = $(cardUse).parents('.ebanx-debit-card-option').find('.ebanx-card-brand-use').val();
					debitcard.masked_number = $(cardUse).parents('.ebanx-debit-card-option').find('.ebanx-card-masked-number-use').val();

					var response = {
						data: {
							status: 'SUCCESS',
							token: debitcard.token,
							card_cvv: debitcard.card_cvv,
							payment_type_code: debitcard.brand,
							masked_card_number: debitcard.masked_number
						}
					};

					wc_ebanx_form.renderCvv(debitcard.card_cvv);

					EBANX.deviceFingerprint.setup(function (deviceId) {
						response.data.deviceId = deviceId;

						wc_ebanx_form.onEBANXReponse(response);
					});
				} else {
					wc_ebanx_form.renderCvv(debitcard.card_cvv);

					EBANX.card.createToken(debitcard, wc_ebanx_form.onEBANXReponse);
				}
			}
		},

		onCCFormChange: function () {
			$('.woocommerce-error, .ebanx_debit_token').remove();
		},

		toggleCardUse: function () {
			$(document).on('click', 'li[class*="payment_method_ebanx-debit-card"] .ebanx-debit-card-label', function () {
				$('.ebanx-container-debit-card').hide();
				$(this).siblings('.ebanx-container-debit-card').show();
			});
		},

		onEBANXReponse: function (response) {
			if ( response.data && (response.data.status == 'ERROR' || !response.data.token)) {
				$( document ).trigger('ebanxErrorDebitCard', { response: response } );

        		wc_ebanx_form.removeHiddenInputs();
			} else {
				wc_ebanx_form.form.append('<input type="hidden" name="ebanx_debit_token" id="ebanx_debit_token" value="' + response.data.token + '"/>');
				wc_ebanx_form.form.append('<input type="hidden" name="ebanx_debit_brand" id="ebanx_debit_brand" value="' + response.data.payment_type_code + '"/>');
				wc_ebanx_form.form.append('<input type="hidden" name="ebanx_debit_masked_card_number" id="ebanx_debit_masked_card_number" value="' + response.data.masked_card_number + '"/>');
				wc_ebanx_form.form.append('<input type="hidden" name="ebanx_debit_device_fingerprint" id="ebanx_debit_device_fingerprint" value="' + response.data.deviceId + '">');
				wc_ebanx_form.form.submit();
			}
		},

		renderCvv: function (cvv) {
		  wc_ebanx_form.form.append('<input type="hidden" name="ebanx_billing_cvv" id="ebanx_billing_cvv" value="' + cvv + '">');
		},

		removeHiddenInputs: function () {
		  $('#ebanx_debit_token').remove();
	      $('#ebanx_debit_brand').remove();
	      $('#ebanx_debit_masked_card_number').remove();
	      $('#ebanx_debit_device_fingerprint').remove();
		  $('#ebanx_billing_cvv').remove();
		}
	};

	wc_ebanx_form.init( $( "form.checkout, form#order_review, form#add_payment_method, form.woocommerce-checkout" ) );
	
	wc_ebanx_form.toggleCardUse();
} );
