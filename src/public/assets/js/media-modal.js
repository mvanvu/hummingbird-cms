cmsCore.initMediaModal = function (elementId, multiple) {
    var frame = $('#' + elementId + '-frame'),
        container = $('#' + elementId + '-container'),
        preview = $('#' + elementId + '-preview'),
        imgInput = $('#' + elementId + ''),
        baseUri = cmsCore.uri.root + '/upload/',
        media,
        updateValue = function () {
            imgInput.empty();
            var opt;
            preview.find('[data-src]').each(function () {
                opt = document.createElement('option');
                opt.value = $(this).attr('data-src');
                opt.selected = true;
                imgInput.append(opt);
            });

            imgInput.trigger('change');
        },
        updatePreview = function (media) {
            preview.empty();

            if (typeof media === 'string') {
                media = $.trim(media);
                media = media.length ? [media] : [];
            }

            for (var index in media) {
                if (media[index]) {
                    var temp = $(container.find('script').text().replace('{src}', baseUri + media[index]));
                    temp.find('img').attr('data-src', media[index]);
                    preview.append(temp);
                }
            }
        },
        initValue = function () {
            updatePreview(imgInput.val());
        };

    frame.on('load', function () {
        var contents = frame.contents();
        contents.on('click', 'a.upload-file.image', function (e) {
            e.preventDefault();
            var
                a = $(this),
                media = [],
                opt;
            imgInput.empty();

            if (multiple) {
                a.toggleClass('active');
                contents.find('a.upload-file.image.active img').each(function () {
                    opt = document.createElement('option');
                    opt.value = $(this).attr('src').replace(/^.*\/upload\//g, '');
                    opt.selected = true;
                    media.push(opt.value);
                    imgInput.append(opt);
                });
                updatePreview(media);
                imgInput.trigger('change');
            } else {
                opt = document.createElement('option');
                opt.value = a.find('img').attr('src').replace(/^.*\/upload\//g, '');
                opt.selected = true;
                updatePreview([opt.value]);
                imgInput.append(opt).trigger('change');
                UIkit.modal('#' + elementId + '-modal').hide();
            }
        });

        if (multiple) {
            media = imgInput.val();
            contents.find('a.upload-file.image').removeClass('active');
            $(media).each(function (image) {
                contents.find('img[src="' + baseUri + image + '"]').parent().addClass('active');
            });
        }
    });

    $('a[href="#' + elementId + '-modal"]').on('click', function (e) {
        e.preventDefault();

        if (!frame.hasClass('loaded')) {
            frame.attr('src', frame.data('src')).addClass('loaded');
        }

        UIkit.modal('#' + elementId + '-modal').show();
    });

    container.find('[uk-sortable]').on('moved', updateValue);
    preview.on('click', '.remove', function () {
        $(this).parents('.col-image').remove();
        updateValue();
    });

    initValue();
};

$(document).ready(function () {
    $('.media-element-container').each(function () {
        var el = $(this);
        cmsCore.initMediaModal(el.data('elementId'), el.data('multiple'));
    });
});