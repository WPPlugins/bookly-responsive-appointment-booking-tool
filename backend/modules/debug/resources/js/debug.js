jQuery(function($) {
    let $constraintModal = $('#bookly-js-add-constraint'),
        $columnModal = $('#bookly-js-add-field'),
        $tableModal = $('#bookly-js-create-table'),
        $status,
        $create;

    $('.collapse').collapse('hide');

    $('#bookly_import_file').change(function() {
        if($(this).val()) {
            $('#bookly_import').submit();
        }
    });

    $('[data-action=fix-constraint]')
        .on('click', function (e) {
            e.preventDefault();
            $status = $(this).closest('td');
            let $tr = $(this).closest('tr'),
                table = $tr.closest('.panel-collapse').attr('id'),
                column = $tr.find('td:eq(0)').html(),
                ref_table = $tr.find('td:eq(1)').html(),
                ref_column = $tr.find('td:eq(2)').html()
            ;
            $('.bookly-js-loading:first-child', $constraintModal).addClass('bookly-loading').removeClass('collapse');
            $('.bookly-js-loading:last-child', $constraintModal).addClass('collapse');
            $('.bookly-js-fix-consistency', $constraintModal).hide();
            $constraintModal.modal();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action    : 'bookly_get_constraint_data',
                    table     : table,
                    column    : column,
                    ref_table : ref_table,
                    ref_column: ref_column,
                    csrf_token: BooklyL10n.csrfToken
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $('#bookly-js-table, .bookly-js-table', $constraintModal).html(table);
                        $('#bookly-js-column, .bookly-js-column', $constraintModal).html(column);
                        $('#bookly-js-ref_table, .bookly-js-ref_table', $constraintModal).html(ref_table);
                        $('#bookly-js-ref_column, .bookly-js-ref_column', $constraintModal).html(ref_column);
                        $('#bookly-js-DELETE_RULE', $constraintModal).val(response.data.DELETE_RULE);
                        $('#bookly-js-UPDATE_RULE', $constraintModal).val(response.data.UPDATE_RULE);
                    } else {
                        $('#bookly-js-DELETE_RULE', $constraintModal).val('');
                        $('#bookly-js-DELETE_RULE', $constraintModal).val('');
                    }
                    $('.bookly-js-loading', $constraintModal).toggleClass('collapse');
                }
            });
        });

    $constraintModal
        .on('click', '.bookly-js-save', function () {
            let ladda = Ladda.create(this);
            ladda.start();
            $.ajax({
                url  : ajaxurl,
                type : 'POST',
                data : {
                    action      : 'bookly_add_constraint',
                    table       : $('#bookly-js-table', $constraintModal).html(),
                    column      : $('#bookly-js-column', $constraintModal).html(),
                    ref_table   : $('#bookly-js-ref_table', $constraintModal).html(),
                    ref_column  : $('#bookly-js-ref_column', $constraintModal).html(),
                    delete_rule : $('#bookly-js-DELETE_RULE', $constraintModal).val(),
                    update_rule : $('#bookly-js-UPDATE_RULE', $constraintModal).val(),
                    csrf_token  : BooklyL10n.csrfToken
                },
                dataType : 'json',
                success  : function (response) {
                    if (response.success) {
                        booklyAlert({success: [response.data.message]});
                        $constraintModal.modal('hide');
                        $status.html('OK');
                    } else {
                        booklyAlert({error : [response.data.message]});
                        $('.bookly-js-fix-consistency', $constraintModal).show();
                    }
                    ladda.stop();
                },
                error: function () {
                    booklyAlert({error: ['Error: Constraint not created.']});
                    ladda.stop();
                }
            });
        })
        .on('click', '[data-action=fix-consistency]', function (e) {
            e.preventDefault();
            let $button     = $(this),
                table       = $('#bookly-js-table', $constraintModal).html(),
                column      = $('#bookly-js-column', $constraintModal).html(),
                ref_table   = $('#bookly-js-ref_table', $constraintModal).html(),
                ref_column  = $('#bookly-js-ref_column', $constraintModal).html(),
                data = {
                    action     : 'bookly_fix_consistency',
                    table      : $('#bookly-js-table', $constraintModal).html(),
                    column     : $('#bookly-js-column', $constraintModal).html(),
                    ref_table  : $('#bookly-js-ref_table', $constraintModal).html(),
                    ref_column : $('#bookly-js-ref_column', $constraintModal).html(),
                    csrf_token : BooklyL10n.csrfToken,
                    rule       : ''
                },
                query       = '',
                ladda       = ''
            ;
            if ($button.hasClass('bookly-js-auto')) {
                data.rule = $('#bookly-js-DELETE_RULE', $constraintModal).val();
                ladda     = Ladda.create(this);
            } else {
                if ($button.hasClass('bookly-js-delete')) {
                    data.rule = 'CASCADE';
                } else if ($button.hasClass('bookly-js-update')) {
                    data.rule = 'SET NULL';
                }
                ladda = Ladda.create($('button[data-action=fix-consistency]')[0]);
            }

            switch (data.rule) {
                case 'NO ACTIONS':
                case 'RESTRICT':
                    booklyAlert({success: ['No manipulation actions were performed']});
                    return false;
                case 'CASCADE':
                    query = 'DELETE FROM `' + table + "`\n" + '          WHERE `' + column + '` NOT IN ( SELECT `' + ref_column + '` FROM `' + ref_table + '` )';
                    break;
                case 'SET NULL':
                    query = 'UPDATE TABLE `' + table + "`\n" + '                SET `' + column + '` = NULL' + "\n" + '           WHERE `' + column + '` NOT IN ( SELECT `' + ref_column + '` FROM `' + ref_table + '` )';
                    break;
            }

            if (confirm('IF YOU DON\'T KNOW WHAT WILL HAPPEN AFTER THIS QUERY EXECUTION? Click cancel.' + "\n\n---------------------------------------------------------------------------------------------------------------------------------\n\n" + query + "\n\n")) {
                ladda.start();
                $.ajax({
                    url  : ajaxurl,
                    type : 'POST',
                    data : data,
                    dataType : 'json',
                    success  : function (response) {
                        if (response.success) {
                            booklyAlert({success: [response.data.message]});
                            $('.bookly-js-fix-consistency', $constraintModal).hide();
                        } else {
                            booklyAlert({error : [response.data.message]});
                        }
                        ladda.stop();
                    }
                });
            }
        });

    $('[data-action=fix-column]')
        .on('click', function (e) {
            e.preventDefault();
            $status = $(this).closest('td');
            let $tr = $(this).closest('tr'),
                table = $tr.closest('.panel-collapse').attr('id'),
                column = $tr.find('td:eq(0)').html()
            ;
            $('.bookly-js-loading:first-child', $columnModal).addClass('bookly-loading').removeClass('collapse');
            $('.bookly-js-loading:last-child', $columnModal).addClass('collapse');
            $('.bookly-js-fix-consistency', $columnModal).hide();
            $columnModal.modal();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action    : 'bookly_get_field_data',
                    table     : table,
                    column    : column,
                    csrf_token: BooklyL10n.csrfToken
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        let sql = 'ALTER TABLE `' + table + '`' +
                            "\n ADD COLUMN `" + column + '` ' + response.data;
                        $('pre', $columnModal).html(sql);
                    } else {
                        $('pre', $columnModal).html('');
                    }
                    $('.bookly-js-loading', $columnModal).toggleClass('collapse');
                }
            });
        });

    $columnModal
        .on('click', '.bookly-js-save', function () {
            let ladda = Ladda.create(this);
            ladda.start();
            $.ajax({
                url  : ajaxurl,
                type : 'POST',
                data : {
                    action      : 'bookly_execute_query',
                    query       : $('pre', $columnModal).html(),
                    csrf_token  : BooklyL10n.csrfToken
                },
                dataType : 'json',
                success  : function (response) {
                    if (response.success) {
                        booklyAlert({success: [response.data.message]});
                        $columnModal.modal('hide');
                        $status.html('OK');
                    } else {
                        booklyAlert({error : [response.data.message]});
                    }
                    ladda.stop();
                },
                error: function () {
                    booklyAlert({error: ['Error: in query execution.']});
                    ladda.stop();
                }
            });
        });

    $('[data-action=fix-create-table]')
        .on('click', function (e) {
            e.preventDefault();
            $create = $(this);
            let table = $create.closest('.panel').find('.panel-collapse').attr('id');
            $('.bookly-js-loading:first-child', $tableModal).addClass('bookly-loading').removeClass('collapse');
            $('.bookly-js-loading:last-child', $tableModal).addClass('collapse');
            $tableModal.modal();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action    : 'bookly_get_field_data',
                    table     : table,
                    column    : 'id',
                    csrf_token: BooklyL10n.csrfToken
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        let field = response.data.replace(' primary key', ','),
                            sql = 'CREATE TABLE `' + table + '` (' +
                            "\n `id` " + field +
                            "\n PRIMARY KEY (`id`));";
                        $('pre', $tableModal).html(sql);
                    } else {
                        $('pre', $tableModal).html('');
                    }
                    $('.bookly-js-loading', $tableModal).toggleClass('collapse');
                }
            });
        });

    $tableModal
        .on('click', '.bookly-js-save', function () {
            let ladda = Ladda.create(this);
            ladda.start();
            $.ajax({
                url  : ajaxurl,
                type : 'POST',
                data : {
                    action      : 'bookly_execute_query',
                    query       : $('pre', $tableModal).html(),
                    csrf_token  : BooklyL10n.csrfToken
                },
                dataType : 'json',
                success  : function (response) {
                    if (response.success) {
                        booklyAlert({success: [response.data.message]});
                        $tableModal.modal('hide');
                        $create.closest('.panel').find('.panel-body').html('Refresh the current page');
                        $create.remove();
                    } else {
                        booklyAlert({error : [response.data.message]});
                    }
                    ladda.stop();
                },
                error: function () {
                    booklyAlert({error: ['Error: in query execution.']});
                    ladda.stop();
                }
            });
        });
});