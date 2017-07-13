<?php
namespace BooklyLite\Backend\Modules\Sms;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\Sms
 */
class Controller extends Lib\Base\Controller
{
    const page_slug = 'bookly-sms';

    public function index()
    {
        global $wp_locale;

        $this->enqueueStyles( array(
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

        $this->enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/datatables.min.js'  => array( 'jquery' ),
                'js/moment.min.js',
                'js/daterangepicker.js' => array( 'jquery' ),
                'js/alert.js' => array( 'jquery' ),
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
            'module'   => array( 'js/sms.js' => array( 'jquery' ) ),
        ) );

        $alert  = array( 'success' => array(), 'error' => array() );
        $prices = array();
        $form   = new \BooklyLite\Backend\Modules\Notifications\Forms\Notifications( 'sms' );
        $sms    = new Lib\SMS();
        $cron_reminder = (array) get_option( 'bookly_cron_reminder_times' );

        if ( $this->hasParameter( 'form-login' ) ) {
            $sms->login( $this->getParameter( 'username' ), $this->getParameter( 'password' ) );
        } elseif ( $this->hasParameter( 'form-logout' ) ) {
            $sms->logout();

        } elseif ( $this->hasParameter( 'form-registration' ) ) {
            if ( $this->getParameter( 'accept_tos', false ) ) {
                $sms->register(
                    $this->getParameter( 'username' ),
                    $this->getParameter( 'password' ),
                    $this->getParameter( 'password_repeat' )
                );
            } else {
                $alert['error'][] = __( 'Please accept terms and conditions.', 'bookly' );
            }
        }

        $is_logged_in = $sms->loadProfile();

        if ( ! $is_logged_in ) {
            if ( $response = $sms->getPriceList() ) {
                $prices = $response->list;
            }
            if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
                // Hide authentication errors on auto login.
                $sms->clearErrors();
            }
        } else {
            switch ( $this->getParameter( 'paypal_result' ) ) {
                case 'success':
                    $alert['success'][] = __( 'Your payment has been accepted for processing.', 'bookly' );
                    break;
                case 'cancel':
                    $alert['error'][] = __( 'Your payment has been interrupted.', 'bookly' );
                    break;
            }
            if ( $this->hasParameter( 'form-notifications' ) ) {
                update_option( 'bookly_sms_administrator_phone', $this->getParameter( 'bookly_sms_administrator_phone' ) );

                $form->bind( $this->getPostParameters() );
                $form->save();
                $alert['success'][] = __( 'Settings saved.', 'bookly' );

                foreach ( array( 'staff_agenda', 'client_follow_up', 'client_reminder' ) as $type ) {
                    $cron_reminder[ $type ] = $this->getParameter( $type . '_cron_hour' );
                }
                update_option( 'bookly_cron_reminder_times', $cron_reminder );
            }
            if ( $this->hasParameter( 'tab' ) ) {
                switch ( $this->getParameter( 'auto-recharge' ) ) {
                    case 'approved':
                        $alert['success'][] = __( 'Auto-Recharge enabled.', 'bookly' );
                        break;
                    case 'declined':
                        $alert['error'][] = __( 'You declined the Auto-Recharge of your balance.', 'bookly' );
                        break;
                }
            }
        }
        $current_tab = $this->hasParameter( 'tab' ) ? $this->getParameter( 'tab' ) : 'notifications';
        $alert['error'] = array_merge( $alert['error'], $sms->getErrors() );
        wp_localize_script( 'bookly-daterangepicker.js', 'BooklyL10n',
            array(
                'alert'         => $alert,
                'apply'         => __( 'Apply', 'bookly' ),
                'are_you_sure'  => __( 'Are you sure?', 'bookly' ),
                'cancel'        => __( 'Cancel', 'bookly' ),
                'country'       => get_option( 'bookly_cst_phone_default_country' ),
                'current_tab'   => $current_tab,
                'custom_range'  => __( 'Custom Range', 'bookly' ),
                'from'          => __( 'From', 'bookly' ),
                'last_30'       => __( 'Last 30 Days', 'bookly' ),
                'last_7'        => __( 'Last 7 Days', 'bookly' ),
                'last_month'    => __( 'Last Month', 'bookly' ),
                'mjsDateFormat' => Lib\Utils\DateTime::convertFormat( 'date', Lib\Utils\DateTime::FORMAT_MOMENT_JS ),
                'startOfWeek'   => (int) get_option( 'start_of_week' ),
                'this_month'    => __( 'This Month', 'bookly' ),
                'to'            => __( 'To', 'bookly' ),
                'today'         => __( 'Today', 'bookly' ),
                'yesterday'     => __( 'Yesterday', 'bookly' ),
                'input_old_password' => __( 'Please enter old password.',  'bookly' ),
                'passwords_no_same'  => __( 'Passwords must be the same.', 'bookly' ),
                'intlTelInput'  => array(
                    'country' => get_option( 'bookly_cst_phone_default_country' ),
                    'enabled' => get_option( 'bookly_cst_phone_default_country' ) != 'disabled',
                    'utils'   => plugins_url( 'intlTelInput.utils.js', Lib\Plugin::getDirectory() . '/frontend/resources/js/intlTelInput.utils.js' ),
                ),
                'calendar'      => array(
                    'longDays'    => array_values( $wp_locale->weekday ),
                    'longMonths'  => array_values( $wp_locale->month ),
                    'shortDays'   => array_values( $wp_locale->weekday_abbrev ),
                    'shortMonths' => array_values( $wp_locale->month_abbrev ),
                ),
                'sender_id'     => array(
                    'sent'        => __( 'Sender ID request is sent.', 'bookly' ),
                    'set_default' => __( 'Sender ID is reset to default.', 'bookly' ),
                ),
                'zeroRecords'   => __( 'No records for selected period.', 'bookly' ),
                'zeroRecords2'  => __( 'No records.', 'bookly' ),
                'processing'    => __( 'Processing...', 'bookly' ),
            )
        );
        $cron_path = realpath( Lib\Plugin::getDirectory() . '/lib/utils/send_notifications_cron.php' );

