<?php
namespace BooklyLite\Lib\Entities;

use BooklyLite\Lib;

/**
 * Class Staff
 * @package BooklyLite\Lib\Entities
 */
class Staff extends Lib\Base\Entity
{
    /** @var  integer */
    protected $wp_user_id;
    /** @var  integer */
    protected $attachment_id;
    /** @var  string */
    protected $full_name;
    /** @var  string */
    protected $email;
    /** @var  string */
    protected $phone;
    /** @var  string */
    protected $google_data;
    /** @var  string */
    protected $google_calendar_id;
    /** @var  string */
    protected $info;
    /** @var  string */
    protected $visibility = 'public';
    /** @var  int */
    protected $position = 9999;

    protected static $table = 'ab_staff';

    protected static $schema = array(
        'id'                 => array( 'format' => '%d' ),
        'wp_user_id'         => array( 'format' => '%d' ),
        'attachment_id'      => array( 'format' => '%d' ),
        'full_name'          => array( 'format' => '%s' ),
        'email'              => array( 'format' => '%s' ),
        'phone'              => array( 'format' => '%s' ),
        'google_data'        => array( 'format' => '%s' ),
        'google_calendar_id' => array( 'format' => '%s' ),
        'info'               => array( 'format' => '%s' ),
        'visibility'         => array( 'format' => '%s' ),
        'position'           => array( 'format' => '%d' ),
    );

    /**
     * Get schedule items of staff member.
     *
     * @return StaffScheduleItem[]
     */
    public function getScheduleItems()
    {
        $start_of_week = (int) get_option( 'start_of_week' );
        // Start of week affects the sorting.
        // If it is 0(Sun) then the result should be 1,2,3,4,5,6,7.
        // If it is 1(Mon) then the result should be 2,3,4,5,6,7,1.
        // If it is 2(Tue) then the result should be 3,4,5,6,7,1,2. Etc.
        return StaffScheduleItem::query()
            ->where( 'staff_id',  1 )
            ->sortBy( "IF(r.day_index + 10 - {$start_of_week} > 10, r.day_index + 10 - {$start_of_week}, 16 + r.day_index)" )
            ->indexBy( 'day_index' )
            ->find();
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
            $staff_services = StaffService::query( 'ss' )
                ->select( 'ss.*, s.title, s.duration, s.price AS service_price, s.color, s.capacity_min AS service_capacity_min, s.capacity_max AS service_capacity_max' )
                ->leftJoin( 'Service', 's', 's.id = ss.service_id' )
                ->where( 'ss.staff_id', 1 )
                ->where( 's.type', $type )
                ->fetchArray();

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
                    ->setCapacityMin( $data['service_capacity_min'] )
                    ->setCapacityMax( $data['service_capacity_max'] );

                $result[] = $ss;
            }
        }

        return $result;
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
            ->whereRaw( 'staff_id = %d OR staff_id IS NULL', array( 1 ) )
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
        return Lib\Utils\Common::getTranslatedString( 'staff_1', $this->getFullName(), $locale );
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getTranslatedInfo( $locale = null )
    {
        return Lib\Utils\Common::getTranslatedString( 'staff_1_info', $this->getInfo(), $locale );
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
     * Gets google data
     *
     * @return string
     */
    public function getGoogleData()
    {
        return $this->google_data;
    }

    /**
     * Sets google data
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
     * Gets google calendar_id
     *
     * @return string
     */
    public function getGoogleCalendarId()
    {
        return $this->google_calendar_id;
    }

    /**
     * Sets google calendar_id
     *
     * @param string $google_calendar_id
     * @return $this
     */
    public function setGoogleCalendarId( $google_calendar_id )
    {
        $this->google_calendar_id = $google_calendar_id;

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

    /**************************************************************************
     * Overridden Methods                                                     *
     **************************************************************************/

    /**
     * Delete staff member.
     */
    public function delete(){}

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

        $return = parent::save();
        if ( $this->isLoaded() ) {
            // Register string for translate in WPML.
            do_action( 'wpml_register_single_string', 'bookly', 'staff_' . $this->getId(), $this->getFullName() );
            do_action( 'wpml_register_single_string', 'bookly', 'staff_' . $this->getId() . '_info', $this->getInfo() );
        }
        if ( $is_new ) {
            // Schedule items.
            $staff_id = $this->getId();
            foreach ( array( 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' ) as $day_index => $week_day ) {
                $item = new StaffScheduleItem();
                $item->setStaffId( $staff_id )
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

        return $return;
    }

}