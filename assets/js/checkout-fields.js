jQuery( function($) {
	var countryField = $('#billing_country');
	countryField.on('change',function() {
		switch(countryField.val().toLowerCase()) {
			case "br":
				console.log("BR");
			break;
			case "mx":
				console.log("MX");
			break;
		}
	});
});
