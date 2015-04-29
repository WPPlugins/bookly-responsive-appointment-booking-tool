<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$settings = array(
    'textarea_name' => $name,
    'media_buttons' => false,
    'tinymce' => array(
        'theme_advanced_buttons1' => 'formatselect,|,bold,italic,underline,|,' .
            'bullist,blockquote,|,justifyleft,justifycenter' .
            ',justifyright,justifyfull,|,link,unlink,|' .
            ',spellchecker,wp_fullscreen,wp_adv'
    )
);
?>
<div class="ab-title"><?php _e('Notifications','ab') ?></div>
<div style="min-width: 800px;margin-top: -20px">
    <form method="post">
        <div class="ab-notifications">
            <?php
            $sender_name  = get_option( 'ab_settings_sender_name' ) == '' ?
                get_option( 'blogname' )    : get_option( 'ab_settings_sender_name' );
            $sender_email = get_option( 'ab_settings_sender_email' ) == ''  ?
                get_option( 'admin_email' ) : get_option( 'ab_settings_sender_email' );
            ?>
            <!-- sender name -->
            <label for="sender_name" style="display: inline;"><?php _e( 'Sender name', 'ab' ); ?></label>
            <input id="sender_name" name="sender_name" class="ab-sender" type="text" value="<?php echo $sender_name ; ?>"/><br>
            <!-- sender email -->
            <label for="sender_email" style="display: inline;"><?php _e( 'Sender email', 'ab' ); ?></label>
            <input id="sender_email" name="sender_email" class="ab-sender" type="text" value="<?php echo $sender_email; ?>"/>
        </div>
        <?php foreach ( $notifications as $k => $slug ): ?>
            <div class="ab-notifications">
                <div class="ab-toggle-arrow"></div>
                <legend id='legend_<?php echo $k; ?>_active'>
                    <input type=checkbox id="<?php echo $k; ?>_active" type="checkbox" value="1" name=<?php echo $k; ?>[active]"/>
                    <label for='<?php echo $k; ?>_active'><?php echo $slug['name']; ?></label>
                </legend>
                <div class="ab-form-field">
                    <div class="ab-form-row">
                        <label class='ab-form-label'><?php __( 'Subject','ab'); ?></label><input type='text' size='70' value='<?php echo $slug['subject']; ?>'/>
                    </div>
                    <div id="message_editor" class="ab-form-row">
                        <label class="ab-form-label" style="margin-top: 35px;"><?php _e( 'Message', 'ab' ) ?></label>
                        <?php wp_editor( $slug['message'], $k . '_message', array_merge(array('name' => $k . '[message]'), $settings) ) ?>
                    </div>
                    <?php if ('provider_info' == $k): ?>
                        <div class='ab-form-row'>
                            <label class='ab-form-label'></label>
                            <div class='left'>
                                <legend>
                                    <input type=checkbox />
                                    <label> <?php _e('Send copy to administrators', 'ab');?></label>
                                </legend>
                            </div>
                        </div>
                    <?php endif ?>
                    <div class="ab-form-row">
                        <label class="ab-form-label"><?php _e( 'Tags ','ab' ) ?></label>
                        <div class="ab-tags-form left">
                            <table>
                                <tbody>
                                <?php include "_tags_{$k}.php"; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="ab-notifications" style="border: 0">
            <input type="submit" value="<?php _e( 'Save Changes', 'ab' )?>" class="btn btn-info ab-update-button" />
            <button class="ab-reset-form" type="reset"><?php _e( 'Reset', 'ab' )?></button>
        </div>
    </form>
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

<script type="text/javascript">
  jQuery(function($) {
    // menu fix for WP 3.8.1
    $('#toplevel_page_ab-system > ul').css('margin-left', '0px');
    // Show-hide Notifications
    $('input:checkbox[id!=_active]').each(function() {
      $(this).change(function() {
        if ( $(this).attr('checked') ) {
          $(this).parent().next('div.ab-form-field').show(200);
          $(this).parents('.ab-notifications').find('.ab-toggle-arrow').css('background','url(<?php echo plugins_url( 'resources/images/notifications-arrow-up.png', dirname(__FILE__).'/../../../AB_Backend.php' ) ?>) 100% 0 no-repeat');
        } else {
          $(this).parent().next('div.ab-form-field').hide(200);
          $(this).parents('.ab-notifications').find('.ab-toggle-arrow').css('background','url(<?php echo plugins_url( 'resources/images/notifications-arrow-down.png', dirname(__FILE__).'/../../../AB_Backend.php' ) ?>) 100% 0 no-repeat');
        }
      }).change();
    });
    $('.ab-toggle-arrow').click(function() {
      $(this).nextAll('.ab-form-field').toggle(200, function() {
        if ( $('.ab-form-field').css('display') == 'block' ) {
          $(this).prevAll('.ab-toggle-arrow').css('background','url(<?php echo plugins_url( 'resources/images/notifications-arrow-up.png', dirname(__FILE__).'/../../../AB_Backend.php' ) ?>) 100% 0 no-repeat');
        } else {
          $(this).prevAll('.ab-toggle-arrow').css('background','url(<?php echo plugins_url( 'resources/images/notifications-arrow-down.png', dirname(__FILE__).'/../../../AB_Backend.php' ) ?>) 100% 0 no-repeat');
        }
      });
    });
    // filter sender name and email
    var escapeXSS = function (infected) {
      var regexp = /([<|(]("[^"]*"|'[^']*'|[^'">])*[>|)])/gi;
      return infected.replace(regexp, '');
    };
    $('input.ab-sender').on('change', function() {
      var $val = $(this).val();
      $(this).val(escapeXSS($val));
    });

    $("input[id$='_active']").change(function(){
      if ($(this).is(':checked')){
        $('#lite_notice').modal('show');
      }
    });
  });
</script>