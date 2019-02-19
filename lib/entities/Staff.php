<?php
namespace Bookly\Lib\Entities;

use Bookly\Lib;

/**
 * Class Staff
 * @package Bookly\Lib\Entities
 */
class Staff extends Lib\Base\Entity
{
    /** @var  integer */
    protected $wp_user_id;
    /** @var  int */
    protected $category_id;
    /** @var  integer */
    protected $attachment_id;
    /** @var  string */
    protected $full_name;
    /** @var  string */
    protected $email;
    /** @var  string */
    protected $phone;
    /** @var  string */
    protected $info;
    /** @var  int */
    protected $working_time_limit;
    /** @var  string */
    protected $visibility = 'public';
    /** @var  int */
    protected $position = 9999;
    /** @var  string */
    protected $google_data;
    /** @var  string */
    protected $outlook_data;

    protected static $table = 'bookly_staff';

    protected static $schema = array(
        'id'                 => array( 'format' => '%d' ),
        'wp_user_id'         => array( 'format' => '%d' ),
        'category_id'        => array( 'format' => '%d' ),
        'attachment_id'      => array( 'format' => '%d' ),
        'full_name'          => array( 'format' => '%s' ),
        'email'              => array( 'format' => '%s' ),
        'phone'              => array( 'format' => '%s' ),
        'info'               => array( 'format' => '%s' ),
        'working_time_limit' => array( 'format' => '%d' ),
        'visibility'         => array( 'format' => '%s' ),
        'position'           => array( 'format' => '%d' ),
        'google_data'        => array( 'format' => '%s' ),
        'outlook_data'       => array( 'format' => '%s' ),
    );

    /**
     * Get schedule items of staff member.
     *
     * @param int $location_id
     * @return StaffScheduleItem[]
     */
    public function getScheduleItems( $location_id = null )
    {
        $start_of_week = (int) get_option( 'start_of_week' );
        // Start of week affects the sorting.
        // If it is 0(Sun) then the result should be 1,2,3,4,5,6,7.
        // If it is 1(Mon) then the result should be 2,3,4,5,6,7,1.
        // If it is 2(Tue) then the result should be 3,4,5,6,7,1,2. Etc.
        $query = StaffScheduleItem::query()
            ->where( 'staff_id',  $this->getId() )
            ->sortBy( "IF(r.day_index + 10 - {$start_of_week} > 10, r.day_index + 10 - {$start_of_week}, 16 + r.day_index)" )
            ->indexBy( 'day_index' );
        $query = Lib\Proxy\Locations::prepareStaffScheduleQuery(
            $query,
            $location_id,
            $this->getId()
        );
        return $query->find();
    }

    /**
     * Get StaffService entities associated with this staff member.
     *
     * @param $type
     * @return StaffService[]
     */
    public function getStaffServices( $type = Service::TYPE_SIMPLE )
    {
        $result = array();

        if ( $this->getId() ) {
            $query = StaffService::query( 'ss' )
                ->select( 'ss.*, s.title, s.duration, s.units_min, s.units_max, s.price AS service_price, s.color, ss.location_id' )
                ->addSelect( sprintf( '%s AS service_capacity_min, %s AS service_capacity_max',
                    Lib\Proxy\Shared::prepareStatement( 1, 's.capacity_min', 'Service' ),
                    Lib\Proxy\Shared::prepareStatement( 1, 's.capacity_max', 'Service' )
                ) )
                ->leftJoin( 'Service', 's', 's.id = ss.service_id' )
                ->where( 'ss.staff_id', $this->getId() )
                ->where( 's.type', $type );
            if ( ! Lib\Proxy\Locations::servicesPerLocationAllowed() ) {
                $query->where( 'ss.location_id', null );
            }
            $staff_services = $query->fetchArray();

            foreach ( $staff_services as $data ) {
                $ss = new StaffService( $data );

                // Inject Service entity.
                $ss->service = new Service();
                $ss->service
                    ->setId( $data['service_id'] )
                    ->setTitle( $data['title'] )
                    ->setColor( $data['color'] )
                    ->setDuration( $data['duration'] )
                    ->setPrice( $data['service_price'] )
                    ->setUnitsMin( $data['units_min'] )
                    ->setUnitsMax( $data['units_max'] )
                    ->setCapacityMin( $data['service_capacity_min'] )
                    ->setCapacityMax( $data['service_capacity_max'] );

                $result[] = $ss;
            }
        }

        return $result;
    }

