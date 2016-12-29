window.addEventListener('DOMContentLoaded', function(){
    var clipboard = new Clipboard('.ebanx-button--copy');

    clipboard.on('success', function(e) {
        e.trigger.classList.add('ebanx-button--copy-success');

        setTimeout(function() {
            e.trigger.classList.remove('ebanx-button--copy-success');
        }, 2000);
    });

    clipboard.on('error', function(e) {
        e.trigger.classList.add('ebanx-button--copy-error');

        setTimeout(function() {
            e.trigger.classList.remove('ebanx-button--copy-error');
        }, 2000);
    });
}, true);