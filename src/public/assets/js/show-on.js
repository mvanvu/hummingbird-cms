jQuery(document).ready(function ($) {
        var
            initShowOn = function () {
                var fields = $('[data-show-on]');
                if (fields.length) {
                    fields.each(function () {
                        fireShowOn($(this));
                    });
                }
            },
            fireShowOn = function (field) {
                var
                    showOnData = field.data('showOn'),
                    willShow = true,
                    targetField, i, n, value, values, first;

                for (i = 0, n = showOnData.length; i < n; i++) {
                    targetField = $('[name="' + showOnData[i].field + '"]');

                    if (!targetField.length) {
                        targetField = $('[name="' + showOnData[i].field + '[]"]');
                    }

                    if (targetField.length) {

                        value = targetField.val();

                        switch (showOnData[i].value) {
                            case 'is not empty':
                                willShow = !!value.length;
                                break;

                            case 'is empty':
                                willShow = !value.length;
                                break;

                            case 'is not checked':
                                willShow = !targetField.prop('checked');
                                break;

                            case 'is checked':
                                willShow = targetField.prop('checked');
                                break;

                            case 'is not selected':
                                willShow = !targetField.prop('selected');
                                break;

                            case 'is selected':
                                willShow = targetField.prop('selected');
                                break;

                            default:
                                first = showOnData[i].value.substring(0, 1);

                                if ('!' === first) {
                                    values = showOnData[i].value.substring(1);

                                    if (-1 === values.indexOf(',')) {
                                        willShow = (values !== value);
                                    } else {
                                        willShow = (values.split(',').indexOf(value) === -1);
                                    }
                                } else {
                                    values = showOnData[i].value;

                                    if (-1 === values.indexOf(',')) {
                                        willShow = (values === value);
                                    } else {
                                        willShow = (values.split(',').indexOf(value) !== -1);
                                    }

                                }

                                break;
                        }

                        if (typeof showOnData[i + 1] === 'object') {
                            if ((willShow && showOnData[i + 1].op === '|')
                                || (!willShow && showOnData[i + 1].op === '&')
                            ) {
                                break;
                            }
                        }
                    }
                }

                willShow ? field.slideDown() : field.slideUp();
            };

        // Init show on
        initShowOn();
        $(document).on('change', 'textarea, input, select', initShowOn);
        $(document).on('initShowOn', initShowOn);
    }
);