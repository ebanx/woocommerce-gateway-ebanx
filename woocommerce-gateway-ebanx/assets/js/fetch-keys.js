;(function($){
	var modal = ebanxSettingsModal;
	var trigger = $('#woocommerce_ebanx-global_fetch_keys_button');

	var fields = {
		sandboxPrivate: $('#woocommerce_ebanx-global_sandbox_private_key'),
		sandboxPublic: $('#woocommerce_ebanx-global_sandbox_public_key'),
		livePrivate: $('#woocommerce_ebanx-global_live_private_key'),
		livePublic: $('#woocommerce_ebanx-global_live_public_key'),

		populate: function(keys) {
			this.sandboxPrivate.val(keys.sandbox.private);
			this.sandboxPublic.val(keys.sandbox.public);
			this.livePrivate.val(keys.live.private);
			this.livePublic.val(keys.live.public);
		}
	};

	var Keys = {
		allow_origin: [
			'https://dashboard.ebanx.com',
			'http://localhost'
		],

		getRequestURL: function() {
			var base = document.location.toString().split('/').slice(0,-1).join('/');
			return base + '/?ebanx=fetch-keys';
		},

		receiveMessage: function(message) {
			fields.populate(message.data.ebanx_integration);
		},

		validateMessage: function(message) {
			var origin = message.origin || message.originalEvent.origin;
			console.log('origin: '+origin);

			if (!Keys.allow_origin.includes(origin))
				return false;

			if (typeof(message.data.ebanx_integration) === 'undefined')
				return false;

			return true;
		}
	};

	trigger.click(function(e) {
		e.preventDefault();
		modal.open(Keys.getRequestURL());
	});

	window.addEventListener("message", function(message) {
		try {
			if (!Keys.validateMessage(message))
				return;

			Keys.receiveMessage(message);
		}
		finally {
			modal.close();
		}
	}, false);
})(jQuery);
