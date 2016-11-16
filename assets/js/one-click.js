jQuery( document ).ready( function($){
    var button = $('.ebanx-one-click-button'),
        hidden = $('[name="ebanx_one_click"]');

    button.one( 'click', function(){
        hidden.val('is_one_click');
    })

});