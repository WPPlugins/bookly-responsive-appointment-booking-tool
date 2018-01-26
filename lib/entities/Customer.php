<?php
namespace BooklyLite\Lib\Entities;

use BooklyLite\Lib;

/**
 * Class Customer
 * @package BooklyLite\Lib\Entities
 */
class Customer extends Lib\Base\Entity
{
    /** @var  int */
    protected $wp_user_id;
    /** @var  string */
    protected $full_name = '';
    /** @var  string */
    protected $first_name = '';
    /** @var  string */
    protected $last_name = '';
    /** @var  string */
    protected $phone = '';
    /** @var  string */
    protected $email = '';
    /** @var  string */
    protected $notes = '';
    /** @var  string */
    protected $birthday;

    protected static $table = 'ab_customers';

    protected static $schema = array(
        'id'         => array( 'format' => '%d' ),
        'wp_user_id' => array( 'format' => '%d' ),
        'full_name'  => array( 'format' => '%s' ),
        'first_name' => array( 'format' => '%s' ),
        'last_name'  => array( 'format' => '%s' ),
        'phone'      => array( 'format' => '%s' ),
        'email'      => array( 'format' => '%s' ),
        'notes'      => array( 'format' => '%s' ),
        'birthday'   => array( 'format' => '%s' ),
    );

    /**
     * Delete customer and associated WP user if requested.
     *
     * @param bool $with_wp_user
     */
    public function deleteWithWPUser( $with_wp_user )
    {
        if ( $with_wp_user && $this->getWpUserId()
             // Can't delete your WP account
             && ( $this->getWpUserId() != get_current_user_id() ) ) {
            wp_delete_user( $this->getWpUserId() );
        }

        /** @var Appointment[] $appointments */
        $appointments = Appointment::query( 'a' )
            ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
            ->where( 'ca.customer_id', $this->getId() )
            ->groupBy( 'a.id' )
            ->find()
        ;

        $this->delete();

        foreach ( $appointments as $appointment ) {
            // Google Calendar.
            $appointment->handleGoogleCalendar();
            // Waiting list.
            Lib\Proxy\WaitingList::handleParticipantsChange( $appointment );
        }
    }

    /**
     * Get upcoming appointments.
     *
     * @return array
     */
    public function getUpcomingAppointments()
    {
        return $this->_buildQueryForAppointments()
            ->whereGte( 'a.start_date', current_time( 'Y-m-d 00:00:00' ) )
            ->fetchArray();
    }

    /**
     * Get past appointments.
     *
     * @param $page
     * @param $limit
     * @return array
     */
    public function getPastAppointments( $page, $limit )
    {
        $result = array( 'more' => true, 'appointments' => array() );

        $records = $this->_buildQueryForAppointments()
            ->whereLt( 'a.start_date', current_time( 'Y-m-d 00:00:00' ) )
            ->limit( $limit + 1 )
            ->offset( ( $page - 1 ) * $limit )
            ->fetchArray();

        $result['more'] = count( $records ) > $limit;
        if ( $result['more'] ) {
            array_pop( $records );
        }

        $result['appointments'] = $records;

        return $result;
    }

