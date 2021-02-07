(function ($) {
    var widgetContainer = $('#widget-container'),
        widgetModal = $('#widget-modal-container'),
        createIFrame = function (position, name, id, title) {
            var iframe = $('<iframe/>');
            iframe.attr('src', cmsCore.uri.base + '/widget/' + position + '/' + name + '/' + id);
            iframe.attr('class', 'uk-width-1-1');
            iframe.on('load', function () {
                var h = this.contentWindow.document.documentElement.scrollHeight;

                if (h < 250) {
                    h = 250;
                }

                this.style.height = h + 'px';
            });

            widgetModal.find('iframe').remove();
            widgetModal.find('.uk-modal-body').append(iframe);
            widgetModal.find('.uk-modal-title').text(title);
            UIkit.modal(widgetModal.get(0).element).show();

            return iframe;
        };
    widgetModal.on('hide', function () {
        if (widgetModal.find('iframe').contents().find('.widget-item').data('refresh')) {
            location.reload();
        }
    });
    widgetContainer.find('button.add').on('click', function () {
        var btn = $(this),
            name = btn.data('widgetName'),
            position = btn.parent('.action-box').find('select').val();
        createIFrame(position, name, 0, btn.data('widgetTitle'));
    });

    widgetContainer.on('click', '.widget-toggle', function (e) {
        e.preventDefault();
        var toggleBody = $(this).parent('.toggle-parent').find('.toggle-body');
        toggleBody.prop('hidden', !toggleBody.prop('hidden'));
    });

    widgetContainer.on('click', '.widget-edit, .widget-delete, .widget-save', function (e) {
        e.preventDefault();
        var a = $(this),
            widgetItem = a.parent('.widget-item'),
            position = widgetItem.data('position'),
            name = widgetItem.data('name'),
            title = widgetItem.data('title'),
            id = widgetItem.data('id');

        if (a.hasClass('widget-delete')) {
            UIkit.modal.confirm(cmsCore.language._('confirm-delete-widget', {title: title})).then(function () {
                a.append('<span uk-spinner></span>', true);
                $.http.delete(cmsCore.uri.base + '/widget/' + position + '/' + name + '/' + id, function (response) {
                    UIkit.notification(cmsCore.language._('widget-removed-msg'), {status: 'success'});
                    widgetItem.remove();
                });
            }, function () {
            });
        } else if (a.hasClass('widget-edit')) {
            createIFrame(position, name, id, title);
        } else {
            widgetModal.find('iframe').contents().find('form').submit();
        }
    });

    widgetContainer.on('stop', '[uk-sortable]', function () {
        var widgets = {};
        widgetContainer.find('.widget-item[data-position]').each(function () {
            var item = $(this),
                pos = item.parent('.widget-position[data-position]').data('position');

            if (!widgets[pos]) {
                widgets[pos] = [];
            }

            widgets[pos].push(item.data('id'));
        });

        $.http.post(cmsCore.uri.base + '/widget/order', {widgets: widgets}, function (response) {
            UIkit.notification(cmsCore.language._('ordering-updated-msg'), {status: 'success'});
        });
    });

    $('#widgetTemplateId').on('change', function () {
        $(this).parent('form').submit();
    });
})(_$);