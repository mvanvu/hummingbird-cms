String.prototype.ucFirst = function () {
    return this.charAt(0).toUpperCase() + this.substring(1);
};

String.prototype.toNonAccentVietnamese = function () {
    return this
        // Lower
        .replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, 'a')
        .replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, 'e')
        .replace(/ì|í|ị|ỉ|ĩ/g, 'i')
        .replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, 'o')
        .replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, 'u')
        .replace(/ỳ|ý|ỵ|ỷ|ỹ/g, 'y')
        .replace(/đ/g, 'd')
        // Upper
        .replace(/À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ/g, 'A')
        .replace(/È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ/g, 'E')
        .replace(/Ì|Í|Ị|Ỉ|Ĩ/g, 'I')
        .replace(/Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ/g, 'O')
        .replace(/Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ/g, 'U')
        .replace(/Ỳ|Ý|Ỵ|Ỷ|Ỹ/g, 'Y')
        .replace(/Đ/g, 'D')
        .replace(/\u0300|\u0301|\u0303|\u0309|\u0323/g, '') // Huyền sắc hỏi ngã nặng
        .replace(/\u02C6|\u0306|\u031B/g, ''); // Â, Ê, Ă, Ơ, Ư
};

function _$(selector) {
    if (selector instanceof $miniQuery) {
        return selector;
    }

    return new $miniQuery(selector);
}

