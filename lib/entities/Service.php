<?php
namespace BooklyLite\Lib\Entities;

use BooklyLite\Lib;

/**
 * Class Service
 * @package BooklyLite\Lib\Entities
 */
class Service extends Lib\Base\Entity
{
    const TYPE_SIMPLE   = 'simple';
    const TYPE_COMPOUND = 'compound';
    const TYPE_PACKAGE  = 'package';

    const PREFERRED_ORDER           = 'order';
    const PREFERRED_LEAST_OCCUPIED  = 'least_occupied';
    const PREFERRED_MOST_OCCUPIED   = 'most_occupied';
    const PREFERRED_LEAST_EXPENSIVE = 'least_expensive';
    const PREFERRED_MOST_EXPENSIVE  = 'most_expensive';

    /** @var  int */
    protected $category_id;
    /** @var  string */
    protected $title;
    /** @var  int */
    protected $duration = 900;
    /** @var  float */
    protected $price = 0;
    /** @var  string */
    protected $color;
    /** @var  int */
    protected $capacity_min = 1;
    /** @var  int */
    protected $capacity_max = 1;
    /** @var  int */
    protected $padding_left = 0;
    /** @var  int */
    protected $padding_right = 0;
    /** @var  string */
    protected $info;
    /** @var  string */
    protected $start_time_info;
    /** @var  string */
    protected $end_time_info;
    /** @var  string */
    protected $type = 'simple';
    /** @var  int */
    protected $package_life_time;
    /** @var  int */
    protected $package_size;
    /** @var  int */
    protected $appointments_limit;
    /** @var  string */
    protected $limit_period = 'off';
    /** @var  string */
    protected $staff_preference = Service::PREFERRED_MOST_EXPENSIVE;
    /** @var  bool */
    protected $recurrence_enabled = 1;
    /** @var  string */
    protected $recurrence_frequencies = 'daily,weekly,biweekly,monthly';
    /** @var  string */
    protected $visibility = 'public';
    /** @var  int */
    protected $position = 9999;


    protected static $table = 'ab_services';

    protected static $schema = array(
        'id'                 => array( 'format' => '%d' ),
        'category_id'        => array( 'format' => '%d', 'reference' => array( 'entity' => 'Category' ) ),
        'title'              => array( 'format' => '%s' ),
        'duration'           => array( 'format' => '%d' ),
        'price'              => array( 'format' => '%f' ),
        'color'              => array( 'format' => '%s' ),
        'capacity_min'       => array( 'format' => '%d' ),
        'capacity_max'       => array( 'format' => '%d' ),
        'padding_left'       => array( 'format' => '%d' ),
        'padding_right'      => array( 'format' => '%d' ),
        'info'               => array( 'format' => '%s' ),
        'start_time_info'    => array( 'format' => '%s' ),
        'end_time_info'      => array( 'format' => '%s' ),
        'type'               => array( 'format' => '%s' ),
        'package_life_time'  => array( 'format' => '%d' ),
        'package_size'       => array( 'format' => '%d' ),
        'appointments_limit' => array( 'format' => '%d' ),
        'limit_period'       => array( 'format' => '%s' ),
        'staff_preference'   => array( 'format' => '%s' ),
        'recurrence_enabled' => array( 'format' => '%d' ),
        'recurrence_frequencies' => array( 'format' => '%s' ),
        'visibility'         => array( 'format' => '%s' ),
        'position'           => array( 'format' => '%d' ),
    );

    /**
     * Get translated title (if empty returns "Untitled").
     *
     * @param string $locale
     * @return string
     */
    public function getTranslatedTitle( $locale = null )
    {
        return $this->getTitle() != ''
            ? Lib\Utils\Common::getTranslatedString( 'service_' . $this->getId(), $this->getTitle(), $locale )
            : __( 'Untitled', 'bookly' );
    }

    /**
     * Get category name.
     *
     * @param string $locale
     * @return string
     */
    public function getTranslatedCategoryName( $locale = null )
    {
        if ( $this->getCategoryId() ) {
            return Category::find( $this->getCategoryId() )->getTranslatedName( $locale );
        }

        return __( 'Uncategorized', 'bookly' );
    }

    /**
     * Get translated info.
     *
     * @param string $locale
     * @return string
     */
    public function getTranslatedInfo( $locale = null )
    {
        return Lib\Utils\Common::getTranslatedString( 'service_' . $this->getId() . '_info', $this->getInfo(), $locale );
    }

