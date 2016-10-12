<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $auth_form_id = uniqid();
?>
<!-- Bookly modal login -->
<div class="bookly-modal bookly-fade" id="bookly-auth-modal-<?php echo $auth_form_id ?>">
    <div class="bookly-modal-dialog">
        <div class="bookly-modal-content">
            <form>
                <div class="bookly-modal-header">
                    <div><?php _e( 'Login', 'bookly' ) ?></div>
                    <button type="button" class="bookly-close">×</button>
                </div>
                <div class="bookly-modal-body bookly-form">
                    <div class="ab-formGroup">
                        <label><?php _e( 'Username' ) ?></label>
                        <div>
                            <input type="text" name="log" required />
                        </div>
                    </div>
                    <div class="ab-formGroup">
                        <label><?php _e( 'Password' ) ?></label>
                        <div>
                            <input type="password" name="pwd" required />
                        </div>
                    </div>
                    <div class="ab-label-error"></div>
                    <div class="ab-formGroup">
                        <label>
                            <input name="action" value="ab_wp_user_login" type="hidden" />
                            <input name="form_id" value="0" type="hidden" />
                            <input type="checkbox" name="rememberme" />
                        </label>
                        <span style="font-size: 14px;"><?php _e( 'Remember Me' ) ?></span>
                    </div>

                </div>
                <div class="bookly-modal-footer">
                    <button class="bookly-btn-submit ladda-button" type="submit" data-spinner-size="40" data-style="zoom-in">
                        <span class="ladda-label"><?php _e( 'Log In' ) ?></span>
                    </button>
                    <a href="javascript:void(0)" class="bookly-btn-cancel"><?php _e( 'Cancel' ) ?></a>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- end modal -->
<a id="bookly-auth-modal-show-<?php echo $auth_form_id ?>" href="javascript:void(0)"><?php _e( 'Log In' ) ?></a>

<script type="text/javascript">
    jQuery(document).ready(function($){
        var $modal = $("#bookly-auth-modal-<?php echo $auth_form_id ?>"),
            $guest_info = $modal.parents('.ab-guest-desc'),
            $form = $modal.find('form');
        $form.find('input[name=form_id]').val($modal.parents('.bookly-form').data('form_id'));
        $modal.appendTo('body');
        $('#bookly-auth-modal-show-<?php echo $auth_form_id ?>').on('click', function(e){
            e.preventDefault();
            $modal.toggleClass('bookly-in');
        });
        $('.bookly-close,.bookly-btn-cancel', $modal).on('click', function(){
            $modal.removeClass('bookly-in');
            $form.trigger('reset');
            $form.find('input').removeClass('ab-field-error');
            $form.find('.ab-label-error').html('');
        });

        $form.find('button[type=submit]').on('click', function(e){
            e.preventDefault();
            var ladda = Ladda.create(this);
            ladda.start();
            $.ajax({
                type: 'POST',
                url:  <?php echo json_encode( admin_url('admin-ajax.php') ) ?>,
                data: $form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        $guest_info.fadeOut('slow');
                        $('.ab-full-name').val(response.data.name).removeClass('ab-field-error');
                        if(response.data.phone){
                            $('.ab-user-phone').intlTelInput("setNumber", response.data.phone).removeClass('ab-field-error');
                        }
                        $('.ab-user-email').val(response.data.email).removeClass('ab-field-error');
                        $('.ab-full-name-error, .ab-user-phone-error, .ab-user-email-error').html('');
                        $modal.remove();
                    } else {
                        $form.find('input').addClass('ab-field-error');
                        $form.find('.ab-label-error').html(response.data.message);
                    }
                    ladda.stop();
                }
            })
        });
    });
</script>