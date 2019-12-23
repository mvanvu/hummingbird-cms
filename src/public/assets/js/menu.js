jQuery(document).ready(function ($) {
    var
        menusContainer = $('#menus-container'),
        token = menusContainer.data('token'),
        updateOutput = function (e) {
            var
                list = e.length ? e : $(e.target),
                data = {
                    items: list.nestable('serialize')
                };
            $.ajax({
                url: cmsCore.uri.base + '/menu/nestableItems',
                type: 'post',
                dataType: 'json',
                headers: {
                    'X-CSRF-Token': token
                },
                data: data,
            });
        };

    menusContainer.on('nestable', '.menu-item-list', function () {
        $(this).find('.nestable').nestable().on('change', updateOutput);
    });

    menusContainer.find('.menu-item-list').trigger('nestable');
    var
        itemBody = $('#item-body'),
        iframe = itemBody.find('iframe'),
        loadItemParamsForm = function (type, id) {
            iframe.attr('src', cmsCore.uri.base + '/menu/item/?type=' + type + '&id=' + id).on('load', function (e) {
                e.preventDefault();
                iframe.contents().find('.btn-add-menu-item').unbind('click').on('click', function (e) {
                    e.preventDefault();
                    var
                        btn = $(this),
                        itemContainer = btn.parents('#menu-item-container'),
                        data;
                    itemContainer.find('[name="FormData[id]"]').val(id);
                    data = itemContainer.find('[name^="FormData["]').serialize();
                    $.ajax({
                        url: cmsCore.uri.base + '/menu/createMenu',
                        type: 'post',
                        dataType: 'json',
                        headers: {
                            'X-CSRF-Token': token
                        },
                        data: data,
                        success: function (response) {
                            UIkit.notification(response.message, {status: response.success ? 'success' : 'warning'});

                            if (response.success) {
                                menusContainer.find('.menu-item-list').html(response.data).trigger('nestable');
                            }
                        }
                    });
                });

                iframe.contents().find('.btn-close-menu-item').unbind('click').on('click', function (e) {
                    e.preventDefault();
                    iframe.removeAttr('src');
                    itemBody.addClass('uk-hidden');
                });
            });

            itemBody.removeClass('uk-hidden');
        };

    $('#item-type-create').on('click', function () {
        var type = menusContainer.find('.item-type-select').val();
        loadItemParamsForm(type, 0);
    });

    var menuTypeSelect = menusContainer.find('.menu-type-select');
    menuTypeSelect.data('prevValue', menuTypeSelect.val());
    menuTypeSelect.on('change', function () {
        var menuSelected = this.value;
        UIkit.modal.confirm(cmsCore.language._('toggle-menu-type-confirm', {menuType: menuSelected})).then(function () {
            $.ajax({
                url: cmsCore.uri.base + '/menu/toggleMenuType',
                type: 'post',
                dataType: 'json',
                headers: {
                    'X-CSRF-Token': token
                },
                data: {
                    menuType: menuSelected,
                },
                success: function (response) {
                    if (response.success) {
                        menusContainer.find('.menu-item-list').html(response.data).trigger('nestable');
                    } else {
                        UIkit.notification(response.message, {status: 'warning'});
                    }
                }
            });
        }, function () {
            menuTypeSelect.val(menuTypeSelect.data('prevValue'));
        });
    });

    UIkit.util.on('#menu-type-create', 'click', function (e) {
        e.preventDefault();
        e.target.blur();
        UIkit.modal.prompt(cmsCore.language._('menu-type-name'), '').then(function (name) {
            var menuType = name.replace(/[^0-9A-Za-z]/g, '');

            if (menuType.length) {
                $.ajax({
                    url: cmsCore.uri.base + '/menu/createMenuType',
                    type: 'post',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-Token': token
                    },
                    data: {
                        menuType: menuType,
                    },
                    success: function (response) {
                        if (response.success) {
                            menuTypeSelect.html(response.data);
                            menuTypeSelect.data('preValue', menuType).val(menuType).trigger('change');
                        } else {
                            UIkit.notification(response.message, {status: 'warning'});
                        }
                    }
                });
            } else {
                UIkit.notification(cmsCore.language._('empty-menu-name-msg'), {status: 'warning'});
            }
        });
    });

    menusContainer.on('click', '.nestable a.edit, .nestable a.remove', function (e) {
        e.preventDefault();
        var a = $(this);
        var p = a.parents('[data-menu]:eq(0)');

        if (a.hasClass('edit')) {
            var menuData = p.data('menu');
            loadItemParamsForm(menuData.type, menuData.id);
        } else {
            UIkit.modal.confirm(cmsCore.language._('remove-menu-item-confirm')).then(
                function () {
                    $.ajax({
                        url: cmsCore.uri.base + '/menu/removeMenuItem',
                        type: 'post',
                        dataType: 'json',
                        headers: {
                            'X-CSRF-Token': token
                        },
                        data: {
                            menuId: p.data('id'),
                        },
                        success: function (response) {
                            UIkit.notification(response.message, {status: response.success ? 'success' : 'warning'});
                            if (response.success) {
                                p.remove();
                            }
                        }
                    });
                },
                function () {
                },
            );
        }
    });

    menusContainer.on('click', '.btn-menu-type-rename, .btn-menu-type-remove', function (e) {
        e.preventDefault();
        var btn = $(this);
        var menu = btn.parents('[data-menu-type]:eq(0)');

        if (btn.hasClass('btn-menu-type-rename')) {
            $.ajax({
                url: cmsCore.uri.base + '/menu/renameMenuType',
                type: 'post',
                dataType: 'json',
                headers: {
                    'X-CSRF-Token': token
                },
                data: {
                    menuType: menu.data('menuType'),
                    newName: menu.find('[name="menuName"]').val(),
                },
                success: function (response) {
                    if (response.success) {
                        menu.attr('data-menu-type', response.data.newName).data('menuType', response.data.newName);
                        menuTypeSelect.find('option[value="' + response.data.menuType + '"]')
                            .attr('value', response.data.newName)
                            .text(response.data.newName);
                        menu.find('[name="menuName"]').val(response.data.newName);
                        location.reload();
                    } else {
                        menu.find('[name="menuName"]').val(menu.data('menuType'));
                    }
                }
            });
        } else {
            UIkit.modal.confirm(cmsCore.language._('remove-menu-type-confirm')).then(
                function () {
                    $.ajax({
                        url: cmsCore.uri.base + '/menu/removeMenuType',
                        type: 'post',
                        dataType: 'json',
                        headers: {
                            'X-CSRF-Token': token
                        },
                        data: {
                            menuType: menu.data('menuType'),
                        },
                        success: function (response) {
                            if (response.success) {
                                location.reload();
                            }
                        }
                    });
                },
                function () {
                },
            );
        }
    });
});