    /**
     * Build query for getUpcomingAppointments and getPastAppointments methods.
     *
     * @return Lib\Query
     */
    private function _buildQueryForAppointments()
    {
        $client_diff = get_option( 'gmt_offset' ) * MINUTE_IN_SECONDS;

        return Appointment::query( 'a' )
            ->select( 'ca.id AS ca_id,
                    c.name AS category,
                    COALESCE(s.title, a.custom_service_name) AS service,
                    st.full_name AS staff,
                    a.staff_id,
                    a.staff_any,
                    a.service_id,
                    s.category_id,
                    ca.status AS appointment_status,
                    ca.extras,
                    ca.compound_token,
                    ca.number_of_persons,
                    ca.custom_fields,
                    ca.appointment_id,
                    IF (ca.compound_service_id IS NULL, COALESCE(ss.price, a.custom_service_price), s.price) AS price,
                    IF (ca.time_zone_offset IS NULL,
                        a.start_date,
                        DATE_SUB(a.start_date, INTERVAL ' . $client_diff . ' + ca.time_zone_offset MINUTE)
                    ) AS start_date,
                    ca.token' )
            ->leftJoin( 'Staff', 'st', 'st.id = a.staff_id' )
            ->leftJoin( 'Customer', 'customer', 'customer.wp_user_id = ' . $this->getWpUserId() )
            ->innerJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id AND ca.customer_id = customer.id' )
            ->leftJoin( 'Service', 's', 's.id = COALESCE(ca.compound_service_id, a.service_id)' )
            ->leftJoin( 'Category', 'c', 'c.id = s.category_id' )
            ->leftJoin( 'StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id' )
            ->leftJoin( 'Payment', 'p', 'p.id = ca.payment_id' )
            ->sortBy( 'start_date' )
            ->order( 'DESC' );
    }

    /**
     * Create new WP user and send email notification.
     *
     * @return int|false
     */
    private function _createWPUser()
    {
        // Generate unique username.
        $base     = Lib\Config::showFirstLastName() ? sanitize_user( sprintf( '%s %s', $this->getFirstName(), $this->getLastName() ), true ) : sanitize_user( $this->getFullName(), true );
        $base     = $base != '' ? $base : 'client';
        $username = $base;
        $i        = 1;
        while ( username_exists( $username ) ) {
            $username = $base . $i;
            ++ $i;
        }
        // Generate password.
        $password = wp_generate_password( 6, true );
        // Create user.
        $user_id = wp_create_user( $username, $password, $this->getEmail() );
        if ( ! $user_id instanceof \WP_Error ) {
            // Set the role
            $user = new \WP_User( $user_id );
            $user->set_role( get_option( 'bookly_cst_new_account_role', 'subscriber' ) );

            // Send email/sms notification.
            Lib\NotificationSender::sendNewUserCredentials( $this, $username, $password );

            return $user_id;
        }

        return false;
    }

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Gets wp_user_id
     *
     * @return int
     */
    public function getWpUserId()
    {
        return $this->wp_user_id;
    }

    /**
     * Associate WP user with customer.
     *
     * @param int $wp_user_id
     * @return $this
     */
    public function setWpUserId( $wp_user_id = 0 )
    {
        if ( $wp_user_id == 0 ) {
            $wp_user_id = $this->_createWPUser();
        }

        if ( $wp_user_id ) {
            $this->wp_user_id = $wp_user_id;
        }

        return $this;
    }

    /**
     * Gets full_name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->full_name;
    }

    /**
     * Sets full_name
     *
     * @param string $full_name
     * @return $this
     */
    public function setFullName( $full_name )
    {
        $this->full_name = $full_name;

        return $this;
    }

    /**
     * Gets first_name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Sets first_name
     *
     * @param string $first_name
     * @return $this
     */
    public function setFirstName( $first_name )
    {
        $this->first_name = $first_name;

        return $this;
    }

    /**
     * Gets last_name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Sets last_name
     *
     * @param string $last_name
     * @return $this
     */
    public function setLastName( $last_name )
    {
        $this->last_name = $last_name;

        return $this;
    }

    /**
     * Gets phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Sets phone
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone( $phone )
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Gets email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail( $email )
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Gets notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Sets notes
     *
     * @param string $notes
     * @return $this
     */
    public function setNotes( $notes )
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Gets birthday
     *
     * @return string
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Sets birthday
     *
     * @param string $birthday
     * @return $this
     */
    public function setBirthday( $birthday )
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**************************************************************************
     * Overridden Methods                                                     *
     **************************************************************************/

    /**
     * Save entity to database.
     * Fill name, first_name, last_name before save
     *
     * @return int|false
     */
    public function save()
    {
        if ( ( ! Lib\Config::showFirstLastName() && $this->getFullName() != '' ) || ( $this->getFullName() != '' && $this->getFirstName() == '' && $this->getLastName() == '' ) ) {
            $full_name = explode( ' ', $this->getFullName(), 2 );
            $this->setFirstName( $full_name[0] );
            $this->setLastName( isset ( $full_name[1] ) ? trim( $full_name[1] ) : '' );
        } else {
            $this->setFullName( trim( rtrim( $this->getFirstName() ) . ' ' . ltrim( $this->getLastName() ) ) );
        }

        return parent::save();
    }

}