<?php
namespace Bookly\Backend\Modules\Customers;

use Bookly\Lib;

/**
 * Class Ajax
 * @package Bookly\Backend\Modules\Customers
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * @inheritdoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'user' );
    }

    /**
     * Get list of customers.
     */
    public static function getCustomers()
    {
        global $wpdb;

        $columns = self::parameter( 'columns' );
        $order   = self::parameter( 'order' );
        $filter  = self::parameter( 'filter' );

        $query = Lib\Entities\Customer::query( 'c' );

        $total = $query->count();

        $select = 'SQL_CALC_FOUND_ROWS c.*,
                (
                    SELECT MAX(a.start_date) FROM ' . Lib\Entities\Appointment::getTableName() . ' a
                        LEFT JOIN ' . Lib\Entities\CustomerAppointment::getTableName() . ' ca ON ca.appointment_id = a.id
                            WHERE ca.customer_id = c.id
                ) AS last_appointment,
                (
                    SELECT COUNT(DISTINCT ca.appointment_id) FROM ' . Lib\Entities\CustomerAppointment::getTableName() . ' ca
                        WHERE ca.customer_id = c.id
                ) AS total_appointments,
                (
                    SELECT SUM(p.total) FROM ' . Lib\Entities\Payment::getTableName() . ' p
                        WHERE p.id IN (
                            SELECT DISTINCT ca.payment_id FROM ' . Lib\Entities\CustomerAppointment::getTableName() . ' ca
                                WHERE ca.customer_id = c.id
                        )
                ) AS payments,
                wpu.display_name AS wp_user';

        $select = Proxy\CustomerGroups::prepareCustomerSelect( $select );

        $query
            ->select( $select )
            ->tableJoin( $wpdb->users, 'wpu', 'wpu.ID = c.wp_user_id' )
            ->groupBy( 'c.id' );

        $query = Proxy\CustomerGroups::prepareCustomerQuery( $query );

        if ( $filter != '' ) {
            $search_value = Lib\Query::escape( $filter );
            $query
                ->whereLike( 'c.full_name', "%{$search_value}%" )
                ->whereLike( 'c.phone', "%{$search_value}%", 'OR' )
                ->whereLike( 'c.email', "%{$search_value}%", 'OR' )
                ->whereLike( 'c.info_fields', "%{$search_value}%", 'OR' )
            ;
        }

        foreach ( $order as $sort_by ) {
            $query
                ->sortBy( str_replace( '.', '_', $columns[ $sort_by['column'] ]['data'] ) )
                ->order( $sort_by['dir'] == 'desc' ? Lib\Query::ORDER_DESCENDING : Lib\Query::ORDER_ASCENDING );
        }

        $query->limit( self::parameter( 'length' ) )->offset( self::parameter( 'start' ) );

        $data = array();
        foreach ( $query->fetchArray() as $row ) {

            $address = Lib\Proxy\Pro::getFullAddressByCustomerData( $row );

            $customer_data = array(
                'id'                 => $row['id'],
                'wp_user_id'         => $row['wp_user_id'],
                'wp_user'            => $row['wp_user'],
                'facebook_id'        => $row['facebook_id'],
                'group_id'           => $row['group_id'],
                'full_name'          => $row['full_name'],
                'first_name'         => $row['first_name'],
                'last_name'          => $row['last_name'],
                'phone'              => $row['phone'],
                'email'              => $row['email'],
                'country'            => $row['country'],
                'state'              => $row['state'],
                'postcode'           => $row['postcode'],
                'city'               => $row['city'],
                'street'             => $row['street'],
                'street_number'      => $row['street_number'],
                'additional_address' => $row['additional_address'],
                'address'            => $address,
                'notes'              => $row['notes'],
                'birthday'           => $row['birthday'] !== null && date_create( $row['birthday'] )->format( 'Y' ) == '0000' ? date_create( $row['birthday'] )->modify( '+ 1900 years' )->format( 'Y-m-d' ) : $row['birthday'],
                'last_appointment'   => $row['last_appointment'] ? Lib\Utils\DateTime::formatDateTime( $row['last_appointment'] ) : '',
                'total_appointments' => $row['total_appointments'],
                'payments'           => Lib\Utils\Price::format( $row['payments'] ),
            );

            $customer_data = Proxy\CustomerGroups::prepareCustomerListData( $customer_data, $row );
            $customer_data = Proxy\CustomerInformation::prepareCustomerListData( $customer_data, $row );

            $data[] = $customer_data;
        }

        wp_send_json( array(
            'draw'            => ( int ) self::parameter( 'draw' ),
            'recordsTotal'    => $total,
            'recordsFiltered' => ( int ) $wpdb->get_var( 'SELECT FOUND_ROWS()' ),
            'data'            => $data,
        ) );
    }

    /**
     * Merge customers.
     */
    public static function mergeCustomers()
    {
        $target_id = self::parameter( 'target_id' );
        $ids       = self::parameter( 'ids', array() );

        // Move appointments.
        Lib\Entities\CustomerAppointment::query()
            ->update()
            ->set( 'customer_id', $target_id )
            ->whereIn( 'customer_id', $ids )
            ->execute();

        // Let add-ons do their stuff.
        Proxy\Shared::mergeCustomers( $target_id, $ids );

        // Merge customer data.
        $target_customer = Lib\Entities\Customer::find( $target_id );
        foreach ( $ids as $id ) {
            if ( $id != $target_id ) {
                $customer = Lib\Entities\Customer::find( $id );
                if ( ! $target_customer->getWpUserId() && $customer->getWpUserId() ) {
                    $target_customer->setWpUserId( $customer->getWpUserId() );
                }
                if ( ! $target_customer->getGroupId() ) {
                    $target_customer->setGroupId( $customer->getGroupId() );
                }
                if ( ! $target_customer->getFacebookId() ) {
                    $target_customer->setFacebookId( $customer->getFacebookId() );
                }
                if ( $target_customer->getFullName() == '' ) {
                    $target_customer->setFullName( $customer->getFullName() );
                }
                if ( $target_customer->getFirstName() == '' ) {
                    $target_customer->setFirstName( $customer->getFirstName() );
                }
                if ( $target_customer->getLastName() == '' ) {
                    $target_customer->setLastName( $customer->getLastName() );
                }
                if ( $target_customer->getPhone() == '' ) {
                    $target_customer->setPhone( $customer->getPhone() );
                }
                if ( $target_customer->getEmail() == '' ) {
                    $target_customer->setEmail( $customer->getEmail() );
                }
                if ( $target_customer->getBirthday() == '' ) {
                    $target_customer->setBirthday( $customer->getBirthday() );
                }
                if ( $target_customer->getCountry() == '' ) {
                    $target_customer->setCountry( $customer->getCountry() );
                }
                if ( $target_customer->getState() == '' ) {
                    $target_customer->setState( $customer->getState() );
                }
                if ( $target_customer->getPostcode() == '' ) {
                    $target_customer->setPostcode( $customer->getPostcode() );
                }
                if ( $target_customer->getCity() == '' ) {
                    $target_customer->setCity( $customer->getCity() );
                }
                if ( $target_customer->getStreet() == '' ) {
                    $target_customer->setStreet( $customer->getStreet() );
                }
                if ( $target_customer->getAdditionalAddress() == '' ) {
                    $target_customer->setAdditionalAddress( $customer->getAdditionalAddress() );
                }
                if ( $target_customer->getNotes() == '' ) {
                    $target_customer->setNotes( $customer->getNotes() );
                }
                // Delete merged customer.
                $customer->delete();
            }
            $target_customer->save();
        }

        wp_send_json_success();
    }

    /**
     * Check if the current user has access to the action.
     *
     * @param string $action
     * @return bool
     */
    protected static function hasAccess( $action )
    {
        if ( parent::hasAccess( $action ) ) {
            if ( ! Lib\Utils\Common::isCurrentUserSupervisor() ) {
                switch ( $action ) {
                    case 'getCustomers':
                        return Lib\Entities\Staff::query()
                            ->where( 'wp_user_id', get_current_user_id() )
                            ->count() > 0;
                }
            } else {
                return true;
            }
        }

        return false;
    }
}