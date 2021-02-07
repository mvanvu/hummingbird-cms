_$('#btn-send-test-mail').on('click', function () {
    UIkit.notification({
        message: cmsCore.language._('please-wait-msg') + ' <span uk-spinner></span>',
        timeout: 20000,
    });

    const data = {};
    _$('.config-system').find('[name^="FormData["]').each(function () {
        data[this.name] = this.value;
    });

    _$.http.post(cmsCore.uri.base + '/config/testMail', data, function(response) {
        UIkit.notification.closeAll();
        UIkit.notification(response.message, {status: response.status});
    });
});