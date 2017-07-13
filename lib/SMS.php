<?php
namespace BooklyLite\Lib;

/**
 * Class SMS
 * @package BooklyLite\Lib
 */
class SMS
{
    const API_URL = 'http://sms.booking-wp-plugin.com/1.0';

    const REGISTER            = '/users';                            //POST
    const AUTHENTICATE        = '/users';                            //GET
    const LOG_OUT             = '/users/%token%/logout';             //GET
    const GET_PROFILE_INFO    = '/users/%token%';                    //GET
    const GET_SMS_LIST        = '/users/%token%/sms';                //GET
    const GET_SMS_SUMMARY     = '/users/%token%/sms/summary';        //GET
    const GET_PURCHASES_LIST  = '/users/%token%/purchases';          //GET
    const SEND_SMS            = '/users/%token%/sms';                //POST
    const GET_PRICES          = '/prices';                           //GET
    const PASSWORD_FORGOT     = '/recoveries';                       //POST
    const PASSWORD_CHANGE     = '/users/%token%';                    //PATCH
    const PREAPPROVAL_CREATE  = '/users/%token%/paypal/preapproval'; //POST
    const PREAPPROVAL_DELETE  = '/users/%token%/paypal/preapproval'; //DELETE
    const GET_SENDER_IDS_LIST = '/users/%token%/sender-ids';         //GET
    const REQUEST_SENDER_ID   = '/users/%token%/sender-ids';         //POST
    const RESET_SENDER_ID     = '/users/%token%/sender-ids/reset';   //GET
    const CANCEL_SENDER_ID    = '/users/%token%/sender-ids/cancel';  //GET

    private $username;

    private $token;

    private $balance;

    private $errors = array();
    /** @var \stdClass */
    private $sender_id;
    /** @var \stdClass */
    private $auto_recharge;

    public function __construct()
    {
        $this->token = get_option( 'bookly_sms_token' );
    }

    /**
     * Register new account.
     *
     * @param string $username
     * @param string $password
     * @param string $password_repeat
     * @return bool
     */
    public function register( $username, $password, $password_repeat )
    {
        $data = array( '_username' => $username, '_password' => $password );

        if ( $password !== $password_repeat && ! empty( $password ) ) {
            $this->errors[] = __( 'Passwords must be the same.', 'bookly' );

            return false;
        }

        $response = $this->sendPostRequest( self::REGISTER, $data );
        if ( $response ) {
            update_option( 'bookly_sms_token', $response->token );
            $this->token = $response->token;

            return true;
        }

        return false;
    }

    /**
     * Log in.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function login( $username, $password )
    {
        $data = array( '_username' => $username, '_password' => $password );

        $response = $this->sendGetRequest( self::AUTHENTICATE, $data );
        if ( $response ) {
            update_option( 'bookly_sms_token', $response->token );
            $this->token = $response->token;

            return true;
        }

        return false;
    }

    /**
     * Change password.
     *
     * @param string $new_password
     * @param string $old_password
     * @return bool
     */
    public function changePassword( $new_password, $old_password )
    {
        $data = array( '_old_password' => $old_password, '_new_password' => $new_password );

        $response = $this->sendPatchRequest( self::PASSWORD_CHANGE, $data );
        if ( $response ) {

            return true;
        }

        return false;
    }

    /**
     * Log out.
     */
    public function logout()
    {
        update_option( 'bookly_sms_token', '' );

        if ( $this->token ) {
            $this->sendGetRequest( self::LOG_OUT );
        }
        $this->token = null;
    }

    /**
     * Get PayPal Preapproval key, (for enabling auto recharge)
     *
     * @param $amount
     * @return string|false
     */
    public function getPreapprovalKey( $amount )
    {
        if ( $this->token ) {
            $response = $this->sendPostRequest(
                self::PREAPPROVAL_CREATE,
                array(
                    'amount'   => $amount,
                    'approved' => admin_url( 'admin.php?page=' . \BooklyLite\Backend\Modules\Sms\Controller::page_slug . '&tab=auto_recharge&auto-recharge=approved' ),
                    'declined' => admin_url( 'admin.php?page=' . \BooklyLite\Backend\Modules\Sms\Controller::page_slug . '&tab=auto_recharge&auto-recharge=declined' ),
                )
            );
            if ( $response ) {
                return $response->preapprovalKey;
            }
        }

        return false;
    }

