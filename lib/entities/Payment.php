<?php
namespace Bookly\Lib\Entities;

use Bookly\Lib;
use Bookly\Lib\DataHolders\Booking as DataHolders;

/**
 * Class Payment
 * @package Bookly\Lib\Entities
 */
class Payment extends Lib\Base\Entity
{
    const TYPE_LOCAL        = 'local';
    const TYPE_COUPON       = 'coupon';  // when price reduced to zero due to coupon
    const TYPE_PAYPAL       = 'paypal';
    const TYPE_STRIPE       = 'stripe';
    const TYPE_AUTHORIZENET = 'authorize_net';
    const TYPE_2CHECKOUT    = '2checkout';
    const TYPE_PAYUBIZ      = 'payu_biz';
    const TYPE_PAYULATAM    = 'payu_latam';
    const TYPE_PAYSON       = 'payson';
    const TYPE_MOLLIE       = 'mollie';
    const TYPE_WOOCOMMERCE  = 'woocommerce';

    const STATUS_COMPLETED  = 'completed';
    const STATUS_PENDING    = 'pending';
    const STATUS_REJECTED   = 'rejected';

    const PAY_DEPOSIT       = 'deposit';
    const PAY_IN_FULL       = 'in_full';

    /** @var int */
    protected $coupon_id;
    /** @var string */
    protected $type;
    /** @var float */
    protected $total;
    /** @var float */
    protected $tax = 0;
    /** @var float */
    protected $paid;
    /** @var float */
    protected $gateway_price_correction;
    /** @var string */
    protected $paid_type = self::PAY_IN_FULL;
    /** @var string */
    protected $status = self::STATUS_COMPLETED;
    /** @var string */
    protected $details;
    /** @var string */
    protected $created;

    protected static $table = 'bookly_payments';

    protected static $schema = array(
        'id'          => array( 'format' => '%d' ),
        'coupon_id'   => array( 'format' => '%d' ),
        'type'        => array( 'format' => '%s' ),
        'total'       => array( 'format' => '%f' ),
        'tax'         => array( 'format' => '%f' ),
        'paid'        => array( 'format' => '%f' ),
        'paid_type'   => array( 'format' => '%s' ),
        'gateway_price_correction' => array( 'format' => '%f' ),
        'status'      => array( 'format' => '%s' ),
        'details'     => array( 'format' => '%s' ),
        'created'     => array( 'format' => '%s' ),
    );

    /**
     * Get display name for given payment type.
     *
     * @param string $type
     * @return string
     */
    public static function typeToString( $type )
    {
        switch ( $type ) {
            case self::TYPE_PAYPAL:       return 'PayPal';
            case self::TYPE_LOCAL:        return __( 'Local', 'bookly' );
            case self::TYPE_STRIPE:       return 'Stripe';
            case self::TYPE_AUTHORIZENET: return 'Authorize.Net';
            case self::TYPE_2CHECKOUT:    return '2Checkout';
            case self::TYPE_PAYUBIZ:      return 'PayUbiz';
            case self::TYPE_PAYULATAM:    return 'PayU Latam';
            case self::TYPE_PAYSON:       return 'Payson';
            case self::TYPE_MOLLIE:       return 'Mollie';
            case self::TYPE_COUPON:       return __( 'Coupon', 'bookly' );
            case self::TYPE_WOOCOMMERCE:  return 'WooCommerce';
            default:                      return '';
        }
    }

    /**
     * Get status of payment.
     *
     * @param string $status
     * @return string
     */
    public static function statusToString( $status )
    {
        switch ( $status ) {
            case self::STATUS_COMPLETED:  return __( 'Completed', 'bookly' );
            case self::STATUS_PENDING:    return __( 'Pending',   'bookly' );
            case self::STATUS_REJECTED:   return __( 'Rejected',  'bookly' );
            default:                      return '';
        }
    }

