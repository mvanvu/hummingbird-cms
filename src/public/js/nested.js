jQuery(document).ready(function ($) {
    var
        nestedContainer = $('#admin-nested-list'),
        list = $('.dd.nestable'),
        token = nestedContainer.data('token'),
        baseUri = nestedContainer.data('baseUri'),
        updateNodes = function () {
            var
                data = list.nestable('serialize'),
                serialize = JSON.stringify(data);

            if (list.data('serialize') === serialize) {
                return true;
            }

            list.data('serialize', serialize);
            $.ajax({
                url: baseUri + '/updateNodes',
                type: 'post',
                dataType: 'json',
                headers: {
                    'X-CSRF-Token': token
                },
                data: {
                    nodes: data,
                },
                success: function (response) {
                    if ('OK' === response) {
                        UIkit.notification(cmsCore.language._('data-rebuilt-msg'), {status: 'success'});
                    }
                }
            });
        };

    list.nestable()
        .data('serialize', JSON.stringify(list.nestable('serialize')))
        .on('change', updateNodes);

    list.on('click', 'a[data-action]', function (e) {
        e.preventDefault();
        var
            a = $(this),
            action = a.data('action'),
            modifyNode = function () {
                $.ajax({
                    url: baseUri + '/modifyNode',
                    type: 'post',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-Token': token
                    },
                    data: {
                        nodeId: a.parents('[data-id]:eq(0)').data('id'),
                        action: action,
                    },
                    success: function (response) {
                        UIkit.notification.closeAll();
                        UIkit.notification(response.message, {status: response.success ? 'success' : 'danger'});

                        if (response.success) {
                            list.html($(response.data).find('.dd.nestable').html());
                            list.data('nestable').init();
                        }
                    }
                });
            };

        if (action === 'U' || action === 'T') {
            UIkit.modal.confirm(cmsCore.language._('modify-node-confirm')).then(
                function () {
                    UIkit.notification('<div uk-spinner="ratio: .85"></div> ' + cmsCore.language._('please-wait-msg'), {timeout: 10000});
                    modifyNode();
                },
                function () {
                }
            );
        } else {
            modifyNode();
        }
    });
});