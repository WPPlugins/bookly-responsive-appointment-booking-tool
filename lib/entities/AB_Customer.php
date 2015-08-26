<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_Customer
 */
class AB_Customer extends AB_Entity
{
    protected static $table = 'ab_customers';

    protected static $schema = array(
        'id'         => array( 'format' => '%d' ),
        'wp_user_id' => array( 'format' => '%d' ),
        'name'       => array( 'format' => '%s', 'default' => '' ),
        'phone'      => array( 'format' => '%s', 'default' => '' ),
        'email'      => array( 'format' => '%s', 'default' => '' ),
        'notes'      => array( 'format' => '%s', 'default' => '' ),
    );

    /**
     * Delete customer and associated WP user if requested.
     *
     * @param bool $with_wp_user
     * @return false|int
     */
    public function deleteWithWPUser( $with_wp_user )
    {
        if ( $with_wp_user && $this->get( 'wp_user_id' )
             // Can't delete your WP account
             && ( $this->get( 'wp_user_id' ) != get_current_user_id() ) ) {
            wp_delete_user( $this->get( 'wp_user_id' ) );
        }

        return $this->delete();
    }

    /**
     * Get array with appointments data for customer profile.
     *
     * @return array
     */
    public function getAppointmentsForProfile()
    {
        $records = array();

        if ( $this->get( 'id' ) ) {
            $result = AB_Appointment::query( 'a' )
                ->select( '`c`.`name`            `category`,
                        `sv`.`title`             `service`,
                        `s`.`full_name`          `staff`,
                        `a`.`start_date`,
                        `ss`.`price`,
                        `ca`.`number_of_persons`,
                        `ca`.`coupon_discount`,
                        `ca`.`coupon_deduction`,
                        `ca`.`time_zone_offset`,
                        `ca`.`token`' )
                ->leftJoin( 'AB_Staff', 's', 's.id = a.staff_id' )
                ->leftJoin( 'AB_Service', 'sv', 'sv.id = a.service_id' )
                ->leftJoin( 'AB_Category', 'c', 'c.id = sv.category_id' )
                ->leftJoin( 'AB_StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id' )
                ->innerJoin( 'AB_CustomerAppointment', 'ca', 'ca.appointment_id = a.id AND ca.customer_id = ' . $this->get( 'id' ) )
                ->fetchArray();
            if ( $result ) {
                foreach ( $result as $row ) {
                    if ( $row['time_zone_offset'] !== null ) {
                        $row['start_date'] = AB_DateTimeUtils::applyTimeZoneOffset( $row[ 'start_date' ], $row[ 'time_zone_offset' ] );
                    }
                    $row['price'] *= $row['number_of_persons'];

                    unset ( $row['time_zone_offset'], $row['coupon_discount'], $row['coupon_deduction'], $row['number_of_persons'] );

                    $records[] = $row;
                }
            }
        }

        return $records;
    }

    /**
     * Associate WP user with customer.
     *
     * @param null $user_id
     */
    public function setWPUser( $user_id = null )
    {
        if ( $user_id === null ) {
            $user_id = $this->_createWPUser();
        }

        if ( $user_id ) {
            $this->set( 'wp_user_id', $user_id );
        }
    }

    /**
     * Create new WP user and send email notification.
     *
     * @return bool|int
     */
    private function _createWPUser()
    {
        // Generate unique username.
        $i        = 1;
        $base     = $this->get( 'name' ) != '' ? sanitize_user( $this->get( 'name' ) ) : 'client';
        $username = $base;
        while ( username_exists( $username ) ) {
            $username = $base . $i;
            ++ $i;
        }
        // Generate password.
        $password = wp_generate_password( 6, true );
        // Create user.
        $user_id = wp_create_user( $username , $password, $this->get( 'email' ) );
        if ( ! $user_id instanceof WP_Error ) {
            // Set the role
            $user = new WP_User( $user_id );
            $user->set_role( 'subscriber' );

            return $user_id;
        }

        return false;
    }

}