    /**
     * @param DataHolders\Order $order
     * @param Lib\CartInfo      $cart_info
     * @return $this
     */
    public function setDetailsFromOrder( DataHolders\Order $order, Lib\CartInfo $cart_info )
    {
        $extras_multiply_nop = get_option( 'bookly_service_extras_multiply_nop', 1 );

        $details = array(
            'items'               => array(),
            'coupon'              => null,
            'subtotal'            => array( 'price' => 0, 'deposit' => 0 ),
            'customer'            => $order->getCustomer()->getFullName(),
            'tax_in_price'        => 'excluded',
            'tax_paid'            => null,
            'extras_multiply_nop' => $extras_multiply_nop,
        );

        foreach ( $order->getItems() as $item ) {
            $items = $item->isSeries() ? $item->getItems() : array( $item );
            /** @var DataHolders\Item $sub_item */
            foreach ( $items as $sub_item ) {
                if ( $sub_item->getCA()->getPaymentId() != $this->getId() ) {
                    // Skip items not related to this payment (e.g. series items with no associated payment).
                    continue;
                }
                $extras    = array();
                $extras_price = 0;
                $sub_items = array();
                if ( $sub_item->isCollaborative() || $sub_item->isCompound() ) {
                    foreach ( $sub_item->getItems() as $si ) {
                        $sub_items[] = $si;
                    }
                } else {
                    $sub_items[] = $sub_item;
                }

                foreach ( $sub_items as $item ) {
                    if ( $item->getCA()->getExtras() != '[]' ) {
                        $_extras = json_decode( $item->getCA()->getExtras(), true );
                        /** @var \BooklyServiceExtras\Lib\Entities\ServiceExtra $extra */
                        foreach ( (array) Lib\Proxy\ServiceExtras::findByIds( array_keys( $_extras ) ) as $extra ) {
                            $quantity = $_extras[ $extra->getId() ];
                            $extras[] = array(
                                'title'    => $extra->getTitle(),
                                'price'    => $extra->getPrice(),
                                'quantity' => $quantity,
                            );
                            $extras_price += $quantity * $extra->getPrice();
                        }
                    }
                }

                $wait_listed = $sub_item->getCA()->getStatus() == CustomerAppointment::STATUS_WAITLISTED;

                $deposit_format = null;
                if ( ! $wait_listed ) {
                    $price  = $sub_item->getServicePrice() * $sub_item->getCA()->getNumberOfPersons();
                    $price += $extras_multiply_nop ? $extras_price * $sub_item->getCA()->getNumberOfPersons() : $extras_price;

                    $details['subtotal']['price']   += $price;
                    if ( Lib\Config::depositPaymentsActive() ) {
                        $deposit_price  = Lib\Proxy\DepositPayments::prepareAmount( $price, $sub_item->getDeposit(), $sub_item->getCA()->getNumberOfPersons() );
                        $deposit_format = Lib\Proxy\DepositPayments::formatDeposit( $deposit_price, $sub_item->getDeposit() );
                        $details['subtotal']['deposit'] += $deposit_price;
                    }
                }

                $details['items'][] = array(
                    'ca_id'             => $sub_item->getCA()->getId(),
                    'appointment_date'  => $sub_item->getAppointment()->getStartDate(),
                    'service_name'      => $sub_item->getService()->getTitle(),
                    'service_price'     => $sub_item->getServicePrice(),
                    'service_tax'       => $wait_listed ? null : $sub_item->getTax(),
                    'wait_listed'       => $wait_listed,
                    'deposit_format'    => $deposit_format,
                    'number_of_persons' => $sub_item->getCA()->getNumberOfPersons(),
                    'units'             => $sub_item->getCA()->getUnits(),
                    'duration'          => $sub_item->getService()->getDuration(),
                    'staff_name'        => $sub_item->getStaff()->getFullName(),
                    'extras'            => $extras,
                );
            }
        }

        $details = Lib\Proxy\Shared::preparePaymentDetails( $details, $order, $cart_info );
        if ( $cart_info->getCoupon() ) {
            $this->coupon_id = $cart_info->getCoupon()->getId();
        }

        $this->details = json_encode( $details );

        return $this;
    }

