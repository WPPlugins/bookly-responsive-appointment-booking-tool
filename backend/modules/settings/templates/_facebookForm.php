<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs as ControlInputs;
use Bookly\Backend\Components\Settings\Inputs;
?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'facebook' ) ) ?>">
    <div class="form-group">
        <h4><?php esc_html_e( 'Instructions', 'bookly' ) ?></h4>
        <p><?php esc_html_e( 'To set up Facebook integration, do the following:', 'bookly' ) ?></p>
        <ol>
            <li><?php _e( 'Follow the steps at <a href="https://developers.facebook.com/docs/apps/register" target="_blank">https://developers.facebook.com/docs/apps/register</a> to create a Developer Account, register and configure your <b>Facebook App</b>. Below the App Details Panel click <b>Add Platform</b> button, select Website and enter your website URL.', 'bookly' ) ?></li>
            <li><?php _e( 'Go to your <a href="https://developers.facebook.com/apps/" target="_blank">App Dashboard</a>. In the left side navigation panel of the App Dashboard, click <b>Settings > Basic</b> to view the App Details Panel with your <b>App ID</b>. Use it in the form below.', 'bookly' ) ?></li>
        </ol>
    </div>
    <?php Inputs::renderText( 'bookly_fb_app_id', __( 'App ID', 'bookly' ) ) ?>
    <div class="panel-footer">
        <?php ControlInputs::renderCsrf() ?>
        <?php Buttons::renderSubmit() ?>
        <?php Buttons::renderReset() ?>
    </div>
</form>