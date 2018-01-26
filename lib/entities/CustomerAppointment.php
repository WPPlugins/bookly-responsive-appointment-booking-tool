<?php
namespace BooklyLite\Lib\Entities;

use BooklyLite\Lib;
use BooklyLite\Lib\DataHolders\Booking as DataHolders;

/**
 * Class CustomerAppointment
 * @package BooklyLite\Lib\Entities
 */
class CustomerAppointment extends Lib\Base\Entity
{
    const STATUS_PENDING    = 'pending';
    const STATUS_APPROVED   = 'approved';
    const STATUS_CANCELLED  = 'cancelled';
    const STATUS_REJECTED   = 'rejected';
    const STATUS_WAITLISTED = 'waitlisted';

    /** @var  int */
    protected $package_id;
    /** @var  int */
    protected $customer_id;
    /** @var  int */
    protected $appointment_id;
    /** @var  int */
    protected $payment_id;
    /** @var  int */
    protected $number_of_persons = 1;
    /** @var  string */
    protected $notes;
    /** @var  string */
    protected $extras = '[]';
    /** @var  string */
    protected $custom_fields = '[]';
    /** @var  string self::STATUS_* */
    protected $status;
    /** @var  string Y-m-d H:i:s */
    protected $status_changed_at;
    /** @var  string */
    protected $token;
    /** @var  string */
    protected $time_zone;
    /** @var  int */
    protected $time_zone_offset;
    /** @var  string */
    protected $locale;
    /** @var  int */
    protected $compound_service_id;
    /** @var  string */
    protected $compound_token;
    /** @var  string */
    protected $created_from;
    /** @var  string */
    protected $created;

    protected static $table = 'ab_customer_appointments';

    protected static $schema = array(
        'id'                  => array( 'format' => '%d' ),
        'package_id'          => array( 'format' => '%d' ),
        'customer_id'         => array( 'format' => '%d', 'reference' => array( 'entity' => 'Customer' ) ),
        'appointment_id'      => array( 'format' => '%d', 'reference' => array( 'entity' => 'Appointment' ) ),
        'payment_id'          => array( 'format' => '%d', 'reference' => array( 'entity' => 'Payment' ) ),
        'number_of_persons'   => array( 'format' => '%d' ),
        'notes'               => array( 'format' => '%s' ),
        'extras'              => array( 'format' => '%s' ),
        'custom_fields'       => array( 'format' => '%s' ),
        'status'              => array( 'format' => '%s' ),
        'status_changed_at'   => array( 'format' => '%s' ),
        'token'               => array( 'format' => '%s' ),
        'time_zone'           => array( 'format' => '%s' ),
        'time_zone_offset'    => array( 'format' => '%d' ),
        'locale'              => array( 'format' => '%s' ),
        'compound_service_id' => array( 'format' => '%d' ),
        'compound_token'      => array( 'format' => '%s' ),
        'created_from'        => array( 'format' => '%s' ),
        'created'             => array( 'format' => '%s' ),
    );

    /** @var Customer */
    public $customer;

    /** @var  string */
    private $last_status;

    /**
     * Delete entity and appointment if there are no more customers.
     *
     * @param bool $compound
     */
    public function deleteCascade( $compound = false )
    {
        $this->delete();
        $appointment = new Appointment();
        if ( $appointment->load( $this->getAppointmentId() ) ) {
            // Check if there are any customers left.
            if ( CustomerAppointment::query()->where( 'appointment_id', $appointment->getId() )->count() == 0 ) {
                // If no customers then delete the appointment.
                $appointment->delete();
            } else {
                // If there are customers then recalculate extras duration.
                if ( $this->getExtras() != '[]' ) {
                    $extras_duration = $appointment->getMaxExtrasDuration();
                    if ( $appointment->getExtrasDuration() != $extras_duration ) {
                        $appointment->setExtrasDuration( $extras_duration );
                        $appointment->save();
                    }
                }
                // Update GC event.
                $appointment->handleGoogleCalendar();
                // Waiting list.
                Lib\Proxy\WaitingList::handleParticipantsChange( $appointment );
            }
            if ( $compound && $this->getCompoundToken() ) {
                // Remove compound CustomerAppointments
                /** @var CustomerAppointment[] $ca_list */
                $ca_list = CustomerAppointment::query()
                    ->where( 'compound_token', $this->getCompoundToken() )
                    ->where( 'compound_service_id', $this->getCompoundServiceId() )
                    ->find();
                foreach ( $ca_list as $ca ) {
                    $ca->deleteCascade();
                }
            }
        }
    }

    public function getStatusTitle()
    {
        return self::statusToString( $this->getStatus() );
    }

