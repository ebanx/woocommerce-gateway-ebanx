jQuery( function($) {
	$(document).find("#ebanx_billing_brazil_birth_date").mask('00/00/0000');
	$(document).find("#ebanx_billing_brazil_document").mask('000.000.000-00');

	var ebanxBillingContainers = function() {
		return jQuery('.woocommerce-checkout').find('p').each(function() {
			if(this.id.match(/^ebanx_billing_.*$/)) {
				jQuery(this).hide().removeAttr("required");
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
						jQuery(this).show().attr("required", true);
					}
				});
			break;
			case "mx":
				ebanxBillingContainers().each(function() {
					if(this.id.match(/^ebanx_billing_mexico_.*$/)) {
						jQuery(this).show().attr("required", true);
					}
				});
			break;
		}
	});
});
