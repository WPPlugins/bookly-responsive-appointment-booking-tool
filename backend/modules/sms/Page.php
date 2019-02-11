<?php
namespace Bookly\Backend\Modules\Sms;

use Bookly\Lib;

/**
 * Class Controller
 * @package Bookly\Backend\Modules\Sms
 */
class Page extends Lib\Base\Component
{
    /**
     * Render page.
     */
    public static function render()
    {
        global $wp_locale;

        self::enqueueStyles( array(
            'frontend' => array_merge(
                array( 'css/ladda.min.css', ),
                get_option( 'bookly_cst_phone_default_country' ) == 'disabled'
                    ? array()
                    : array( 'css/intlTelInput.css' )
            ),
            'backend'  => array(
                'bootstrap/css/bootstrap-theme.min.css',
                'css/daterangepicker.css',
            ),
        ) );

        self::enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/datatables.min.js'          => array( 'jquery' ),
                'js/moment.min.js',
                'js/daterangepicker.js'         => array( 'jquery' ),
                'js/alert.js'                   => array( 'jquery' ),
            ),
            'frontend' => array_merge(
                array(
                    'js/spin.min.js'  => array( 'jquery' ),
                    'js/ladda.min.js' => array( 'jquery' ),
                ),
                get_option( 'bookly_cst_phone_default_country' ) == 'disabled'
                    ? array()
                    : array( 'js/intlTelInput.min.js' => array( 'jquery' ) )
            ),
            'module' => array(
                'js/sms.js' => array( 'bookly-notifications-list.js', ),
                'js/notifications-list.js' => array( 'jquery', ),
            ),
        ) );

        $alert         = array( 'success' => array(), 'error' => array() );
        $prices        = array();
        $sms           = new Lib\SMS();

        $email_confirm_required = false;
        $show_registration_form = false;
        if ( self::hasParameter( 'form-login' ) ) {
            if ( $sms->login( self::parameter( 'username' ), self::parameter( 'password' ) ) === 'ERROR_EMAIL_CONFIRM_REQUIRED' ) {
                $email_confirm_required = self::parameter( 'username' );
            }
        } elseif ( self::hasParameter( 'form-logout' ) ) {
            $sms->logout();
        } elseif ( self::hasParameter( 'form-registration' ) ) {
            if ( self::parameter( 'accept_tos', false ) ) {
                $token = $sms->register(
                    self::parameter( 'username' ),
                    self::parameter( 'password' ),
                    self::parameter( 'password_repeat' )
                );
                if ( $token !== false ) {
                    $email_confirm_required = self::parameter( 'username' );
                    self::_sendEmailConfirmNotification( $token, self::parameter( 'username' ) );
                } else {
                    $show_registration_form = true;
                }
            } else {
                $alert['error'][] = __( 'Please accept terms and conditions.', 'bookly' );
            }
        } elseif ( self::hasParameter( 'token' ) ) {
            $sms->confirmEmail( self::parameter( 'token' ) );
        }
        if ( $email_confirm_required !== false || self::hasParameter( 'form-registration' ) ) {
            $is_logged_in = false;
        } else {
            $is_logged_in = $sms->loadProfile();
        }

        if ( ! $is_logged_in ) {
            if ( $response = $sms->getPriceList() ) {
                $prices = $response->list;
            }
            if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
                // Hide authentication errors on auto login.
                $sms->clearErrors();
            }
        } else {
            switch ( self::parameter( 'paypal_result' ) ) {
                case 'success':
                    $alert['success'][] = __( 'Your payment has been accepted for processing.', 'bookly' );
                    break;
                case 'cancel':
                    $alert['error'][] = __( 'Your payment has been interrupted.', 'bookly' );
                    break;
            }
            if ( self::hasParameter( 'tab' ) ) {
                switch ( self::parameter( 'auto-recharge' ) ) {
                    case 'approved':
                        $alert['success'][] = __( 'Auto-Recharge enabled.', 'bookly' );
                        break;
                    case 'declined':
                        $alert['error'][] = __( 'You declined the Auto-Recharge of your balance.', 'bookly' );
                        break;
                }
            }
        }
        $current_tab    = self::hasParameter( 'tab' ) ? self::parameter( 'tab' ) : 'notifications';
        $alert['error'] = array_merge( $alert['error'], $sms->getErrors() );
        // Services in custom notifications where the recipient is client only.
        $only_client = Lib\Entities\Service::query()->whereIn( 'type', array( Lib\Entities\Service::TYPE_COMPOUND, Lib\Entities\Service::TYPE_COLLABORATIVE ) )->fetchCol( 'id' );
        wp_localize_script( 'bookly-daterangepicker.js', 'BooklyL10n',
            array(
                'csrfToken'          => Lib\Utils\Common::getCsrfToken(),
                'alert'              => $alert,
                'apply'              => __( 'Apply', 'bookly' ),
                'areYouSure'         => __( 'Are you sure?', 'bookly' ),
                'cancel'             => __( 'Cancel', 'bookly' ),
                'country'            => get_option( 'bookly_cst_phone_default_country' ),
                'current_tab'        => $current_tab,
                'custom_range'       => __( 'Custom range', 'bookly' ),
                'from'               => __( 'From', 'bookly' ),
                'last_30'            => __( 'Last 30 days', 'bookly' ),
                'last_7'             => __( 'Last 7 days', 'bookly' ),
                'last_month'         => __( 'Last month', 'bookly' ),
                'mjsDateFormat'      => Lib\Utils\DateTime::convertFormat( 'date', Lib\Utils\DateTime::FORMAT_MOMENT_JS ),
                'startOfWeek'        => (int) get_option( 'start_of_week' ),
                'this_month'         => __( 'This month', 'bookly' ),
                'to'                 => __( 'To', 'bookly' ),
                'today'              => __( 'Today', 'bookly' ),
                'yesterday'          => __( 'Yesterday', 'bookly' ),
                'input_old_password' => __( 'Please enter old password.', 'bookly' ),
                'passwords_no_same'  => __( 'Passwords must be the same.', 'bookly' ),
                'intlTelInput'       => array(
                    'country' => get_option( 'bookly_cst_phone_default_country' ),
                    'utils'   => is_rtl() ? '' : plugins_url( 'intlTelInput.utils.js', Lib\Plugin::getDirectory() . '/frontend/resources/js/intlTelInput.utils.js' ),
                    'enabled' => get_option( 'bookly_cst_phone_default_country' ) != 'disabled',
                ),
                'calendar'           => array(
                    'longDays'    => array_values( $wp_locale->weekday ),
                    'longMonths'  => array_values( $wp_locale->month ),
                    'shortDays'   => array_values( $wp_locale->weekday_abbrev ),
                    'shortMonths' => array_values( $wp_locale->month_abbrev ),
                ),
                'sender_id'          => array(
                    'sent'        => __( 'Sender ID request is sent.', 'bookly' ),
                    'set_default' => __( 'Sender ID is reset to default.', 'bookly' ),
                ),
                'zeroRecords'        => __( 'No records for selected period.', 'bookly' ),
                'zeroRecords2'       => __( 'No records.', 'bookly' ),
                'processing'         => __( 'Processing...', 'bookly' ),
                'onlyClient'         => $only_client,
                'invoice'         => array(
                    'button' => __( 'Invoice', 'bookly' ),
                    'alert'  => __( 'To generate an invoice you should fill in company information in Bookly > SMS Notifications > Send invoice.', 'bookly' ),
                    'link'   => $sms->getInvoiceLink()
                ),
                'state'              => array( __( 'Disabled', 'bookly' ), __( 'Enabled', 'bookly' ) ),
                'action'             => array( __( 'enable', 'bookly' ), __( 'disable', 'bookly' ) ),
                'edit'               => __( 'Edit...', 'bookly' ),
                'settingsSaved'      => __( 'Settings saved.', 'bookly' ),
                'gateway'            => 'sms'
            )
        );
        foreach ( range( 1, 23 ) as $hours ) {
            $bookly_ntf_processing_interval_values[] = array( $hours, Lib\Utils\DateTime::secondsToInterval( $hours * HOUR_IN_SECONDS ) );
        }

        // Number of undelivered sms.
        $undelivered_count = Lib\SMS::getUndeliveredSmsCount();

        self::renderTemplate( 'index', compact( 'sms', 'is_logged_in', 'prices', 'bookly_ntf_processing_interval_values', 'undelivered_count', 'email_confirm_required', 'show_registration_form' ) );
    }

    /**
     * Send notification to confirm email.
     *
     * @param string $token
     * @param string $email
     */
    private static function _sendEmailConfirmNotification( $token, $email )
    {
        $confirm_url = admin_url( 'admin.php?' . build_query( array( 'page' => self::pageSlug(), 'token' => $token ) ) );
        $message     = sprintf( __( "Hello,\n\nThank you for registering at Bookly SMS service. Please click the link below to verify your email address.\n\n<a href='%s'>%s</a>\n\nBookly", 'bookly' ), $confirm_url, $confirm_url );

        wp_mail(
            $email,
            __( 'Bookly SMS service – email confirmation', 'bookly' ),
            get_option( 'bookly_email_send_as' ) == 'html' ? wpautop( $message ) : $message,
            Lib\Utils\Common::getEmailHeaders()
        );
    }

    /**
     * Show 'SMS Notifications' submenu with counter inside Bookly main menu.
     */
    public static function addBooklyMenuItem()
    {
        $sms   = __( 'SMS Notifications', 'bookly' );
        $count = Lib\SMS::getUndeliveredSmsCount();

        add_submenu_page(
            'bookly-menu',
            $sms,
            $count ? sprintf( '%s <span class="update-plugins count-%d"><span class="update-count">%d</span></span>', $sms, $count, $count ) : $sms,
            'manage_options',
            self::pageSlug(),
            function () { Page::render(); }
        );
    }
}