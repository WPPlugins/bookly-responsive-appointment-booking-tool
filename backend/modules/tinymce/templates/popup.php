<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="ab-tinymce-popup" style="display: none">
    <form id="ab-shortcode-form">
        <table>
            <tr>
                <td>
                    <label for="ab-select-category"><?php _e( 'Default value for category select', 'ab' ); ?></label>
                </td>
                <td>
                    <select class="select-list" id="ab-select-category">
                        <option value=""><?php _e( 'Select category', 'ab' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="checkbox" id="ab-hide-categories" /> <?php _e( 'Hide this field', 'ab' ); ?>
                    <p><?php _e( 'Please be aware that a value in this field is required in the frontend. If you choose to hide this field, please be sure to select a default value for it', 'ab' ) ?></p>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="ab-select-service"><?php _e( 'Default value for service select', 'ab' ); ?></label>
                </td>
                <td>
                    <select class="select-list" id="ab-select-service">
                        <option value=""><?php _e('Select service') ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="checkbox" id="ab-hide-services" /> <?php _e( 'Hide this field', 'ab' ); ?>
                    <p><?php _e( 'Please be aware that a value in this field is required in the frontend. If you choose to hide this field, please be sure to select a default value for it', 'ab' ) ?></p>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="ab-select-employee"><?php _e('Default value for employee select', 'ab') ?></label>
                </td>
                <td>
                    <select class="select-list ab-select-mobile" id="ab-select-employee">
                        <option value=""><?php _e( 'Any', 'ab' ) ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="checkbox" id="ab-hide-employee" /> <?php _e( 'Hide this field', 'ab' ); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="ab-hide-available"><?php _e('"I\'m available on …" block', 'ab') ?></label>
                </td>
                <td>
                    <input type="checkbox" id="ab-hide-available" /> <?php _e( 'Hide this block', 'ab' ) ?>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input class="button button-primary" id="ab-insert-shortcode" type="submit" value="<?php _e( 'Insert', 'ab' ); ?>" />
                </td>
            </tr>
        </table>
    </form>
</div>
<style type="text/css">
#ab-shortcode-form { margin-top: 15px; }
#ab-shortcode-form table { width: 100%; }
#ab-shortcode-form table td { padding: 5px; vertical-align: 0; }
#ab-shortcode-form table td select { width: 100%; margin-bottom: 5px; }
.ab-media-icon {
  display: inline-block;
  width: 16px;
  height: 16px;
  vertical-align: text-top;
  margin: 0 2px;
  background: url("<?php echo plugins_url( 'resources/images/calendar.png' , __DIR__ ) ?>") 0 0 no-repeat;
}
.ab-booking_form-units {
    width: 17%;
    margin-left: 5px;
}
</style>
<script type="text/javascript">
jQuery(function ($) {
    var $select_category = $('#ab-select-category'),
        $select_service  = $('#ab-select-service'),
        $select_employee = $('#ab-select-employee'),
        $hide_categories = $('#ab-hide-categories'),
        $hide_services   = $('#ab-hide-services'),
        $hide_staff      = $('#ab-hide-employee'),
        $hide_available  = $('#ab-hide-available'),
        $add_button      = $('#add-ap-booking'),
        $insert          = $('#ab-insert-shortcode'),
        abCategories     = <?php echo $categoriesJson ?>,
        abStaff          = <?php echo $staffJson ?>,
        abServices       = <?php echo $servicesJson ?>;

    $add_button.on('click', function () {
        window.parent.tb_show(<?php echo json_encode( __( 'Insert Appointment Booking Form', 'ab' ) ) ?>, this.href);
    });

    var AB_Category = Backbone.Model.extend();
    var AB_Service = Backbone.Model.extend();
    var AB_Employee =  Backbone.Model.extend();

    var AB_Categories = Backbone.Collection.extend({
        model: AB_Category
    });

    var AB_Services = Backbone.Collection.extend({
        model: AB_Service
    });

    var AB_Staff = Backbone.Collection.extend({
        model: AB_Employee
    });

    var AB_OptionView = Backbone.View.extend({
        tagName: "option",

        initialize: function(){
            _.bindAll(this, 'render');
        },
        render: function(){
            this.$el.attr('value', this.model.get('id')).html(this.model.get('name'));
            return this;
        }
    });

    var AB_SelectView = Backbone.View.extend({
        events: {
            "change": "changeSelected"
        },

        initialize: function() {
            _.bindAll(this, 'addOne', 'addAll');
            this.selectView = [];
            this.collection.bind('reset', this.addAll);
        },
        addOne: function(location) {
            var optionView = new AB_OptionView({ model: location });
            this.selectView.push(optionView);
            this.$el.append(optionView.render().el);
        },
        addAll: function() {
            _.each(this.selectView, function(optionView) { optionView.remove(); });
            this.selectView = [];

            this.collection.each(this.addOne);

            if (this.selectedId) {
                this.$el.val(this.selectedId);
            }
        },
        changeSelected: function() {
            this.setSelectedId(this.$el.val());
        }
    });

    var AB_CategoriesView = AB_SelectView.extend({
        setSelectedId: function(categoryId) {
            this.selectedId = categoryId;
            if (this.staffView.selectedId && !this.servicesView.selectedId) {
                this.servicesView.collection.reset();
                this.servicesView.setCategoryEmployeeIds(categoryId, this.staffView.selectedId);
            } else {
                this.servicesView.selectedId = null;
                this.staffView.selectedId = null;
                this.servicesView.collection.reset();
                this.staffView.collection.reset();
                if (categoryId) {
                    this.servicesView.setCategoryId(categoryId);
                    this.staffView.setCategoryId(categoryId);
                } else {
                    this.servicesView.setDefaultValues();
                    this.staffView.setDefaultValues();
                }
            }
        },
        setEmployeeId: function(employeeId) {
            this.populate(abStaff[employeeId].categories);
        },
        populate: function(categories) {
            var category;
            for (var category_id in categories) {
                category = new AB_Service();
                category.set({
                    id: category_id,
                    name: categories[category_id].name
                });
                this.collection.push(category);
            }
            this.addAll();
        },
        setDefaultValues: function () {
            var category;
            for (var category_id in abCategories) {
                category = new AB_Category();
                category.set({
                    id: category_id,
                    name: abCategories[category_id].name
                });
                this.collection.push(category);
            }
            this.addAll();
        }
    });

    var AB_ServicesView = AB_SelectView.extend({
        setSelectedId: function(serviceId) {
            this.selectedId = serviceId;
            this.staffView.selectedId = null;
            if (serviceId) {
                if (!this.categoriesView.selectedId) {
                    this.categoriesView.selectedId = abServices[serviceId].category_id;
                    this.categoriesView.$el.val(this.categoriesView.selectedId);
                }
                this.staffView.collection.reset();
                this.staffView.setServiceId(serviceId);
            } else if (this.categoriesView.selectedId) {
                this.staffView.$el.val('');
                this.staffView.setCategoryId(this.categoriesView.selectedId)
            }
        },
        setCategoryId: function(categoryId) {
            this.populate(abCategories[categoryId].services);
        },
        setEmployeeId: function(employeeId) {
            this.populate(abStaff[employeeId].services);
        },
        setCategoryEmployeeIds: function(categoryId, employeeId) {
            var service, collection = this.collection, employee = abStaff[employeeId];
            // It is possible that employeeId does not exist and remain only as short code argument
            if (employee) {
                _.each(employee.services, function(srv, serviceId) {
                    if (Number(srv.category_id) == categoryId) {
                        service = new AB_Service();
                        service.set({
                            id: serviceId,
                            name: employee.services[serviceId].name
                        });
                        collection.push(service);
                    }
                });
                this.addAll();
            }
        },
        populate: function(services) {
            var service;
            for (var service_id in services) {
                service = new AB_Service();
                service.set({
                    id: service_id,
                    name: services[service_id].name
                });
                this.collection.push(service);
            }
            this.addAll();
        },
        setDefaultValues: function () {
            var service;
            for (var service_id in abServices) {
                service = new AB_Service();
                service.set({
                    id: service_id,
                    name: abServices[service_id].name
                });
                this.collection.push(service);
            }
            this.addAll();
        }
    });

    var AB_StaffView = AB_SelectView.extend({
        setSelectedId: function(employeeId) {
            this.selectedId = employeeId;
            if (employeeId) {
                if (!this.categoriesView.selectedId && !this.servicesView.selectedId) {
                    this.categoriesView.collection.reset();
                    this.servicesView.collection.reset();
                    this.servicesView.setEmployeeId(employeeId);
                    this.categoriesView.setEmployeeId(employeeId);
                } else if (!this.servicesView.selectedId) {
                    this.servicesView.collection.reset();
                    this.servicesView.setCategoryEmployeeIds(this.categoriesView.selectedId, employeeId);
                }
            } else if (!this.categoriesView.selectedId && !this.servicesView.selectedId) {
                this.categoriesView.collection.reset();
                this.servicesView.collection.reset();
                this.categoriesView.setDefaultValues();
                this.servicesView.setDefaultValues();
            } else if (this.categoriesView.selectedId && !this.servicesView.selectedId) {
                this.categoriesView.collection.reset();
                this.categoriesView.setDefaultValues();
                this.categoriesView.$el.val(this.categoriesView.selectedId);
                this.servicesView.setCategoryId(this.categoriesView.selectedId);
            }
        },
        setServiceId: function(serviceId) {
            this.populate(abServices[serviceId].staff);
        },
        setCategoryId: function(categoryId) {
            this.populate(abCategories[categoryId].staff);
        },
        populate: function(staff) {
            var employee;
            for (var employee_id in staff) {
                employee = new AB_Employee();
                employee.set({
                    id: employee_id,
                    name: staff[employee_id].name
                });
                this.collection.push(employee);
            }
            this.addAll();
        },
        setDefaultValues: function () {
            var employee;
            for (var employee_id in abStaff) {
                employee = new AB_Employee;
                employee.set({
                    id: employee_id,
                    name: abStaff[employee_id].name
                });
                this.collection.push(employee);
            }
            this.addAll();
        }
    });

    var categoriesView = new AB_CategoriesView({el: $select_category, collection: new AB_Categories() });
    var servicesView = new AB_ServicesView({el: $select_service, collection: new AB_Services() });
    var staffView = new AB_StaffView({el: $select_employee, collection: new AB_Staff() });

    categoriesView.servicesView = servicesView;
    categoriesView.staffView = staffView;
    servicesView.staffView = staffView;
    servicesView.categoriesView = categoriesView;
    staffView.categoriesView = categoriesView;
    staffView.servicesView = servicesView;
    categoriesView.setDefaultValues();
    servicesView.setDefaultValues();
    staffView.setDefaultValues();

    $insert.on('click', function (e) {
        e.preventDefault();

        var insert = '[ap-booking';

        if ($select_category.val()) {
            insert += ' cid="' + $select_category.val() + '"';
        }

        if ($hide_categories.is(':checked')) {
            insert += ' ch="1"';
        }

        if ($select_service.val()) {
            insert += ' sid="' + $select_service.val() + '"';
        }

        if ($hide_services.is(':checked')) {
            insert += ' hs="1"';
        }

        if ($select_employee.val()) {
            insert += ' eid="' + $select_employee.val() + '"';
        }

        if ($hide_staff.is(':checked')) {
            insert += ' he="1"';
        }

        if ($hide_available.is(':checked')) {
            insert += ' ha="1"';
        }

        insert += ']';

        window.send_to_editor(insert);

        $select_category.val('');
        $select_service.val('');
        $select_employee.val('');
        $hide_categories.prop('checked', false);
        $hide_services.prop('checked', false);
        $hide_staff.prop('checked', false);
        $hide_available.prop('checked', false);

        window.parent.tb_remove();
        return false;
    });
});
</script>