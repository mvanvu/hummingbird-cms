var
    bar = document.getElementById('js-progressbar'),
    mediaContainer = $('#media-container'),
    token = mediaContainer.data('token'),
    baseUri = cmsCore.uri.base,
    subDirs = mediaContainer.data('subDirs');
UIkit.upload('.js-upload', {
    url: baseUri + '/media/upload/' + subDirs,
    multiple: true,
    mime: 'image/*',
    beforeSend: function (environment) {
        environment.headers['X-CSRF-Token'] = token;
    },
    loadStart: function (e) {
        bar.removeAttribute('hidden');
        bar.max = e.total;
        bar.value = e.loaded;
    },

    progress: function (e) {
        bar.max = e.total;
        bar.value = e.loaded;
    },

    loadEnd: function (e) {
        bar.max = e.total;
        bar.value = e.loaded;
    },

    complete: function (xhr) {
        try {
            var responseJson = JSON.parse(xhr.responseText);
            mediaContainer.find('.upload-assets-container').html(responseJson.outputHTML);
            UIkit.notification(responseJson.message, {status: responseJson.success ? 'success' : 'danger'});

        } catch (err) {
            console.log(err);
        }
    },

    completeAll: function (xhr) {
        setTimeout(function () {
            bar.setAttribute('hidden', 'hidden');
        }, 1000);
    }

});

mediaContainer.on('click', 'a.upload-dir button, a.upload-file button', function (e) {
    e.preventDefault();
    e.stopPropagation();
    var
        a = $(this).parent(),
        delDirText = cmsCore.language._('confirm-remove-folder'),
        delImageText = cmsCore.language._('confirm-remove-image');
    UIkit.modal.confirm(a.hasClass('upload-dir') ? delDirText : delImageText).then(function () {
        $.ajax({
            url: a.attr('href'),
            type: 'delete',
            dataType: 'json',
            headers: {
                'X-CSRF-Token': token,
            },
            success: function (response) {
                if (response.success) {
                    a.remove();
                    UIkit.notification(response.message, {status: 'success'})
                } else {
                    UIkit.notification(response.message, {status: 'danger'})
                }
            }
        });
    }, function () {
    });
});

$('#toggle-folder a').on('click', function (e) {
    e.preventDefault();
    var input = $(this).next();

    if (input.val().length) {
        $.ajax({
            url: baseUri + '/media/createFolder/' + subDirs,
            type: 'post',
            dataType: 'json',
            headers: {
                'X-CSRF-Token': token
            },
            data: {
                name: input.val()
            },
            success: function (response) {
                if (response.success) {
                    input.val('');
                    mediaContainer.find('.upload-assets-container').html(response.outputHTML);
                    UIkit.notification(response.message, {status: 'success'})
                } else {
                    UIkit.notification(response.message, {status: 'danger'})
                }
            }
        });
    }
});