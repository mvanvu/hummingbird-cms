$('.options-repeat-container').each(function () {
    var
        optContainer = $(this),
        fieldValue = $('#' + optContainer.data('inputId'));

    optContainer.on('click', '.add, .remove', function (e) {
        e.preventDefault();
        var
            a = $(this),
            row = a.parents('.row:eq(0)');

        if (a.hasClass('add')) {
            row.after(row.clone());
        } else {
            if (optContainer.find('.row').length > 1) {
                row.remove();
            } else {
                row.find('input').val('');
            }
        }
    });

    if (fieldValue.val().length) {
        try {
            var
                options = JSON.parse(fieldValue.val()),
                row = optContainer.find('.row'),
                newRow, value;
            if (Object.keys(options).length) {
                for (value in options) {
                    newRow = row.clone();
                    newRow.find('.text').val(options[value]);
                    newRow.find('.value').val(value);
                    optContainer.append(newRow);
                }

                row.remove();
            }
        } catch (e) {

        }
    }

    optContainer.parents('form').on('submit', function () {
        var
            options = {},
            r, t, v;

        optContainer.find('.row').each(function () {
            r = $(this);
            t = $.trim(r.find('.text').val());
            v = $.trim(r.find('.value').val());

            if (t.length) {
                options[v] = t;
            }
        });

        fieldValue.val(JSON.stringify(options));

        return true;
    });
});