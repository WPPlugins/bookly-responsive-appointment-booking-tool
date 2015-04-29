<?php
/**
 * @var AB_Staff $staff
 * @var string $authUrl
 * @var array $staff_errors
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div id="ab-edit-staff">
    <?php if( isset($update) ): ?>
        <div class="alert">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?php _e( 'Settings saved.', 'ab' )?>
        </div>
    <?php endif ?>
    <?php if ($staff_errors): ?>
        <div class="error">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?php
                foreach ($staff_errors as $staff_error){
                    echo $staff_error . "<br>";
                }
            ?>
        </div>
    <?php endif ?>
    <div style="overflow: hidden; position: relative">
        <h2 class="left"><?php echo $staff->get( 'full_name' ) ?></h2>
    </div>
    <div class="tabbable">
        <ul class="nav nav-tabs ab-nav-tabs">
            <li class="active"><a id="ab-staff-details-tab" href="#tab1" data-toggle="tab"><?php _e( 'Details', 'ab' ) ?></a></li>
            <li><a id="ab-staff-services-tab" href="#tab2" data-toggle="tab"><?php _e( 'Services', 'ab' ) ?></a></li>
            <li><a id="ab-staff-schedule-tab" href="#tab3" data-toggle="tab"><?php _e( 'Schedule', 'ab') ?></a></li>
            <li><a id="ab-staff-holidays-tab" href="#tab4" data-toggle="tab"><?php _e( 'Days off', 'ab') ?></a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="tab1">
                <div id="ab-staff-details-container" class="ab-staff-tab-content">
                    <form class="ab-staff-form bs-docs-example form-horizontal" action="" name="ab_staff" method="POST" enctype="multipart/form-data">
                        <table cellspacing="0">
                            <tbody>
                            <tr>
                                <td class="control-group">
                                    <label class="control-label" for="ab-staff-wpuser"><?php _e( 'User', 'ab') ?></label>
                                    <div class="controls">
                                        <select name="wp_user_id" id="ab-staff-wpuser">
                                            <option value=""><?php _e( 'Select from WP users', 'ab') ?></option>
                                            <?php foreach ( $form->getUsersForStaff( $staff->id ) as $user ) : ?>
                                                <option value="<?php echo $user->ID ?>" data-email="<?php echo $user->user_email ?>" <?php selected($user->ID, $staff->get( 'wp_user_id' )) ?>><?php echo $user->display_name ?></option>
                                            <?php endforeach ?>
                                        </select>
                                        <img
                                            src="<?php echo plugins_url( 'backend/resources/images/help.png', AB_PATH . '/main.php' ) ?>"
                                            alt=""
                                            class="ab-popover-ext"
                                            data-ext_id="ab-staff-popover-ext"
                                            />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="control-group">
                                    <label class="control-label" for="ab-staff-full-name"><?php _e( 'Photo', 'ab') ?></label>
                                    <div class="controls">
                                        <div id="ab-staff-avatar-image">
                                            <?php if ( $staff->get( 'avatar_url' ) ) : ?>
                                                <img src="<?php echo $staff->get( 'avatar_url' ) ?>" alt="<?php _e( 'Avatar', 'ab') ?>"/>
                                                <a id="ab-delete-avatar" href="javascript:void(0)"><?php _e( 'Delete current photo', 'ab') ?></a>
                                            <?php endif ?>
                                        </div>
                                        <input id="ab-staff-avatar" name="avatar" type="file"/>
                                    </div>
                                </td>
                            </tr>
                            <tr class="form-field form-required">
                                <td class="control-group">
                                    <label class="control-label" for="ab-staff-full-name"><?php _e( 'Full name', 'ab') ?></label>
                                    <div class="controls">
                                        <input id="ab-staff-full-name" name="full_name" value="<?php echo esc_attr($staff->get('full_name')) ?>" type="text"/><span class="red"> *</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="control-group">
                                    <label class="control-label" for="ab-staff-email"><?php _e( 'Email', 'ab') ?></label>
                                    <div class="controls">
                                        <input id="ab-staff-email" name="email" value="<?php echo esc_attr($staff->get( 'email' )) ?>" type="text"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="control-group">
                                    <label class="control-label" for="ab-staff-phone"><?php _e( 'Phone', 'ab') ?></label>
                                    <div class="controls">
                                        <input id="ab-staff-phone" name="phone" value="<?php echo esc_attr($staff->get( 'phone')) ?>" type="text"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="control-group">
                                    <h4 style="float: left"><?php _e( 'Google Calendar integration', 'ab' ) ?></h4>
                                    <img style="float: left;margin-top: 8px;margin-left:15px;"
                                         src="<?php echo plugins_url( 'backend/resources/images/help.png', AB_PATH . '/main.php' ) ?>"
                                         alt=""
                                         class="ab-popover-ext"
                                         data-ext_id="ab-staff-google-popover-ext"
                                        />
                                </td>
                            </tr>
                            <tr>
                                <td class="control-group">
                                    <label class="control-label" for="ab-staff-google-pass">
                                        <?php _e( 'Please configure Google Calendar <a href="?page=ab-system-settings">settings</a> first', 'ab' ) ?>
                                    </label>
                                    <div class="controls">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="control-group">
                                    <label class="control-label"></label>
                                    <div class="controls">
                                        <input id="ab-update-staff" type="submit" value="<?php _e( 'Update', 'ab') ?>" class="btn btn-info ab-update-button">
                                        <button class="ab-reset-form" type="reset"><?php _e( 'Reset', 'ab') ?></button>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <input type="hidden" name="id" value="<?php echo $staff->get( 'id' ) ?>"/>
                        <input type="hidden" name="action" value="ab_update_staff"/>
                    </form>
                </div>
            </div>
            <div class="tab-pane" id="tab2">
                <div id="ab-staff-services-container" class="ab-staff-tab-content" style="display: none"></div>
            </div>
            <div class="tab-pane" id="tab3">
                <div id="ab-staff-schedule-container" class="ab-staff-tab-content" style="display: none"></div>
            </div>
            <div class="tab-pane" id="tab4">
                <div id="ab-staff-holidays-container" class="ab-staff-tab-content" style="display: none"></div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="lite_notice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><?php _e('Notice', 'ab') ?></h4>
      </div>
      <div class="modal-body">
        <?php _e('This function is disabled in the lite version of Bookly. If you find the plugin useful for your business please consider buying a licence for the full version. It costs just $38 and for this money you will get many useful functions, lifetime free update and excellent support! More information can be found here', 'ab'); ?>: <a href="http://bookly.ladela.com" target="_blank">http://bookly.ladela.com</a>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close', 'ab') ?></button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
  jQuery('.ab-staff-google').on('focus', function(){
    jQuery('#lite_notice').modal('show');
  });
</script>