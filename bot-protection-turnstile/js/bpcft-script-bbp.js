/* global turnstile */

var bpcft_bbp_cft_forms = {};

function bpcft_bbp_register_cft_render(uid, bbpFormType) {
    bpcft_bbp_cft_forms[uid] = bbpFormType;
}

function bpcft_bbp_onload_cft() {
    Object.entries(bpcft_bbp_cft_forms).forEach(function ([uid, bbpFormType]) {
        // console.log([uid, bbpFormType]);
        const containerSelector = '#cf-turnstile-' + uid;

        const container = document.querySelector(containerSelector);

        if (!container) {
            // console.log(containerSelector + ' does not exits!');
            return;
        }

        const closestFrom = container.closest('form');
        if (!closestFrom) {
            // console.log('Wrapper form does not exits!');
            return;
        }

        let allowRender = true;
        switch (bbpFormType) {
            case 'pass-reset':
                if (closestFrom.querySelector('.bbp-password')) { // Pass reset form should not have a password field.
                    allowRender = false;
                }
                break;
            case 'login':
                if (!closestFrom.querySelector('.bbp-password')) { // Login form should have a password field.
                    allowRender = false;
                }
                break;
            default:
                break;
        }

        if (allowRender) {
            return turnstile.render(containerSelector);
        }

    })

}

document.addEventListener('bpcftOnloadTurnstileCallback', bpcft_bbp_onload_cft)