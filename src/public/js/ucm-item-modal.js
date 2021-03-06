$hb.initUcmElementModal = function (elementId) {
    var $ = _$,
        frame = $('#' + elementId + '-frame'),
        input = $('#' + elementId),
        container = $('#' + elementId + '-container'),
        list = $('#' + elementId + '-list'),
        multiple = input.prop('multiple'),
        appendItem = function (id, title) {
            $(container.find('[type="template/ucm-item"]').text())
                .appendTo(list).attr('data-id', id).find('.title').text(title);
        },
        updateValue = function () {
            var opt;
            input.empty();
            list.find('[data-id]').each(function () {
                opt = document.createElement('option');
                opt.value = $(this).attr('data-id');
                opt.checked = true;
                input.append(opt);
            });

            input.trigger('change');
        };

    frame.attr('src', frame.attr('data-src'));
    frame.on('load', function () {
        var mQ = this.contentWindow._$,
            contents = mQ(this.contentDocument);
        contents.on('click', '[data-id] a', function (e) {
            e.preventDefault();
            var a = mQ(this),
                p = a.parent('[data-id]'),
                title = mQ.trim(p.attr('data-title'));

            if (multiple) {
                if (!list.find('[data-id="' + p.attr('data-id') + '"]').length) {
                    UIkit.notification(cmsCore.language._('item-added-success', {title: title}), {status: 'success'});
                    appendItem(p.attr('data-id'), title);
                }

                updateValue();

            } else {
                list.empty();
                appendItem(p.attr('data-id'), title);
                updateValue();
                UIkit.modal('#' + elementId + '-modal').hide();
            }
        });
    });

    list.on('click', 'a.remove', function (e) {
        e.preventDefault();
        $(this).parent('.list-item').remove();
        updateValue();
    });
};