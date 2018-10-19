<?php
namespace Bookly\Backend\Modules\Staff\Forms;

/**
 * Class StaffMemberNew
 * @package Bookly\Backend\Modules\Staff\Forms
 */
class StaffMemberNew extends StaffMember
{
    public function configure()
    {
        $this->setFields( array( 'wp_user_id', 'full_name' ) );
    }

}