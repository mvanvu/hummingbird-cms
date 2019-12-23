cmsCore.initUcmElementModal = function (elementId) {
    var $ = jQuery;
    var frame = $('#' + elementId + '-frame');
    var input = $('#' + elementId);
    var container = $('#' + elementId + '-container');
    var list = $('#' + elementId + '-list');
    var multiple = input.prop('multiple');
    var appendItem = function (id, title) {
        $(container.find('[type="template/ucm-item"]').text())
            .appendTo(list).attr('data-id', id).find('.title').text(title);
    };

    var updateValue = function () {
        input.empty();
        var opt;
        list.find('[data-id]').each(function () {
            opt = document.createElement('option');
            opt.value = $(this).attr('data-id');
            opt.selected = true;
            input.append(opt);
        });

        input.trigger('change');
    };

    (function () {
        frame.attr('src', frame.data('src'));
    })();

    frame.on('load', function () {
        var contents = frame.contents();

        contents.on('click', '[data-id] a', function (e) {
            e.preventDefault();
            var a = $(this);
            var p = a.parents('[data-id]');
            var title = $.trim(p.data('title'));

            if (multiple) {
                if (!list.find('[data-id="' + p.data('id') + '"]').length) {
                    UIkit.notification(cmsCore.language._('item-added-success', {title: title}), {status: 'success'});
                    appendItem(p.data('id'), title);
                }

                updateValue();

            } else {
                list.empty();
                appendItem(p.data('id'), title);
                updateValue();
                UIkit.modal('#' + elementId + '-modal').hide();
            }
        });
    });

    list.on('click', 'a.remove', function (e) {
        e.preventDefault();
        $(this).parents('.list-item').remove();
        updateValue();
    });
};