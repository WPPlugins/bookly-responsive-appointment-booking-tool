<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="ab-tinymce-popup" style="display: none">
    <form id="ab-shortcode-form">
        <table>
            <?php do_action( 'bookly_render_popup_head' ) ?>
            <tr>
                <td>
                    <label for="ab-select-category"><?php _e( 'Default value for category select', 'bookly' ) ?></label>
                </td>
                <td>
                    <select id="ab-select-category">
                        <option value=""><?php _e( 'Select category', 'bookly' ) ?></option>
                    </select>
                    <div><label><input type="checkbox" id="ab-hide-categories" /><?php _e( 'Hide this field', 'bookly' ) ?></label></div>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="ab-select-service"><?php _e( 'Default value for service select', 'bookly' ) ?></label>
                </td>
                <td>
                    <select id="ab-select-service">
                        <option value=""><?php _e( 'Select service', 'bookly' ) ?></option>
                    </select>
                    <div><label><input type="checkbox" id="ab-hide-services" /><?php _e( 'Hide this field', 'bookly' ) ?></label></div>
                    <i><?php _e( 'Please be aware that a value in this field is required in the frontend. If you choose to hide this field, please be sure to select a default value for it', 'bookly' ) ?></i>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="ab-select-employee"><?php _e( 'Default value for employee select', 'bookly' ) ?></label>
                </td>
                <td>
                    <select class="ab-select-mobile" id="ab-select-employee">
                        <option value=""><?php _e( 'Any', 'bookly' ) ?></option>
                    </select>
                    <div><label><input type="checkbox" id="ab-hide-employee" /><?php _e( 'Hide this field', 'bookly' ) ?></label></div>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="ab-hide-number-of-persons"><?php echo esc_html( get_option( 'bookly_l10n_label_number_of_persons' ) ) ?></label>
                </td>
                <td>
                    <label><input type="checkbox" id="ab-hide-number-of-persons" checked /><?php _e( 'Hide this field', 'bookly' ) ?></label>
                </td>
            </tr>
            <?php do_action( 'bookly_render_popup_controls' ) ?>
            <tr>
                <td>
                    <label for="ab-hide-date"><?php _e( 'Date', 'bookly' ) ?></label>
                </td>
                <td>
                    <label><input type="checkbox" id="ab-hide-date" /><?php _e( 'Hide this block', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="ab-hide-week_days"><?php _e( 'Week days', 'bookly' ) ?></label>
                </td>
                <td>
                    <label><input type="checkbox" id="ab-hide-week_days" /><?php _e( 'Hide this block', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="ab-hide-time_range"><?php _e( 'Time range', 'bookly' ) ?></label>
                </td>
                <td>
                    <label><input type="checkbox" id="ab-hide-time_range" /><?php _e( 'Hide this block', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input class="button button-primary" id="ab-insert-shortcode" type="submit" value="<?php _e( 'Insert', 'bookly' ) ?>" />
                </td>
            </tr>
        </table>
    </form>
</div>
<style type="text/css">
    #ab-shortcode-form { margin-top: 15px; }
    #ab-shortcode-form table { width: 100%; }
    #ab-shortcode-form table td select { width: 100%; margin-bottom: 5px; }
    .ab-media-icon {
        display: inline-block;
        width: 16px;
        height: 16px;
        vertical-align: text-top;
        margin: 0 2px;
        background: url("<?php echo plugins_url( 'resources/images/calendar.png', __DIR__ ) ?>") 0 0 no-repeat;
    }
    #TB_overlay { z-index: 100001 !important; }
    #TB_window { z-index: 100002 !important; }
</style>
<script type="text/javascript">
    jQuery(function ($) {
        var $select_location        = $('#ab-select-location'),
            $select_category        = $('#ab-select-category'),
            $select_service         = $('#ab-select-service'),
            $select_employee        = $('#ab-select-employee'),
            $hide_locations         = $('#ab-hide-locations'),
            $hide_categories        = $('#ab-hide-categories'),
            $hide_services          = $('#ab-hide-services'),
            $hide_staff             = $('#ab-hide-employee'),
            $hide_number_of_persons = $('#ab-hide-number-of-persons'),
            $hide_quantity          = $('#ab-hide-quantity'),
            $hide_date              = $('#ab-hide-date'),
            $hide_week_days         = $('#ab-hide-week_days'),
            $hide_time_range        = $('#ab-hide-time_range'),
            $add_button             = $('#add-bookly-form'),
            $insert                 = $('#ab-insert-shortcode'),
            locations               = <?php echo json_encode( $casest['locations'] ) ?>,
            categories              = <?php echo json_encode( $casest['categories'] ) ?>,
            services                = <?php echo json_encode( $casest['services'] ) ?>,
            staff                   = <?php echo json_encode( $casest['staff'] ) ?>
            ;

        $add_button.on('click', function () {
            window.parent.tb_show(<?php echo json_encode( __( 'Insert Appointment Booking Form', 'bookly' ) ) ?>, this.href);
            window.setTimeout(function(){
                $('#TB_window').css({
                    'overflow-x': 'auto',
                    'overflow-y': 'hidden'
                });
            },100);
        });

        function setSelect($select, data, value) {
            // reset select
            $('option:not([value=""])', $select).remove();
            // and fill the new data
            var docFragment = document.createDocumentFragment();

            function valuesToArray(obj) {
                return Object.keys(obj).map(function (key) { return obj[key]; });
            }

            function compare(a, b) {
                if (parseInt(a.pos) < parseInt(b.pos))
                    return -1;
                if (parseInt(a.pos) > parseInt(b.pos))
                    return 1;
                return 0;
            }

            // sort select by position
            data = valuesToArray(data).sort(compare);

            $.each(data, function(key, object) {
                var option = document.createElement('option');
                option.value = object.id;
                option.text = object.name;
                docFragment.appendChild(option);
            });
            $select.append(docFragment);
            // set default value of select
            $select.val(value);
        }

        function setSelects(location_id, category_id, service_id, staff_id) {
            var _staff = {}, _services = {}, _categories = {}, _nop = {};
            $.each(staff, function(id, staff_member) {
                if (location_id == '' || locations[location_id].staff.hasOwnProperty(id)) {
                    if (service_id == '') {
                        if (category_id == '') {
                            _staff[id] = staff_member;
                        } else {
                            $.each(staff_member.services, function(s_id) {
                                if (services[s_id].category_id == category_id) {
                                    _staff[id] = staff_member;
                                    return false;
                                }
                            });
                        }
                    } else if (staff_member.services.hasOwnProperty(service_id)) {
                        if (staff_member.services[service_id].price != null) {
                            _staff[id] = {
                                id   : id,
                                name : staff_member.name + ' (' + staff_member.services[service_id].price + ')',
                                pos  : staff_member.pos
                            };
                        } else {
                            _staff[id] = staff_member;
                        }
                    }
                }
            });
            if (location_id == '') {
                _categories = categories;
                $.each(services, function(id, service) {
                    if (category_id == '' || service.category_id == category_id) {
                        if (staff_id == '' || staff[staff_id].services.hasOwnProperty(id)) {
                            _services[id] = service;
                        }
                    }
                });
            } else {
                var category_ids = [];
                $.each(locations[location_id].staff, function(st_id) {
                    $.each(staff[st_id].services, function(s_id) {
                        category_ids.push(services[s_id].category_id);
                    });
                });
                $.each(categories, function(id, category) {
                    if ($.inArray(parseInt(id), category_ids) > -1) {
                        _categories[id] = category;
                    }
                });
                $.each(services, function(id, service) {
                    if ($.inArray(service.category_id, category_ids) > -1) {
                        if (staff_id == '' || staff[staff_id].services.hasOwnProperty(id)) {
                            _services[id] = service;
                        }
                    }
                });
            }
            setSelect($select_category, _categories, category_id);
            setSelect($select_service, _services, service_id);
            setSelect($select_employee, _staff, staff_id);
        }

        // Location select change
        $select_location.on('change', function () {
            var location_id = this.value,
                category_id = $select_category.val(),
                service_id  = $select_service.val(),
                staff_id    = $select_employee.val()
                ;

            // Validate selected values.
            if (location_id != '') {
                if (staff_id != '' && !locations[location_id].staff.hasOwnProperty(staff_id)) {
                    staff_id = '';
                }
                if (service_id != '') {
                    var valid = false;
                    $.each(locations[location_id].staff, function(id) {
                        if (staff[id].services.hasOwnProperty(service_id)) {
                            valid = true;
                            return false;
                        }
                    });
                    if (!valid) {
                        service_id = '';
                    }
                }
                if (category_id != '') {
                    var valid = false;
                    $.each(locations[location_id].staff, function(id) {
                        $.each(staff[id].services, function(s_id) {
                            if (services[s_id].category_id == category_id) {
                                valid = true;
                                return false;
                            }
                        });
                        if (valid) {
                            return false;
                        }
                    });
                    if (!valid) {
                        category_id = '';
                    }
                }
            }
            setSelects(location_id, category_id, service_id, staff_id);
        });

        // Category select change
        $select_category.on('change', function () {
            var location_id = $select_location.val(),
                category_id = this.value,
                service_id  = $select_service.val(),
                staff_id    = $select_employee.val()
                ;

            // Validate selected values.
            if (category_id != '') {
                if (service_id != '') {
                    if (services[service_id].category_id != category_id) {
                        service_id = '';
                    }
                }
                if (staff_id != '') {
                    var valid = false;
                    $.each(staff[staff_id].services, function(id) {
                        if (services[id].category_id == category_id) {
                            valid = true;
                            return false;
                        }
                    });
                    if (!valid) {
                        staff_id = '';
                    }
                }
            }
            setSelects(location_id, category_id, service_id, staff_id);
        });

        // Service select change
        $select_service.on('change', function () {
            var location_id = $select_location.val(),
                category_id = '',
                service_id  = this.value,
                staff_id    = $select_employee.val()
                ;

            // Validate selected values.
            if (service_id != '') {
                if (staff_id != '' && !staff[staff_id].services.hasOwnProperty(service_id)) {
                    staff_id = '';
                }
            }
            setSelects(location_id, category_id, service_id, staff_id);
            if (service_id) {
                $select_category.val(services[service_id].category_id);
            }
        });

        // Staff select change
        $select_employee.on('change', function() {
            var location_id = $select_location.val(),
                category_id = $select_category.val(),
                service_id  = $select_service.val(),
                staff_id    = this.value
                ;

            setSelects(location_id, category_id, service_id, staff_id);
        });

        // Set up draft selects.
        setSelect($select_location, locations);
        setSelect($select_category, categories);
        setSelect($select_service,  services);
        setSelect($select_employee, staff);

        $insert.on('click', function (e) {
            e.preventDefault();

            var insert = '[bookly-form';
            var hide   = [];
            if ($select_location.val()) {
                insert += ' location_id="' + $select_location.val() + '"';
            }
            if ($select_category.val()) {
                insert += ' category_id="' + $select_category.val() + '"';
            }
            if ($hide_locations.is(':checked')) {
                hide.push('locations');
            }
            if ($hide_categories.is(':checked')) {
                hide.push('categories');
            }
            if ($select_service.val()) {
                insert += ' service_id="' + $select_service.val() + '"';
            }
            if ($hide_services.is(':checked')) {
                hide.push('services');
            }
            if ($select_employee.val()) {
                insert += ' staff_member_id="' + $select_employee.val() + '"';
            }
            if ($hide_number_of_persons.is(':not(:checked)')) {
                insert += ' show_number_of_persons="1"';
            }
            if ($hide_quantity.is(':checked')) {
                hide.push('quantity');
            }
            if ($hide_staff.is(':checked')) {
                hide.push('staff_members');
            }
            if ($hide_date.is(':checked')) {
                hide.push('date')
            }
            if ($hide_week_days.is(':checked')) {
                hide.push('week_days')
            }
            if ($hide_time_range.is(':checked')) {
                hide.push('time_range');
            }
            if (hide.length > 0) {
                insert += ' hide="' + hide.join() + '"';
            }
            insert += ']';

            window.send_to_editor(insert);

            $select_location.val('');
            $select_category.val('');
            $select_service.val('');
            $select_employee.val('');
            $hide_locations.prop('checked', false);
            $hide_categories.prop('checked', false);
            $hide_services.prop('checked', false);
            $hide_staff.prop('checked', false);
            $hide_date.prop('checked', false);
            $hide_week_days.prop('checked', false);
            $hide_time_range.prop('checked', false);
            $hide_number_of_persons.prop('checked', true);

            window.parent.tb_remove();
            return false;
        });
    });
</script>