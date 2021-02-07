_$('input[name="requestType"]').on('change', function () {
    if (this.value === 'P') {
        _$('#user-reset-pwd-desc').removeClass('uk-hidden');
        _$('#user-remind-username-desc').addClass('uk-hidden');
    } else {
        _$('#user-reset-pwd-desc').addClass('uk-hidden');
        _$('#user-remind-username-desc').removeClass('uk-hidden');
    }
});