    public function cancel()
    {
        $appointment = new Appointment();
        if ( $appointment->load( $this->getAppointmentId() ) ) {
            if ( $this->getStatus() != CustomerAppointment::STATUS_CANCELLED
                && $this->getStatus()!= CustomerAppointment::STATUS_REJECTED
            ) {
                $this->setStatus( CustomerAppointment::STATUS_CANCELLED );
                Lib\NotificationSender::sendSingle( DataHolders\Simple::create( $this ) );
            }

            if ( get_option( 'bookly_cst_cancel_action' ) == 'delete' ) {
                $this->deleteCascade( true );
            } else {
                if ( $this->getCompoundToken() ) {
                    Lib\Proxy\CompoundServices::cancelAppointment( $this );
                } else {
                    $this->save();
                    if ( $this->getExtras() != '[]' ) {
                        $extras_duration = $appointment->getMaxExtrasDuration();
                        if ( $appointment->getExtrasDuration() != $extras_duration ) {
                            $appointment->setExtrasDuration( $extras_duration );
                            $appointment->save();
                        }
                    }
                    // Google Calendar.
                    $appointment->handleGoogleCalendar();
                    // Waiting list.
                    Lib\Proxy\WaitingList::handleParticipantsChange( $appointment );
                }
            }
        }
    }

    public static function statusToString( $status )
    {
        switch ( $status ) {
            case self::STATUS_PENDING:    return __( 'Pending',   'bookly' );
            case self::STATUS_APPROVED:   return __( 'Approved',  'bookly' );
            case self::STATUS_CANCELLED:  return __( 'Cancelled', 'bookly' );
            case self::STATUS_REJECTED:   return __( 'Rejected',  'bookly' );
            case self::STATUS_WAITLISTED: return __( 'On waiting list',  'bookly' );
            default: return '';
        }
    }

    /**
     * @return array
     */
    public static function getStatuses()
    {
        $statuses = array(
            CustomerAppointment::STATUS_PENDING,
            CustomerAppointment::STATUS_APPROVED,
            CustomerAppointment::STATUS_CANCELLED,
            CustomerAppointment::STATUS_REJECTED,
        );
        if ( Lib\Config::waitingListActive() ) {
            $statuses[] = CustomerAppointment::STATUS_WAITLISTED;
        }

        return $statuses;
    }

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Gets customer_id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * Sets package
     * @param \BooklyPackages\Lib\Entities\Package $package
     * @return $this
     */
    public function setPackage( \BooklyPackages\Lib\Entities\Package $package )
    {
        return $this->setPackageId( $package->getId() );
    }

    /**
     * Sets service_id
     *
     * @param int $package_id
     * @return $this
     */
    public function setPackageId( $package_id )
    {
        $this->package_id = $package_id;

        return $this;
    }

    /**
     * Gets service_id
     *
     * @return int
     */
    public function getPackageId()
    {
        return $this->package_id;
    }

    /**
     * Sets customer
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer( Customer $customer )
    {
        return $this->setCustomerId( $customer->getId() );
    }

    /**
     * Sets customer_id
     *
     * @param int $customer_id
     * @return $this
     */
    public function setCustomerId( $customer_id )
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    /**
     * Gets appointment_id
     *
     * @return int
     */
    public function getAppointmentId()
    {
        return $this->appointment_id;
    }

    /**
     * @param Appointment $appointment
     * @return $this
     */
    public function setAppointment( Appointment $appointment )
    {
        return $this->setAppointmentId( $appointment->getId() );
    }
    /**
     * Sets appointment_id
     *
     * @param int $appointment_id
     * @return $this
     */
    public function setAppointmentId( $appointment_id )
    {
        $this->appointment_id = $appointment_id;

        return $this;
    }

    /**
     * Gets payment_id
     *
     * @return int
     */
    public function getPaymentId()
    {
        return $this->payment_id;
    }

    /**
     * Sets payment_id
     *
     * @param int $payment_id
     * @return $this
     */
    public function setPaymentId( $payment_id )
    {
        $this->payment_id = $payment_id;

        return $this;
    }

    /**
     * Gets notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Sets notes
     *
     * @param string $notes
     * @return $this
     */
    public function setNotes( $notes )
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Gets number_of_persons
     *
     * @return int
     */
    public function getNumberOfPersons()
    {
        return $this->number_of_persons;
    }

    /**
     * Sets number_of_persons
     *
     * @param int $number_of_persons
     * @return $this
     */
    public function setNumberOfPersons( $number_of_persons )
    {
        $this->number_of_persons = $number_of_persons;

        return $this;
    }

