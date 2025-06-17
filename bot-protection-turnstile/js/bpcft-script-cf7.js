/**
 * Reset the cft widget after form submission.
 *
 * Contact Form 7 DOM events: https://contactform7.com/dom-events/#coding-event-handler
 */
document.addEventListener('wpcf7submit', function (event) {
    const cf7_form = event.target;
    const bpcft = cf7_form.querySelector('.bp-cf-turnstile-div');

    if (bpcft) {
        setTimeout(function () {
            turnstile.reset(bpcft);
        }, 1000)
    }
}, false)