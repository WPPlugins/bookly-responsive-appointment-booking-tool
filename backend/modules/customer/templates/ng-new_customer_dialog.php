<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div>
  <a href=#ab_new_customer_dialog class="{{btn_class}}" data-toggle=modal data-backdrop={{backdrop}}><?php _e( 'New customer' , 'ab' ) ?></a>
  <div id=ab_new_customer_dialog class="modal hide fade" tabindex=-1 role=dialog aria-labelledby=myModalLabel aria-hidden=true>
    <div class=dialog-content>
      <form class=form-horizontal ng-hide=loading>
        <div class=modal-header>
          <button type=button class=close data-dismiss=modal aria-hidden=true>×</button>
          <h3 id=myModalLabel><?php _e( 'New Customer', 'ab' ) ?></h3>
        </div>
        <div class=modal-body>
          <div class=control-group>
            <label class=control-label><?php _e( 'Name' , 'ab' ) ?></label>
            <div class=controls>
              <input type=text ng-model=form.name required />
              <span style="font-size: 11px;color: red" ng-show=errors.name.required><?php _e( 'Required' , 'ab' ) ?></span>
            </div>
          </div>
          <div class=control-group>
            <label class=control-label><?php _e( 'Phone' , 'ab' ) ?></label>
            <div class=controls>
              <input type=text ng-model=form.phone />
            </div>
          </div>
          <div class=control-group>
            <label class=control-label><?php _e( 'Email' , 'ab' ) ?></label>
            <div class=controls>
              <input type=text ng-model=form.email />
            </div>
          </div>
          <div class=control-group>
            <label class=control-label><?php _e( 'Notes' , 'ab' ) ?></label>
            <div class=controls>
              <textarea ng-model=form.notes></textarea>
            </div>
          </div>
        </div>
        <div class=modal-footer>
          <div class=ab-modal-button>
            <button ng-click=processForm() class="btn btn-info ab-popup-save ab-update-button"><?php _e( 'Save customer' , 'ab' ) ?></button>
            <button class=ab-reset-form data-dismiss=modal aria-hidden=true><?php _e( 'Cancel' , 'ab' ) ?></button>
          </div>
        </div>
      </form>
      <div ng-show=loading class=loading-indicator>
        <img src="<?php echo plugins_url( 'backend/resources/images/ajax_loader_32x32.gif', AB_PATH . '/main.php' ) ?>" alt="" />
      </div>
    </div>
  </div>
</div>