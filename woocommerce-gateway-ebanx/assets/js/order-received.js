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
  var iframe = $('.woocommerce-order-received iframe')[0];

  function resizeIframe(iframe) {
    iframe.style.height = iframe.contentWindow.document.body.parentElement.scrollHeight + 'px';
  }

  $(window).on('load resize', function () {
    resizeIframe(iframe);
  });

  iframe.contentWindow.addEventListener('resize', function () {
    resizeIframe(iframe);
  });
});
