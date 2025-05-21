/**
 * This callback invoked upon success of the challenge.
 */
function bpcft_callback(){
    console.log('[BPCFT] Cloudflare turnstile challenge successful.');
}

/**
 * This callback invoked when there is an error
 */
function bpcft_error_callback(error_code) {
    bpcft_handle_error_callback(error_code);

    return true; // Returning non-falsy value means the error was handled.
}

function bpcft_handle_error_callback(error_code){
    document.querySelectorAll('.bp-cf-turnstile-div').forEach(el => {
        const existingErrorMsg = el.querySelector('.bpcft-error-msg');
        if ( existingErrorMsg != null){
            existingErrorMsg.remove();
        }

        const error = bpcft_error_msg_by_code(error_code);
        if (error.show){
            // Show the error to visitor.
            const errorEl = bpcft_create_error_msg_html("Cloudflare turnstile error: " + error.message);
            el.appendChild(errorEl);
        } else {
            // Don't show the error to visitor, console log instead.
            console.log("[BPCFT] " + error.message)
        }
    })
}

function bpcft_create_error_msg_html(message){
    const errorHtml = document.createElement('p');
    errorHtml.classList.add('bpcft-error-msg');
    errorHtml.innerText = message;

    return errorHtml;
}

/**
 * This callback invoked when the token expires and does not reset the widget.
 */
function bpcft_expired_callback() {
    console.log('[BPCFT] Cloudflare turnstile token has expired.');
}

function bpcft_error_msg_by_code(code = 0){
    const errors = [
        {
            codeRegex: /100/,
            message: 'There was a problem initializing Turnstile before a challenge could be started.',
            show: true
        },
        {
            codeRegex: /105/,
            message: 'Turnstile was invoked in a deprecated or invalid way.',
            show: true
        },
        {
            codeRegex: /10[2-6]/,
            message: 'Invalid Parameters: The visitor sent an invalid parameter as part of the challenge towards Turnstile.',
            show: false
        },
        {
            codeRegex: /1101[0-1]0/,
            message: 'Turnstile was invoked with an invalid sitekey or a sitekey that is no longer active.',
            show: true
        },
        {
            codeRegex: /110200/,
            message: 'Unknown domain: Domain not allowed',
            show: false
        },
        {
            codeRegex: /1106/,
            message: 'Challenge timed out: The visitor took too long to solve the challenge and the challenge timed out.',
            show: false
        },
        {
            codeRegex: /300/,
            message: 'Generic client execution error: An unspecified error occurred in the visitor while they were solving a challenge.',
            show: false
        },
        {
            codeRegex: /400/,
            message: 'Incorrect turnstile configuration',
            show: true
        },
        {
            codeRegex: /600/,
            message: 'Challenge execution failure: A visitor failed to solve a Turnstile Challenge.',
            show: false
        },
        {
            // This is an unknown error.
            codeRegex: /.*/,
            message: 'Unknown error with code: ' + code,
            show: false
        }
    ];

    // Search for the matching error.
    for (const i in errors) {
        let result = errors[i].codeRegex.test(code.toString());
        if (result){
            return errors[i];
        }
    }

    // return the unknown error.
    return errors[errors.length - 1];
}

// For wp comment form.
document.addEventListener("DOMContentLoaded", function () {
    document.body.addEventListener("click", function (event) {
        if (event.target.matches(".comment-reply-link, #cancel-comment-reply-link")) {
            turnstile.reset(".comment-form .cf-turnstile");
        }
    });
});