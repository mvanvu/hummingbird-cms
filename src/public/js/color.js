document.querySelectorAll('.color-picker[data-color]').forEach(function (el) {
    Pickr.create({
        el: el,
        default: el.getAttribute('data-color'),
        theme: 'monolith',
        useAsButton: true,
        comparison: false,
        swatches: [
            'rgba(244, 67, 54, 1)',
            'rgba(233, 30, 99, 0.95)',
            'rgba(156, 39, 176, 0.9)',
            'rgba(103, 58, 183, 0.85)',
            'rgba(63, 81, 181, 0.8)',
            'rgba(33, 150, 243, 0.75)',
            'rgba(3, 169, 244, 0.7)'
        ],
        defaultRepresentation: el.getAttribute('data-mode'),
        components: {
            preview: true,
            opacity: true,
            hue: true,
            interaction: {
                hex: false,
                rgba: false,
                hsva: false,
                input: true,
                clear: true,
                save: true
            }
        },
        strings: {
            save: cmsCore.language._('save'),
            clear: cmsCore.language._('clear'),
            cancel: cmsCore.language._('cancel')
        }
    })
        .on('save', function (color, instance) {
            var el = instance.options.el;
            var input = el.parentElement.querySelector('input');

            if (!input) {
                return false;
            }

            if (!color) {
                input.value = '';

                return true;
            }

            switch (el.getAttribute('data-mode')) {
                case 'RGBA':
                    input.value = color.toRGBA().toString();
                    break;
                case 'HSVA':
                    input.value = color.toHSVA().toString();
                    break;

                case 'HSLA':
                    input.value = color.toHSLA().toString();
                    break;

                case 'CMYK':
                    input.value = color.toCMYK().toString();
                    break;

                default:
                    input.value = color.toHEXA().toString();
                    break;
            }

            return true;
        });
});