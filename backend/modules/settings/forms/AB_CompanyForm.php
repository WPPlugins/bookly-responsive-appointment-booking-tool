<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_CompanyForm extends AB_Form {

    public function __construct() {
        $this->setFields(array(
           'ab_settings_company_name',
           'ab_settings_company_logo',
           'ab_settings_company_address',
           'ab_settings_company_phone',
           'ab_settings_company_website',
           'ab_settings_company_logo_path',
           'ab_settings_company_logo_url',
        ));
    }

    /**
     * @param array $post
     * @param array $files
     */
    public function bind( array $post, array $files = array() ) {

        parent::bind( $post, $files );

        // remove the old image
        if ( isset( $post['ab_remove_logo'] ) && file_exists( get_option( 'ab_settings_company_logo_path' ) ) ) {
            unlink( get_option( 'ab_settings_company_logo_path' ) );
            update_option( 'ab_settings_company_logo_path', '' );
            update_option( 'ab_settings_company_logo_url', '' );
        }

        // and add new
        if ( isset ($files['ab_settings_company_logo']) && $files['ab_settings_company_logo']['tmp_name'] ) {

            if ( in_array( $files['ab_settings_company_logo']['type'], array( "image/gif", "image/jpeg", "image/png" ) ) ) {
                $movefile = wp_handle_upload( $files['ab_settings_company_logo'], array( 'test_form' => false ) );
                if ( $movefile ) {
                    $imageResize = new AB_ImageResize($movefile['file']);
                    $imageResize->resizeImage( 150, 150 );
                    $imageResize->saveImage($movefile['file']);

                    $this->data['ab_settings_company_logo_path'] = $movefile['file'];
                    $this->data['ab_settings_company_logo_url'] = $movefile['url'];

                    // remove the old image
                    if ( file_exists( get_option( 'ab_settings_company_logo_path' ) ) ) {
                        unlink( get_option( 'ab_settings_company_logo_path' ) );
                    }
                }
            }
        }
    }

    public function save() {
        foreach ( $this->data as $field => $value ) {
            update_option( $field, $value );
        }
    }
}