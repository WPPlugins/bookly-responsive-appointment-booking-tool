<?php
namespace BooklyLite\Backend\Modules\Staff\Forms;

use BooklyLite\Lib;

/**
 * Class StaffMemberEdit
 * @package BooklyLite\Backend\Modules\Staff\Forms
 */
class StaffMemberEdit extends StaffMember
{
    private $errors = array();

    public function configure()
    {
        $this->setFields( array(
            'wp_user_id',
            'full_name',
            'email',
            'phone',
            'attachment_id',
            'google_calendar_id',
            'position',
            'info',
            'visibility',
        ) );
    }

    /**
     * @return Lib\Entities\Staff|false
     */
    public function save()
    {
        // Verify google calendar.
        if ( array_key_exists( 'google_calendar_id', $this->data ) && $this->data['google_calendar_id'] != '' ) {
            $google = new Lib\Google();
            if ( ! $google->loadByStaffId( $this->data['id'] ) || ! $google->validateCalendar( $this->data['google_calendar_id'] ) ) {
                $this->errors['google_calendar_error'] = implode( '<br>', $google->getErrors() );

                return false;
            }
        }

        return parent::save();
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}
