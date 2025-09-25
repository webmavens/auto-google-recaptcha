function getRecaptchaConfig() {
    const el = document.getElementById('recaptcha-config');
    if (!el) return {};
    try {
        return JSON.parse(el.textContent);
    } catch (e) {
        console.error('Invalid reCAPTCHA config');
        return {};
    }
}

var onloadCallback = function () {
    const { sitekey, other_config } = getRecaptchaConfig();
    const allowedMethodsArr = other_config.allowed_methods || ['POST', 'PUT', 'DELETE'];
    const allowedMethods = allowedMethodsArr.map(m => m.toLowerCase());

    const forms = Array.from(document.querySelectorAll('form:not([data-no-captcha])'))
    .filter(form => allowedMethods.includes(form.method.toLowerCase()));

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
            'sitekey': sitekey,
            'size': 'invisible',
            'callback': function (token) {
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

    // Add invisible captcha for ajax requests
    const ajaxDiv = document.createElement('div');
    ajaxDiv.id = 'captcha-ajax';
    ajaxDiv.style.display = 'none';
    document.body.appendChild(ajaxDiv);

    const originalAjax = $.ajax;
    let ajaxCaptchaWidgetId;

    // Render one invisible captcha for ajax requests
    ajaxCaptchaWidgetId = grecaptcha.render('captcha-ajax', {
        'sitekey': sitekey,
        'size': 'invisible'
    });

    // Override $.ajax globally
    $.ajax = function (options) {
        const isPostOrPut = options.type && allowedMethods.includes(options.type.toLowerCase());
        const isRecaptchaUrl = options.url && options.url.includes('google.com/recaptcha/api');
        const disableCaptcha = options.disableCaptcha === true;

        if (isPostOrPut && !isRecaptchaUrl && !disableCaptcha && ajaxCaptchaWidgetId !== undefined) {
            return new Promise(function (resolve, reject) {
                grecaptcha.execute(ajaxCaptchaWidgetId).then(function (token) {
                    // Add token to request
                    if (typeof options.data === 'string') {
                        options.data += (options.data ? '&' : '') + `g-recaptcha-response=${encodeURIComponent(token)}`;
                    } else if (options.data instanceof FormData) {
                        options.data.append('g-recaptcha-response', token);
                    } else if (typeof options.data === 'object') {
                        options.data['g-recaptcha-response'] = token;
                    } else {
                        options.data = { 'g-recaptcha-response': token };
                    }

                    // Send request
                    const jqXHR = originalAjax.call($, options);

                    // Reset after completion
                    jqXHR.always(() => grecaptcha.reset(ajaxCaptchaWidgetId));

                    jqXHR.then(resolve).catch(reject);
                });
            });
        } else {
            return originalAjax.call($, options);
        }
    };
};