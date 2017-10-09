;(function($) {
	function prepareModalElements() {
		if(this.element != null)
			return;

		var frame = $(document.createElement('iframe'));


		var closeButton = $(document.createElement('a'))
			.html('&times;');

		var container = $(document.createElement('div'))
			.addClass('container')
			.append(closeButton)
			.append(frame);

		this.element = $(document.createElement('div'))
			.attr('id', 'ebanx-settings-modal')
			.append(container)
			.hide();

		var _self = this;
		closeButton.click(function(e){
			e.preventDefault();
			_self.close();
		});

		this.element.navigate = function(url){
			frame.attr('src', url);
			return this;
		};

		$(document.body).append(this.element);
	}

	window.ebanxSettingsModal = {
		element: null,
		open: function(url) {
			prepareModalElements.apply(this);
			this.element
				.navigate(url);
			var _self = this;
			setTimeout(function(){
				_self.element.fadeIn();
			}, 200);
		},
		close: function() {
			var _self = this;
			this.element.fadeOut('fast',function(){
				_self.element.navigate('about:blank');
			});
		}
	};

})(jQuery);
