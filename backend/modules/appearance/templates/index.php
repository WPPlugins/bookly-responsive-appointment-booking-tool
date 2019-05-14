<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib;
use Bookly\Backend\Components\Support;
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Modules\Appearance\Proxy;
?>
    <style type="text/css">
        .bookly-columnizer .bookly-hour.bookly-slot-in-waiting-list span.bookly-time-additional {
            color: #f4662f!important;
        }
        #bookly-step-settings > div > .col-md-3:nth-child(4n+1) {
            clear: both;
        }
        #bookly-tbs .bookly-cart .bookly-extras-cart-title {
            padding-left: 25px;
        }
        #bookly-tbs .bookly-animate {
            -webkit-transition: background-color 500ms ease-in;
            -ms-transition: background-color 500ms ease-in;
            transition: background-color 500ms ease-in;
        }
    </style>
<?php if ( trim( $custom_css ) ) : ?>
    <style type="text/css">
        <?php echo $custom_css ?>
    </style>
<?php endif ?>

<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php esc_html_e( 'Appearance', 'bookly' ) ?>
            </div>
            <?php Support\Buttons::render( $self::pageSlug() ) ?>
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
                        <div class="col-sm-3 col-lg-2">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id=bookly-show-progress-tracker <?php checked( get_option( 'bookly_app_show_progress_tracker' ) ) ?>>
                                    <?php esc_html_e( 'Show form progress tracker', 'bookly' ) ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-3 col-lg-2">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="bookly-align-buttons-left" <?php checked( get_option( 'bookly_app_align_buttons_left' ) ) ?>>
                                    <?php esc_html_e( 'Align buttons to the left', 'bookly' ) ?>
                                </label>
                            </div>
                        </div>
                        <?php Proxy\ServiceExtras::renderShowStep() ?>
                        <?php Proxy\RecurringAppointments::renderShowStep() ?>
                        <?php Proxy\Cart::renderShowStep() ?>
                    </div>

                    <ul class="bookly-nav bookly-nav-tabs bookly-margin-top-lg bookly-js-appearance-steps" role="tablist">
                        <?php $i = 1 ?>
                        <?php foreach ( $steps as $data ) : ?>
                            <li class="bookly-nav-item <?php if ( $data['step'] == 1 ) : ?>active<?php endif ?>" data-target="#bookly-step-<?php echo $data['step'] ?>" data-toggle="tab" <?php if ( ! $data['show'] ) : ?>style="display: none;"<?php endif ?>>
                                <span class="bookly-js-step-number"><?php echo $data['show'] ? $i++ : $i ?></span>. <?php echo esc_html( $data['title'] ) ?>
                            </li>
                        <?php endforeach ?>
                    </ul>

                    <?php if ( ! get_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_appearance_notice', true ) ): ?>
                        <div class="alert alert-info alert-dismissible fade in bookly-margin-top-lg bookly-margin-bottom-remove" id="bookly-js-hint-alert" role="alert">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <p>
                            <?php esc_html_e( 'Click on the underlined text to edit.', 'bookly' ) ?>
                            </p>
                            <p><?php esc_html_e( 'How to publish this form on your web site?', 'bookly' ) ?>
                            <br>
                            <?php esc_html_e( 'Open the page where you want to add the booking form in page edit mode and click on the "Add Bookly booking form" button. Choose which fields you\'d like to keep or remove from the booking form. Click Insert, and the booking form will be added to the page.', 'bookly' ) ?>
                            <a href="<?php echo Bookly\Lib\Utils\Common::prepareUrlReferrers( 'https://support.booking-wp-plugin.com/hc/en-us/articles/212800185-Publish-Booking-Form', 'appearance' ) ?>" target="_blank"><?php esc_html_e( 'Read more', 'bookly' ) ?></a>
                            </p>
                        </div>
                    <?php endif ?>

                    <div class="row" id="bookly-step-settings">
                        <div class="bookly-js-service-settings bookly-margin-top-lg">
                            <?php Proxy\Locations::renderShowLocation() ?>
                            <?php Proxy\CustomDuration::renderShowCustomDuration() ?>
                            <?php Proxy\GroupBooking::renderShowNOP() ?>
                            <?php Proxy\MultiplyAppointments::renderShowQuantity() ?>
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id=bookly-required-employee <?php checked( get_option( 'bookly_app_required_employee' ) ) ?>>
                                        <?php esc_html_e( 'Make selecting employee required', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id=bookly-staff-name-with-price <?php checked( get_option( 'bookly_app_staff_name_with_price' ) ) ?>>
                                        <?php esc_html_e( 'Show service price next to employee name', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id=bookly-service-name-with-duration <?php checked( get_option( 'bookly_app_service_name_with_duration' ) ) ?>>
                                        <?php esc_html_e( 'Show service duration next to service name', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                            <?php Proxy\Shared::renderServiceStepSettings() ?>
                        </div>
                        <div class="bookly-js-time-settings bookly-margin-top-lg" style="display:none">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="bookly-show-calendar" <?php checked( get_option( 'bookly_app_show_calendar' ) ) ?>>
                                        <?php esc_html_e( 'Show calendar', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="bookly-show-blocked-timeslots" <?php checked( get_option( 'bookly_app_show_blocked_timeslots' ) ) ?>>
                                        <?php esc_html_e( 'Show blocked timeslots', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="bookly-show-day-one-column" <?php checked( get_option( 'bookly_app_show_day_one_column' ) ) ?>>
                                        <?php esc_html_e( 'Show each day in one column', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                            <?php Proxy\Pro::renderTimeZoneSwitcherCheckbox() ?>
                            <?php Proxy\Shared::renderTimeStepSettings() ?>
                        </div>

                        <?php Proxy\Cart::renderCartStepSettings() ?>

                        <div class="bookly-js-details-settings bookly-margin-top-lg container-fluid" style="display:none">

                            <div class="col-md-3">
                                <select id="bookly-cst-required-details" class="form-control" data-default="<?php echo ! array_diff( array( 'phone', 'email' ), get_option( 'bookly_cst_required_details', array() ) ) ? 'both' : current( get_option( 'bookly_cst_required_details', array() ) ) ?>">
                                    <option value="phone"<?php selected( in_array( 'phone', get_option( 'bookly_cst_required_details', array() ) ) && ! in_array( 'email', get_option( 'bookly_cst_required_details', array() ) ) ) ?><?php disabled( get_option( 'bookly_cst_create_account' ) ) ?>><?php esc_html_e( 'Phone field required', 'bookly' ) ?></option>
                                    <option value="email"<?php selected( in_array( 'email', get_option( 'bookly_cst_required_details', array() ) ) && ! in_array( 'phone', get_option( 'bookly_cst_required_details', array() ) ) ) ?>><?php esc_html_e( 'Email field required', 'bookly' ) ?></option>
                                    <option value="both"<?php selected( ! array_diff( array( 'phone', 'email' ), get_option( 'bookly_cst_required_details', array() ) ) ) ?>><?php esc_html_e( 'Both email and phone fields required', 'bookly' ) ?></option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="bookly-show-login-button" <?php checked( get_option( 'bookly_app_show_login_button' ) ) ?>>
                                        <?php esc_html_e( 'Show Login button', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="bookly-cst-first-last-name" <?php checked( get_option( 'bookly_cst_first_last_name' ) ) ?> data-toggle="popover" data-trigger="focus" data-placement="auto bottom" data-content="<?php esc_attr_e( 'Do not forget to update your email and SMS codes for customer names', 'bookly' ) ?>">
                                        <?php esc_html_e( 'Use first and last name instead of full name', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="bookly-cst-show-email-confirm" <?php checked( get_option( 'bookly_app_show_email_confirm' ) ) ?>>
                                        <?php esc_html_e( 'Email confirmation field', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="bookly-show-notes" <?php checked( get_option( 'bookly_app_show_notes' ) ) ?>>
                                        <?php esc_html_e( 'Show notes field', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                            <?php Proxy\Pro::renderShowBirthday() ?>
                            <?php Proxy\Pro::renderShowAddress() ?>
                            <?php Proxy\GoogleMapsAddress::renderShowGoogleMaps() ?>
                            <?php Proxy\CustomFields::renderShowCustomFields() ?>
                            <?php Proxy\Files::renderShowFiles() ?>
                            <?php Proxy\CustomerInformation::renderShowCustomerInformation() ?>
                            <?php Proxy\Pro::renderShowFacebookButton() ?>
                        </div>
                        <div class="bookly-js-payment-settings bookly-margin-top-lg" style="display:none">
                            <?php Proxy\Coupons::renderShowCoupons() ?>
                            <?php Proxy\Pro::renderMultipleBookingSelector() ?>
                        </div>

                        <div class="bookly-js-done-settings bookly-margin-top-lg" style="display:none">
                            <div class="col-md-12">
                                <div class="alert alert-info bookly-margin-top-lg bookly-margin-bottom-remove bookly-flexbox">
                                    <div class="bookly-flex-row">
                                        <div class="bookly-flex-cell" style="width:39px"><i class="alert-icon"></i></div>
                                        <div class="bookly-flex-cell">
                                            <div>
                                                <?php esc_html_e( 'The booking form on this step may have different set or states of its elements. It depends on various conditions such as installed/activated add-ons, settings configuration or choices made on previous steps. Select option and click on the underlined text to edit.', 'bookly' ) ?>
                                            </div>
                                            <div class="bookly-margin-top-lg">
                                                <select id="bookly-done-step-view" class="form-control">
                                                    <option value="booking-success"><?php esc_html_e( 'Form view in case of successful booking', 'bookly' ) ?></option>
                                                    <option value="booking-limit-error"><?php esc_html_e( 'Form view in case the number of bookings exceeds the limit', 'bookly' ) ?></option>
                                                    <option value="booking-processing"><?php esc_html_e( 'Form view in case of payment has been accepted for processing', 'bookly' ) ?></option>
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
                                            case 2: Proxy\ServiceExtras::renderStep( $self::renderTemplate( '_progress_tracker', compact( 'step' ), false ) );
                                                break;
                                            case 3: include '_3_time.php';      break;
                                            case 4: Proxy\RecurringAppointments::renderStep( $self::renderTemplate( '_progress_tracker', compact( 'step' ), false ) );
                                                break;
                                            case 5: Proxy\Cart::renderStep( $self::renderTemplate( '_progress_tracker', compact( 'step' ), false ) );
                                                break;
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
                        <?php $self::renderTemplate( '_custom_css', array( 'custom_css' => $custom_css ) ) ?>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <?php Buttons::renderSubmit( 'ajax-send-appearance' ) ?>
                <?php Buttons::renderReset() ?>
            </div>
        </div>
    </div>
</div>