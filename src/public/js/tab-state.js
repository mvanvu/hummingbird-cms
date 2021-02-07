_$.ready(function ($) {
    const storageKey = window.location.href.toString().split(window.location.host)[1].replace(/&return=[a-zA-Z0-9%]+/, '').split('#')[0];
    let index = 0;
    const tabs = $('[uk-tab]');

    // Initial tab key
    tabs.each(function () {
        $(this).data('tabKey', storageKey + '#tab' + (index++));
    });

    // Set active tab
    tabs.find('li>a').on('click', function () {
        const a = $(this);
        $.storage.set(a.parent('.uk-tab').data('tabKey'), a.parent().index());
    });

    // Load active tab
    tabs.each(function () {
        const index = $.storage.get($(this).data('tabKey')) || 0;
        UIkit.tab(this).show(index);
    });
});