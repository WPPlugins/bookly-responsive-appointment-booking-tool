<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_StaffMemberNewForm
 */
class AB_StaffMemberNewForm extends AB_StaffMemberForm {

    public function configure()
    {
        $this->setFields( array( 'wp_user_id', 'full_name' ) );
    }
}
