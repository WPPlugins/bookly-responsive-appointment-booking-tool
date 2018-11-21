<?php
namespace Bookly\Backend\Modules\Staff\Forms;

use Bookly\Lib;

/**
 * Class StaffMember
 * @package Bookly\Backend\Modules\Staff\Forms
 */
class StaffMember extends Lib\Base\Form
{
    protected static $entity_class = 'Staff';

    protected $wp_users;

    /**
     * Get list of users available for particular staff.
     *
     * @param integer $staff_id If null then it means new staff
     * @return array
     */
    public function getUsersForStaff( $staff_id = null )
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        if ( ! is_multisite() ) {
            $query = sprintf(
                'SELECT ID, user_email, display_name FROM ' . $wpdb->users . '
               WHERE ID NOT IN(SELECT DISTINCT IFNULL( wp_user_id, 0 ) FROM ' . Lib\Entities\Staff::getTableName() . ' %s)
               ORDER BY display_name',
                $staff_id !== null
                    ? 'WHERE ' . Lib\Entities\Staff::getTableName() . '.id <> ' . (int) $staff_id
                    : ''
            );
            $users = $wpdb->get_results( $query );
        } else {
            // In Multisite show users only for current blog.
            $query = Lib\Entities\Staff::query( 's' )->select( 'DISTINCT wp_user_id' )->whereNot( 'wp_user_id', null );
            if ( $staff_id != null ) {
                $query->whereNot( 'id', $staff_id );
            }
            $exclude_wp_users = array();
            foreach ( $query->fetchArray() as $staff ) {
                $exclude_wp_users[] = $staff['wp_user_id'];
            }
            $users = array_map(
                function ( \WP_User $wp_user ) {
                    $obj = new \stdClass();
                    $obj->ID = $wp_user->ID;
                    $obj->user_email = $wp_user->data->user_email;
                    $obj->display_name = $wp_user->data->display_name;

                    return $obj;
                },
                get_users( array( 'blog_id' => get_current_blog_id(), 'orderby' => 'display_name', 'exclude' => $exclude_wp_users ) )
            );
        }

        return $users;
    }

}
