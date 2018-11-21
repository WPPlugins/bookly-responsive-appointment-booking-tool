<?php
namespace Bookly\Frontend\Components\Booking;

use Bookly\Lib;
use Bookly\Frontend\Modules\Booking\Proxy;
use Bookly\Frontend\Modules\Booking\Lib\Steps;

/**
 * Class InfoText
 * @package Bookly\Frontend\Components\Booking
 */
class InfoText
{
    /**
     * Render info text into a variable.
     *
     * @since 10.9 format codes {code}, [[CODE]] is deprecated.
     * @param integer             $step
     * @param string              $text
     * @param Lib\UserBookingData $userData
     * @return string
     */
    public static function prepare( $step, $text, Lib\UserBookingData $userData )
    {
        static $info_text_codes = array();
        if ( empty ( $info_text_codes ) ) {

            switch ( $step ) {
                case Steps::SERVICE:
                    break;
                case Steps::EXTRAS:
                case Steps::TIME:
                case Steps::REPEAT:
                    $data = array(
                        'category_names'      => array(),
                        'numbers_of_persons'  => array(),
                        'service_date'        => array(),
                        'service_duration'    => array(),
                        'service_info'        => array(),
                        'service_names'       => array(),
                        'service_prices'      => array(),
                        'service_time'        => array(),
                        'staff_info'          => array(),
                        'staff_names'         => array(),
                        'staff_photo'         => array(),
                        'total_deposit_price' => 0,
                        'total_price'         => 0,
                    );

                    if ( $step == Steps::REPEAT ) {
                        $slots = $userData->getSlots();
                    }

                    /** @var Lib\ChainItem $chain_item */
                    foreach ( $userData->chain->getItems() as $num => $chain_item ) {
                        $data['numbers_of_persons'][] = $chain_item->getNumberOfPersons();
                        /** @var Lib\Entities\Service $service */
                        $service                  = Lib\Entities\Service::find( $chain_item->getServiceId() );
                        $data['service_names'][]  = $service->getTranslatedTitle();
                        $data['service_info'][]   = $service->getTranslatedInfo();
                        $data['category_names'][] = $service->getTranslatedCategoryName();
                        if ( $service->withSubServices() ) {
                            $duration = 0;
                            foreach ( $service->getSubServices() as $sub_service ) {
                                if ( $service->isCompound() ) {
                                    $duration += $sub_service->getDuration();
                                } else if ( $service->isCollaborative() ) {
                                    $duration = max( $duration, $sub_service->getDuration() );
                                }
                            }
                            $data['service_duration'][] = Lib\Utils\DateTime::secondsToInterval( $duration );
                            if ( $step == Steps::REPEAT ) {
                                $service_dp             = Lib\Slots\DatePoint::fromStr( $slots[ $num ][2] )->toClientTz();
                                $data['service_date'][] = $slots[ $num ][2] !== null ? $service_dp->formatI18nDate() : __( 'N/A', 'bookly' );
                                $data['service_time'][] = $slots[ $num ][2] !== null ? ( $duration >= DAY_IN_SECONDS ? $service->getStartTimeInfo() : $service_dp->formatI18nTime() ) : __( 'N/A', 'bookly' );
                            }
                        } else {
                            $data['service_duration'][] = Lib\Utils\DateTime::secondsToInterval( $chain_item->getUnits() * $service->getDuration() );
                            if ( $step == Steps::REPEAT ) {
                                $service_dp             = Lib\Slots\DatePoint::fromStr( $slots[ $num ][2] )->toClientTz();
                                $data['service_date'][] = $slots[ $num ][2] !== null ? $service_dp->formatI18nDate() : __( 'N/A', 'bookly' );
                                $data['service_time'][] = $slots[ $num ][2] !== null ? ( $chain_item->getUnits() * $service->getDuration() >= DAY_IN_SECONDS ? $service->getStartTimeInfo() : $service_dp->formatI18nTime() ) : __( 'N/A', 'bookly' );
                            }
                        }
                        /** @var Lib\Entities\Staff $staff */
                        $staff     = null;
                        $staff_ids = $chain_item->getStaffIds();
                        $staff_photo = '';
                        if ( count( $staff_ids ) == 1 ) {
                            $staff = Lib\Entities\Staff::find( $staff_ids[0] );
                        }
                        if ( $staff ) {
                            $data['staff_names'][] = $staff->getTranslatedName();
                            $data['staff_info'][]  = $staff->getTranslatedInfo();
                            if ( $staff->getAttachmentId() && $img = wp_get_attachment_image_src( $staff->getAttachmentId(), 'full' ) ) {
                                $staff_photo = '<img src="' . $img[0] . '"/>';
                            }
                            if ( $service->withSubServices() ) {
                                $price         = $service->getPrice();
                                $deposit_price = $price;
                            } else {
                                $staff_service = new Lib\Entities\StaffService();
                                $staff_service->loadBy( array(
                                    'staff_id'    => $staff->getId(),
                                    'service_id'  => $service->getId(),
                                    'location_id' => $chain_item->getLocationId() ?: null,
                                ) );
                                if ( ! $staff_service->getId() ) {
                                    $staff_service->loadBy( array(
                                        'staff_id'    => $staff->getId(),
                                        'service_id'  => $service->getId(),
                                        'location_id' => null,
                                    ) );
                                }
                                $price = $staff_service->getPrice() * $chain_item->getUnits();
                                $deposit_price = Lib\Proxy\DepositPayments::prepareAmount( $chain_item->getNumberOfPersons() * $price, $staff_service->getDeposit(), $chain_item->getNumberOfPersons() );
                            }
                        } else {
                            $data['staff_names'][] = __( 'Any', 'bookly' );
                            $price                 = false;
                            $deposit_price         = false;
                        }
                        $data['service_prices'][]     = $price !== false ? Lib\Utils\Price::format( $price ) : '-';
                        $data['staff_photo'][]        = $staff_photo;
                        $data['total_price']         += $price * $chain_item->getNumberOfPersons();
                        $data['total_deposit_price'] += $deposit_price;

                        $data = Proxy\Shared::prepareChainItemInfoText( $data, $chain_item );
                    }

                    $info_text_codes = array(
                        '{amount_due}'        => '<b>' . Lib\Utils\Price::format( $data['total_price'] - $data['total_deposit_price'] ) . '</b>',
                        '{amount_to_pay}'     => '<b>' . Lib\Utils\Price::format( $data['total_deposit_price'] ) . '</b>',
                        '{category_name}'     => '<b>' . implode( ', ', $data['category_names'] ) . '</b>',
                        '{deposit_value}'     => '<b>' . Lib\Utils\Price::format( $data['total_deposit_price'] ) . '</b>',
                        '{number_of_persons}' => '<b>' . implode( ', ', $data['numbers_of_persons'] ) . '</b>',
                        '{service_date}'      => '<b>' . implode( ', ', $data['service_date'] ) . '</b>',
                        '{service_duration}'  => '<b>' . implode( ', ', $data['service_duration'] ) . '</b>',
                        '{service_info}'      => '<b>' . implode( ', ', $data['service_info'] ) . '</b>',
                        '{service_name}'      => '<b>' . implode( ', ', $data['service_names'] ) . '</b>',
                        '{service_price}'     => '<b>' . implode( ', ', $data['service_prices'] ) . '</b>',
                        '{service_time}'      => '<b>' . implode( ', ', $data['service_time'] ) . '</b>',
                        '{staff_info}'        => '<b>' . implode( ', ', $data['staff_info'] ) . '</b>',
                        '{staff_name}'        => '<b>' . implode( ', ', $data['staff_names'] ) . '</b>',
                        '{staff_photo}'       => '<b>' . implode( ', ', $data['staff_photo'] ) . '</b>',
                        '{total_price}'       => '<b>' . Lib\Utils\Price::format( $data['total_price'] ) . '</b>',
                    );
                    $info_text_codes = Proxy\Shared::prepareInfoTextCodes( $info_text_codes, $data );

                    break;
                default:
                    $data = array(
                        'booking_number'    => $userData->getBookingNumbers(),
                        'category_name'     => array(),
                        'extras'            => array(),
                        'number_of_persons' => array(),
                        'service'           => array(),
                        'service_date'      => array(),
                        'service_duration'  => array(),
                        'service_info'      => array(),
                        'service_name'      => array(),
                        'service_price'     => array(),
                        'service_time'      => array(),
                        'staff_info'        => array(),
                        'staff_name'        => array(),
                        'staff_photo'       => array(),
                    );
                    /** @var Lib\CartItem $cart_item */
                    foreach ( $userData->cart->getItems() as $cart_item ) {
                        $service    = $cart_item->getService();
                        $slots      = $cart_item->getSlots();
                        $service_dp = Lib\Slots\DatePoint::fromStr( $slots[0][2] )->toClientTz();

                        $data['category_name'][]     = $service->getTranslatedCategoryName();
                        $data['number_of_persons'][] = $cart_item->getNumberOfPersons();
                        $data['service_date'][]      = $slots[0][2] !== null ? $service_dp->formatI18nDate() : __( 'N/A', 'bookly' );
                        $data['service_info'][]      = $service->getTranslatedInfo();
                        $data['service_name'][]      = $service->getTranslatedTitle();
                        $data['service_price'][]     = Lib\Utils\Price::format( $cart_item->getServicePrice() );
                        if ( $cart_item->getService()->withSubServices() ) {
                            $duration = 0;
                            foreach ( $cart_item->getService()->getSubServices() as $sub_service ) {
                                if ( $cart_item->getService()->isCompound() ) {
                                    $duration += $sub_service->getDuration();
                                } else if ( $cart_item->getService()->isCollaborative() ) {
                                    $duration = max( $duration, $sub_service->getDuration() );
                                }
                            }
                            $data['service_duration'][] = Lib\Utils\DateTime::secondsToInterval( $duration );
                            $data['service_time'][]     = $slots[0][2] !== null ? ( $duration >= DAY_IN_SECONDS ? $service->getStartTimeInfo() : $service_dp->formatI18nTime() ) : __( 'N/A', 'bookly' );
                        } else {
                            $data['service_duration'][] = Lib\Utils\DateTime::secondsToInterval( $cart_item->getUnits() * $cart_item->getService()->getDuration() );
                            $data['service_time'][]     = $slots[0][2] !== null ? ( $cart_item->getUnits() * $cart_item->getService()->getDuration() >= DAY_IN_SECONDS ? $service->getStartTimeInfo() : $service_dp->formatI18nTime() ) : __( 'N/A', 'bookly' );
                        }
                        $data['staff_info'][]        = $cart_item->getStaff()->getTranslatedInfo();
                        $data['staff_name'][]        = $cart_item->getStaff()->getTranslatedName();
                        if ( $cart_item->getStaff()->getAttachmentId() && $img = wp_get_attachment_image_src( $cart_item->getStaff()->getAttachmentId(), 'full' ) ) {
                            $data['staff_photo'][]   = '<img src="' . $img[0] . '"/>';
                        } else {
                            $data['staff_photo'][]   = '';
                        }
                        $data = Proxy\Shared::prepareCartItemInfoText( $data, $cart_item );
                    }

                    $with_coupon = $step == Steps::PAYMENT || $step == Steps::DONE; // >= step payment
                    $gateway     = $step == Steps::DONE ? $userData->getPaymentType() : null;
                    $cart_info   = $userData->cart->getInfo( $gateway, $with_coupon );
                    $data['_cart_info'] = $cart_info;
                    $info_text_codes = array(
                        '{amount_due}'         => '<b>' . Lib\Utils\Price::format( $cart_info->getDue() ) . '</b>',
                        '{amount_to_pay}'      => '<b>' . Lib\Utils\Price::format( $cart_info->getPayNow() ) . '</b>',
                        '{appointments_count}' => '<b>' . count( $userData->cart->getItems() ) . '</b>',
                        '{booking_number}'     => '<b>' . implode( ', ', $data['booking_number'] ) . '</b>',
                        '{category_name}'      => '<b>' . implode( ', ', $data['category_name'] ) . '</b>',
                        '{deposit_value}'      => '<b>' . Lib\Utils\Price::format( $cart_info->getDepositPay() ) . '</b>',
                        '{number_of_persons}'  => '<b>' . implode( ', ', $data['number_of_persons'] ) . '</b>',
                        '{service_date}'       => '<b>' . implode( ', ', $data['service_date'] ) . '</b>',
                        '{service_duration}'   => '<b>' . implode( ', ', $data['service_duration'] ) . '</b>',
                        '{service_info}'       => '<b>' . implode( ', ', $data['service_info'] ) . '</b>',
                        '{service_name}'       => '<b>' . implode( ', ', $data['service_name'] ) . '</b>',
                        '{service_price}'      => '<b>' . implode( ', ', $data['service_price'] ) . '</b>',
                        '{service_time}'       => '<b>' . implode( ', ', $data['service_time'] ) . '</b>',
                        '{staff_info}'         => '<b>' . implode( ', ', $data['staff_info'] ) . '</b>',
                        '{staff_name}'         => '<b>' . implode( ', ', $data['staff_name'] ) . '</b>',
                        '{staff_photo}'        => '<b>' . implode( ', ', $data['staff_photo'] ) . '</b>',
                        '{total_price}'        => '<b>' . Lib\Utils\Price::format( $cart_info->getTotal() ) . '</b>',
                    );
                    if ( $step == Steps::DETAILS ) {
                        $info_text_codes['{login_form}'] = ! get_current_user_id() && ! $userData->getFacebookId()
                            ? sprintf( '<a class="bookly-js-login-show" href="#">%s</a>', __( 'Log In' ) )
                            : '';
                    }
                    $info_text_codes = Proxy\Shared::prepareInfoTextCodes( $info_text_codes, $data );

                    break;
            }

            // Support deprecated codes [[CODE]]
            foreach ( array_keys( $info_text_codes ) as $code_key ) {
                if ( $code_key{1} == '[' ) {
                    $info_text_codes[ '{' . strtolower( substr( $code_key, 2, - 2 ) ) . '}' ] = $info_text_codes[ $code_key ];
                } else {
                    $info_text_codes[ '[[' . strtoupper( substr( $code_key, 1, - 1 ) ) . ']]' ] = $info_text_codes[ $code_key ];
                }
            }
        }

        return strtr( nl2br( $text ), $info_text_codes );
    }
}