    /**
     * Check whether staff is archived or not.
     * @return bool
     */
    public function isArchived()
    {
        return $this->getVisibility() == 'archive';
    }

    /**
     * Check whether staff is on holiday on given day.
     *
     * @param \DateTime $day
     * @return bool
     */
    public function isOnHoliday( \DateTime $day )
    {
        $query = Holiday::query()
            ->whereRaw( '( DATE_FORMAT( date, %s ) = %s AND repeat_event = 1 ) OR date = %s', array( '%m-%d', $day->format( 'm-d' ), $day->format( 'Y-m-d' ) ) )
            ->whereRaw( 'staff_id = %d OR staff_id IS NULL', array( $this->getId() ) )
            ->limit( 1 );
        $rows = $query->execute( Lib\Query::HYDRATE_NONE );

        return $rows != 0;
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getTranslatedName( $locale = null )
    {
        return Lib\Utils\Common::getTranslatedString( 'staff_' . $this->getId(), $this->getFullName(), $locale );
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getTranslatedInfo( $locale = null )
    {
        return Lib\Utils\Common::getTranslatedString( 'staff_' . $this->getId() . '_info', $this->getInfo(), $locale );
    }

    /**
     * Get workload for given date.
     *
     * @param string $date 'Y-m-d'
     * @param array  $exclude list of appointment id's to exclude
     * @return int
     */
    public function getWorkload( $date, $exclude = array() )
    {
        $start_date   = $date . ' 00:00:00';
        $end_date     = date_create( $date )->modify( '+1 day' )->format( 'Y-m-d H:i:s' );
        $appointments = Lib\Entities\CustomerAppointment::query( 'ca' )
            ->select( 'a.start_date, DATE_ADD(`a`.`end_date`, INTERVAL `a`.`extras_duration` SECOND) as end_date' )
            ->leftJoin( 'Appointment', 'a', 'a.id = ca.appointment_id' )
            ->where( 'a.staff_id', $this->getId() )
            ->whereLt( 'a.start_date', $end_date )
            ->whereGt( 'a.end_date', $start_date )
            ->whereIn( 'ca.status', Lib\Proxy\CustomStatuses::prepareBusyStatuses( array(
                Lib\Entities\CustomerAppointment::STATUS_PENDING,
                Lib\Entities\CustomerAppointment::STATUS_APPROVED,
                Lib\Entities\CustomerAppointment::STATUS_WAITLISTED,
            ) ) )
            ->whereNotIn( 'a.id', $exclude )
            ->fetchArray();

        $workload = 0;
        foreach ( $appointments as $appointment ) {
            $workload += min( strtotime( $end_date ), strtotime( $appointment['end_date'] ) ) - max( strtotime( $start_date ), strtotime( $appointment['start_date'] ) );
        }

        return $workload;
    }

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Gets wp_user_id
     *
     * @return int
     */
    public function getWpUserId()
    {
        return $this->wp_user_id;
    }

    /**
     * Sets wp_user_id
     *
     * @param int $wp_user_id
     * @return $this
     */
    public function setWpUserId( $wp_user_id )
    {
        $this->wp_user_id = $wp_user_id;

        return $this;
    }

    /**
     * Gets category_id
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->category_id;
    }

    /**
     * Sets category_id
     *
     * @param int $category_id
     * @return $this
     */
    public function setCategoryId( $category_id )
    {
        $this->category_id = $category_id;

        return $this;
    }

    /**
     * Gets attachment_id
     *
     * @return int
     */
    public function getAttachmentId()
    {
        return $this->attachment_id;
    }

    /**
     * Sets attachment_id
     *
     * @param int $attachment_id
     * @return $this
     */
    public function setAttachmentId( $attachment_id )
    {
        $this->attachment_id = $attachment_id;

        return $this;
    }

    /**
     * Gets full name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->full_name;
    }

    /**
     * Sets full name
     *
     * @param string $full_name
     * @return $this
     */
    public function setFullName( $full_name )
    {
        $this->full_name = $full_name;

        return $this;
    }

    /**
     * Gets email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail( $email )
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Gets phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Sets phone
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone( $phone )
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Gets info
     *
     * @return string
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Sets info
     *
     * @param string $info
     * @return $this
     */
    public function setInfo( $info )
    {
        $this->info = $info;

        return $this;
    }

    /**
     * Gets working_time_limit
     *
     * @return int
     */
    public function getWorkingTimeLimit()
    {
        return $this->working_time_limit;
    }

    /**
     * Sets working_time_limit
     *
     * @param int $working_time_limit
     * @return $this
     */
    public function setWorkingTimeLimit( $working_time_limit )
    {
        $this->working_time_limit = $working_time_limit;

        return $this;
    }

    /**
     * Gets visibility
     *
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Sets visibility
     *
     * @param string $visibility
     * @return $this
     */
    public function setVisibility( $visibility )
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Gets position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Sets position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition( $position )
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Gets Google data
     *
     * @return string
     */
    public function getGoogleData()
    {
        return $this->google_data;
    }

    /**
     * Sets Google data
     *
     * @param string $google_data
     * @return $this
     */
    public function setGoogleData( $google_data )
    {
        $this->google_data = $google_data;

        return $this;
    }

    /**
     * Gets Microsoft data
     *
     * @return string
     */
    public function getOutlookData()
    {
        return $this->outlook_data;
    }

    /**
     * Sets Microsoft data
     *
     * @param string $outlook_data
     * @return $this
     */
    public function setOutlookData($outlook_data )
    {
        $this->outlook_data = $outlook_data;

        return $this;
    }

    /**************************************************************************
     * Overridden Methods                                                     *
     **************************************************************************/

    /**
     * Delete staff member.
     */
    public function delete()
    {
        Lib\Proxy\Pro::revokeGoogleCalendarToken( $this );

        parent::delete();
    }

    /**
     * @return false|int
     */
    public function save()
    {
        $is_new = ! $this->getId();

        if ( $is_new && $this->getWpUserId() ) {
            $user = get_user_by( 'id', $this->getWpUserId() );
            if ( $user ) {
                $this->setEmail( $user->get( 'user_email' ) );
            }
        }

        $saved = parent::save();

        if ( $saved ) {
            // Register string for translate in WPML.
            do_action( 'wpml_register_single_string', 'bookly', 'staff_' . $this->getId(), $this->getFullName() );
            do_action( 'wpml_register_single_string', 'bookly', 'staff_' . $this->getId() . '_info', $this->getInfo() );

            if ( $is_new ) {
                // Schedule items.
                $staff_id = $this->getId();
                foreach ( array( 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' ) as $day_index => $week_day ) {
                    $item = new StaffScheduleItem();
                    $item
                        ->setStaffId( $staff_id )
                        ->setDayIndex( $day_index + 1  )
                        ->setStartTime( get_option( 'bookly_bh_' . $week_day . '_start' ) ?: null )
                        ->setEndTime( get_option( 'bookly_bh_' . $week_day . '_end' ) ?: null )
                        ->save();
                }

                // Create holidays for staff
                self::$wpdb->query( sprintf(
                    'INSERT INTO `' . Holiday::getTableName(). '` (`parent_id`, `staff_id`, `date`, `repeat_event`)
                SELECT `id`, %d, `date`, `repeat_event` FROM `' . Holiday::getTableName() . '` WHERE `staff_id` IS NULL',
                    $staff_id
                ) );
            }
        }

        return $saved;
    }

}