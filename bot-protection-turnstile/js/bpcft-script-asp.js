
/**
 * This callback invoked upon success of the challenge. This is specific to ASP plugin.
 */
function bpcft_asp_checkout_form_callback(token){
    console.log('[BPCFT] Cloudflare turnstile challenge successful for ASP checkout.');

    // Here, the 'vars' object is available by the ASP plugin.
    if (vars.data){
        // Add the response token to the vars.data object so that later it can be passed to asp asp_pp_create_pi ajax payload.
        vars.data.bpcft_token_response = token;
    }
}

/**
 * For ASP plugin integration.
 */
class BPCftHandlerNG {

    constructor(data) {
        this.data = data;
    }

    /**
     * This is an addon action hook callback function which triggers before the 'asp_pp_create_pi' ajax request executes.
     */
    csBeforeRegenParams(){
        // console.log('[BPCFT]: Adding response token to csBeforeRegenParams');

        // Adding the response token to asp_pp_create_pi ajax payload.
        const token = this.data.bpcft_token_response || '';
        this.data.csRegenParams += '&bpcft_token_response=' + token;
    }
}
window.BPCftHandlerNG = BPCftHandlerNG; // Adding the class to the window object for global access.