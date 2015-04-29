<?php

/**
 * Class AB_Customer
 */
class AB_Customer extends AB_Entity {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->table_name = 'ab_customer';
        $this->schema = array(
            'id'      => array( ),
            'name'    => array( 'format' => '%s', 'default' => '' ),
            'phone'   => array( 'format' => '%s', 'default' => '' ),
            'email'   => array( 'format' => '%s', 'default' => '' ),
            'notes'   => array( 'format' => '%s', 'default' => '' ),
        );
        parent::__construct();
    }

    public function loadByEmail($email) {
        $row = $this->wpdb->get_row( sprintf( 'SELECT * FROM %s WHERE email = %d', 'ab_customer', $email ) );

        if ( $row ) {
            $this->setData( $row );
            $this->loaded = true;
        } else {
            $this->loaded = false;
        }

        return $this->loaded;
    }
}