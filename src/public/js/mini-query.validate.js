_$.fn.validate = function () {
    let isValid = true;
    this.elements.forEach(function (el) {
        let value = el.value;
        const $el = _$(el);
        const messages = [];

        if (!$el.hasClass('has-validate-handle')) {
            $el.addClass('has-validate-handle');
            $el.on('change', function () {
                $el.validate();
            });
        }

        if (el.hasAttribute('required')) {

            if (typeof value === 'string') {
                value = _$.trim(el.value);
            }

            if (!value.length) {
                messages.push(el.getAttribute('data-msg-required') || 'The value is required');
            }
        }

        for (let dk in el.dataset) {

            if (dk.indexOf('rule') !== 0) {
                continue;
            }

            let rule = dk.substring(4);
            let ruleValue = el.dataset[dk];
            let msg = el.dataset['msg' + rule];

            if (rule) {

                switch (rule) {
                    case 'Date':

                        if (isNaN(Date.parse(value))) {
                            messages.push(msg || 'The value is not a valid date');
                        }

                        break;

                    case 'Email':

                        if (!value.match(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/g)) {
                            messages.push(msg || 'The email is invalid');
                        }

                        break;

                    case 'Digits':

                        if (!value.match(/^[0-9]*$/g)) {
                            messages.push(msg || 'Please enter only digits');
                        }

                        break;

                    case 'Min':

                        if (parseInt(value) < parseInt(ruleValue)) {
                            messages.push(msg || 'The value must lesser than ' + ruleValue);
                        }

                        break;

                    case 'MinLength':

                        if (value.length < parseInt(ruleValue)) {
                            messages.push(msg || 'The minimum length of value is ' + ruleValue);
                        }

                        break;

                    case 'Max':

                        if (parseInt(value) > parseInt(ruleValue)) {
                            messages.push(msg || 'The value must greater than ' + ruleValue);
                        }

                        break;

                    case 'MaxLength':

                        if (value.length > parseInt(ruleValue)) {
                            messages.push(msg || 'The maximum length of value is ' + ruleValue);
                        }

                        break;

                    case 'Number':

                        if (isNaN(value)) {
                            messages.push(msg || value + ' is not a number');
                        }

                        break;

                    case 'EqualTo':

                        const target = _$(ruleValue);

                        if (!target.length || value !== target.val()) {
                            messages.push(msg || 'The value is not match');
                        }

                        break;

                    case 'Regex':

                        const regex = new RegExp(ruleValue, 'g');

                        if (!regex.test(value)) {
                            messages.push(msg || 'The regex pattern is not match');
                        }

                        break;
                }
            }
        }

        const p = $el.parent('.mq-validate-input-wrap');

        if (p.length) {
            p.find('.mq-validate-text-warning').remove();
        } else {
            $el.siblings('.mq-validate-text-warning').remove();
        }

        if (messages.length) {
            isValid = false;
            $el.addClass('invalid uk-form-danger');
            const m = '<div class="mq-validate-text-warning uk-text-danger uk-text-italic text-danger font-italic">' + messages.join('<br/>') + '</div>';
            p.length ? p.append(m) : $el.insert(m);
        } else {
            $el.removeClass('invalid uk-form-danger');
        }
    });

    if (!isValid) {
        document.querySelector('.invalid').focus();
    }

    return isValid;
};

_$.ready(function ($) {
    $('form[data-form-validation]').on('submit', function (e) {
        if (!$(this).find('input, select, textarea').validate()) {
            e.preventDefault();
        }
    });
});