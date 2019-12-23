jQuery(document).ready(function ($) {
    var listForm = $('#admin-list-form');
    var editForm = $('#admin-edit-form');
    var toolbars = $('.toolbars-container');

    if (listForm.length) {
        var filterFields = listForm.find('[name^="FilterForm["]').not('[name="FilterForm[search]"]');
        var cid = listForm.find('input[type="checkbox"][name="cid[]"]');
        listForm.find('.check-all').on('change', function () {
            cid.prop('checked', this.checked);
        });

        toolbars.find('a.toolbar-delete, a.toolbar-trash, a.toolbar-copy, a.toolbar-unlock').on('click', function (e) {
            e.preventDefault();
            var a = $(this);

            if (cid.filter(':checked').length) {

                var submitForm = function () {
                    listForm.attr('action', a.attr('href'));

                    if (a.hasClass('toolbar-trash')) {
                        listForm.find('[name="postAction"]').val('T');
                    }

                    listForm.submit();
                };

                if (a.hasClass('toolbar-delete')) {
                    UIkit.modal.confirm(cmsCore.language._('confirm-delete-items')).then(submitForm, function () {
                    });
                } else {
                    submitForm();
                }

            } else {
                UIkit.notification(cmsCore.language._('select-items-first'), {status: 'warning'});
            }
        });

        toolbars.find('.toolbar-rebuild').on('click', function (e) {
            e.preventDefault();
            var action = $(this).attr('href');
            UIkit.modal.confirm(cmsCore.language._('confirm-rebuild-nested-msg')).then(
                function () {
                    listForm.attr('action', action);
                    listForm.submit();
                }, function () {
                }
            );
        });

        if (filterFields.length) {
            filterFields.on('change', function () {
                listForm.submit();
            });
        }

        listForm.find('a[data-sort]').on('click', function (e) {
            e.preventDefault();
            var sort = listForm.find('[name="_sort"]');

            if (sort.length) {
                sort.remove();
            }

            listForm.append('<input type="hidden" name="_sort" value="' + $(this).data('sort') + '"/>');
            listForm.submit();
        });

        listForm.find('a.reset-filter').on('click', function (e) {
            e.preventDefault();
            listForm.find('[name^="FilterForm["]').val('');
            listForm.find('[name="postAction"]').val('resetFilter');
            listForm.submit();
        });

        listForm.find('.search-icon').on('click', function (e) {
            e.preventDefault();
            listForm.submit();
        });

        listForm.find('[name="FilterForm[search]"]').on('keyup', function (e) {
            if (e.key === 'Enter') {
                listForm.submit();
            }
        });

        listForm.find('.item-status a[data-state]').on('click', function (e) {
            e.preventDefault();
            var a = $(this);
            var p = a.parent();
            listForm.find('[name="postAction"]').val(a.data('state'));
            listForm.find('[name="entityId"]').val(p.data('entityId'));
            listForm.attr('action', p.data('uri'));
            listForm.submit();
        });

        listForm.find('.ucm-children-link').on('click', function (e) {
            e.preventDefault();
            listForm.find('[name="postAction"]').val('loadChildren');
            listForm.find('[name="entityId"]').val($(this).parents('[data-id]').data('id'));
            listForm.submit();
        });
    }

    if (editForm.length) {
        toolbars.find('a.toolbar-save, a.toolbar-save2close').on('click', function (e) {
            e.preventDefault();

            editForm.find('[required]').each(function () {
                var element = $(this);

                if (this.value.length) {
                    element.removeClass('uk-form-danger');
                } else {
                    element.addClass('uk-form-danger');
                }
            });

            if (!editForm.find('.uk-form-danger').length) {
                editForm.attr('action', $(this).attr('href'));
                editForm.submit();
            }
        });
    }

    $(document).on('click', 'a.item-language-title', function (e) {
        e.preventDefault();
        var a = $(this);
        a.addClass('active').siblings('.item-language-title').removeClass('active');
        $('input[data-language="' + a.data('language') + '"]').removeClass('uk-hidden').siblings('[data-language]').addClass('uk-hidden');
    });

    $(document).on('change', 'input.item-language-input', function (e) {
        e.preventDefault();
        var
            el = $(this),
            dataLangTitle = {}, input;
        el.siblings('input.item-language-input').andSelf().each(function () {
            input = $(this);
            dataLangTitle[input.data('language')] = $.trim(input.val());
        });

        el.siblings('.item-language-input-value').val(JSON.stringify(dataLangTitle));
    });

    if (cmsCore.uri.isHome) {
        $('#admin-aside .home-dashboard').addClass('uk-text-success');
    } else {
        var activeUri = '/' + $('#admin-aside nav[data-active-uri]').data('activeUri');

        $('#admin-aside .uk-nav-sub a').each(function () {
            if (this.href && this.href.toString().indexOf(activeUri) !== -1) {
                $(this).addClass('uk-text-success')
                    .parents('.uk-parent:eq(0)').find('>a')
                    .addClass('uk-text-bold');
            }
        });
    }

    // Plugin
    $('[data-plugin] a[data-text-confirm]').on('click', function (e) {
        e.preventDefault();
        var a = $(this);
        UIkit.modal.confirm(a.data('textConfirm')).then(function() {
            var
                form = $('#admin-plugin-form'),
                row = a.parents('[data-plugin]');
            form.find('[name="group"]').val(row.data('group'));
            form.find('[name="plugin"]').val(row.data('plugin'));
            form.submit();
        }, function () {});

    });
});