    /**
     * Decline PayPal Preapproval. (disable auto recharge)
     *
     * @return bool
     */
    public function declinePreapproval()
    {
        if ( $this->token ) {
            $response = $this->sendDeleteRequest( self::PREAPPROVAL_DELETE, array() );
            if ( $response ) {

                return true;
            }
        }

        return false;
    }

    /**
     * Send SMS.
     *
     * @param string $phone_number
     * @param string $message
     * @param int    $type_id
     * @return bool
     */
    public function sendSms( $phone_number, $message, $type_id = null )
    {
        if ( $this->token ) {
            $data = array(
                'message'  => $message,
                'phone'    => $this->normalizePhoneNumber( $phone_number ),
                'type'     => $type_id,
                'site_url' => site_url(),
            );
            if ( $data['phone'] != '' ) {
                $response = $this->sendPostRequest( self::SEND_SMS, $data );
                if ( $response ) {
                    if ( property_exists( $response, 'notify_low_balance' ) && get_option( 'bookly_sms_notify_low_balance' ) ) {
                        if ( $response->notify_low_balance ) {
                            $this->_sendLowBalanceNotification();
                        }
                    }
                    if ( property_exists( $response, 'gateway_status' ) ) {
                        /** array description on @see SMS::getSmsList */
                        if ( in_array( $response->gateway_status, array( 1, 10, 11, 12, 13 ) ) ) {

                            return true;
                        }
                    }
                }
            } else {
                $this->errors[] = __( 'Phone number is empty.', 'bookly' );
            }
        }

        return false;
    }

    /**
     * Return phone_number in international format without +
     *
     * @param $phone_number
     * @return string
     */
    public function normalizePhoneNumber( $phone_number )
    {
        // Remove everything except numbers and "+".
        $phone_number = preg_replace( '/[^\d\+]/', '', $phone_number );

        if ( strpos( $phone_number, '+' ) === 0 ) {
            // ok.
        } elseif ( strpos( $phone_number, '00' ) === 0 ) {
            $phone_number = ltrim( $phone_number, '0' );
        } else {
            // Default country code can contain not permitted characters. Remove everything except numbers.
            $phone_number = ltrim( preg_replace( '/\D/', '', get_option( 'bookly_cst_default_country_code', '' ) ), '0' )  . ltrim( $phone_number, '0' );
        }

        // Finally remove "+" if there were any among digits.
        return str_replace( '+', '', $phone_number );
    }

    /**
     * Load user profile info.
     *
     * @return bool
     */
    public function loadProfile()
    {
        if ( $this->token ) {
            $response = $this->sendGetRequest( self::GET_PROFILE_INFO );
            if ( $response ) {
                $this->username      = $response->username;
                $this->balance       = $response->balance;
                $this->sender_id     = $response->sender_id;
                $this->auto_recharge = $response->auto_recharge;

                return true;
            }
        }

        return false;
    }

    /**
     * User forgot password for sms
     *
     * @param null $username
     * @param null $step
     * @param null $code
     * @param null $password
     * @return \stdClass|false
     */
    public function forgotPassword( $username = null, $step = null, $code = null, $password = null )
    {
        $data = array( '_username' => $username, 'step' => $step );
        switch ( $step ) {
            case 0:
                break;
            case 1:
                $data['code'] = $code;
                break;
            case 2:
                $data['code'] = $code;
                $data['password'] = $password;
                break;
        }
        $response = $this->sendPostRequest( self::PASSWORD_FORGOT, $data );

        if ( $response ) {

            return $response;
        }

        return false;
    }

