<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div ng-controller=appointmentDialogCtrl id=ab_appointment_dialog style="display: none">

  <div ng-hide=loading class=dialog-content>
    <form ng-submit=processForm() class=form-horizontal>

      <div class=control-group>
        <label class=control-label><?php _e('Provider', 'ab') ?></label>
        <div class=controls>
          <select class="field" ng-model=form.staff ng-options="s.full_name for s in dataSource.data.staff"></select>
        </div>
      </div>

      <div class=control-group>
        <label class=control-label><?php _e('Service', 'ab') ?></label>
        <div class=controls>
          <select class="field" ng-model=form.service ng-options="s.title for s in form.staff.services" ng-change=onServiceChange()>
            <option value=""><?php _e('-- Select a service --', 'ab') ?></option>
          </select>
        </div>
      </div>

      <div class=control-group>
        <label class=control-label><?php _e('Date', 'ab') ?></label>
        <div class=controls>
          <input class="field" type=text ng-model=form.date ui-date="dateOptions" />
        </div>
      </div>

      <div class=control-group>
        <label class=control-label><?php _e('Period', 'ab') ?></label>
        <div class=controls>
          <div my-slide-up=errors.date_interval_not_available id=date_interval_not_available_msg>
            <?php _e( 'The selected period is occupied by another appointment!', 'ab' ) ?>
          </div>
          <select class="field-col-2" ng-model=form.start_time ng-options="t.title for t in dataSource.data.time" ng-change=onStartTimeChange()></select>
          <span><?php _e( ' to ', 'ab' ) ?></span>
          <select class="field-col-2" ng-model=form.end_time ng-options="t.title for t in dataSource.getDataForEndTime()" ng-change=onEndTimeChange()></select>
          <div my-slide-up=errors.date_interval_warning id=date_interval_warning_msg>
            <?php _e( 'The selected period does\'t match default duration for the selected service!', 'ab' ) ?>
          </div>
        </div>
      </div>

      <div class=control-group>
        <label class=control-label><?php _e('Customer', 'ab') ?></label>
        <div class=controls>
            <select id="chosen" multiple data-placeholder="<?php _e('-- Select a customer --', 'ab') ?>" class="field chzn-select" chosen="dataSource.data.customers"
                    ng-model="form.customers" ng-options="c.name for c in dataSource.data.customers">
            </select>
          <div new-customer-dialog=createCustomer(customer) backdrop=false btn-class=""></div>
        </div>
        <div class=controls>
            <div my-slide-up="errors.overflow_capacity" ng-bind="errors.overflow_capacity_message" style="color: red;"></div>
        </div>
      </div>

      <div class="control-group" ng-show="show_notes">
        <label class=control-label><?php _e('Notes', 'ab') ?></label>
        <div class=controls>
          <textarea class="field" ng-model=form.notes></textarea>
        </div>
      </div>

      <div class=control-group>
        <label class=control-label></label>
        <div class=controls>
          <input style="margin-top: 0" type="checkbox" id="email_notification" /> <?php _e('Send email notifications', 'ab') ?>
            <img
                src="<?php echo plugins_url( 'backend/resources/images/help.png', AB_PATH . '/main.php' ) ?>"
                alt=""
                class="ab-popover"
                popover="<?php echo esc_attr(__('If email notifications are enabled and you want the customer or the staff member to be notified about this appointment after saving, tick this checkbox before clicking Save.', 'ab')) ?>"
                style="width:16px;margin-left:0;"
                />
	        <div id="email_notification_text" style="display: none; margin-top: 10px;"><?php _e('This function is disabled in the lite version of Bookly. If you find the plugin useful for your business please consider buying a licence for the full version. It costs just $38 and for this money you will get many useful functions, lifetime free update and excellent support! More information can be found here', 'ab'); ?>: <a href="http://bookly.ladela.com" target="_blank">http://bookly.ladela.com</a></div>
        </div>
      </div>

      <div class=control-group>
        <label class=control-label></label>
        <div class=controls>
          <div class=dialog-button-wrapper>
            <input type=submit class="btn btn-info ab-update-button" value="<?php _e('Save') ?>" />
            <a ng-click=closeDialog() class=ab-reset-form href=""><?php _e('Cancel') ?></a>
          </div>
        </div>
      </div>

    </form>
  </div>

  <div ng-show=loading class=loading-indicator>
    <img src="<?php echo plugins_url('resources/images/ajax_loader_32x32.gif', dirname(__FILE__) . '/../../../AB_Backend.php') ?>" alt="" />
  </div>

</div>