    /**
     * Get sub services.
     *
     * @return Service[]
     */
    public function getSubServices()
    {
        return Service::query( 's' )
            ->select( 's.*' )
            ->innerJoin( 'SubService', 'ss', 'ss.sub_service_id = s.id' )
            ->where( 'ss.service_id', $this->getId() )
            ->sortBy( 'ss.position' )
            ->find();
    }

    /**
     * Check if given customer has reached the appointments limit for this service.
     *
     * @param int $customer_id
     * @param string $appointment_date format( 'Y-m-d H:i:s' )
     * @return bool
     */
    public function appointmentsLimitReached( $customer_id, $appointment_date )
    {
        if ( $this->getLimitPeriod() != 'off' && $this->getAppointmentsLimit() > 0 ) {
            $bound_start = date_create( $appointment_date )->modify( sprintf( '-1 %s', $this->getLimitPeriod() ) )->format( 'Y-m-d H:i:s' );
            $bound_end   = $appointment_date;
            if ( $this->isCompound() ) {
                // Compound service.
                $sub_services        = $this->getSubServices();
                $compound_service_id = $this->getId();
                $service_id          = $sub_services[0]->getId();
            } else {
                // Simple service.
                $compound_service_id = null;
                $service_id          = $this->getId();
            }
            $count = CustomerAppointment::query( 'ca' )
                ->leftJoin( 'Appointment', 'a', 'ca.appointment_id = a.id' )
                ->where( 'a.service_id', $service_id )
                ->where( 'ca.compound_service_id', $compound_service_id )
                ->where( 'ca.customer_id', $customer_id )
                ->whereGt( 'a.start_date', $bound_start )
                ->whereLt( 'a.start_date', $bound_end )
                ->whereNot( 'ca.status', CustomerAppointment::STATUS_WAITLISTED )
                ->count();
            if ( $count >= $this->getAppointmentsLimit() ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if service is a compound service.
     *
     * @return bool
     */
    public function isCompound()
    {
        return $this->getType() == self::TYPE_COMPOUND;
    }

    /**
     * Check if service is a package.
     *
     * @return bool
     */
    public function isPackage()
    {
        return $this->getType() == self::TYPE_PACKAGE;
    }

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

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
     * Sets category
     *
     * @param Lib\Entities\Category $category
     * @return $this
     */
    public function setCategory( Lib\Entities\Category $category )
    {
        return $this->setCategoryId( $category->getId() );
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
     * Gets title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle( $title )
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Gets duration
     *
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Sets duration
     *
     * @param int $duration
     * @return $this
     */
    public function setDuration( $duration )
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Gets price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Sets price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice( $price )
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Gets color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Sets color
     *
     * @param string $color
     * @return $this
     */
    public function setColor( $color )
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Gets capacity_min
     *
     * @return int
     */
    public function getCapacityMin()
    {
        return $this->capacity_min;
    }

    /**
     * Sets capacity_min
     *
     * @param int $capacity_min
     * @return $this
     */
    public function setCapacityMin( $capacity_min )
    {
        $this->capacity_min = $capacity_min;

        return $this;
    }

    /**
     * Gets capacity_max
     *
     * @return int
     */
    public function getCapacityMax()
    {
        return $this->capacity_max;
    }

    /**
     * Sets capacity_max
     *
     * @param int $capacity_max
     * @return $this
     */
    public function setCapacityMax( $capacity_max )
    {
        $this->capacity_max = $capacity_max;

        return $this;
    }

    /**
     * Gets padding_left
     *
     * @return int
     */
    public function getPaddingLeft()
    {
        return $this->padding_left;
    }

    /**
     * Sets padding_left
     *
     * @param int $padding_left
     * @return $this
     */
    public function setPaddingLeft( $padding_left )
    {
        $this->padding_left = $padding_left;

        return $this;
    }

    /**
     * Gets padding_right
     *
     * @return int
     */
    public function getPaddingRight()
    {
        return $this->padding_right;
    }

    /**
     * Sets padding_right
     *
     * @param int $padding_right
     * @return $this
     */
    public function setPaddingRight( $padding_right )
    {
        $this->padding_right = $padding_right;

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
     * Gets start time info
     *
     * @return string
     */
    public function getStartTimeInfo()
    {
        return $this->start_time_info;
    }

    /**
     * Sets start time info
     *
     * @param string $start_time_info
     * @return $this
     */
    public function setStartTimeInfo( $start_time_info )
    {
        $this->start_time_info = $start_time_info;

        return $this;
    }

    /**
     * Gets end time info
     *
     * @return string
     */
    public function getEndTimeInfo()
    {
        return $this->end_time_info;
    }

    /**
     * Sets end time info
     *
     * @param string $end_time_info
     * @return $this
     */
    public function setEndTimeInfo( $end_time_info )
    {
        $this->end_time_info = $end_time_info;

        return $this;
    }

    /**
     * Gets type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets type
     *
     * @param string $type
     * @return $this
     */
    public function setType( $type )
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets package_life_time
     *
     * @return int
     */
    public function getPackageLifeTime()
    {
        return $this->package_life_time;
    }

    /**
     * Sets package_life_time
     *
     * @param int $package_life_time
     * @return $this
     */
    public function setPackageLifeTime( $package_life_time )
    {
        $this->package_life_time = $package_life_time;

        return $this;
    }

    /**
     * Gets package_size
     *
     * @return int
     */
    public function getPackageSize()
    {
        return $this->package_size;
    }

    /**
     * Sets package_size
     *
     * @param int $package_size
     * @return $this
     */
    public function setPackageSize( $package_size )
    {
        $this->package_size = $package_size;

        return $this;
    }

    /**
     * Gets appointments_limit
     *
     * @return int
     */
    public function getAppointmentsLimit()
    {
        return $this->appointments_limit;
    }

    /**
     * Sets appointments_limit
     *
     * @param int $appointments_limit
     * @return $this
     */
    public function setAppointmentsLimit( $appointments_limit )
    {
        $this->appointments_limit = $appointments_limit;

        return $this;
    }

    /**
     * Gets limit_period
     *
     * @return string
     */
    public function getLimitPeriod()
    {
        return $this->limit_period;
    }

    /**
     * Sets limit_period
     *
     * @param string $limit_period
     * @return $this
     */
    public function setLimitPeriod( $limit_period )
    {
        $this->limit_period = $limit_period;

        return $this;
    }

    /**
     * Gets staff_preference
     *
     * @return string
     */
    public function getStaffPreference()
    {
        return $this->staff_preference;
    }

    /**
     * Sets staff_preference
     *
     * @param string $staff_preference
     * @return $this
     */
    public function setStaffPreference( $staff_preference )
    {
        $this->staff_preference = $staff_preference;

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
     * Gets recurrence_enabled
     *
     * @return string
     */
    public function getRecurrenceEnabled()
    {
        return $this->recurrence_enabled;
    }

    /**
     * Sets recurrence_enabled
     *
     * @param string $recurrence_enabled
     * @return $this
     */
    public function setRecurrenceEnabled( $recurrence_enabled )
    {
        $this->recurrence_enabled = $recurrence_enabled;

        return $this;
    }

    /**
     * Gets recurrence_frequencies
     *
     * @return string
     */
    public function getRecurrenceFrequencies()
    {
        return $this->recurrence_frequencies;
    }

    /**
     * Sets recurrence_frequencies
     *
     * @param string $recurrence_frequencies
     * @return $this
     */
    public function setRecurrenceFrequencies( $recurrence_frequencies )
    {
        $this->recurrence_frequencies = $recurrence_frequencies;

        return $this;
    }

    /**************************************************************************
     * Overridden Methods                                                     *
     **************************************************************************/

    /**
     * Save service.
     *
     * @return false|int
     */
    public function save()
    {
        if ( is_array( $this->recurrence_frequencies ) ) {
            $this->recurrence_frequencies = implode( ',', $this->recurrence_frequencies );
        }

        $return = parent::save();
        if ( $this->isLoaded() ) {
            // Register string for translate in WPML.
            do_action( 'wpml_register_single_string', 'bookly', 'service_' . $this->getId(), $this->getTitle() );
            do_action( 'wpml_register_single_string', 'bookly', 'service_' . $this->getId() . '_info', $this->getInfo() );
        }

        return $return;
    }

    /**
     * Delete service
     *
     * @return bool|int
     */
    public function delete()
    {
        Lib\Proxy\Shared::serviceDeleted( $this->getId() );

        return parent::delete();
    }

}