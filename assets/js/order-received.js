jQuery(document).ready(function ($) {
  var clipboard = new Clipboard('.ebanx-button--copy');

  clipboard.on('success', function(e) {
    var $target = $(e.trigger);

    $target
    .addClass('ebanx-button--copy-success')
    .text('✔︎ Copiado!');

    setTimeout(function() {
      $target
      .removeClass('ebanx-button--copy-success')
      .text('Copiar');

    }, 2000);
  });

  clipboard.on('error', function(e) {
    var $target = $(e.trigger);

    $target
      .addClass('ebanx-button--copy-error')
      .text('Erro! :(');

    setTimeout(function() {
      $target
        .addClass('ebanx-button--copy-error')
        .text('Copiar');
    }, 2000);
  });

  // iFrame Resizer
  var iframe = $('.woocommerce-order-received iframe');

  if (iframe) {
    var resizeIframe = function resizeIframe() {
      iframe.height(iframe.contents().height());
    };

    var resizeIframeWhenBarcodeIsZero = function resizeIframeWhenBarcodeIsZero() {
      if ($('#hide_boleto_details').html()) {
        iframe.css('width', '100%');
        iframe.css('border', '0px');
        iframe.css('height', '1600px');
      }
    };

    $(window).on('load', function () {
      resizeIframe();
      resizeIframeWhenBarcodeIsZero();
    });

    iframe.contents().on('resize', function () {
      resizeIframe();
      resizeIframeWhenBarcodeIsZero();
    });
  }
});
