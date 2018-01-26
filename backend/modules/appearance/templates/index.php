<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template to show appearance page
 * @var array $steps list of steps in booking form, could be string (the name of step) or false if step disabled
 * @var string $custom_css custom css text
 */
?>

<?php if ( trim( $custom_css ) ) : ?>
    <style type="text/css">
        <?php echo $custom_css ?>
    </style>
<?php endif ?>

<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php _e( 'Appearance', 'bookly' ) ?>
            </div>
            <?php \BooklyLite\Backend\Modules\Support\Components::getInstance()->renderButtons( $this::page_slug ) ?>
        </div>
        <div class="panel panel-default bookly-main">
            <div class="panel-body">
                <div id="bookly-appearance">
                    <div class="row">
                        <div class="col-sm-3 col-lg-2 bookly-color-picker-wrapper">
                            <input type="text" name="color" class="bookly-js-color-picker"
                                   value="<?php form_option( 'bookly_app_color' ) ?>"
                                   data-selected="<?php form_option( 'bookly_app_color' ) ?>" />
                        </div>
                        <div class="col-sm-9 col-lg-10">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id=bookly-show-progress-tracker <?php checked( get_option( 'bookly_app_show_progress_tracker' ) ) ?>>
                                    <?php _e( 'Show form progress tracker', 'bookly' ) ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <ul class="bookly-nav bookly-nav-tabs bookly-margin-top-lg" role="tablist">
                        <?php $i = 1 ?>
                        <?php foreach ( $steps as $step => $step_name ) : ?>
                            <?php if ( ( $step != 2 || \BooklyLite\Lib\Config::serviceExtrasEnabled() )
                                    && ( $step != 4 || \BooklyLite\Lib\Config::recurringAppointmentsEnabled() ) ) : ?>
                                <li class="bookly-nav-item <?php if ( $step == 1 ) : ?>active<?php endif ?>" data-target="#bookly-step-<?php echo $step ?>" data-toggle="tab">
                                    <?php echo $i++ ?>. <?php echo esc_html( $step_name ) ?>
                                </li>
                            <?php endif ?>
                        <?php endforeach ?>
                    </ul>

                    <?php if ( ! get_user_meta( get_current_user_id(), \BooklyLite\Lib\Plugin::getPrefix() . 'dismiss_appearance_notice', true ) ): ?>
                        <div class="alert alert-info alert-dismissible fade in bookly-margin-top-lg bookly-margin-bottom-remove" id="bookly-js-hint-alert" role="alert">
                            <button type="button" class="close" data-dismiss="alert"></button>
                            <?php _e( 'Click on the underlined text to edit.', 'bookly' ) ?>
                        </div>
                    <?php endif ?>

                    <div class="row" id="bookly-step-settings">
                        <div class="bookly-js-service-settings bookly-margin-top-lg">
                            <?php \BooklyLite\Lib\Proxy\Shared::renderAppearanceStepServiceSettings() ?>
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id=bookly-required-employee <?php checked( get_option( 'bookly_app_required_employee' ) ) ?>>
                                        <?php _e( 'Make selecting employee required', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id=bookly-staff-name-with-price <?php checked( get_option( 'bookly_app_staff_name_with_price' ) ) ?>>
                                        <?php _e( 'Show service price next to employee name', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id=bookly-service-name-with-duration <?php checked( get_option( 'bookly_app_service_name_with_duration' ) ) ?>>
                                        <?php _e( 'Show service duration next to service name', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="bookly-js-time-settings bookly-margin-top-lg" style="display:none">
                            <div class="col-md-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="bookly-show-calendar" <?php checked( get_option( 'bookly_app_show_calendar' ) ) ?>>
                                        <?php _e( 'Show calendar', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="bookly-show-blocked-timeslots" <?php checked( get_option( 'bookly_app_show_blocked_timeslots' ) ) ?>>
                                        <?php _e( 'Show blocked timeslots', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="bookly-show-day-one-column" <?php checked( get_option( 'bookly_app_show_day_one_column' ) ) ?>>
                                        <?php _e( 'Show each day in one column', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="bookly-js-details-settings bookly-margin-top-lg" style="display:none">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="bookly-cst-required-phone" <?php checked( get_option( 'bookly_cst_required_phone' ) ) ?>>
                                        <?php _e( 'Make phone field required', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="bookly-show-login-button" <?php checked( get_option( 'bookly_app_show_login_button' ) ) ?>>
                                        <?php _e( 'Show Login button', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="bookly-cst-first-last-name" <?php checked( get_option( 'bookly_cst_first_last_name' ) ) ?> data-toggle="popover" data-trigger="focus" data-placement="auto bottom" data-content="<?php _e( 'Do not forget to update your email and SMS codes for customer names', 'bookly' ) ?>">
                                        <?php _e( 'Use first and last name instead of full name', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="bookly-show-notes" <?php checked( get_option( 'bookly_app_show_notes' ) ) ?>>
                                        <?php _e( 'Show notes field', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="bookly-js-payment-settings bookly-margin-top-lg" style="display:none">
                            <div class="col-md-12">
                                <div class="alert alert-info bookly-margin-top-lg bookly-margin-bottom-remove bookly-flexbox">
                                    <div class="bookly-flex-row">
                                        <div class="bookly-flex-cell" style="width:39px"><i class="alert-icon"></i></div>
                                        <div class="bookly-flex-cell">
                                            <div>
                                                <?php _e( 'The booking form on this step may have different set or states of its elements. It depends on various conditions such as installed/activated add-ons, settings configuration or choices made on previous steps. Select option and click on the underlined text to edit.', 'bookly' ) ?>
                                            </div>
                                            <div class="bookly-margin-top-lg">
                                                <select id="bookly-payment-step-view" class="form-control">
                                                    <option value="single-app"><?php _e( 'Form view in case of single booking', 'bookly' ) ?></option>
                                                    <option value="several-apps"><?php _e( 'Form view in case of multiple booking', 'bookly' ) ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bookly-js-done-settings bookly-margin-top-lg" style="display:none">
                            <div class="col-md-12">
                                <div class="alert alert-info bookly-margin-top-lg bookly-margin-bottom-remove bookly-flexbox">
                                    <div class="bookly-flex-row">
                                        <div class="bookly-flex-cell" style="width:39px"><i class="alert-icon"></i></div>
                                        <div class="bookly-flex-cell">
                                            <div>
                                                <?php _e( 'The booking form on this step may have different set or states of its elements. It depends on various conditions such as installed/activated add-ons, settings configuration or choices made on previous steps. Select option and click on the underlined text to edit.', 'bookly' ) ?>
                                            </div>
                                            <div class="bookly-margin-top-lg">
                                                <select id="bookly-done-step-view" class="form-control">
                                                    <option value="booking-success"><?php _e( 'Form view in case of successful booking', 'bookly' ) ?></option>
                                                    <option value="booking-limit-error"><?php _e( 'Form view in case the number of bookings exceeds the limit', 'bookly' ) ?></option>
                                                    <option value="booking-processing"><?php _e( 'Form view in case of payment has been accepted for processing', 'bookly' ) ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default bookly-margin-top-lg">
                        <div class="panel-body">
                            <div class="tab-content">
                                <?php foreach ( $steps as $step => $step_name ) : ?>
                                    <div id="bookly-step-<?php echo $step ?>" class="tab-pane <?php if ( $step == 1 ) : ?>active<?php endif ?>" data-target="<?php echo $step ?>">
                                        <?php // Render unique data per step
                                        switch ( $step ) :
                                            case 1: include '_1_service.php';   break;
                                            case 2: \BooklyLite\Lib\Proxy\ServiceExtras::renderAppearance( $this->render( '_progress_tracker', compact( 'step', 'editable' ), false ) );
                                                break;
                                            case 3: include '_3_time.php';      break;
                                            case 4: \BooklyLite\Lib\Proxy\RecurringAppointments::renderAppearance( $this->render( '_progress_tracker', compact( 'step', 'editable' ), false ) );
                                                break;
                                            case 5: include '_5_cart.php';      break;
                                            case 6: include '_6_details.php';   break;
                                            case 7: include '_7_payment.php';   break;
                                            case 8: include '_8_complete.php';  break;
                                        endswitch ?>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>
                    </div>
                    <div>
                        <?php $this->render( '_custom_css', array( 'custom_css' => $custom_css) ); ?>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <?php \BooklyLite\Lib\Utils\Common::submitButton( 'ajax-send-appearance' ) ?>
                <?php \BooklyLite\Lib\Utils\Common::resetButton() ?>
            </div>
        </div>
    </div>
</div>