    /**
     * Get purchases list.
     *
     * @param null $start_date
     * @param null $end_date
     * @return \stdClass|array
     */
    public function getPurchasesList( $start_date = null, $end_date = null )
    {
        if ( $this->token ) {
            $response = $this->sendGetRequest(
                self::GET_PURCHASES_LIST,
                array( 'start_date' => $start_date, 'end_date' => $end_date )
            );
            if ( $response ) {
                array_walk( $response->list, function( &$item ) {
                    $date_time  = Utils\DateTime::UTCToWPTimeZone( $item->datetime );
                    $item->date = Utils\DateTime::formatDate( $date_time );
                    $item->time = Utils\DateTime::formatTime( $date_time );
                } );

                return $response;
            }
        }

        return array( 'success' => false, 'list' => array() );
    }

    /**
     * Get purchases list.
     *
     * @param null $start_date
     * @param null $end_date
     * @return \stdClass|false
     */
    public function getSummary( $start_date = null, $end_date = null )
    {
        if ( $this->token ) {
            $response = $this->sendGetRequest(
                self::GET_SMS_SUMMARY,
                array( 'start_date' => $start_date, 'end_date' => $end_date )
            );
            if ( $response ) {

                return $response->summary;
            }
        }

        return false;
    }

    /**
     * Get SMS list.
     *
     * @param null $start_date
     * @param null $end_date
     * @return \stdClass|array
     */
    public function getSmsList( $start_date = null, $end_date = null )
    {
        if ( $this->token ) {
            $response = $this->sendGetRequest(
                self::GET_SMS_LIST,
                array( 'start_date' => $start_date, 'end_date' => $end_date )
            );
            if ( $response ) {
                array_walk( $response->list, function( &$item ) {
                    $date_time  = Utils\DateTime::UTCToWPTimeZone( $item->datetime );
                    $item->date = Utils\DateTime::formatDate( $date_time );
                    $item->time = Utils\DateTime::formatTime( $date_time );
                    $item->message = nl2br( htmlspecialchars( $item->message ) );
                    $item->phone   = '+' . $item->phone;
                    $item->charge  = rtrim( $item->charge, '0' );
                    switch ( $item->status ) {
                        case 1:
                        case 10:
                            $item->status = __( 'Queued', 'bookly' );
                            $item->charge = '$' . $item->charge;
                            break;
                        case 2:
                        case 16:
                            $item->status = __( 'Error', 'bookly' );
                            $item->charge = '';
                            break;
                        case 3:
                            $item->status = __( 'Out of credit', 'bookly' );
                            $item->charge = '';
                            break;
                        case 4:
                            $item->status = __( 'Country out of service', 'bookly' );
                            $item->charge = '';
                            break;
                        case 11:
                            $item->status = __( 'Sending', 'bookly' );
                            $item->charge = '$' . $item->charge;
                            break;
                        case 12:
                            $item->status = __( 'Sent', 'bookly' );
                            $item->charge = '$' . $item->charge;
                            break;
                        case 13:
                            $item->status = __( 'Delivered', 'bookly' );
                            $item->charge = '$' . $item->charge;
                            break;
                        case 14:
                            $item->status = __( 'Failed', 'bookly' );
                            $item->charge = '$' . $item->charge;
                            break;
                        case 15:
                            $item->status = __( 'Undelivered', 'bookly' );
                            $item->charge = '$' . $item->charge;
                            break;
                        default:
                            $item->status = __( 'Error', 'bookly' );
                            $item->charge = '';
                    }
                } );

                return $response;
            }
        }

        return array( 'success' => false, 'list' => array() );
    }

    /**
     * Get Price list.
     *
     * @return \stdClass|array
     */
    public function getPriceList()
    {
        $response = $this->sendGetRequest( self::GET_PRICES );
        if ( $response ) {
            return $response;
        }

        return (object) array( 'success' => false, 'list' => array() );
    }

