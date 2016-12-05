jQuery( document ).ready( function($){
    var button = $('.ebanx-one-click-button'),
        hidden = $('[name="ebanx_one_click"]');

    button.click(function() {
        event.preventDefault();
        $('#ebanx-one-click-modal').show();
    });

    $("#ebanx-one-click-pay").click(function() {
        hidden.val('is_one_click');
        var cardUse = $('.ebanx-card-use:checked');
        var cardUseCvv = $('input[name="ebanx_one_click_cvv"]').val();

        if (!cardUse || !cardUse.val() || !cardUseCvv|| cardUseCvv.trim().length !== 3) {
            return false;
        }

        $('input[name="ebanx_one_click_token"]').val(cardUse.val());

        $("form.cart").submit();
    });

});