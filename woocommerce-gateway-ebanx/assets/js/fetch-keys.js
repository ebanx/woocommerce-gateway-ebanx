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

	var receiveKeys = function(result){
		fields.sandboxPrivate.val(result.sandbox.private);
		fields.sandboxPublic.val(result.sandbox.public);
		fields.livePrivate.val(result.live.private);
		fields.livePublic.val(result.live.public);
	};

	var clickFetchKeys = function(e){
		e.preventDefault();
		modal.open('//localhost/fetchkeys.php');
	};

	window.SetIntegrationKeys = function(result){
		receiveKeys(result);
		modal.close();
	};

	$('#woocommerce_ebanx-global_fetch_keys_button')
		.click(clickFetchKeys);
})(jQuery);
