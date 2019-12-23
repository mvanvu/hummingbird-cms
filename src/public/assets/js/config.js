$('#btn-send-test-mail').on('click', function () {
    UIkit.notification({
        message: cmsCore.language._('please-wait-msg') + ' <span uk-spinner></span>',
        timeout: 20000,
    });
    $.ajax({
        url: cmsCore.uri.base + '/config/testMail',
        type: 'post',
        dataType: 'json',
        data: $('.config-system').find('[name^="FormData["]').serialize(),
        success: function (response) {
            UIkit.notification.closeAll();
            UIkit.notification(response.message, {status: response.status});
        }
    });
});