    /**
     * Get list of all requests for SENDER IDs.
     *
     * @return \stdClass|array
     */
    public function getSenderIdsList()
    {
        $response = $this->sendGetRequest( self::GET_SENDER_IDS_LIST );
        if ( $response ) {
            $response->pending = null;
            foreach ( $response->list as &$item ) {
                $item->date = Utils\DateTime::formatDate( Utils\DateTime::UTCToWPTimeZone( $item->date ) );
                if ($item->name == '') {
                    $item->name = '<i>' . __( 'Default', 'bookly' ) . '</i>';
                }
                $item->status_date = $item->status_date ? Utils\DateTime::formatDate( Utils\DateTime::UTCToWPTimeZone( $item->status_date ) ) : '';
                switch ( $item->status ) {
                    case 0:
                        $item->status = __( 'Pending', 'bookly' );
                        $response->pending = $item->name;
                        break;
                    case 1:
                        $item->status = __( 'Approved', 'bookly' );
                        break;
                    case 2:
                        $item->status = __( 'Declined', 'bookly' );
                        break;
                    case 3:
                        $item->status = __( 'Cancelled', 'bookly' );
                        break;
                }
            }

            return $response;
        }

        return array( 'success' => false, 'list' => array(), 'pending' => null );
    }

    /**
     * Request new SENDER ID.
     *
     * @param $sender_id
     * @return \stdClass|false
     */
    public function requestSenderId( $sender_id )
    {
        if ( $this->token ) {
            $response = $this->sendPostRequest( self::REQUEST_SENDER_ID, array( 'name' => $sender_id ) );
            if ( $response ) {

                return $response;
            }
        }

        return false;
    }

    /**
     * Cancel request for SENDER ID.
     *
     * @return bool
     */
    public function cancelSenderId()
    {
        if ( $this->token ) {
            $response = $this->sendGetRequest( self::CANCEL_SENDER_ID );
            if ( $response ) {

                return true;
            }
        }

        return false;
    }

    /**
     * Reset SENDER ID to default (Bookly).
     *
     * @return bool
     */
    public function resetSenderId()
    {
        if ( $this->token ) {
            $response = $this->sendGetRequest( self::RESET_SENDER_ID );
            if ( $response ) {

                return true;
            }
        }

        return false;
    }

    /**
     * Send GET request.
     *
     * @param       $path
     * @param array $data
     * @return \stdClass|false
     */
    private function sendGetRequest( $path, array $data = array() )
    {
        $url = $this->_prepareUrl( $path, $data );

        return $this->_handleResponse( $this->_sendRequest( 'GET', $url, $data ) );
    }

    /**
     * Send POST request.
     *
     * @param       $path
     * @param array $data
     * @return \stdClass|false
     */
    private function sendPostRequest( $path, array $data )
    {
        $url = $this->_prepareUrl( $path, $data );

        return $this->_handleResponse( $this->_sendRequest( 'POST', $url, $data ) );
    }

    /**
     * Send PATCH request.
     *
     * @param       $path
     * @param array $data
     * @return \stdClass|false
     */
    private function sendPatchRequest( $path, array $data )
    {
        $url = $this->_prepareUrl( $path, $data );

        return $this->_handleResponse( $this->_sendRequest( 'PATCH', $url, $data ) );
    }

    /**
     * Send DELETE request.
     *
     * @param       $path
     * @param array $data
     * @return \stdClass|false
     */
    private function sendDeleteRequest( $path, array $data )
    {
        $url = $this->_prepareUrl( $path, $data );

        return $this->_handleResponse( $this->_sendRequest( 'DELETE', $url, $data ) );
    }

    private function _prepareUrl( $path, array &$data )
    {
        $url = self::API_URL . str_replace( '%token%', $this->token, $path );
        foreach ( $data as $key => $value ) {
            if ( $key{0} == '%' && substr( $key,-1 ) == '%' ) {
                $url = str_replace( $key, $value, $url );
                unset( $data[ $key ] );
            }
        }

        return $url;
    }

    /**
     * Send HTTP request.
     *
     * @param $method
     * @param $url
     * @param $data
     * @return mixed
     */
    private function _sendRequest( $method, $url, $data )
    {
        $curl = new Curl\Curl();
        $curl->options['CURLOPT_CONNECTTIMEOUT'] = 8;
        $curl->options['CURLOPT_TIMEOUT']        = 30;

        $method   = strtolower( $method );
        $response = $curl->$method( $url, $data );
        $error = $curl->error();
        if ( $error ) {
            $this->errors[] = $error;
        }

        return $response;
    }

