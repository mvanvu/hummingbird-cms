$(document).on('keyup', '.icon-input', function (e) {
    e.preventDefault();
    var
        value = $.trim(this.value),
        drop = $(this).next('.icon-drop');

    if (value.length > 1) {
        drop.find('[data-icon]').each(function () {
            if (this.getAttribute('data-icon').indexOf(value) === -1) {
                this.hidden = true;
            } else {
                this.hidden = false;
            }
        });

        drop.prop('hidden', false);
    } else {
        drop.prop('hidden', true);
    }
});

$(document).on('click', '.icon-list > li > [data-icon]', function (e) {
    e.preventDefault();
    var
        icon = $(this),
        drop = icon.parents('.icon-drop:eq(0)'),
        value = icon.data('icon'),
        input = drop.prev('.icon-input').val(value);
    drop.prop('hidden', true);
    input.prev('.uk-form-icon').remove();
    input.before('<span class="uk-form-icon"><svg class="icon-' + value + '" width="20" height="20"><use xlink:href="' + cmsCore.uri.root + '/assets/images/icons.svg#icon-' + value + '"></use></svg></span>');
});