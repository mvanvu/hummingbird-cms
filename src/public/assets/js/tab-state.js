jQuery(document).ready(function ($) {

    if (!window.sessionStorage) {
        return false;
    }

    var storageKey = window.location.href.toString().split(window.location.host)[1].replace(/&return=[a-zA-Z0-9%]+/, '').split('#')[0];
    var index = 0;
    var tabs = $('[uk-tab]');

    // Initial tab key
    tabs.each(function () {
        $(this).attr('data-tab-key', storageKey + '#tab' + (index++));
    });

    // Set active tab
    tabs.find('>li>a').on('click', function () {
        var a = $(this);
        sessionStorage.setItem(a.parents('.uk-tab:eq(0)').attr('data-tab-key'), a.parent().index());
    });

    // Load active tab
    tabs.each(function () {
        var index = sessionStorage.getItem(this.getAttribute('data-tab-key')) || 0;
        UIkit.tab(this).show(index);
    });
});