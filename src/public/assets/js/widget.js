var
    $ = window.jQuery,
    widgetContainer = $('#widget-container'),
    ajaxData = widgetContainer.data('ajax');
widgetContainer.find('button.add').on('click', function () {
    var
        btn = $(this),
        widgetData = {
            requestType: 'loadWidget',
            name: btn.data('widget'),
            position: btn.parents('.action-box:eq(0)').find('select').val(),
        };
    widgetData[ajaxData.token] = 1;

    $.ajax({
        url: ajaxData.uri,
        type: 'post',
        dataType: 'json',
        data: widgetData,
        success: function (response) {
            var
                posArea = $('[data-position="' + widgetData.position + '"]'),
                form = $(response).appendTo(posArea);
            form.find('[name="FormData[position]"]').val(widgetData.position);
            form.find('.toggle-body').addClass('refresh-codemirror').prop('hidden', false);

            if (form.find('textarea.js-editor-tinyMCE').length) {
                form.find('textarea.js-editor-tinyMCE').each(function () {
                    cmsCore.initTinyMCE(this, 250);
                });
            }

            if (form.find('textarea.js-editor-codemirror').length) {
                form.find('textarea.js-editor-codemirror').each(function () {
                    cmsCore.initCodeMirror(this);
                });
            }

            form.find('.media-element-container').each(function () {
                var mediaElement = $(this);
                cmsCore.initMediaModal(mediaElement.data('elementId'), mediaElement.data('multiple'));
            });

            form.find('[data-ucm-item-element-id]').each(function () {
                cmsCore.initUcmElementModal(this.getAttribute('data-ucm-item-element-id'));
            });
        }
    });
});

widgetContainer.on('click', '.widget-toggle', function (e) {
    e.preventDefault();
    var toggleBody = $(this).parents('.toggle-parent:eq(0)').find('.toggle-body');
    toggleBody.prop('hidden', !toggleBody.prop('hidden'));

    if (!toggleBody.hasClass('refresh-codemirror')) {
        toggleBody.addClass('refresh-codemirror');
        toggleBody.find('textarea.js-editor-codemirror').each(function () {
            var editor = $(this).data('editor');

            if (editor) {
                editor.refresh();
            }
        });
    }
});

widgetContainer.on('keyup', 'input[name="FormData[title]"]', function () {
    $(this).parents('.widget-item').find('.widget-title').text(this.value);
});

var reorderWidgets = function () {
    var
        postData = {
            requestType: 'orderWidget',
            widgets: {},
        },
        position,
        widgetPosition,
        widgetItem;
    postData[ajaxData.token] = 1;
    widgetContainer.find('[data-position]').each(function () {
        widgetPosition = $(this);
        position = widgetPosition.data('position');
        postData.widgets[position] = [];
        widgetPosition.find('>.widget-item').each(function () {
            widgetItem = $(this);
            postData.widgets[position].push($(this).find('[name="FormData[id]"]').val());
        });
    });

    $.ajax({
        url: ajaxData.uri,
        type: 'post',
        dataType: 'json',
        data: postData,
        success: function (response) {
            UIkit.notification(cmsCore.language._('ordering-updated-msg'), {status: 'success'});
        }
    });
};

widgetContainer.on('click', '.widget-delete, .widget-save', function (e) {
    e.preventDefault();
    var
        a = $(this),
        widgetItem = a.parents('.widget-item:eq(0)'),
        widgetId = widgetItem.find('[name="FormData[id]"]').val(),
        postData = {};
    postData[ajaxData.token] = 1;

    if (a.hasClass('widget-delete')) {
        UIkit.modal.confirm(cmsCore.language._('confirm-delete-widget')).then(function () {
            widgetItem.remove();
            if (widgetId) {
                postData.requestType = 'deleteWidget';
                postData.id = widgetId;
                $.ajax({
                    url: ajaxData.uri,
                    type: 'post',
                    dataType: 'json',
                    data: postData,
                    success: function (response) {
                        UIkit.notification(cmsCore.language._('widget-removed-msg'), {status: 'success'});
                    }
                });
            }

        }, function () {
        });
    } else {
        a.prepend('<span uk-spinner="ratio: .45"/>');
        postData.requestType = 'saveWidget';
        postData.serialize = widgetItem.find('[name^="FormData["]').serialize();
        $.ajax({
            url: ajaxData.uri,
            type: 'post',
            dataType: 'json',
            data: postData,
            success: function (response) {
                a.find('[uk-spinner]').remove();
                if (response.success) {
                    widgetItem.find('input[name="FormData[id]"]').val(response.data.id);
                    widgetItem.find('.toggle-body').prop('hidden', true);
                    UIkit.notification(cmsCore.language._('widget-saved-msg'), {status: 'success'});
                    if (!widgetId) {
                        reorderWidgets();
                    }
                } else {
                    UIkit.notification(response.message, {status: 'danger'});
                }
            }
        });
    }
});

widgetContainer.on('stop', '[uk-sortable]', reorderWidgets);