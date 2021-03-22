_$.ready(function ($) {
    var container = $('.comments-container'),
        form = $('#modal-form' + container.data('itemId')),
        baseUri = $hb.uri.root + '/' + container.data('context'),
        resetForm = function () {
            form.find('[name="userComment"]').val('');
            form.data('parentId', 0);
        };

    container.find('a.post-comment').on('click', function (e) {
        e.preventDefault();
        $.http.post(
            baseUri + '/comment',
            {
                referenceId: container.data('itemId'),
                parentId: form.data('parentId') || 0,
                userName: form.find('[name="userName"]').val() || '',
                userEmail: form.find('[name="userEmail"]').val() || '',
                userComment: form.find('[name="userComment"]').val() || '',
            },
            function (response) {
                UIkit.notification(response.message, {status: response.success ? 'success' : 'danger'});

                if (response.success) {
                    var resContainer = $(response.data);
                    container.find('.comments-count').text(resContainer.find('.comments-count').text());
                    container.find('.comments-list').html(resContainer.find('.comments-list').html());
                    UIkit.modal('#' + form.attr('id')).hide();
                    resetForm();
                }
            }
        );
    });

    container.on('click', 'a.view-more', function (e) {
        e.preventDefault();
        var a = $(this);
        $.http.get(
            baseUri + '/comment/' + container.data('itemId') + '/' + container.data('offset'),
            function (response) {
                if (response.success) {
                    a.parent('li').remove();
                    container.find('.comments-list').append($(response.data).find('.comments-list').html());

                    if (container.data('total') <= container.find('.comments-list > [data-comment-id]').length && container.find('a.view-more').length) {
                        container.find('a.view-more').parent('li').remove();
                    }
                }
            }
        );
    });

    container.on('click', 'a.reply', function (e) {
        e.preventDefault();
        var a = $(this);
        UIkit.modal('#' + form.attr('id')).show();
        form.data('parentId', a.data('parentId'));

        setTimeout(function () {
            form.find('[name="userComment"]').val('@' + a.data('author') + ' ').focus();
        }, 100);
    });

    container.on('click', 'a.show-modal', function (e) {
        e.preventDefault();
        resetForm();
        UIkit.modal('#' + form.attr('id')).show();
    });

    container.on('click', 'a.show-replies', function (e) {
        e.preventDefault();
        var a = $(this);
        a.next().removeClass('uk-hidden');
        a.remove();
    });
});