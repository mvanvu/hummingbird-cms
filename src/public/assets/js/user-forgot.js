$('input[name="requestType"]').on('change', function () {
    if (this.value === 'P') {
        $('#user-reset-pwd-desc').removeClass('uk-hidden');
        $('#user-remind-username-desc').addClass('uk-hidden');
    } else {
        $('#user-reset-pwd-desc').addClass('uk-hidden');
        $('#user-remind-username-desc').removeClass('uk-hidden');
    }
});