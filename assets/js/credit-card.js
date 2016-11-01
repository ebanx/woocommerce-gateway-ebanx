/* global wc_ebanx_params */

Ebanx.config.setPublishableKey(wc_ebanx_params.key);

jQuery( function( $ ) {
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
				.on(
					'submit checkout_place_order_ebanx',
					this.onSubmit
				);

			$( document )
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

		isEBANXChosen: function() {
			return $( '#payment_method_ebanx-credit-card' ).is( ':checked' ) && ( ! $( 'input[name="wc-ebanx-payment-token"]:checked' ).length || 'new' === $( 'input[name="wc-ebanx-payment-token"]:checked' ).val() );
		},

		hasToken: function() {
			return 0 < $( 'input.ebanx_token' ).length;
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

		onError: function( e, responseObject ) {
			$( '.woocommerce-error, .ebanx_token' ).remove();
			$( '#ebanx-card-number' ).closest( 'p' ).before( '<ul class="woocommerce_error woocommerce-error"><li>' + responseObject.response.status_message + '</li></ul>' );
			wc_ebanx_form.unblock();
		},

		onSubmit: function( e ) {
			if ( wc_ebanx_form.isEBANXChosen() && ! wc_ebanx_form.hasToken() ) {

				e.preventDefault();
				wc_ebanx_form.block();

				var card       = $( '#ebanx-card-number' ).val(),
					cvc        = $( '#ebanx-card-cvc' ).val(),
					expires    = $( '#ebanx-card-expiry' ).payment( 'cardExpiryVal' ),
					first_name = $( '#billing_first_name' ).length ? $( '#billing_first_name' ).val() : wc_ebanx_params.billing_first_name,
					last_name  = $( '#billing_last_name' ).length ? $( '#billing_last_name' ).val() : wc_ebanx_params.billing_last_name,
					address    = {

					},
					data       = {
						"payment_type_code": "visa",
						"country": "br",
						"creditcard": {
							"card_number": parseInt(card.replace(/ /g,'')),
							"card_name": first_name + ' ' + last_name,
							"card_due_date": (parseInt( expires['month'] ) || 0) + '/' + (parseInt( expires['year'] ) || 0),
							"card_cvv": parseInt(cvc),
							country: 'br' // TODO: dynamic ?????
						}
					};

				if ( jQuery('#billing_address_1').length > 0 ) {
					data.address_line1   = $( '#billing_address_1' ).val();
					data.address_line2   = $( '#billing_address_2' ).val();
					data.address_state   = $( '#billing_state' ).val();
					data.address_city    = $( '#billing_city' ).val();
					data.address_zip     = $( '#billing_postcode' ).val();
					data.address_country = $( '#billing_country' ).val();
				} else if ( data.address_line1 ) {
					data.address_line1   = wc_ebanx_params.billing_address_1;
					data.address_line2   = wc_ebanx_params.billing_address_2;
					data.address_state   = wc_ebanx_params.billing_state;
					data.address_city    = wc_ebanx_params.billing_city;
					data.address_zip     = wc_ebanx_params.billing_postcode;
					data.address_country = wc_ebanx_params.billing_country;
				}

				Ebanx.card.createToken(data.creditcard, wc_ebanx_form.onEBANXReponse);

				// Prevent form submitting
				return false;
			}
		},

		onCCFormChange: function() {
			$( '.woocommerce-error, .ebanx_token' ).remove();
		},

		onEBANXReponse: function(response ) {
			if ( response.data && (response.data.status == 'ERROR' || !response.data.token)) {
				$( document ).trigger( 'ebanxError', { response: response } );
			} else {
				// token contains id, last4, and card type
				var token = response.data.token;

				// insert the token into the form so it gets submitted to the server
				wc_ebanx_form.form.append( "<input type='hidden' class='ebanx_token' name='ebanx_token' value='" + token + "'/>" );
				wc_ebanx_form.form.submit();
			}
		}
	};

	wc_ebanx_form.init( $( "form.checkout, form#order_review, form#add_payment_method" ) );
} );