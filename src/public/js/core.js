var html = document.documentElement,
    cmsCore = $hb = window.cmsCore || {
        uri: {
            isHome: html.getAttribute('data-uri-home') === '1',
            root: html.getAttribute('data-uri-root') || '',
            base: html.getAttribute('data-uri-base') || '',
        },
        public: function (asset) {
            return $hb.uri.root + '/' + asset;
        },
        language: {
            strings: {},
            load: function (objData) {
                cmsCore.language.strings = Object.assign(cmsCore.language.strings, objData);
            },
            _: function (string, placeholders) {

                let ret = string;

                if (cmsCore.language.strings.hasOwnProperty(string)) {
                    ret = cmsCore.language.strings[string];

                    if (typeof placeholders === 'object') {

                        for (let k in placeholders) {
                            ret = ret.replace('%' + k + '%', placeholders[k]);
                        }

                        return ret;
                    }
                }

                return ret;
            }
        },
        socket: {
            get: function (context) {
                return window['hbWebSocket' + context.ucFirst()] || null;
            },
            create: function (context, options) {
                var sok = {
                    instance: null,
                    options: {},
                    send: function () {
                    },
                };

                if (!'WebSocket' in window) {
                    if (window.MozWebSocket) {
                        window.WebSocket = MozWebSocket;
                    } else {
                        console.warn('Your browser is not support WebSocket!');
                        return sok;
                    }
                }

                options = Object.assign({
                    host: location.host,
                    port: 2053, // Default port which supports the both Cloudflare and localhost
                    ssl: location.protocol === 'https:',
                    plugin: '',
                    params: {},
                    onOpen: null,
                    onMessage: null,
                    onError: null,
                    onClose: null,
                }, options || {});

                options.host = options.host.replace(/:[0-9]+$/g, '');
                var url = (options.ssl ? 'wss' : 'ws') + '://' + options.host + ':' + options.port.toString() + '/hb/io/ws/' + options.plugin,
                    token = document.head.querySelector('meta[name="csrf"]');

                if (token) {
                    options.params.CSRFToken = token.getAttribute('content');
                }

                window.hbSocketQueues = window.hbSocketQueues || [];

                try {
                    sok.options = options;
                    sok.instance = new WebSocket(url);
                    sok.instance.addEventListener('open', function () {
                        if (window.hbSocketQueues.length) {
                            for (var i = 0, n = window.hbSocketQueues.length; i < n; i++) {
                                sok.instance.send(window.hbSocketQueues[i]);
                                window.hbSocketQueues.splice(i, 1);
                            }
                        }
                    });

                    if (typeof options.onClose !== 'function') {
                        options.onClose = function () {
                            console.log('Socket is closed. Reconnect will be attempted in 3 seconds...');
                            setTimeout(function () {
                                $hb.socket.create(context, options);
                            }, 3000);
                        };
                    }

                    ['Open', 'Message', 'Error', 'Close'].forEach(function (listener) {
                        var callBack = 'on' + listener;
                        listener = listener.toLowerCase();

                        if (typeof options[callBack] === 'function') {
                            sok.instance.addEventListener(listener, options[callBack]);
                        }
                    });

                    sok.send = function (data) {

                        if (typeof data === 'object') {
                            data = JSON.stringify(data);
                        }

                        if (typeof data === 'string') {
                            if (sok.instance.readyState === 1) {
                                sok.instance.send(data);
                            } else {
                                window.hbSocketQueues.push(data);
                            }
                        }
                    };
                } catch (err) {
                    console.warn(err);

                    return sok;
                }

                window['hbWebSocket' + context.ucFirst()] = sok;

                return sok;
            },
        },

        currency: {
            code: html.getAttribute('data-currency-code') || 'USD',
            symbol: html.getAttribute('data-currency-symbol') || '$',
            decimals: html.getAttribute('data-currency-decimals') || '2',
            separator: html.getAttribute('data-currency-separator') || ',',
            point: html.getAttribute('data-currency-point') || '.',
            format: function (number) {
                var format = html.getAttribute('data-currency-format') || '{symbol}{value}',
                    value = parseFloat(number);

                if (isNaN(value)) {
                    return number;
                }

                value = value.toFixed(parseInt($hb.currency.decimals));
                value = value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, $hb.currency.separator);
                value = format.toString().replace('{value}', value);
                value = value.replace('{symbol}', $hb.currency.symbol);
                value = value.replace('{code}', $hb.currency.code);

                return value;
            },
        },
    };

_$.ready(function ($) {
    $('.js-count-down').each(function () {
        var el = $(this),
            seconds = parseInt(el.text().match(/\d+/g)[0] || 0);

        if (!isNaN(seconds)) {
            var interval = setInterval(function () {
                if (seconds < 1) {
                    clearInterval(interval);
                    el.parent().remove();

                } else {
                    seconds--;
                    el.text(el.text().replace(/\d+/g, seconds));
                }
            }, 1000);
        }
    });

    $(document).on('keyup', '.cms-currency-input > input', function () {
        var input = $(this),
            help = input.parent('.cms-currency-input').find('.uk-help-text');
        help.html($hb.currency.format(input.val()));
    });

    $('.cms-currency-input > input').trigger('keyup');
});