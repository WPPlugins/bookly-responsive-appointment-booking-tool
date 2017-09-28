<?php
namespace BooklyLite\Backend\Modules\Staff\Forms;

/**
 * Class StaffMemberNew
 * @package BooklyLite\Backend\Modules\Staff\Forms
 */
class StaffMemberNew extends StaffMember
{
    public function configure()
    {
        $this->setFields( array( 'wp_user_id', 'full_name' ) );
    }

}