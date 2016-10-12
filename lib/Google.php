<?php
namespace BooklyLite\Lib;

/**
 * Class Google
 *
 * @package BooklyLite\Lib
 */
class Google
{
    const EVENTS_PER_REQUEST = 250;

    /** @var \Google_Client */
    private $client;

    /** @var \Google_Service_Calendar */
    private $service;

    /** @var \Google_Service_Calendar_CalendarListEntry */
    private $calendar;

    /** @var \Google_Service_Calendar_Event */
    private $event;

    /** @var \BooklyLite\Lib\Entities\Staff */
    private $staff;

    private $errors = array();

    public function __construct()
    {
    }

    /**
     * Load Google and Calendar Service data by Staff
     *
     * @param Entities\Staff $staff
     * @return bool
     */
    public function loadByStaff( Entities\Staff $staff )
    {
        return false;
    }

    /**
     * Load Google and Calendar Service data by Staff ID
     *
     * @param int $staff_id
     * @return bool
     */
    public function loadByStaffId( $staff_id )
    {
        $staff = Entities\Staff::find( 1 );

        return $this->loadByStaff( $staff );
    }

    /**
     * Create Event and return id
     *
     * @param Entities\Appointment $appointment
     * @return mixed
     */
    public function createEvent( Entities\Appointment $appointment )
    {
        return false;
    }

    /**
     * Update event
     *
     * @param Entities\Appointment $appointment
     * @return bool
     */
    public function updateEvent( Entities\Appointment $appointment )
    {
        return false;
    }

    /**
     * Get list of Google Calendars.
     *
     * @return array
     */
    public function getCalendarList()
    {
        $result = array();

        return $result;
    }

    /**
     * Returns a collection of Google calendar events
     *
     * @param \DateTime $startDate
     * @return array|false
     */
    public function getCalendarEvents( \DateTime $startDate )
    {
        return false;
    }

    /**
     * @param $code
     * @return bool
     */
    public function authCodeHandler( $code )
    {
        return false;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return null;
    }

    /**
     * Log out from Google Calendar.
     */
    public function logout()
    {
        $this->staff->set( 'google_data', null );
        $this->staff->set( 'google_calendar_id', null );
        $this->staff->save();
    }

    /**
     * @param $staff_id
     * @return string
     */
    public function createAuthUrl( $staff_id )
    {

        return null;
    }

    /**
     * Delete event by id
     *
     * @param $event_id
     * @return bool
     */
    public function delete( $event_id )
    {
        return true;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return string
     */
    private function getCalendarID()
    {
        return $this->staff->get( 'google_calendar_id' ) ?: 'primary';
    }

    /**
     * @return string [freeBusyReader, reader, writer, owner]
     */
    private function getCalendarAccess()
    {
        return 'reader';
    }

    /**
     * Validate calendar
     *
     * @param null $calendar_id (send this parameter on unsaved form)
     * @return bool
     */
    public function validateCalendar( $calendar_id = null )
    {
        return false;
    }

    /**
     * @return string
     */
    public static function generateRedirectURI()
    {
        return admin_url( 'admin.php?page=' . \BooklyLite\Backend\Modules\Staff\Controller::page_slug );
    }

}