        $this->render( 'index', compact( 'form', 'sms', 'is_logged_in', 'prices', 'cron_path', 'cron_reminder' ) );
    }

    public function executeGetPurchasesList()
    {
        $sms = new Lib\SMS();

        $dates = explode( ' - ', $this->getParameter( 'range' ), 2 );
        $start = Lib\Utils\DateTime::applyTimeZoneOffset( $dates[0], 0 );
        $end   = Lib\Utils\DateTime::applyTimeZoneOffset( date( 'Y-m-d', strtotime( '+1 day', strtotime( $dates[1] ) ) ), 0 );

        wp_send_json( $sms->getPurchasesList( $start, $end ) );
    }

    public function executeGetSmsList()
    {
        $sms = new Lib\SMS();

        $dates = explode( ' - ', $this->getParameter( 'range' ), 2 );
        $start = Lib\Utils\DateTime::applyTimeZoneOffset( $dates[0], 0 );
        $end   = Lib\Utils\DateTime::applyTimeZoneOffset( date( 'Y-m-d', strtotime( '+1 day', strtotime( $dates[1] ) ) ), 0 );

        wp_send_json( $sms->getSmsList( $start, $end ) );
    }

    public function executeGetPriceList()
    {
        $sms  = new Lib\SMS();
        wp_send_json( $sms->getPriceList() );
    }

    /**
     * Initial for enabling Auto-Recharge balance
     */
    public function executeInitAutoRecharge()
    {
        $sms = new Lib\SMS();
        $key = $sms->getPreapprovalKey( $this->getParameter( 'amount' ) );
        if ( $key !== false ) {
            wp_send_json_success( array( 'paypal_preapproval' => 'https://www.paypal.com/cgi-bin/webscr?cmd=_ap-preapproval&preapprovalkey=' . $key ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Auto-Recharge has failed, please replenish your balance directly.', 'bookly' ) ) );
        }
    }

    /**
     * Disable Auto-Recharge balance
     */
    public function executeDeclineAutoRecharge()
    {
        $sms = new Lib\SMS();
        $declined = $sms->declinePreapproval();
        if ( $declined !== false ) {
            wp_send_json_success( array( 'message' => __( 'Auto-Recharge disabled', 'bookly' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Error. Can\'t disable Auto-Recharge, you can perform this action in your PayPal account.', 'bookly' ) ) );
        }
    }

    public function executeChangePassword()
    {
        $sms  = new Lib\SMS();
        $old_password = $this->getParameter( 'old_password' );
        $new_password = $this->getParameter( 'new_password' );

        $result = $sms->changePassword( $new_password, $old_password );
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( $sms->getErrors() ) ) );
        } else {
            wp_send_json_success();
        }
    }

    public function executeSendTestSms()
    {
        $sms = new Lib\SMS();
        $phone_number = $this->getParameter( 'phone_number' );
        if ( $phone_number != '' ) {
            $response = array( 'success' => $sms->sendSms( $phone_number, 'Bookly test SMS.', Lib\Entities\Notification::$type_ids['test_message'] ) );
            if ( $response['success'] ) {
                $response['message'] = __( 'SMS has been sent successfully.', 'bookly' );
            } else {
                $response['message'] = __( 'Failed to send SMS.', 'bookly' );
            }
            wp_send_json( $response );
        } else {
            wp_send_json( array( 'success' => false, 'message' => __( 'Phone number is empty.', 'bookly' ) ) );
        }
    }

    public function executeForgotPassword()
    {
        $sms      = new Lib\SMS();
        $step     = $this->getParameter( 'step' );
        $code     = $this->getParameter( 'code' );
        $username = $this->getParameter( 'username' );
        $password = $this->getParameter( 'password' );
        $result   = $sms->forgotPassword( $username, $step, $code, $password );
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( $sms->getErrors() ) ) );
        } else {
            wp_send_json_success();
        }
    }

    public function executeGetSenderIdsList()
    {
        $sms    = new Lib\SMS();
        wp_send_json( $sms->getSenderIdsList() );
    }

    /**
     * Request new Sender ID.
     */
    public function executeRequestSenderId()
    {
        $sms    = new Lib\SMS();
        $result = $sms->requestSenderId( $this->getParameter( 'sender_id' ) );
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( $sms->getErrors() ) ) );
        } else {
            wp_send_json_success( array( 'request_id' => $result->request_id ) );
        }
    }

    /**
     * Cancel request for Sender ID.
     */
    public function executeCancelSenderId()
    {
        $sms    = new Lib\SMS();
        $result = $sms->cancelSenderId();
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( $sms->getErrors() ) ) );
        } else {
            wp_send_json_success();
        }
    }

    /**
     * Reset Sender ID to default (Bookly).
     */
    public function executeResetSenderId()
    {
        $sms    = new Lib\SMS();
        $result = $sms->resetSenderId();
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( $sms->getErrors() ) ) );
        } else {
            wp_send_json_success();
        }
    }

    /**
     * Enable or Disable administrators email reports.
     */
    public function executeAdminNotify()
    {
        if ( in_array( $this->getParameter( 'option_name' ), array( 'bookly_sms_notify_low_balance', 'bookly_sms_notify_weekly_summary' ) ) ) {
            update_option( $this->getParameter( 'option_name' ), $this->getParameter( 'value' ) );
        }
        wp_send_json_success();
    }

    /**
     * Override parent method to add 'wp_ajax_bookly_' prefix
     * so current 'execute*' methods look nicer.
     *
     * @param string $prefix
     */
    protected function registerWpActions( $prefix = '' )
    {
        parent::registerWpActions( 'wp_ajax_bookly_' );
    }

}