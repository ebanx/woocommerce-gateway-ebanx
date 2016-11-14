jQuery( function($) {
	var ebanxBillingContainers = function() {
		return jQuery('.woocommerce-checkout').find('p').each(function() {
			if(this.id.match(/^ebanx_billing_.*$/)) {
				jQuery(this).hide();
			}
			return this;
		});
	};

	var countryField = $('#billing_country');

	countryField.on('change',function() {
		ebanxBillingContainers();

		switch(countryField.val().toLowerCase()) {
			case "br":
				ebanxBillingContainers().each(function() {
					if(this.id.match(/^ebanx_billing_brazil_.*$/)) {
						jQuery(this).show();
					}
				});
			break;
			case "mx":
				ebanxBillingContainers().each(function() {
					if(this.id.match(/^ebanx_billing_mexico_.*$/)) {
						jQuery(this).show();
					}
				});
			break;
		}
	});
});
