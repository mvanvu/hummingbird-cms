_$.ready(function ($) {
    const commentsContainer = $('.comments-container');
    if (commentsContainer.length) {
        commentsContainer.on('click', 'a[href^="#comment-form"]', function (e) {
            e.preventDefault();
            $('[id^="comment-form"]').addClass('uk-hidden');
            const a = $(this),
                target = $(a.prop('hash')),
                author = a.data('targetAuthor');
            if (a.hasClass('close')) {
                target.addClass('uk-hidden');
            } else {
                target.removeClass('uk-hidden');
                target.find('[name="userComment"]').val(author ? '@' + author + ' ' : author).focus();
                $('html, body').scrollTo(target.offset().top);
            }
        });

        commentsContainer.on('click', 'a.post-comment', function (e) {
            e.preventDefault();
            const a = $(this),
                formContainer = a.parent('.comment-form-container'),
                data = {
                    userName: formContainer.find('[name="userName"]').val(),
                    userEmail: formContainer.find('[name="userEmail"]').val(),
                    userComment: formContainer.find('[name="userComment"]').val(),
                    referenceId: commentsContainer.data('referenceId'),
                    parentId: parseInt(a.data('parentId')),
                    type: a.data('type'),
                };

            $.http.post(cmsCore.uri.base + '/' + commentsContainer.data('referenceContext') + '/comment', data, function (responseJson) {
                UIkit.notification(responseJson.message, {status: responseJson.status});
                let commentInstance = $(responseJson.data).find('[data-comment-id]'),
                    parentTarget;

                if (commentInstance.length) {
                    if (data.parentId) {
                        parentTarget = commentsContainer.find('[data-comment-id="' + data.parentId + '"]');
                        parentTarget.html(commentInstance.html());
                        parentTarget.find('a[href^="#replies-4-"]').trigger('click');
                    } else {
                        parentTarget = commentsContainer.find('.comment-list-container[data-length]');

                        if (parentTarget.find('.view-more').length) {
                            parentTarget.find('.view-more').parent().insert(commentInstance, true);
                        } else {
                            parentTarget.append(commentInstance);
                        }

                        a.parent('.comment-form-container').addClass('uk-hidden').find('input,textarea').val('');
                    }
                }
            });
        });

        commentsContainer.on('click', 'a[href^="#replies-4-"]', function (e) {
            e.preventDefault();

            const a = $(this),
                target = $(a.attr('href'));

            if (target.length) {
                target.prop('hidden', !target.prop('hidden'));
                a.text(a.data(target.prop('hidden') ? 'showText' : 'hideText'));
            }
        });

        commentsContainer.on('click', 'a.view-more', function (e) {
            e.preventDefault();
            let a = $(this),
                commentsList = commentsContainer.find('.comment-list-container[data-length]'),
                length = parseInt(commentsList.attr('data-length')),
                total = parseInt(commentsList.attr('data-total-items'));
            $.http.post(cmsCore.uri.base + '/' + commentsContainer.data('referenceContext') + '/comment/' + length, {
                referenceId: commentsContainer.data('referenceId'),
            }, function (responseJson) {
                if (responseJson.data.length) {
                    a.parent().remove();
                    const newList = $(responseJson.data).find('.comment-list-container[data-length]');

                    if (newList.length) {
                        length += parseInt(newList.attr('data-length'));

                        if (total <= length) {
                            newList.find('a.view-more').parent().remove();
                        }

                        commentsList.append(newList.html());
                        commentsList.attr('data-length', length);
                    }
                }
            });
        });
    }
});