<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include AB_PATH . '/lib/entities/AB_Staff.php';

/**
 * Class AB_StaffMemberForm
 *
 * @property AB_Staff $staff
 */
class AB_StaffMemberForm extends AB_Form {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::$entity_class = 'AB_Staff';
        parent::__construct();
    }

    protected $wp_users;


    // Help methods for rendering widgets.

    /**
     * Get list of users available for particular staff.
     *
     * @global string $table_prefix
     * @param integer $staff_id If null then it means new staff
     * @return array
     */
    public function getUsersForStaff($staff_id = null) {
        /** @var wpdb $wpdb */
        global $wpdb;
        global $table_prefix;

        $query = sprintf(
            'SELECT ID, user_email, display_name FROM %susers
               WHERE ID NOT IN(SELECT DISTINCT IFNULL( wp_user_id, 0 ) FROM ab_staff %s)
               ORDER BY display_name',
            $table_prefix,
            $staff_id !== null
                ? "WHERE ab_staff.id <> $staff_id"
                : ''
        );

        return $wpdb->get_results( $query );
    }
}
