<?php
namespace Bookly\Lib\DataHolders\Booking;

use Bookly\Lib;

/**
 * Class Simple
 * @package Bookly\Lib\DataHolders\Booking
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
     * @inheritdoc
     */
    public function getAppointment()
    {
        if ( ! $this->appointment ) {
            $this->appointment = Lib\Entities\Appointment::find( $this->ca->getAppointmentId() );
        }

        return $this->appointment;
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
     * @inheritdoc
     */
    public function getCA()
    {
        return $this->ca;
    }

    /**
     * @inheritdoc
     */
    public function getDeposit()
    {
        if ( ! $this->staff_service ) {
            $this->staff_service = new Lib\Entities\StaffService();
            $this->staff_service->loadBy(
                array(
                    'staff_id'    => $this->getStaff()->getId(),
                    'service_id'  => $this->getService()->getId(),
                    'location_id' => Lib\Proxy\Locations::prepareStaffLocationId( $this->appointment->getLocationId(), $this->getStaff()->getId() ) ?: null,
                ) );
        }

        return $this->staff_service->getDeposit();
    }

    /**
     * @inheritdoc
     */
    public function getExtras()
    {
        return json_decode( $this->getCA()->getExtras(), true );
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getServiceDuration()
    {
        return $this->getService()->getDuration() * $this->getCA()->getUnits();
    }

    /**
     * @inheritdoc
     */
    public function getServicePrice()
    {
        if ( $this->getService()->getId() ) {
            if ( ! $this->staff_service ) {
                $this->staff_service = new Lib\Entities\StaffService();
                $this->staff_service->loadBy(
                    array(
                        'staff_id'    => $this->getStaff()->getId(),
                        'service_id'  => $this->getService()->getId(),
                        'location_id' => Lib\Proxy\Locations::prepareStaffLocationId( $this->appointment->getLocationId(), $this->getStaff()->getId() ) ?: null,
                    ) );
            }

            return (float) Lib\Proxy\SpecialHours::adjustPrice(
                $this->staff_service->getPrice() * $this->getCA()->getUnits(),
                $this->getStaff()->getId(),
                $this->getService()->getId(),
                Lib\Proxy\Locations::prepareStaffLocationId( $this->appointment->getLocationId(), $this->getStaff()->getId() ) ?: null,
                $this->getAppointment()->getStartDate(),
                $this->getCA()->getUnits()
            );
        } else {
            return (float) $this->getAppointment()->getCustomServicePrice();
        }
    }

    /**
     * @inheritdoc
     */
    public function getStaff()
    {
        if ( ! $this->staff ) {
            $this->staff = Lib\Entities\Staff::find( $this->getAppointment()->getStaffId() );
        }

        return $this->staff;
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
     * @inheritdoc
     */
    public function getTax()
    {
        if ( ! $this->tax ) {
            $rates = Lib\Proxy\Taxes::getServiceTaxRates();
            if ( $rates ) {
                $this->tax = Lib\Proxy\Taxes::calculateTax( $this->getTotalPrice(), $rates[ $this->getService()->getId() ] );
            }
        }

        return $this->tax;
    }

    /**
     * Set tax.
     *
     * @param float $tax
     * @return $this
     */
    public function setTax( $tax )
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTotalEnd()
    {
        return Lib\Slots\DatePoint::fromStr( $this->getAppointment()->getEndDate() )
            ->modify( $this->getAppointment()->getExtrasDuration() );
    }

    /**
     * @inheritdoc
     */
    public function getTotalPrice()
    {
        // Service price.
        $price = $this->getServicePrice();
        $nop   = $this->getCA()->getNumberOfPersons();

        return Lib\Proxy\ServiceExtras::prepareServicePrice( $price * $nop, $price, $nop, json_decode( $this->getCA()->getExtras(), true ) );
    }

    /**
     * @inheritdoc
     */
    public function setStatus( $status )
    {
        $this->ca->setStatus( $status );
    }

    /**
     * Get items.
     *
     * @return Simple[]
     */
    public function getItems()
    {
        return array( $this );
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