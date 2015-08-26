<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! function_exists( 'wp_handle_upload' ) ) { require_once( ABSPATH . 'wp-admin/includes/file.php' ); }

class AB_StaffMemberEditForm extends AB_StaffMemberForm {

    private $errors = array();

    public function configure()
    {
        $this->setFields( array(
            'wp_user_id',
            'full_name',
            'email',
            'phone',
            'avatar',
            'position',
        ) );
    }

    /**
     * @param array $post
     * @param array $files
     */
    public function bind( array $post, array $files = array() )
    {
        parent::bind( $post );

        if ( isset ( $files['avatar'] ) && $files['avatar']['tmp_name'] ) {

            if ( in_array( $files['avatar']['type'], array( 'image/gif', 'image/jpeg', 'image/png' ) ) ) {
                $uploaded = wp_handle_upload( $files['avatar'], array( 'test_form' => false ) );
                if ( $uploaded ) {
                    $editor = wp_get_image_editor( $uploaded['file'] );
                    $editor->resize( 200, 200 );
                    $editor->save( $uploaded['file'] );

                    $this->data['avatar_path'] = $uploaded['file'];
                    $this->data['avatar_url']  = $uploaded['url'];

                    // Remove old image.
                    $staff = new AB_Staff();
                    $staff->load( $post['id'] );
                    if ( file_exists( $staff->get( 'avatar_path' ) ) ) {
                        unlink( $staff->get( 'avatar_path' ) );
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}
