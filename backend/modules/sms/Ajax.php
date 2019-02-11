<?php
namespace Bookly\Backend\Modules\Sms;

use Bookly\Lib;

/**
 * Class Ajax
 * @package Bookly\Backend\Modules\Sms
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * Get purchases list.
     */
    public static function getPurchasesList()
    {
        $sms = new Lib\SMS();

        $dates = explode( ' - ', self::parameter( 'range' ), 2 );
        $start = Lib\Utils\DateTime::applyTimeZoneOffset( $dates[0], 0 );
        $end   = Lib\Utils\DateTime::applyTimeZoneOffset( date( 'Y-m-d', strtotime( '+1 day', strtotime( $dates[1] ) ) ), 0 );

        wp_send_json( $sms->getPurchasesList( $start, $end ) );
    }

    /**
     * Get SMS list.
     */
    public static function getSmsList()
    {
        $sms = new Lib\SMS();

        $dates = explode( ' - ', self::parameter( 'range' ), 2 );
        $start = Lib\Utils\DateTime::applyTimeZoneOffset( $dates[0], 0 );
        $end   = Lib\Utils\DateTime::applyTimeZoneOffset( date( 'Y-m-d', strtotime( '+1 day', strtotime( $dates[1] ) ) ), 0 );

        wp_send_json( $sms->getSmsList( $start, $end ) );
    }

    /**
     * Get price-list.
     */
    public static function getPriceList()
    {
        $sms  = new Lib\SMS();
        wp_send_json( $sms->getPriceList() );
    }

    /**
     * Initial for enabling Auto-Recharge balance
     */
    public static function initAutoRecharge()
    {
        $sms = new Lib\SMS();
        $key = $sms->getPreapprovalKey( self::parameter( 'amount' ) );
        if ( $key !== false ) {
            wp_send_json_success( array( 'paypal_preapproval' => 'https://www.paypal.com/cgi-bin/webscr?cmd=_ap-preapproval&preapprovalkey=' . $key ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Auto-Recharge has failed, please replenish your balance directly.', 'bookly' ) ) );
        }
    }

    /**
     * Disable Auto-Recharge balance
     */
    public static function declineAutoRecharge()
    {
        $sms = new Lib\SMS();
        $declined = $sms->declinePreapproval();
        if ( $declined !== false ) {
            wp_send_json_success( array( 'message' => __( 'Auto-Recharge disabled', 'bookly' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Error. Can\'t disable Auto-Recharge, you can perform this action in your PayPal account.', 'bookly' ) ) );
        }
    }

    /**
     * Change password.
     */
    public static function changePassword()
    {
        $sms  = new Lib\SMS();
        $old_password = self::parameter( 'old_password' );
        $new_password = self::parameter( 'new_password' );

        $result = $sms->changePassword( $new_password, $old_password );
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( $sms->getErrors() ) ) );
        } else {
            wp_send_json_success();
        }
    }

    /**
     * Send test SMS.
     */
    public static function sendTestSms()
    {
        $sms = new Lib\SMS();

        $response = array( 'success' => $sms->sendSms(
            self::parameter( 'phone_number' ),
            'Bookly test SMS.',
            Lib\Entities\Notification::$type_ids['test_message']
        ) );

        if ( $response['success'] ) {
            $response['message'] = __( 'SMS has been sent successfully.', 'bookly' );
        } else {
            $response['message'] = implode( ' ', $sms->getErrors() );
        }

        wp_send_json( $response );
    }

    /**
     * Forgot password.
     */
    public static function forgotPassword()
    {
        $sms      = new Lib\SMS();
        $step     = self::parameter( 'step' );
        $code     = self::parameter( 'code' );
        $username = self::parameter( 'username' );
        $password = self::parameter( 'password' );
        $result   = $sms->forgotPassword( $username, $step, $code, $password );
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( $sms->getErrors() ) ) );
        } else {
            wp_send_json_success();
        }
    }

    /**
     * Get Sender IDs list.
     */
    public static function getSenderIdsList()
    {
        $sms    = new Lib\SMS();
        wp_send_json( $sms->getSenderIdsList() );
    }

    /**
     * Request new Sender ID.
     */
    public static function requestSenderId()
    {
        $sms    = new Lib\SMS();
        $result = $sms->requestSenderId( self::parameter( 'sender_id' ) );
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( $sms->getErrors() ) ) );
        } else {
            wp_send_json_success( array( 'request_id' => $result->request_id ) );
        }
    }

    /**
     * Cancel request for Sender ID.
     */
    public static function cancelSenderId()
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
    public static function resetSenderId()
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
    public static function adminNotify()
    {
        if ( in_array( self::parameter( 'option_name' ), array( 'bookly_sms_notify_low_balance', 'bookly_sms_notify_weekly_summary' ) ) ) {
            update_option( self::parameter( 'option_name' ), self::parameter( 'value' ) );
        }
        wp_send_json_success();
    }

    /**
     * Send client info for invoice.
     */
    public static function saveInvoiceData()
    {
        $sms    = new Lib\SMS();
        $result = $sms->sendInvoiceData( (array) self::parameter( 'invoice' ) );
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( $sms->getErrors() ) ) );
        } else {
            wp_send_json_success( array( 'message' => __( 'Settings saved.', 'bookly' ) ) );
        }
    }

    /**
     * Delete notification.
     */
    public static function deleteNotification()
    {
        Lib\Entities\Notification::query()
            ->delete()
            ->where( 'id', self::parameter( 'id' ) )
            ->execute();

        wp_send_json_success();
    }

    /**
     * Get data for notification list.
     */
    public static function getNotifications()
    {
        $types = Lib\Entities\Notification::getTypes( self::parameter( 'gateway' ) );

        $notifications = Lib\Entities\Notification::query()
            ->select( 'id, name, active, type' )
            ->where( 'gateway', self::parameter( 'gateway' ) )
            ->whereIn( 'type', $types )
            ->fetchArray();

        foreach ( $notifications as &$notification ) {
            $notification['order'] = array_search( $notification['type'], $types );
            $notification['icon']  = Lib\Entities\Notification::getIcon( $notification['type'] );
            $notification['title'] = Lib\Entities\Notification::getTitle( $notification['type'] );
        }

        wp_send_json_success( $notifications );
    }

    /**
     * Activate/Suspend notification.
     */
    public static function setNotificationState()
    {
        Lib\Entities\Notification::query()
            ->update()
            ->set( 'active', (int) self::parameter( 'active' ) )
            ->where( 'id', self::parameter( 'id' ) )
            ->execute();

        wp_send_json_success();
    }

    /**
     * Remove notification(s).
     */
    public static function deleteNotifications()
    {
        $notifications = array_map( 'intval', self::parameter( 'notifications', array() ) );
        Lib\Entities\Notification::query()->delete()->whereIn( 'id', $notifications )->execute();
        wp_send_json_success();
    }

    public static function saveAdministratorPhone()
    {
        update_option( 'bookly_sms_administrator_phone', self::parameter( 'bookly_sms_administrator_phone' ) );
        wp_send_json_success();
    }
}