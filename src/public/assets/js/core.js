var cmsCore = {
    uri: {},
    language: {
        strings: {},
        fetch: function () {
            $.ajax({
                url: cmsCore.uri.base + '/request/get/load-strings',
                type: 'get',
                dataType: 'json',
                async: true,
                success: function (response) {
                    cmsCore.language.load(response.data);
                }
            });
        },
        load: function (objData) {
            cmsCore.language.strings = Object.assign(cmsCore.language.strings, objData);
        },
        _: function (string, placeholders) {

            var ret = string;

            if (cmsCore.language.strings.hasOwnProperty(string)) {
                ret = cmsCore.language.strings[string];

                if (typeof placeholders === 'object') {

                    for (var k in placeholders) {
                        ret = ret.replace('%' + k + '%', placeholders[k]);
                    }

                    return ret;
                }
            }

            return ret;
        }
    },
    initTinyMCE: function (element) {
    },
    initCodeMirror: function (element) {
    },
    initMediaModal: function (element) {
    },
    initUcmElementModal: function (elementId) {
    },
    storage: {
        get: function (key, defaultValue) {
            if (localStorage) {
                return localStorage.getItem(key) || defaultValue;
            }

            return defaultValue;
        },
        set: function (key, value) {
            if (localStorage) {
                localStorage.setItem(key, value);
            }
        },
        remove: function (key) {
            if (localStorage) {
                localStorage.removeItem(key);
            }
        }
    },
};

var html = $('html');
cmsCore.uri.root = html.data('uriRoot');
cmsCore.uri.base = html.data('uriBase');
cmsCore.uri.isHome = html.data('uriHome');