    /**
     * Payment data for rendering payment details and invoice.
     *
     * @return array
     */
    public function getPaymentData()
    {
        $customer = Lib\Entities\Customer::query( 'c' )
            ->select( 'c.full_name' )
            ->leftJoin( 'CustomerAppointment', 'ca', 'ca.customer_id = c.id' )
            ->where( 'ca.payment_id', $this->getId() )
            ->fetchRow();

        $details = json_decode( $this->getDetails(), true );

        return array(
            'payment' => array(
                'id'               => $this->id,
                'status'           => $this->status,
                'type'             => $this->type,
                'created'          => $this->created,
                'customer'         => empty ( $customer ) ? $details['customer'] : $customer['full_name'],
                'items'            => $details['items'],
                'subtotal'         => $details['subtotal'],
                'group_discount'   => isset( $details['customer_group']['discount_format'] ) ? $details['customer_group']['discount_format'] : false,
                'coupon'           => $details['coupon'],
                'price_correction' => $this->gateway_price_correction,
                'paid'             => $this->paid,
                'tax_paid'         => $details['tax_paid'],
                'total'            => $this->total,
                'tax_total'        => $this->tax,
                'tax_in_price'     => $details['tax_in_price'],
                'from_backend'     => isset( $details['from_backend'] ) ? $details['from_backend'] : false,
                'extras_multiply_nop' => isset( $details['extras_multiply_nop'] ) ? $details['extras_multiply_nop'] : 1,
            ),
            'adjustments'         => isset( $details['adjustments'] ) ? $details['adjustments'] : array(),
        );
    }

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Gets coupon_id
     *
     * @return int
     */
    public function getCouponId()
    {
        return $this->coupon_id;
    }

    /**
     * Sets coupon_id
     *
     * @param int $coupon_id
     * @return $this
     */
    public function setCouponId( $coupon_id )
    {
        $this->coupon_id = $coupon_id;

        return $this;
    }

    /**
     * Gets details
     *
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Sets details
     *
     * @param string $details
     * @return $this
     */
    public function setDetails( $details )
    {
        $this->details = $details;

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
     * Gets total
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Sets total
     *
     * @param float $total
     * @return $this
     */
    public function setTotal( $total )
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Gets tax
     *
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Sets tax
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
     * Gets paid
     *
     * @return float
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * Sets paid
     *
     * @param float $paid
     * @return $this
     */
    public function setPaid( $paid )
    {
        $this->paid = $paid;

        return $this;
    }

    /**
     * Gets fee
     *
     * @return float
     */
    public function getGatewayPriceCorrection()
    {
        return $this->gateway_price_correction;
    }

    /**
     * Sets fee
     *
     * @param float $gateway_price_correction
     * @return $this
     */
    public function setGatewayPriceCorrection( $gateway_price_correction )
    {
        $this->gateway_price_correction = $gateway_price_correction;

        return $this;
    }

    /**
     * Gets paid_type
     *
     * @return string
     */
    public function getPaidType()
    {
        return $this->paid_type;
    }

    /**
     * Sets paid_type
     *
     * @param string $paid_type
     * @return $this
     */
    public function setPaidType( $paid_type )
    {
        $this->paid_type = $paid_type;

        return $this;
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
        $this->status = $status;

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
     * @param Lib\CartInfo $cart_info
     * @return $this
     */
    public function setCartInfo( Lib\CartInfo $cart_info )
    {
        $this
            ->setTotal( $cart_info->getTotal() )
            ->setPaid( $cart_info->getPayNow() )
            ->setGatewayPriceCorrection( $cart_info->getPriceCorrection() )
            ->setPaidType( $cart_info->getTotal() == $cart_info->getPayNow() ? self::PAY_IN_FULL : self::PAY_DEPOSIT )
            ->setTax( $cart_info->getTotalTax() );

        return $this;
    }

    public function save()
    {
        if ( $this->getId() == null ) {
            $this->setCreated( current_time( 'mysql' ) );
        }
        return parent::save();
    }
}