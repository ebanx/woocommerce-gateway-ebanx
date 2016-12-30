jQuery(document).ready(function ($) {
    var button = $('#ebanx-one-click-button');
    var hidden = $('#ebanx-one-click');
    var close = $('.ebanx-one-click-close-button');
    var tooltip = $('.ebanx-one-click-tooltip');

    button.on('click', function (e) {
      e.preventDefault();
      tooltip.toggleClass('is-active');
    });

    close.on('click', function (e) {
      e.preventDefault();
      tooltip.removeClass('is-active');
    });

    $('.ebanx-one-click-pay').on('click', function (e) {
      e.preventDefault();

      hidden.val('is_one_click');
      var cardSelected = $('.ebanx-card-use:checked');
      var cvv = $('#ebanx-one-click-cvv-input').val();
      var self = $(this);

      if (!!cvv.length === false) {
        return false;
      }

      self.text(self.attr('data-processing-label')).attr('disabled', 'disabled');

      $('form.cart').submit();
    });
});