    /**
     * Gets extras
     *
     * @return string
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     * Sets extras
     *
     * @param string $extras
     * @return $this
     */
    public function setExtras( $extras )
    {
        $this->extras = $extras;

        return $this;
    }

    /**
     * Sets custom_fields
     *
     * @param string $custom_fields
     * @return $this
     */
    public function setCustomFields( $custom_fields )
    {
        $this->custom_fields = $custom_fields;

        return $this;
    }

    /**
     * Gets custom_fields
     *
     * @return string
     */
    public function getCustomFields()
    {
        return $this->custom_fields;
    }

    /**
     * Gets status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus( $status )
    {
        if ( $this->last_status === null ) {
            $this->last_status = $status;
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Gets status_changed_at
     *
     * @return string
     */
    public function getStatusChangedAt()
    {
        return $this->status_changed_at;
    }

    /**
     * Sets status_changed_at
     *
     * @param string $status_changed_at
     * @return $this
     */
    public function setStatusChangedAt( $status_changed_at )
    {
        $this->status_changed_at = $status_changed_at;

        return $this;
    }

    /**
     * Gets token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Sets token
     *
     * @param string $token
     * @return $this
     */
    public function setToken( $token )
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Gets time_zone
     *
     * @return string
     */
    public function getTimeZone()
    {
        return $this->time_zone;
    }

    /**
     * Sets time_zone
     *
     * @param string $time_zone
     * @return $this
     */
    public function setTimeZone( $time_zone )
    {
        $this->time_zone = $time_zone;

        return $this;
    }

    /**
     * Gets time_zone_offset
     *
     * @return int
     */
    public function getTimeZoneOffset()
    {
        return $this->time_zone_offset;
    }

    /**
     * Sets time_zone_offset
     *
     * @param int $time_zone_offset
     * @return $this
     */
    public function setTimeZoneOffset( $time_zone_offset )
    {
        $this->time_zone_offset = $time_zone_offset;

        return $this;
    }

    /**
     * Gets locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Sets locale
     *
     * @param string $locale
     * @return $this
     */
    public function setLocale( $locale )
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Gets compound_service_id
     *
     * @return int
     */
    public function getCompoundServiceId()
    {
        return $this->compound_service_id;
    }

    /**
     * Sets compound_service_id
     *
     * @param int $compound_service_id
     * @return $this
     */
    public function setCompoundServiceId( $compound_service_id )
    {
        $this->compound_service_id = $compound_service_id;

        return $this;
    }

    /**
     * Gets compound_token
     *
     * @return string
     */
    public function getCompoundToken()
    {
        return $this->compound_token;
    }

    /**
     * Sets compound_token
     *
     * @param string $compound_token
     * @return $this
     */
    public function setCompoundToken( $compound_token )
    {
        $this->compound_token = $compound_token;

        return $this;
    }

    /**
     * Gets created_from
     *
     * @return string
     */
    public function getCreatedFrom()
    {
        return $this->created_from;
    }

    /**
     * Sets created_from
     *
     * @param string $created_from
     * @return $this
     */
    public function setCreatedFrom( $created_from )
    {
        $this->created_from = $created_from;

        return $this;
    }

    /**
     * Gets created
     *
     * @return string
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Sets created
     *
     * @param string $created
     * @return $this
     */
    public function setCreated( $created )
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Gets last_status
     *
     * @return string
     */
    public function getLastStatus()
    {
        return $this->last_status;
    }

    /**************************************************************************
     * Overridden Methods                                                     *
     **************************************************************************/

    public function setFields( $data, $overwrite_loaded_values = false )
    {
        if ( $data = (array) $data ) {
            if ( $this->last_status === null && array_key_exists( 'status', $data ) ) {
                $this->last_status = $data['status'];
            }
        }
        return parent::setFields( $data, $overwrite_loaded_values );
    }

    /**
     * Save entity to database.
     * Generate token before saving.
     *
     * @return int|false
     */
    public function save()
    {
        // Generate new token if it is not set.
        if ( $this->getToken() == '' ) {
            $this->setToken( Lib\Utils\Common::generateToken( get_class( $this ), 'token' ) );
        }
        if ( $this->getLocale() === null ) {
            $this->setLocale( apply_filters( 'wpml_current_language', null ) );
        }

        if ( $this->status != $this->last_status ) {
            $this->setStatusChangedAt( current_time( 'mysql' ) );
        }

        $is_new = $this->getId() === null;

        $return = parent::save();

        if ( $is_new ) {
            Lib\NotificationSender::sendOnCACreated( $this );
        } elseif ( $this->status != $this->last_status ) {
            Lib\NotificationSender::sendOnCAStatusChanged( $this );
        }

        return $return;
    }

}