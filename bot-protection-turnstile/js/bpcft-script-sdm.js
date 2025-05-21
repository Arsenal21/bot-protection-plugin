document.addEventListener('DOMContentLoaded', function (){

    // For a regular sdm download, when the download link button is clicked, intercept that request and
    // append the cft token to that link, so that the response token can be validated form server side.

    const sdm_dl_links = document.querySelectorAll('a.sdm_download, a.sdm_fancy2_download_dl_link');
    sdm_dl_links.forEach(function(dl_link_btn){
        dl_link_btn.addEventListener('click', function (e){
            e.preventDefault();
            // Parse the href attribute and get the download id.
            const dl_url = new URL(this.href);
            const dl_id = dl_url.searchParams.get('download_id');

            // Get the nearest cft response field using the unique class name assigned for that specific download item.
            const cft_div = document.querySelector(".bpcft-sdm-dl-" + dl_id);
            if (cft_div){
                const cft_response_field = cft_div.querySelector('input[name="cf-turnstile-response"]');
                dl_url.searchParams.set('cf-turnstile-response', cft_response_field.value)
            }

            // continue the request propagation.
            window.location.href = dl_url;
        })
    })
})

/**
 * Find the sdm download form element from next nearest siblings.
 */
function bpcft_get_dl_form(referenceEL){
    let currentEl = referenceEL;
    currentEl = currentEl?.nextElementSibling;

    let targetForm = null;

    while (currentEl){
        let dl_form = currentEl.querySelector('form.sdm-download-form, .sdm_sf_tpl_container form');
        if (dl_form){
            targetForm = dl_form;
            break;
        }

        currentEl = currentEl?.nextElementSibling;
    }

    return targetForm;
}

/**
 * The successful cft response processor function for sdm download items.
 */
function bpcft_sdm_process_cft_response(token, unique_id){
    console.log('[BPCFT] Cloudflare turnstile challenge for SDM is successful.');

    // const cft_cont_id = 'cf-turnstile-' + '<?php echo esc_js($cft_unique_id) ?>';
    const cft_cont_id = 'cf-turnstile-' + unique_id;
    const cft_cont = document.getElementById(cft_cont_id);

    // Get the download form (if there is any).
    const dl_form = bpcft_get_dl_form(cft_cont);
    if(dl_form){
        // Append cft token as am input field, so that it can be captured after form submission.

        // First check if the token input field already exists or not.
        const existing_cft_token_field = dl_form.querySelector('input[name="cf-turnstile-response"]');
        if (existing_cft_token_field){
            // Input field already exists, update token value.
            existing_cft_token_field.value = token;
        } else {
            const cft_token_field = document.createElement('input');
            cft_token_field.type = 'hidden';
            cft_token_field.name = 'cf-turnstile-response';
            cft_token_field.value = token;

            dl_form.appendChild(cft_token_field);
        }
    } else {
        // Download form not found, It could be a download link button without a form wrap. Do nothing.
    }
}