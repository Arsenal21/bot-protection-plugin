/**
 * Append the cft response token to the manual checkout payload.
 */
document.addEventListener('wpscOnManualCheckoutSubmit', function (e){
    const {paymentForm, payload} = e.detail;

    const formData = new FormData(paymentForm);

    payload['bpcftResponse'] = formData.get('cf-turnstile-response');
})