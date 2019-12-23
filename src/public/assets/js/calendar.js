jQuery(function ($) {

    window.calendarField = function (idInput, idAlias, format, time) {
        var
            input = $('#' + idInput),
            datepicker = $('#' + idAlias);
        datepicker.prev('a').on('click', function (e) {
            e.preventDefault();
            datepicker.trigger('focus');
        });

        datepicker.datepicker({
            showButtonPanel: true,
            dateFormat: format,
            altFormat: 'yy-mm-dd',
            altField: '#' + idInput,
            showTime: time,
            changeMonth: true,
            changeYear: true,
            yearRange: 'c-30:c+10',
        });

        datepicker.on('change', function () {
            try {
                var date = $.datepicker.parseDate('dd-mm-yy', this.value);
                input.val($.datepicker.formatDate('yy-mm-dd', date));
            } catch (e) {

            }
        });
    };

    $('[data-calendar]').each(function () {
        var
            field = $(this),
            data = field.data('calendar');

        if (!field.hasClass('has-calendar')) {
            field.addClass('has-calendar');
            window.calendarField(data.input, data.alias, data.format, data.time);
        }
    });
});