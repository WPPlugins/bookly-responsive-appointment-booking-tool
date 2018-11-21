<?php
namespace Bookly\Backend\Modules\Staff\Forms;

/**
 * Class StaffMemberEdit
 * @package Bookly\Backend\Modules\Staff\Forms
 */
class StaffMemberEdit extends StaffMember
{
    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setFields( array(
            'wp_user_id',
            'category_id',
            'working_time_limit',
            'full_name',
            'email',
            'phone',
            'attachment_id',
            'info',
            'visibility',
            'position',
        ) );
    }
}
