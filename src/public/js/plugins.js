_$.ready(function ($) {
    $('.admin-plugins-list-form .switcher-plugin').on('change', function (e) {
        e.preventDefault();
        var el = this,
            input = $(el),
            form = $('#admin-list-form.admin-plugins-list-form'),
            row = input.parent('[data-sort-id]');
        UIkit.modal.confirm(
            cmsCore.language._((input.prop('checked') ? 'activate' : 'deactivate') + '-plugin-confirm', {
                title: row.attr('data-title'),
            }))
            .then(function () {
                form.attr('action', $hb.uri.base + '/plugin/toggle/' + row.attr('data-sort-id'))
                form.submit();
            }, function () {
                el.checked = !el.checked;
            });
    });

    $('.admin-plugins-list-form .btn-uninstall').on('click', function (e) {
        e.preventDefault();
        var el = $(this),
            row = el.parent('tr[data-sort-id]');
        UIkit.modal.confirm(
            $hb.language._('uninstall-plugin-confirm', {
                title: row.attr('data-title'),
            }))
            .then(function () {
                UIkit.notification('<span uk-spinner></span> ' + $hb.language._('please-wait-msg'), {timeout: 50000});
                $.http.delete(
                    $hb.uri.base + '/plugin/uninstall-package/' + row.attr('data-sort-id'),
                    function (response) {
                        UIkit.notification.closeAll();

                        if (response.success) {
                            location.reload();
                        } else {
                            UIkit.notification(response.message, {status: 'danger'});
                        }
                    }
                );
            }, function () {
                el.checked = !el.checked;
            });
    });

    var installModal = $('#plugin-modal-container');
    installModal.find('input[type="file"]').on('change', function () {
        if (this.files.length) {
            var formData = new FormData(),
                file = this.files[0];
            formData.append('package', file);
            this.value = null;
            UIkit.notification('<span uk-spinner></span> ' + $hb.language._('please-wait-msg'), {timeout: 50000});
            $.http.request(
                $hb.uri.base + '/plugin/install-package',
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': null,
                    },
                    processData: false,
                    data: formData,
                    success: function (response) {
                        UIkit.notification.closeAll();

                        if (response.success) {
                            location.reload();
                        } else {
                            UIkit.notification(response.message, {status: 'danger'});
                        }
                    }
                }
            );
        }
    });

    installModal.on('click', '.btn-install', function (e) {
        e.preventDefault();
        var a = $(this),
            source = this.href;

        if (source) {
            a.addClass('uk-disabled');
            UIkit.notification('<span uk-spinner></span> ' + $hb.language._('please-wait-msg'), {timeout: 50000});
            $.http.post(
                $hb.uri.base + '/plugin/install-package',
                {
                    source: source,
                },
                function (response) {
                    a.removeClass('uk-disabled');
                    UIkit.notification.closeAll();

                    if (response.success) {
                        location.reload();
                    } else {
                        UIkit.notification(response.message, {status: 'danger'});
                    }
                }
            );
        }
    });

    $('a.toolbar-installation-packages').on('click', function (e) {
        e.preventDefault();
        UIkit.modal(installModal.element).show();
    });

    $.http.get($hb.uri.base + '/plugin/get-packages', function (html) {
        installModal.find('.uk-modal-body').html(html);
    });
});