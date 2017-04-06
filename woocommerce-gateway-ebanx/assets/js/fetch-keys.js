;(function($){
	var modal = {
		element: null,
		_prepareElement: function(){
			if(this.element != null)
				return;

			var _self = this;

			var frame = $(document.createElement('iframe'))
				.attr('width', '500')
				.attr('height', '200')
				.css({
					'display': 'block',
					'margin': '20px auto',
					'background-color': '#ffffff'
				});

			var closeButton = $(document.createElement('button'))
				.css({
					'display': 'block',
					'width': '100px',
					'margin': '170px auto 0px auto'
				})
				.text('Close');

			this.element = $(document.createElement('div'))
				.css({
					'position': 'fixed',
					'width': '100%',
					'height': '100%',
					'top': '0px',
					'left': '0px',
					'background-color': 'rgba(0,0,0,0.5)'
				})
				.append(closeButton)
				.append(frame)
				.hide();

			closeButton.click(function(e){
				e.preventDefault();
				_self.close();
			});

			this.element.navigate = function(url){
				frame.attr('src', url);
				return this;
			};

			$(document.body).append(this.element);
		},
		open: function(url) {
			this._prepareElement();

			this.element
				.navigate(url)
				.fadeIn();
		},
		close: function() {
			var _self = this;
			this.element.fadeOut('fast',function(){
				_self.element.navigate('about:blank');
			});
		}
	};

	var fields = {
		sandboxPrivate: $('#woocommerce_ebanx-global_sandbox_private_key'),
		sandboxPublic: $('#woocommerce_ebanx-global_sandbox_public_key'),
		livePrivate: $('#woocommerce_ebanx-global_live_private_key'),
		livePublic: $('#woocommerce_ebanx-global_live_public_key')
	};

	var receiveKeys = function(keys){
		fields.sandboxPrivate.val(keys.sandbox.private);
		fields.sandboxPublic.val(keys.sandbox.public);
		fields.livePrivate.val(keys.live.private);
		fields.livePublic.val(keys.live.public);
	};

	var clickFetchKeys = function(e){
		e.preventDefault();
		var base = document.location.toString().split('/').slice(0,-1).join('/');
		modal.open(base + '/?ebanx=fetch-keys');
	};

	var receiveIframeMessage = function(e){
		var origin = e.origin || e.originalEvent.origin;
		console.log('origin: '+origin);
		// if (origin !== 'https://dashboard-v2.ebanx.com' && origin !== 'https://dashboard.ebanx.com')
		// 	return;

		receiveKeys(e.data);
		modal.close();
	};
	window.addEventListener("message", receiveIframeMessage, false);

	$('#woocommerce_ebanx-global_fetch_keys_button')
		.click(clickFetchKeys);
})(jQuery);
