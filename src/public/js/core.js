var html = document.documentElement,
    cmsCore = $hb = window.cmsCore || {
        globalData: {},
        uri: {},
        public: function (asset) {
            return $hb.uri.root + '/' + asset;
        },
        setData: function (key, val) {
            if (typeof key === 'object') {
                for (var k in key) {
                    this.globalData[k] = key[k];
                }
            } else {
                this.globalData[key] = val;
            }
        },
        getData: function (key, def) {
            return typeof this.globalData[key] === 'undefined' ? def : this.globalData[key];
        },
        storage: {
            setData: function (name, value) {
                if (window.localStorage) {
                    window.localStorage.setItem(name, value);
                } else {
                    $hb.setCookie(name, value);
                }
            },
            getData: function (name) {
                if (window.localStorage) {
                    return window.localStorage.getItem(name);
                }

                return $hb.getCookie(name);
            }
        },
        getCookie: function (cname) {
            var name = cname + '=',
                ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1);
                if (c.indexOf(name) === 0) return c.substring(name.length, c.length);
            }

            return '';

        },
        setCookie: function (cname, cvalue) {
            var exdays = 1,
                d = new Date();
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
            document.cookie = cname + '=' + cvalue + '; expires=' + d.toUTCString();
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

                    if (typeof options.onOpen !== 'function') {
                        options.onOpen = function () {
                            console.log(options.plugin + ' Socket connected.');
                        };
                    }

                    if (typeof options.onClose !== 'function') {
                        options.onClose = function () {
                            console.log('Socket closed. Reconnect will be attempted in 3 seconds...');
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
            code: 'USD',
            symbol: '$',
            decimals: '2',
            separator: ',',
            point: '.',
            formatPattern: '{symbol}{value}',
            format: function (number) {
                var format = $hb.currency.formatPattern,
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

    $('[data-m1][data-m1][data-m3]').each(function () {
        var el = $(this),
            m1 = $.trim(el.data('m1') || ''),
            m2 = $.trim(el.data('m2') || ''),
            m3 = $.trim(el.data('m3') || '');

        if (m1.length && m2.length && m3.length) {
            var email = m1 + '@' + m2 + '.' + m3;
            el.insert('<a href="mailto:' + email + '">' + email + '</a>');
            el.remove();
        }
    });
});