_$.ready(function($){
    $('.options-repeat-container').each(function () {
        const optContainer = $(this),
            fieldValue = $('#' + optContainer.data('inputId'));

        optContainer.on('click', '.add, .remove', function (e) {
            e.preventDefault();
            const a = $(this), row = a.parent('.row');

            if (a.hasClass('add')) {
                row.insert(row.clone());
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
                let options = JSON.parse(fieldValue.val()),
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

        optContainer.parent('form').on('submit', function () {
            var options = {}, r, t, v;
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
});