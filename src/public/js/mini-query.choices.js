_$.fn.choices = function (options) {
    options = Object.assign({
        applyCount: 10,
    }, options || {});
    this.parent('form').attr('autocomplete', 'off');
    this.elements.forEach(function (el) {
        var $el = _$(el);
        if (el.nodeName !== 'SELECT'
            || el.hasAttribute('readonly')
            || $el.prop('disabled')
            || $el.addClass('mini-choices').find('option').length < options.applyCount
            || $el.hasClass('no-choices')
        ) {
            return;
        }

        var box = _$('<div class="uk-card uk-card-small uk-card-default uk-position-absolute uk-position-z-index uk-hidden">' +
            '   <div class="uk-card-header">' +
            '       <input class="uk-input uk-border-pill choices-search" type="text" value="" autocomplete="off"/>' +
            '   </div>' +
            '   <div class="uk-card-body"><div><ul class="uk-nav uk-nav-default"></ul></div></div>' +
            '</div>'),
            nav = box.find('ul.uk-nav'),
            appendOption = function (opt, group) {
                nav.append('<li data-text="' + opt.innerText + '"  data-value="' + (opt.value || '') + '" data-group="' + group + '"><a class="uk-link-muted" href="#">' + opt.innerText + '</a></li>');
            };

        el.childNodes.forEach(function (opt) {
            if (opt.nodeName === 'OPTGROUP' || opt.nodeName === 'OPTION') {
                if (opt.nodeName === 'OPTGROUP') {
                    nav.append('<li class="uk-nav-header uk-text-bold uk-margin-small choices-group" data-group="' + opt.label + '">' + opt.label + '</li>');

                    if (opt.childNodes.length) {
                        opt.childNodes.forEach(function (o) {
                            appendOption(o, opt.label);
                        });
                    }
                } else {
                    appendOption(opt, '');
                }
            }
        });

        box.find('.choices-search').on('keyup', function () {
            var value = _$.trim(this.value).toLowerCase().replace(/\s+/, ' ');
            setTimeout(function () {
                if (value.length) {
                    nav.find('[data-value]').each(function () {

                        if (this.getAttribute('data-text').toLowerCase().replace(/\s+/, ' ').indexOf(value) === -1) {
                            this.setAttribute('hidden', true);
                            var group = this.getAttribute('data-group') || '';

                            if (group.length) {
                                var pGroup = box.find('.choices-group[data-group="' + group + '"]');

                                if (pGroup.nextAll('[data-group="' + group + '"]:not([hidden])').length) {
                                    pGroup.show();
                                } else {
                                    pGroup.hide();
                                }
                            }
                        } else {
                            this.removeAttribute('hidden');
                        }
                    });
                } else {
                    nav.find('[data-value]').show();
                }
            }, 100);

        }).on('blur', function () {
            setTimeout(function () {
                box.hide();
            }, 300);
        });

        box.find('[data-value] > a').on('click', function (e) {
            e.preventDefault();
            $el.val(this.parentNode.getAttribute('data-value')).trigger('change');
            box.hide();
        });

        $el.on('focus', function () {
            box.show();
            setTimeout(function () {
                $el.blur();
                box.find('.choices-search').focus();
            }, 100);
        });

        $el.insert(box).on('choices:refresh', function () {
            box.remove();
            $el.choices(options);
        });

        box.css({
            //width: el.offsetWidth,
            maxHeight: 550,
            overflowY: 'auto',
        });
        $el.find('*').hide();
    });
};

_$.ready(function ($) {
    $('[data-choices-count]').each(function () {
        $(this).choices({
            applyCount: this.getAttribute('data-choices-count') || 10
        });
    });
});