    /**
     * Check response for errors.
     *
     * @param mixed $response
     * @return \stdClass|false
     */
    private function _handleResponse( $response )
    {
        $response = json_decode( $response );

        if ( $response !== null && property_exists( $response, 'success' ) ) {
            if ( $response->success == true ) {

                return $response;
            }
            $this->errors[] = $this->translateError( $response->message );
        } else {
            $this->errors[] = __( 'Error connecting to server.', 'bookly' );
        }

        return false;
    }

    /**
     * Send notification to administrators about low balance.
     */
    private function _sendLowBalanceNotification()
    {
        $add_money_url = admin_url( 'admin.php?' . build_query( array( 'page' => \BooklyLite\Backend\Modules\Sms\Controller::page_slug, 'tab' => 'add_money' ) ) );
        $message = sprintf( __( "Dear Bookly SMS customer.\nWe would like to notify you that your Bookly SMS balance fell lower than 5 USD. To use our service without interruptions please recharge your balance by visiting Bookly SMS page <a href='%s'>here</a>.\n\nIf you want to stop receiving these notifications, please update your settings <a href='%s'>here</a>.", 'bookly' ), $add_money_url, $add_money_url );

        wp_mail(
            Utils\Common::getAdminEmails(),
            __( 'Bookly SMS - Low Balance', 'bookly' ),
            get_option( 'bookly_email_send_as' ) == 'html' ? wpautop( $message ) : $message,
            Utils\Common::getEmailHeaders()
        );
    }

    public function getUserName()
    {
        return $this->username;
    }

    public function getBalance()
    {
        return $this->balance;
    }

    public function getSenderId()
    {
        return $this->sender_id->value;
    }

    public function getSenderIdApprovalDate()
    {
        return $this->sender_id->approved_at;
    }

    public function isAutoRechargeEnabled()
    {
        return $this->auto_recharge->enabled;
    }

    public function getAutoRechargeAmount()
    {
        return $this->auto_recharge->amount;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    public function clearErrors()
    {
        $this->errors = array();
    }

    /**
     * Translate error message.
     *
     * @param string $error_code
     * @return string
     */
    private function translateError( $error_code )
    {
        $error_codes = array(
            'ERROR_EMPTY_PASSWORD'                   => __( 'Empty password.', 'bookly' ),
            'ERROR_INCORRECT_PASSWORD'               => __( 'Incorrect password.', 'bookly' ),
            'ERROR_INCORRECT_RECOVERY_CODE'          => __( 'Incorrect recovery code.', 'bookly' ),
            'ERROR_INCORRECT_USERNAME_OR_PASSWORD'   => __( 'Incorrect email or password.', 'bookly' ),
            'ERROR_INVALID_SENDER_ID'                => __( 'Incorrect sender ID', 'bookly' ),
            'ERROR_INVALID_USERNAME'                 => __( 'Invalid email.', 'bookly' ),
            'ERROR_PENDING_SENDER_ID_ALREADY_EXISTS' => __( 'Pending sender ID already exists.', 'bookly' ),
            'ERROR_RECOVERY_CODE_EXPIRED'            => __( 'Recovery code expired.', 'bookly' ),
            'ERROR_SENDING_EMAIL'                    => __( 'Error sending email.', 'bookly' ),
            'ERROR_USER_NOT_FOUND'                   => __( 'User not found.', 'bookly' ),
            'ERROR_USERNAME_ALREADY_EXISTS'          => __( 'Email already in use.', 'bookly' ),
        );
        if ( array_key_exists( $error_code, $error_codes ) ) {
            $message = $error_codes[ $error_code ];
        } else {
            // Build message from error code.
            $message = __( ucfirst( strtolower ( str_replace( '_', ' ', substr( $error_code, 6 ) ) ) ), 'bookly' );
        }

        return $message;
    }

}