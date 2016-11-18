/* global wc_ebanx_params */

Ebanx.config.setMode(wc_ebanx_params.mode);
Ebanx.config.setPublishableKey(wc_ebanx_params.key);

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

			$( this.form )
				.on( 'click', '#place_order', this.onSubmit )
				.on( 'submit checkout_place_order_ebanx-credit-card' );

			$(document)
				.on(
					'change',
					'#wc-ebanx-cc-form :input',
					this.onCCFormChange
				)
				.on(
					'ebanxError',
					this.onError
				);
		},

		isEbanxPaymentMethod: function() {
			return $('input[value=ebanx-credit-card]').is(':checked') && (!$('input[name="wc-ebanx-payment-token"]:checked').length || 'new' === $( 'input[name="wc-ebanx-payment-token"]:checked').val());
		},

		hasToken: function() {
			return 0 < $( 'input#ebanx_token' ).length;
		},
    
		hasDeviceFingerprint: function() {
			return 0 < $( 'input#ebanx_device_fingerprint' ).length;
		},

		block: function() {
			wc_ebanx_form.form.block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
		},

		unblock: function() {
			wc_ebanx_form.form.unblock();
		},

		onError: function(e, res) {
      wc_ebanx_form.removeErrors();

			$('#ebanx-credit-cart-form').prepend('<p class="woocommerce-error">' + res.response.error.message + '</p>');
			wc_ebanx_form.unblock();
		},

    removeErrors: function () {
      $('.woocommerce-error, .ebanx_token').remove();
    },

		onSubmit: function (e) {
			if (wc_ebanx_form.isEbanxPaymentMethod() && (!wc_ebanx_form.hasToken() || !wc_ebanx_form.hasDeviceFingerprint())) {
				e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

				wc_ebanx_form.block();

				var card     = $( '#ebanx-card-number' ).val();
				var cvc        = $( '#ebanx-card-cvc' ).val();
				var expires    = $( '#ebanx-card-expiry' ).payment( 'cardExpiryVal' );
				var card_name  = $('#ebanx-card-holder-name').val();
				var country = $('#billing_country').val().toLowerCase();

				var cardUse = $('input[name="ebanx-credit-card-use"]:checked');

				var creditcard = {
					"card_number": parseInt(card.replace(/ /g,'')),
					"card_name": card_name,
					"card_due_date": (parseInt( expires['month'] ) || 0) + '/' + (parseInt( expires['year'] ) || 0),
					"card_cvv": parseInt(cvc),
					country: country
				};

				if (cardUse && cardUse.val() !== 'new') {
					creditcard.token = cardUse.val();
					creditcard.card_cvv = $(cardUse).siblings(".ebanx-container-credit-card").find("#ebanx-card-cvc-use").val();
					creditcard.brand = $(cardUse).siblings(".ebanx-container-credit-card").find("#ebanx-card-brand-use").val();
					creditcard.masked_number = $(cardUse).siblings(".ebanx-container-credit-card").find("#ebanx-card-masked-number-use").val();

					var response = {
						data: {
							status: 'SUCCESS',
							token: creditcard.token,
							cvv: creditcard.card_cvv,
							payment_type_code: creditcard.brand,
							masked_card_number: creditcard.masked_number
						}
					};
					wc_ebanx_form.onEBANXReponse(response);
				} else {
					Ebanx.card.createToken(creditcard, wc_ebanx_form.onEBANXReponse);
				}
			}
		},

		onCCFormChange: function() {
			$( '.woocommerce-error, .ebanx_token' ).remove();
		},

		toggleCardUse: function() {
			$(document).on('click', 'div.payment_method_ebanx-credit-card .input-radio[name=ebanx-credit-card-use]', function(){
				$(".ebanx-container-credit-card").hide();
				$(this).siblings(".ebanx-container-credit-card").slideToggle();
			});
		},

		onEBANXReponse: function(response ) {
			if ( response.data && (response.data.status == 'ERROR' || !response.data.token)) {
				$( document ).trigger( 'ebanxError', { response: response } );
			} else {
				// insert the token into the form so it gets submitted to the server and generate the device fingerprint
				EBANX.deviceFingerprint(function (session_id) {
					wc_ebanx_form.form.append( '<input type="hidden" name="ebanx_token" id="ebanx_token" value="' + response.data.token + '"/>' );
					wc_ebanx_form.form.append( '<input type="hidden" name="ebanx_brand" id="ebanx_brand" value="' + response.data.payment_type_code + '"/>' );
					wc_ebanx_form.form.append( '<input type="hidden" name="ebanx_masked_card_number" id="ebanx_masked_card_number" value="' + response.data.masked_card_number + '"/>' );
					wc_ebanx_form.form.append( '<input type="hidden" name="ebanx_device_fingerprint" id="ebanx_device_fingerprint" value="' + session_id + '">' );
            
					wc_ebanx_form.form.submit();
				});
				
			}
		}
	};

	wc_ebanx_form.init( $( "form.checkout, form#order_review, form#add_payment_method, form.woocommerce-checkout" ) );

	wc_ebanx_form.toggleCardUse();
} );