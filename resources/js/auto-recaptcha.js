var onloadCallback = function() {
    let forms = document.querySelectorAll('form:not([data-no-captcha]):not([method="get"])');

    forms.forEach((form, index) => {
        // create a container div for captcha
        let captchaId = 'captcha-form-' + index;
        let captchaDiv = document.createElement('div');
        captchaDiv.id = captchaId;
        form.appendChild(captchaDiv); // hidden inside the form

        // prevent normal submit
        form.addEventListener('submit', function (e) {
        e.preventDefault();
        grecaptcha.execute(widgetIds[index]);
        });

        // render captcha in invisible mode
        let widgetId = grecaptcha.render(captchaId, {
            'sitekey' : window.RECAPTCHA_SITEKEY,
            'size' : 'invisible',
            'callback' : function(token) {
                // on success, submit the form
                form.submit();
            }
        });

        // store widget id for later execution
        if (typeof window.widgetIds === 'undefined') {
            window.widgetIds = [];
        }
        window.widgetIds[index] = widgetId;
    });
};