function $miniQuery(selector) {
    var mQ = this;

    if (selector instanceof NodeList) {
        mQ.elements = Array.from(selector);
    } else if (Array.isArray(selector)) {
        mQ.elements = selector;
    } else if (selector instanceof Window
        || selector instanceof Document
        || selector instanceof HTMLHeadElement
        || selector instanceof HTMLBodyElement
        || selector instanceof Element
    ) {
        mQ.elements = [selector];
    } else if (typeof selector === 'string') {
        var trimmedStr = _$.trim(selector).replace(/^[\"\']|[\"\']$/g, '');

        if (trimmedStr.match(/\<[^\>]+\/?\>/g)) {
            var div = document.createElement('div');
            div.innerHTML = trimmedStr;
            mQ.elements = [div.firstChild];
        } else if (selector.length) {
            mQ.elements = Array.from(document.querySelectorAll(selector));
        }
    } else {
        mQ.elements = [];
    }

    mQ.length = mQ.elements.length;
    mQ.element = mQ.length ? mQ.elements[0] : null;
    var events = [
        'blur', 'focus', 'focusin', 'focusout', 'resize', 'scroll', 'click', 'dblclick',
        'mousedown', 'mouseup', 'mousemove', 'mouseover', 'mouseout', 'mouseenter', 'mouseleave',
        'change', 'select', 'keydown', 'keypress', 'keyup', 'contextmenu',
    ];

    events.forEach(function (eventName) {
        mQ[eventName] = function () {
            mQ.elements.forEach(function (el) {
                if (typeof el[eventName] === 'function') {
                    el[eventName].call(el);
                }
            });
        };
    });

    for (var fn in _$.fn) {
        if (typeof _$.fn[fn] === 'function') {
            mQ[fn] = _$.fn[fn].bind(mQ);
        } else {
            mQ[fn] = _$.fn[fn];
        }
    }

    return mQ;
}

_$.fn = {
    submit: function () {
        if (this.element && this.element.nodeName === 'FORM') {
            var input = this.element.ownerDocument.createElement('input');
            input.type = 'submit';
            input.style.display = 'none';
            this.element.appendChild(input);
            input.click();
            this.element.removeChild(input);
        }
    },

    get: function (index) {
        return this.elements[index] ? _$(this.elements[index]) : _$();
    },

    first: function () {
        return this.get(0);
    },

    last: function () {
        return this.get(this.length - 1);
    },

    each: function (callback) {
        if (typeof callback === 'function') {
            this.elements.forEach(function (el, index) {
                callback.call(el, index);
            });
        }

        return this;
    },

    contents: function () {
        if (this.element
            && this.element.contentWindow
            && this.element.contentWindow._$
            && this.element.contentDocument) {
            return this.element.contentWindow._$(this.element.contentDocument);
        }

        return _$();
    },

    toObject: function () {
        var obj = {};

        this.elements.forEach(function (el) {
            if (el.name) {
                obj[el.name] = _$(el).val();
            }
        });

        return obj;
    },

    serialize: function () {
        var str = '';

        this.elements.forEach(function (el) {
            if (el.name) {
                str += el.name + '=' + _$(el).val() + '&';
            }
        });

        return str.replace(/&$/g, '');
    },

    parent: function (selector) {
        if (this.element && this.element.parentNode) {
            var p = this.element.parentNode;

            if (selector) {
                while (typeof p.matches === 'function' && !p.matches(selector) && p.parentNode) {
                    p = p.parentNode;
                }

                if (typeof p.matches === 'function' && p.matches(selector)) {
                    return _$(p);
                }
            } else {
                return _$(p);
            }
        }

        return _$();
    },

    children: function (selector) {
        var els = [];

        if (this.element && this.element.childNodes.length) {
            if (selector) {
                this.element.childNodes.forEach(function (child) {
                    if (child instanceof Element && child.matches(selector)) {
                        els.push(child);
                    }
                });
            } else {
                els = this.element.childNodes;
            }
        }

        return _$(els);
    },

    besideSiblings: function (selector, objKey, first = true) {
        if (this.element && this.element[objKey]) {
            var siblings = [],
                sibling = this.element[objKey];

            while (sibling) {
                if (selector) {
                    if (sibling.matches(selector)) {
                        if (first) {
                            return _$(sibling);
                        }

                        siblings.push(sibling);
                    }
                } else {
                    if (first) {
                        return _$(sibling);
                    }

                    siblings.push(sibling);
                }

                sibling = sibling[objKey];
            }

            return _$(siblings);
        }

        return _$();
    },

    prev: function (selector, first = true) {
        return this.besideSiblings(selector, 'previousElementSibling', first);
    },

    prevAll: function (selector) {
        return this.besideSiblings(selector, 'previousElementSibling', false);
    },

    next: function (selector, first = true) {
        return this.besideSiblings(selector, 'nextElementSibling', first);
    },

    nextAll: function (selector) {
        return this.besideSiblings(selector, 'nextElementSibling', false);
    },

    siblings: function (selector) {
        return _$(this.prevAll(selector).elements.concat(this.nextAll(selector).elements));
    },

    parseClasses: function (classes, type) {
        classes = classes.replace(/^\s*/g, '');
        classes = classes.replace(/\s*$/g, '');
        classes = classes.replace(/\s+/g, ' ');
        classes = classes.split(' ');
        this.elements.forEach(function (el) {
            classes.forEach(function (cls) {
                if ('add' === type) {
                    el.classList.add(cls);
                } else if ('remove' === type) {
                    el.classList.remove(cls);
                } else if ('toggle' === type) {
                    el.classList.contains(cls) ? el.classList.remove(cls) : el.classList.add(cls);
                }
            });
        });

        return this;
    },

    addClass: function (classes) {
        return this.parseClasses(classes, 'add');
    },

    removeClass: function (classes) {
        return this.parseClasses(classes, 'remove');
    },

    toggleClass: function (classes) {
        return this.parseClasses(classes, 'toggle');
    },

    append: function (selector, first) {
        var mQ = this;

        if (mQ.element) {
            first = first || false;
            var inst;

            if (selector instanceof $miniQuery) {
                inst = selector;
            } else {
                inst = _$(selector);
            }

            if (inst.elements.length) {
                inst.elements.forEach(function (el) {
                    if (first && mQ.element.firstChild) {
                        mQ.element.insertBefore(el, mQ.element.firstChild);
                    } else {
                        mQ.element.appendChild(el);
                    }
                });
            }
        }

        return mQ;
    },

    appendTo: function (selector, first) {

        if (!(selector instanceof $miniQuery)) {
            selector = _$(selector);
        }

        selector.append(this, first);

        return this;
    },

    on: function (eventNames, selector, callback) {
        var mQ = this;
        var setUpEvent = function (elements, func) {
            elements.forEach(function (el) {
                _$.trim(eventNames).split(' ').forEach(function (eventName) {
                    if (!el.miniQueryData) {
                        el.miniQueryData = {};
                    }

                    if (!el.miniQueryData.eventsMap) {
                        el.miniQueryData.eventsMap = [];
                    }

                    if (!el.miniQueryData.eventsMap[eventName]) {
                        el.miniQueryData.eventsMap[eventName] = [];
                    }

                    if (el.miniQueryData.eventsMap[eventName].find(function (fn) {
                        return fn[0] === func;
                    })) {
                        return true;
                    }

                    var handler = function (event) {
                        if (false === func.call(el, event)) {
                            event.preventDefault();
                        }
                    };

                    el.miniQueryData.eventsMap[eventName].push([func, handler]);
                    el.addEventListener(eventName, handler, false);
                });
            });
        };

        if (typeof selector === 'function') {
            setUpEvent(mQ.elements, selector);
        } else if (typeof callback === 'function') {
            setUpEvent(mQ.find(selector).elements, callback);

            if (mQ.element) {
                if (window.MutationObserver) {
                    var observer = new MutationObserver(function (mutations) {
                        setUpEvent(mQ.find(selector).elements, callback);
                    });

                    mQ.elements.forEach(function (element) {
                        observer.observe(element, {childList: true, subtree: true});
                    });

                } else {
                    mQ.elements.forEach(function (element) {
                        element.addEventListener('DOMNodeInserted', function () {
                            setUpEvent(mQ.find(selector).elements, callback);
                        }, false);
                    });
                }
            }
        }

        return this;
    },

    off: function (eventNames) {
        var mQ = this;
        _$.trim(eventNames).split(' ').forEach(function (eventName) {
            mQ.elements.forEach(function (el) {
                if (el.miniQueryData
                    && el.miniQueryData.eventsMap
                    && el.miniQueryData.eventsMap[eventName]
                ) {
                    el.miniQueryData.eventsMap[eventName].forEach(function (func) {
                        el.removeEventListener(eventName, func[1], false);
                    });
                    el.miniQueryData.eventsMap[eventName] = [];
                }
            });
        });

        return this;
    },

    remove: function () {
        var mQ = this;
        mQ.elements.forEach(function (el, index) {
            el.parentNode.removeChild(el);
            delete mQ[index];
        });
        mQ.elements = [];
        mQ.element = null;
    },

    css: function (k, v) {

        if (!this.element) {
            return null;
        }

        if (k === undefined && v === undefined) {
            return this.element.style;
        }

        var cssVal = function (k, v) {
            var isPixels = [
                'width',
                'height',
                'maxWidth',
                'maxHeight',
                'top',
                'left',
                'right',
                'bottom',
            ];

            if (isPixels.indexOf(k) !== -1 && !v.toString().match(/px$/g)) {
                return v + 'px';
            }

            return v;
        }

        if (typeof k === 'object') {
            this.elements.forEach(function (el) {
                for (var i in k) {
                    el.style[i] = cssVal(i, k[i]);
                }
            });

            return this;
        }

        if (typeof k === 'string') {
            if (v === undefined) {
                return this.element.style[k];
            }

            this.elements.forEach(function (el) {
                el.style[k] = cssVal(k, v);
            });
        }

        return this;
    },

    show: function () {
        this.elements.forEach(function (el) {
            if (el.style.display === 'none') {
                el.style.display = '';
            }

            el.removeAttribute('hidden');
            el.classList.remove('hide');
            el.classList.remove('hidden');
            el.classList.remove('uk-hidden');
        });

        return this;
    },

    hide: function () {
        return this.css('display', 'none');
    },

    toggle: function () {
        this.elements.forEach(function (el) {
            if (el.style.display === 'none') {
                _$(el).show();
            } else {
                el.style.display = 'none';
            }
        });

        return this;
    },

    find: function (selector) {

        if (selector && selector.indexOf('>') === 0) {
            selector = selector.substring(1);

            return this.children(selector);
        }

        var els = [];
        this.elements.forEach(function (element) {
            element.querySelectorAll(selector || '*').forEach(function (el) {
                if (els.indexOf(el) === -1) {
                    els.push(el);
                }
            });
        });

        return _$(els);
    },

    val: function (value) {
        if (typeof value === 'undefined') {
            if (this.element) {
                if (this.element.nodeName === 'SELECT' && this.element.hasAttribute('multiple')) {
                    var selected = [];

                    this.element.querySelectorAll('option:checked').forEach(function (option) {
                        selected.push(option.value);
                    });

                    return selected;
                }

                return this.element.value;
            }

            return null;
        }

        this.elements.forEach(function (el) {
            el.value = value;
        });

        return this;
    },

    trigger: function (eventNames, detailData) {
        var mQ = this;
        _$.trim(eventNames).split(' ').forEach(function (eventName) {
            var isCustomEvent = typeof document.body['on' + eventName] === 'undefined',
                params = {cancelable: true},
                evt;

            if (typeof detailData === 'object') {
                params.detail = detailData;
            }

            if (isCustomEvent) {
                evt = new CustomEvent(eventName, params);
            } else {
                evt = new Event(eventName, params);
            }

            mQ.elements.forEach(function (el) {
                el.dispatchEvent(evt);
            });
        });

        return this;
    },

    text: function (str) {
        if (str) {
            this.elements.forEach(function (el) {
                el.innerText = str;
            });

            return this;
        }

        return this.element ? this.element.innerText : null;
    },

    html: function (html) {
        if (html) {
            this.elements.forEach(function (el) {
                el.innerHTML = html;
            });

            return this;
        }

        return this.element ? this.element.innerHTML : null;
    },

    clone: function () {
        var newElements = [];
        this.elements.forEach(function (el) {
            newElements.push(el.cloneNode(true));
        });

        return _$(newElements);
    },

    offset: function () {
        if (!this.element || !this.element.getClientRects().length) {
            return {
                top: 0,
                left: 0,
            };
        }

        var rect = this.element.getBoundingClientRect();
        var win = this.element.ownerDocument.defaultView;

        return {
            top: rect.top + win.pageYOffset,
            left: rect.left + win.pageXOffset
        };
    },

    hasClass: function (classes) {
        if (!this.element) {
            return false;
        }

        classes = _$.trim(classes).split(' ');

        for (var i = 0, n = classes.length; i < n; i++) {
            if (!this.element.classList.contains(classes[i])) {
                return false;
            }
        }

        return true;
    },

    prop: function (name, value) {
        var mQ = this;

        if (value === undefined) {
            return mQ.element ? mQ.element[name] : null;
        }

        this.elements.forEach(function (el) {
            var nType = el.nodeType;

            // Don't get/set properties on text, comment and attribute nodes
            if (nType === 2 || nType === 3 || nType === 8) {
                return mQ;
            }

            el[name] = value;
        });

        return mQ;
    },

    not: function (selector) {
        var newElements = [];

        if (this.length) {
            this.elements.forEach(function (el) {
                if (!el.matches(selector)) {
                    newElements.push(el);
                }
            });
        }

        return _$(newElements);
    },

    attr: function (k, v) {
        if (v === undefined) {
            return this.element ? this.element.getAttribute(k) : null;
        }

        this.elements.forEach(function (el) {
            el.setAttribute(k, v);
        });

        return this;
    },

    removeAttr: function (attrName) {
        this.elements.forEach(function (el) {
            el.removeAttribute(attrName)
        });

        return this;
    },

    and: function (inst) {
        var newElements = this.elements;

        if (inst instanceof $miniQuery) {
            inst.elements.forEach(function (el) {
                newElements.push(el);
            });
        }

        return _$(newElements);
    },

    index: function () {
        if (this.element) {
            return _$(this.element).prevAll().length;
        }

        return -1;
    },

    empty: function () {

        this.elements.forEach(function (el) {
            el.innerHTML = '';
        });

        return this;
    },

    data: function (k, v) {
        if (!this.element) {
            return {};
        }

        if (k === undefined && v === undefined) {
            return this.element.dataset;
        }

        if (v === undefined) {
            if (this.element.storageData && this.element.storageData[k]) {
                v = this.element.storageData[k];
            } else {
                v = this.element.dataset && this.element.dataset[k] ? this.element.dataset[k] : null;

                if (v && v.match(/^\[|\{/g)) {
                    try {
                        v = JSON.parse(v);
                    } catch (e) {

                    }
                }
            }

            return v;
        }

        this.elements.forEach(function (el) {
            if (typeof v === 'object' && null !== v) {
                el.storageData = el.storageData || {};
                el.storageData[k] = v;
            } else {
                el.dataset[k] = v;
            }
        });

        return this;
    },

    insert: function (selector, before) {
        var p = this.parent();
        var n = _$(selector);

        if (this.element && p.element && n.element) {
            if (before) {
                p.element.insertBefore(n.element, this.element);
            } else {
                if (this.element.nextElementSibling) {
                    p.element.insertBefore(n.element, this.element.nextElementSibling);
                } else {
                    p.element.appendChild(n.element);
                }
            }
        }

        return this;
    },

    scrollTo: function (top, left) {
        if (top === undefined && left === undefined) {
            return this.element ? {top: this.element.scrollTop, left: this.element.scrollLeft} : {top: 0, left: 0};
        }

        if (top !== undefined || left !== undefined) {
            this.elements.forEach(function (el) {
                if (top) {
                    el.scrollTop = top;
                }

                if (left) {
                    el.scrollLeft = left;
                }
            });
        }

        return this;
    },
};

_$.ready = function (callback) {
    window.addEventListener('DOMContentLoaded', function (event) {
        callback.call(window, _$, event);
    });
};

_$.storage = {
    get: function (key, defaultValue) {
        if (window.sessionStorage) {
            return window.sessionStorage.getItem(key) || defaultValue;
        }

        return _$.cookie(key) || defaultValue;
    },
    set: function (key, value) {
        if (window.sessionStorage) {
            window.sessionStorage.setItem(key, value);
        } else {
            _$.cookie(key, value);
        }
    },
    remove: function (key) {
        if (window.sessionStorage) {
            window.sessionStorage.removeItem(key);
        } else {
            _$.cookie(key, '', -1);
        }
    },
};

_$.cookie = function (k, v, exDays) {
    if (!k && !v) {
        return document.cookie;
    }

    var name = k + '=';

    if (typeof v === 'string') {
        var d = new Date;
        exDays = exDays || 1;
        d.setTime(d.getTime() + (exDays * 24 * 60 * 60 * 1000));
        document.cookie = name + v + '; expires=' + d.toUTCString();

        return v;
    }

    var ca = document.cookie.split(';');

    for (var i = 0, n = ca.length; i < n; i++) {
        var c = ca[i];

        while (c.charAt(0) === ' ') {
            c = c.substring(1);
        }

        if (c.indexOf(name) === 0) {
            return c.substring(name.length, c.length);
        }
    }

    return null;
};

_$.http = {
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-Ajax-Engine': 'HummingbirdCms',
        'X-Language-ISO-Code': '',
        'X-Api-Platform': 'WebApplication',
    },
    buildParams: function (objParams) {
        var strParams = '',
            buildDataParams = function (params, prefix) {
                prefix = prefix || '';

                for (var k in params) {
                    var paramValue = params[k];

                    if (typeof paramValue === 'function') {
                        paramValue = paramValue();
                    } else if (paramValue == null) {
                        paramValue = '';
                    }

                    if (typeof paramValue === 'object') {
                        buildDataParams(paramValue, prefix.length ? prefix + '[' + k + ']' : prefix + k);
                    } else {
                        if (typeof paramValue === 'boolean') {
                            paramValue = paramValue ? 1 : 0;
                        }

                        strParams += encodeURIComponent(prefix.length ? prefix + '[' + k + ']' : k) + '=' + encodeURIComponent(paramValue) + '&';
                    }
                }

            };

        buildDataParams(objParams);
        strParams = strParams.replace(/%20/g, '+').replace(/&$/g, '');

        return strParams;
    },
    request: function (url, options) {
        var xhr = new XMLHttpRequest,
            data = null;
        xhr.withCredentials = typeof options.withCredentials === 'boolean' ? options.withCredentials : true;
        xhr.async = typeof options.async === 'boolean' ? options.async : true;
        xhr.addEventListener('readystatechange', function () {
            if (xhr.readyState === 4) {
                var response;

                try {
                    response = JSON.parse(xhr.responseText);
                } catch (err) {
                    response = xhr.responseText;
                }

                if (xhr.status === 200) {
                    _$(document).trigger('httpRequestSuccess', {response: response, xhr: xhr});
                    options.success && options.success.call(window, response, xhr);
                } else {
                    _$(document).trigger('httpRequestError', {response: response, xhr: xhr});
                    options.error && options.error.call(window, response, xhr);
                }
            }
        });

        var metaLanguage = document.head.querySelector('meta[name="languageIsoCode"]');

        if (!_$.http.headers['X-Language-ISO-Code'] && metaLanguage) {
            _$.http.headers['X-Language-ISO-Code'] = metaLanguage.getAttribute('content') || '';
        }

        xhr.open(options.method || 'GET', url, typeof options.async === 'boolean' ? options.async : true);

        for (var globalHeader in _$.http.headers) {
            xhr.setRequestHeader(globalHeader, _$.http.headers[globalHeader]);
        }

        if (options.method === 'POST') {
            var token = document.head.querySelector('meta[name="csrf"]'),
                optionsHeaders = options.headers || {};

            if (token) {
                xhr.setRequestHeader('X-CSRF-Token', token.getAttribute('content'));
            }

            if (!optionsHeaders.hasOwnProperty('Content-Type')) {
                optionsHeaders['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
            }

            if (options.data) {
                data = typeof options.data === 'object' && options.processData !== false ? _$.http.buildParams(options.data) : options.data;
            }
        }

        for (var optionHeader in optionsHeaders) {
            if (optionsHeaders[optionHeader]) {
                xhr.setRequestHeader(optionHeader, optionsHeaders[optionHeader]);
            }
        }

        xhr.send(data);
    },
    get: function (url, callBack, errorCallback) {
        _$.http.request(url, {
            method: 'GET',
            success: callBack || null,
            error: errorCallback || null,
        });
    },
    post: function (url, data, callBack, errorCallback) {
        _$.http.request(url, {
            method: 'POST',
            data: data || null,
            success: callBack || null,
            error: errorCallback || null,
        });
    },
    delete: function (url, callBack, errorCallback) {
        _$.http.request(url, {
            method: 'DELETE',
            success: callBack || null,
            error: errorCallback || null,
        });
    },
};

_$.trim = function (str) {
    return str.replace(/^[\s\n]*|[\s\n]*$/gm, '');
};

window.miniQuery = window._$ = _$;