<?php
namespace BooklyLite\Lib\DataHolders\Booking;

use BooklyLite\Lib;

/**
 * Class Simple
 * @package BooklyLite\Lib\DataHolders\Booking
 */
class Simple extends Item
{
    /** @var Lib\Entities\Service */
    protected $service;
    /** @var Lib\Entities\Staff */
    protected $staff;
    /** @var Lib\Entities\Appointment */
    protected $appointment;
    /** @var Lib\Entities\CustomerAppointment */
    protected $ca;
    /** @var Lib\Entities\StaffService */
    protected $staff_service;

    /**
     * Constructor.
     *
     * @param Lib\Entities\CustomerAppointment $ca
     */
    public function __construct( Lib\Entities\CustomerAppointment $ca )
    {
        $this->type = Item::TYPE_SIMPLE;
        $this->ca   = $ca;
    }

    /**
     * Set service.
     *
     * @param Lib\Entities\Service $service
     * @return $this
     */
    public function setService( Lib\Entities\Service $service )
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service.
     *
     * @return Lib\Entities\Service
     */
    public function getService()
    {
        if ( ! $this->service ) {
            if ( $this->getAppointment()->getServiceId() ) {
                $this->service = Lib\Entities\Service::find( $this->getAppointment()->getServiceId() );
            } else {
                // Custom service.
                $this->service = new Lib\Entities\Service();
                $this->service
                    ->setTitle( $this->getAppointment()->getCustomServiceName() )
                    ->setDuration(
                        Lib\Slots\DatePoint::fromStr( $this->getAppointment()->getEndDate() )
                            ->diff( Lib\Slots\DatePoint::fromStr( $this->getAppointment()->getStartDate() ) )
                    )
                    ->setPrice( $this->getAppointment()->getCustomServicePrice() );
            }
        }

        return $this->service;
    }

    /**
     * Set staff.
     *
     * @param Lib\Entities\Staff $staff
     * @return $this
     */
    public function setStaff( Lib\Entities\Staff $staff )
    {
        $this->staff = $staff;

        return $this;
    }

    /**
     * Get staff.
     *
     * @return Lib\Entities\Staff
     */
    public function getStaff()
    {
        if ( ! $this->staff ) {
            $this->staff = Lib\Entities\Staff::find( $this->getAppointment()->getStaffId() );
        }

        return $this->staff;
    }

    /**
     * Set appointment.
     *
     * @param Lib\Entities\Appointment $appointment
     * @return $this
     */
    public function setAppointment( Lib\Entities\Appointment $appointment )
    {
        $this->appointment = $appointment;

        return $this;
    }

    /**
     * Get appointment.
     *
     * @return Lib\Entities\Appointment
     */
    public function getAppointment()
    {
        if ( ! $this->appointment ) {
            $this->appointment = Lib\Entities\Appointment::find( $this->ca->getAppointmentId() );
        }

        return $this->appointment;
    }

    /**
     * Get customer appointment.
     *
     * @return Lib\Entities\CustomerAppointment
     */
    public function getCA()
    {
        return $this->ca;
    }

    /**
     * Get service price.
     *
     * @return float
     */
    public function getServicePrice()
    {
        if ( $this->getService()->getId() ) {
            if ( ! $this->staff_service ) {
                $this->staff_service = new Lib\Entities\StaffService();
                $this->staff_service->loadBy( array( 'staff_id' => $this->getStaff()->getId(), 'service_id' => $this->getService()->getId() ) );
            }

            return (float) Lib\Proxy\SpecialHours::preparePrice(
                $this->staff_service->getPrice(),
                $this->getStaff()->getId(),
                $this->getService()->getId(),
                $this->getAppointment()->getStartDate()
            );
        } else {
            return (float) $this->getAppointment()->getCustomServicePrice();
        }
    }

    /**
     * Get total price.
     *
     * @return float
     */
    public function getTotalPrice()
    {
        // Service price.
        $service_price = $this->getServicePrice();

        // Extras.
        $extras = (array) Lib\Proxy\ServiceExtras::getInfo( json_decode( $this->getCA()->getExtras(), true ), true );
        $extras_total_price = 0.0;
        foreach ( $extras as $extra ) {
            $extras_total_price += $extra['price'];
        }

        return ( $service_price + $extras_total_price ) * $this->getCA()->getNumberOfPersons();
    }

    /**
     * Get deposit.
     *
     * @return string
     */
    public function getDeposit()
    {
        if ( ! $this->staff_service ) {
            $this->staff_service = new Lib\Entities\StaffService();
            $this->staff_service->loadBy( array( 'staff_id' => $this->getStaff()->getId(), 'service_id' => $this->getService()->getId() ) );
        }

        return $this->staff_service->getDeposit();
    }

    /**
     * Create new item.
     *
     * @param Lib\Entities\CustomerAppointment $ca
     * @return static
     */
    public static function create( Lib\Entities\CustomerAppointment $ca )
    {
        return new static( $ca );
    }
}