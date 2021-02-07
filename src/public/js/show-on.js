window.addEventListener('load', function () {
    var fireShowOn = function (field) {
        try {
            var showOnData = JSON.parse(field.dataset.showOn),
                willShow = true,
                targetField, i, n, condValues, values, first, op, multiple;
            for (i = 0, n = showOnData.length; i < n; i++) {
                op = showOnData[i].op;

                if ((willShow && op === '|') || (!willShow && op === '&')) {
                    break;
                }

                multiple = false;
                condValues = [];
                targetField = document.querySelectorAll('[name="' + showOnData[i].field + '"]');

                if (!targetField.length) {
                    targetField = document.querySelectorAll('[name="' + showOnData[i].field + '[]"]');
                }

                if (targetField.length) {

                    if (targetField[0].nodeName === 'INPUT' && -1 !== ['radio', 'checkbox'].indexOf(targetField[0].type)) {
                        multiple = true;
                        targetField.forEach(function (n) {
                            if (n.checked) {
                                condValues.push(n.value);
                            }
                        });

                    } else if (targetField[0].nodeName === 'SELECT') {
                        if (targetField[0].multiple) {
                            multiple = true;
                            targetField[0].querySelectorAll('option:checked').forEach(function (option) {
                                condValues.push(option.value);
                            });
                        } else {
                            condValues = targetField[0].querySelector('option:checked');
                            condValues = condValues ? [condValues.value] : [];
                        }

                    } else {
                        condValues = [targetField[0].value];
                    }

                    switch (showOnData[i].value) {
                        case '': // empty
                            willShow = multiple ? !condValues.length : !condValues[0].length;
                            break;

                        case '!': // not empty
                            willShow = multiple ? !!condValues.length : !!condValues[0].length;
                            break;

                        default:

                            var o = showOnData[i].value.substring(0, 2);

                            if ('>=' === o || '<=' === o) {
                                if (!multiple) {
                                    condValues = condValues.length ? condValues[0] : '';
                                }

                                if ('>=' === o) {
                                    willShow = condValues.length >= showOnData[i].value.substring(2);
                                } else {
                                    willShow = condValues.length <= showOnData[i].value.substring(2);
                                }
                            } else {
                                first = showOnData[i].value.substring(0, 1);

                                if ('!' === first) {
                                    values = showOnData[i].value.substring(1).split(',');
                                } else {
                                    values = showOnData[i].value.split(',');
                                }

                                if (multiple) {
                                    values = JSON.stringify(values);
                                    condValues = JSON.stringify(condValues);
                                    willShow = ('!' === first && condValues !== values) || ('!' !== first && condValues === values);
                                } else {
                                    condValues = condValues.length ? condValues[0] : '';
                                    willShow = ('!' === first && -1 === values.indexOf(condValues)) || ('!' !== first && -1 !== values.indexOf(condValues));
                                }
                            }

                            break;
                    }
                }
            }

            if (window.jQuery) {
                willShow ? window.jQuery(field).slideDown() : window.jQuery(field).slideUp();
            } else {
                if (willShow) {
                    field.style.display = field.dataset.originDisplay;
                    setTimeout(function () {
                        field.style.height = field.dataset.originHeight;
                        field.classList.add('on-shown');
                    }, 1);
                } else {
                    field.style.height = '0px';
                    field.classList.remove('on-shown');
                    setTimeout(function () {
                        field.style.display = 'none';
                    }, 1);
                }
            }

        } catch (err) {
            console.log(err);
        }
    };

    window.setUpPhpFormJsShowOn = function () {
        var setUpShowOn = function () {
            var fields = document.querySelectorAll('[data-show-on]');

            if (fields.length) {
                fields.forEach(function (field) {
                    if (!window.jQuery && !field.dataset.originHeight) {
                        var compStyles = window.getComputedStyle(field);
                        field.dataset.originHeight = compStyles.height;
                        field.dataset.originDisplay = compStyles.display;
                        field.style['-webkit-transition'] = 'all .35s ease-in-out';
                        field.style['-moz-transition'] = 'all .35s ease-in-out';
                        field.style['-o-transition'] = 'all .35s ease-in-out';
                        field.style['transition'] = 'all .35s ease-in-out';
                        field.style['overflow'] = 'all';
                    }

                    fireShowOn(field);
                });
            }
        };

        // Init show on
        setUpShowOn();

        // Init events
        document.querySelectorAll('textarea:not(.show-on-input-handled), input:not(.show-on-input-handled), select:not(.show-on-input-handled)').forEach(function (input) {
            input.classList.add('show-on-input-handled');
            input.addEventListener('change', setUpShowOn);
            input.addEventListener('keyup', setUpShowOn);
        });
    };

    // Run
    setUpPhpFormJsShowOn();
    document.addEventListener('DOMNodeInserted', setUpPhpFormJsShowOn);
});