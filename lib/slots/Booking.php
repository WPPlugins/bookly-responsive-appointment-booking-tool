<?php
namespace BooklyLite\Lib\Slots;

/**
 * Class Booking
 * @package BooklyLite\Lib\Slots
 */
class Booking
{
    /** @var int */
    protected $location_id;
    /** @var int */
    protected $service_id;
    /** @var int */
    protected $nop;
    /** @var Range */
    protected $range;
    /** @var Range */
    protected $range_with_padding;
    /** @var int */
    protected $extras_duration;
    /** @var bool */
    protected $from_google;

    /**
     * Constructor.
     *
     * @param int $service_id
     * @param int $location_id
     * @param int $nop
     * @param string $start  Format Y-m-d H:i[:s]
     * @param string $end    Format Y-m-d H:i[:s]
     * @param int $padding_left
     * @param int $padding_right
     * @param int $extras_duration
     * @param bool $from_google
     */
    public function __construct( $location_id, $service_id, $nop, $start, $end, $padding_left, $padding_right, $extras_duration, $from_google )
    {
        $this->location_id        = (int) $location_id;
        $this->service_id         = (int) $service_id;
        $this->nop                = (int) $nop;
        $this->range              = Range::fromDates( $start, $end );
        $this->range_with_padding = $this->range->transform( - (int) $padding_left, (int) $padding_right );
        $this->extras_duration    = (int) $extras_duration;
        $this->from_google        = (bool) $from_google;
    }

    /**
     * Get location ID.
     *
     * @return int
     */
    public function getLocationId()
    {
        return $this->location_id;
    }

    /**
     * Get service ID.
     *
     * @return int
     */
    public function getServiceId()
    {
        return $this->service_id;
    }

    /**
     * Get number of persons.
     *
     * @return int
     */
    public function getNop()
    {
        return $this->nop;
    }

    /**
     * Increase number of persons by given value.
     *
     * @param int $value
     * @return static
     */
    public function incNop( $value )
    {
        $this->nop += $value;

        return $this;
    }

    /**
     * Get range.
     *
     * @return Range
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * Get range with padding.
     *
     * @return Range
     */
    public function getRangeWithPadding()
    {
        return $this->range_with_padding;
    }

    /**
     * Get extras duration.
     *
     * @return int
     */
    public function getExtrasDuration()
    {
        return $this->extras_duration;
    }

    /**
     * Check if it is from GC.
     *
     * @return bool
     */
    public function isFromGoogle()
    {
        return $this->from_google;
    }
}