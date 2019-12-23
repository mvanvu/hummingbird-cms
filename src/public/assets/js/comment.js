var commentsContainer = $('.comments-container');
if (commentsContainer.length) {
    commentsContainer.on('click', 'a[href^="#comment-form"]', function (e) {
        e.preventDefault();
        $('[id^="comment-form"]').addClass('uk-hidden');

        var
            a = $(this),
            target = $(a.prop('hash')),
            author = a.data('targetAuthor');
        if (a.hasClass('close')) {
            target.addClass('uk-hidden');
        } else {
            target.removeClass('uk-hidden');
            target.find('[name="userComment"]').val(author ? '@' + author + ' ' : author).focus();
            $('html, body').animate({
                scrollTop: target.offset().top
            }, 600);
        }

    });

    commentsContainer.on('click', 'a.post-comment', function (e) {
        e.preventDefault();
        var
            a = $(this),
            formContainer = a.parents('.comment-form-container:eq(0)'),
            data = {
                userName: formContainer.find('[name="userName"]').val(),
                userEmail: formContainer.find('[name="userEmail"]').val(),
                userComment: formContainer.find('[name="userComment"]').val(),
                referenceId: commentsContainer.data('referenceId'),
                parentId: parseInt(a.data('parentId')),
                type: a.data('type'),
            };
        $.ajax({
            url: cmsCore.uri.base + '/' + commentsContainer.data('referenceContext') + '/comment',
            type: 'post',
            dataType: 'json',
            data: data,
            success: function (responseJson) {
                UIkit.notification(responseJson.message, {status: responseJson.status});
                var
                    commentInstance = $(responseJson.data).find('[data-comment-id]:eq(0)'),
                    parentTarget;

                if (commentInstance.length) {
                    if (data.parentId) {
                        parentTarget = commentsContainer.find('[data-comment-id="' + data.parentId + '"]');
                        parentTarget.html(commentInstance.html());
                        parentTarget.find('a[href^="#replies-4-"]').trigger('click');
                    } else {
                        parentTarget = commentsContainer.find('.comment-list-container[data-length]');

                        if (parentTarget.find('.view-more').length) {
                            parentTarget.find('.view-more').parent().before(commentInstance);
                        } else {
                            parentTarget.append(commentInstance);
                        }

                        a.parents('.comment-form-container:eq(0)').addClass('uk-hidden').find('input,textarea').val('');
                    }
                }
            }
        });
    });

    commentsContainer.on('click', 'a[href^="#replies-4-"]', function (e) {
        e.preventDefault();

        var
            a = $(this),
            target = $(a.attr('href'));

        if (target.length) {
            target.prop('hidden', !target.prop('hidden'));
            a.text(a.data(target.prop('hidden') ? 'showText' : 'hideText'));
        }
    });

    commentsContainer.on('click', 'a.view-more', function (e) {
        e.preventDefault();
        var
            a = $(this),
            commentsList = commentsContainer.find('.comment-list-container[data-length]'),
            length = parseInt(commentsList.attr('data-length')),
            total = parseInt(commentsList.attr('data-total-items'));
        $.ajax({
            url: cmsCore.uri.base + '/' + commentsContainer.data('referenceContext') + '/comment/' + length,
            type: 'post',
            dataType: 'json',
            data: {
                referenceId: commentsContainer.data('referenceId'),
            },
            success: function (responseJson) {
                if (responseJson.data.length) {
                    a.parent().remove();
                    var newList = $(responseJson.data).find('.comment-list-container[data-length]');

                    if (newList.length) {
                        length += parseInt(newList.attr('data-length'));

                        if (total <= length) {
                            newList.find('a.view-more').parent().remove();
                        }

                        commentsList.append(newList.html());
                        commentsList.attr('data-length', length);
                    }
                }
            }
        });
    });
}