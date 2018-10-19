jQuery(function($) {

    var $custom_notifications   = $('#bookly-js-custom-notifications'),
        $tiny_mce_container     = $('#bookly-js-tinymce-container'),
        $container              = $('.bookly-main');

    Ladda.bind( 'button[type=submit]' );

    // menu fix for WP 3.8.1
    $('#toplevel_page_ab-system > ul').css('margin-left', '0px');

    /* exclude checkboxes in form */
    var $checkboxes = $('.bookly-js-collapse .panel-title > input:checkbox');

    $checkboxes.change(function () {
        $(this).parents('.panel-heading').next().collapse(this.checked ? 'show' : 'hide');
    });

    $container.on('show.bs.collapse', '.panel', function () {
        var $panel     = $(this),
            $old_panel = $('#bookly-js-tinymce-container').closest('.panel'),
            message_id = $panel.find('.panel-collapse').attr('id');

        $container.find('.panel .collapse.in').collapse('hide');
        if ($old_panel.hasClass('bookly-js-collapse')) {
            $old_panel.find('.bookly-js-message-input').val(tinymce.get('bookly-js-tinymce-area').getContent());
            $old_panel.find('#bookly-js-tinymce-area-tmce').click();
        }
        tinymce.remove("#bookly-js-tinymce-area");
        $tiny_mce_container.detach().appendTo('#' + message_id + ' .bookly-js-tinymce-message');
        tinymce.init(tinyMCEPreInit.mceInit['bookly-js-tinymce-area']);
        tinymce.get('bookly-js-tinymce-area').setContent($panel.find('.bookly-js-message-input').val());
    });

    $container.on('shown.bs.collapse', '.panel', function () {
        var $panel = $(this),
            $subject = $panel.find('input[id$="subject"]');

        $subject.focus();
        $('html, body').animate({
            scrollTop: Math.max($panel.offset().top - 40, 0)
        }, 1000);
    });

    $('#bookly-save').on('click', function (e) {
        e.preventDefault();
        var $panel = $('#bookly-js-tinymce-container').closest('.panel');

        if ($panel.hasClass('bookly-js-collapse')) {
            $panel.find('.bookly-js-message-input').val(tinymce.get('bookly-js-tinymce-area').getContent());
        }

        $('#bookly-save').closest('form').submit();
    });

    $('[data-toggle="popover"]').popover({
        html: true,
        placement: 'top',
        trigger: 'hover',
        template: '<div class="popover bookly-font-xs" style="width: 220px" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
    });

    $custom_notifications
        .on('change', "select[name$='[type]']", function () {
            var $panel        = $(this).closest('.panel'),
                $settings     = $panel.find('.bookly-js-settings'),
                $attach       = $panel.find('.bookly-js-attach'),
                value         = $(this).find(':selected').val(),
                set           = $(this).find(':selected').data('set'),
                to            = $(this).find(':selected').data('to'),
                showAttach    = $(this).find(':selected').data('attach-show')||[]
            ;

            $panel.find('table.bookly-codes').each(function () {
                $(this).toggle($(this).hasClass('bookly-js-codes-' + value));
            });

            $.each(['customer', 'staff', 'admin'], function (index, value) {
                $panel.find("[name$='[to_" + value + "]']").closest('.checkbox-inline').toggle(to.indexOf(value) != -1);
            });

            $attach.hide();
            $.each(showAttach, function (index, value) {
                $('.bookly-js-' + value, $panel).show();
            });

            $settings.each(function () {
                $(this).toggle($(this).hasClass('bookly-js-' + set));
            });

            switch (set) {
                case 'after_event':
                    var $set = $panel.find('.bookly-js-' + set);
                    $set.find('.bookly-js-to').toggle(value == 'ca_status_changed');
                    $set.find('.bookly-js-with').toggle(value != 'ca_status_changed');
                    break;
            }
        })
        .on('click', '.bookly-js-delete', function () {
            if (confirm(BooklyL10n.are_you_sure)) {
                var $button = $(this),
                    id    = $button.data('notification_id'),
                    ladda = Ladda.create(this);
                    ladda.start();
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bookly_delete_custom_notification',
                        id: id,
                        csrf_token: BooklyL10n.csrf_token
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            var $panel = $button.closest('.panel');
                            if ($panel.find('#bookly-js-tinymce-container').length != 0) {
                                $panel.find('#bookly-js-tinymce-area-tmce').click();
                                tinymce.remove("#bookly-js-tinymce-area");
                                $tiny_mce_container.detach().appendTo('#bookly-js-tinymce-wrap');
                                tinymce.init(tinyMCEPreInit.mceInit['bookly-js-tinymce-area']);
                                tinymce.get('bookly-js-tinymce-area').setContent($panel.find('.bookly-js-message-input').val());
                            }
                            $panel.remove();
                            ladda.stop();
                        }
                    }
                });
            }
        })
        .find("select[name$='[type]']").trigger('change');

    $('button[type=reset]').on('click', function () {
        setTimeout(function () {
            $("select[name$='[type]']", $custom_notifications).trigger('change');
        }, 0);
    });

    $("#bookly-js-new-notification").on('click', function () {
        var ladda = Ladda.create(this);
        ladda.start();
        $.ajax({
            url : ajaxurl,
            type: 'POST',
            data: {
                action    : 'bookly_pro_create_custom_notification',
                render    : true,
                csrf_token: BooklyL10n.csrf_token
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $custom_notifications.append(response.data.html);
                    var $subject = $custom_notifications.find('[name="notification[' + response.data.id + '][subject]"]'),
                        $panel = $subject.closest('.panel-collapse');

                    $panel.collapse('show');
                    $panel.find("select[name$='[type]']").trigger('change');
                    $subject.focus();
                }
            },
            complete: function () {
                ladda.stop();
            }
        });
    });

    booklyAlert(BooklyL10n.alert);
});