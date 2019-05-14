<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Dialogs\Notifications;
use Bookly\Backend\Components\Support;
use Bookly\Lib\Utils\Common;
use Bookly\Lib\Config;
?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php esc_html_e( 'Email notifications', 'bookly' ) ?>
            </div>
            <?php Support\Buttons::render( $self::pageSlug() ) ?>
        </div>
        <form method="post" action="<?php echo Common::escAdminUrl( $self::pageSlug() ) ?>">
            <div class="panel panel-default bookly-main">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <input class="form-control" type="text" id="bookly-filter" placeholder="<?php esc_attr_e( 'Quick search notifications', 'bookly' ) ?>" />
                            </div>
                        </div>
                        <div class="col-md-8 form-inline bookly-margin-bottom-lg text-right">
                            <div class="form-group">
                                <?php Buttons::renderCustom( 'bookly-js-settings', 'btn-default', esc_html__( 'General settings...', 'bookly' ) ) ?>
                            </div>
                            <?php Notifications\Dialog::renderNewNotificationButton() ?>
                        </div>
                    </div>
                    <table id="bookly-js-notification-list" class="table table-striped" style="width: 100%">
                        <thead>
                        <tr>
                            <th width="1"></th>
                            <th><?php esc_html_e( 'Name', 'bookly' ) ?></th>
                            <th><?php esc_html_e( 'State', 'bookly' ) ?></th>
                            <th></th>
                            <th width="16"><input type="checkbox" class="bookly-js-check-all"/></th>
                        </tr>
                        </thead>
                    </table>

                    <div class="form-inline bookly-margin-bottom-lg text-right">
                        <?php Inputs::renderCsrf() ?>
                        <?php Buttons::renderDelete( 'bookly-js-delete-notifications' ) ?>

                        <div class="pull-left">
                            <button type="button" class="btn btn-default" id="bookly-js-test-email-notifications">
                                <?php esc_html_e( 'Test email notifications...', 'bookly' ) ?>
                            </button>
                        </div>
                    </div>
                    <?php if ( Config::proActive() ) : ?>
                        <div class="alert alert-info">
                            <div class="row">
                                <div class="col-md-12">
                                    <?php if ( is_multisite() ) : ?>
                                        <p><?php printf( __( 'To send scheduled notifications please refer to <a href="%1$s">Bookly Multisite</a> add-on <a href="%2$s">message</a>.', 'bookly' ), Common::prepareUrlReferrers( 'http://codecanyon.net/item/bookly-multisite-addon/13903524?ref=ladela', 'cron_setup' ), network_admin_url( 'admin.php?page=bookly-multisite-network' ) ) ?></p>
                                    <?php else : ?>
                                        <p><?php esc_html_e( 'To send scheduled notifications please execute the following command hourly with your cron:', 'bookly' ) ?></p>
                                        <code class="bookly-text-wrap">wget -q -O - <?php echo site_url( 'wp-cron.php' ) ?></code>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>
                    <?php endif ?>
                </div>
            </div>
        </form>

        <?php $self::renderTemplate( '_test_email_modal' ) ?>
        <?php $self::renderTemplate( '_general_settings_modal' ) ?>
        <?php Notifications\Dialog::render() ?>
    </div>
</div>