<?php
namespace BooklyLite\Frontend\Modules\Booking;

use BooklyLite\Lib;

/**
 * Class Components
 * @package BooklyLite\Frontend\Modules\Booking
 */
class Components extends Lib\Base\Components
{
    /**
     * Render appointment dialog.
     * @throws \Exception
     */
    public function renderCardPayment()
    {
        $this->render( '_card_payment' );
    }

    /**
     * Render info text into a variable.
     *
     * @since 10.9 format codes {code}, [[CODE]] is deprecated.
     *
     * @param integer             $step
     * @param string              $text
     * @param Lib\UserBookingData $userData
     * @return string
     */
    public function prepareInfoText( $step, $text, Lib\UserBookingData $userData )
    {
        static $info_text_codes = array();
        if ( empty ( $info_text_codes ) ) {
            if ( $step == 1 ) {
                // No replacements.
            } elseif ( $step < 5 ) {
                $data = array(
                    'category_names'      => array(),
                    'numbers_of_persons'  => array(),
                    'service_date'        => '',
                    'service_info'        => array(),
                    'service_names'       => array(),
                    'service_prices'      => array(),
                    'service_time'        => '',
                    'staff_info'          => array(),
                    'staff_names'         => array(),
                    'total_deposit_price' => 0,
                    'total_price'         => 0,
                );

                /** @var Lib\ChainItem $chain_item */
                foreach ( $userData->chain->getItems() as $chain_item ) {
                    $data['numbers_of_persons'][] = $chain_item->getNumberOfPersons();
                    /** @var Lib\Entities\Service $service */
                    $service = Lib\Entities\Service::find( $chain_item->getServiceId() );
                    $data['service_names'][]  = $service->getTranslatedTitle();
                    $data['service_info'][]   = $service->getTranslatedInfo();
                    $data['category_names'][] = $service->getTranslatedCategoryName();
                    /** @var Lib\Entities\Staff $staff */
                    $staff     = null;
                    $staff_ids = $chain_item->getStaffIds();
                    if ( count( $staff_ids ) == 1 ) {
                        $staff = Lib\Entities\Staff::find( $staff_ids[0] );
                    }
                    if ( $staff ) {
                        $data['staff_names'][] = $staff->getTranslatedName();
                        $data['staff_info'][]  = $staff->getTranslatedInfo();
                        if ( $service->getType() == Lib\Entities\Service::TYPE_COMPOUND ) {
                            $price = $service->getPrice();
                            $deposit_price = $price;
                        } else {
                            $staff_service = new Lib\Entities\StaffService();
                            $staff_service->loadBy( array(
                                'staff_id'   => $staff->getId(),
                                'service_id' => $service->getId(),
                            ) );
                            $price = $staff_service->getPrice();
                            $deposit_price = Lib\Proxy\DepositPayments::prepareAmount( ( $chain_item->getNumberOfPersons() * $price ), $staff_service->getDeposit(), $chain_item->getNumberOfPersons() );
                        }
                    } else {
                        $data['staff_names'][] = __( 'Any', 'bookly' );
                        $price = false;
                        $deposit_price = false;
                    }
                    $data['service_prices'][] = $price !== false ? Lib\Utils\Price::format( $price ) : '-';
                    $data['total_price'] += $price * $chain_item->getNumberOfPersons();
                    $data['total_deposit_price'] += $deposit_price * $chain_item->getNumberOfPersons();

                    $data = Lib\Proxy\Shared::prepareChainItemInfoText( $data, $chain_item );
                }

                if ( $step == 4 ) {
                    // For Repeat step set service date and time based on the first slot.
                    $slots = $userData->getSlots();
                    $service_dp = Lib\Slots\DatePoint::fromStr( $slots[0][2] )->toClientTz();
                    $data['service_date'] = $service_dp->formatI18nDate();
                    $data['service_time'] = $service_dp->formatI18nTime();
                }

                $info_text_codes = array(
                    '{amount_due}'        => '<b>' . Lib\Utils\Price::format( $data['total_price'] - $data['total_deposit_price'] ) . '</b>',
                    '{amount_to_pay}'     => '<b>' . Lib\Utils\Price::format( $data['total_deposit_price'] ) . '</b>',
                    '{category_name}'     => '<b>' . implode( ', ', $data['category_names'] ) . '</b>',
                    '{number_of_persons}' => '<b>' . implode( ', ', $data['numbers_of_persons'] ) . '</b>',
                    '{service_date}'      => '<b>' . $data['service_date'] . '</b>',
                    '{service_info}'      => '<b>' . implode( ', ', $data['service_info'] ) . '</b>',
                    '{service_name}'      => '<b>' . implode( ', ', $data['service_names'] ) . '</b>',
                    '{service_price}'     => '<b>' . implode( ', ', $data['service_prices'] ) . '</b>',
                    '{service_time}'      => '<b>' . $data['service_time'] . '</b>',
                    '{staff_info}'        => '<b>' . implode( ', ', $data['staff_info'] ) . '</b>',
                    '{staff_name}'        => '<b>' . implode( ', ', $data['staff_names'] ) . '</b>',
                    '{total_price}'       => '<b>' . Lib\Utils\Price::format( $data['total_price'] ) . '</b>',
                );
                $info_text_codes = Lib\Proxy\Shared::prepareInfoTextCodes( $info_text_codes, $data );
            } else {
                $data = array(
                    'booking_number'    => $userData->getBookingNumbers(),
                    'category_name'     => array(),
                    'extras'            => array(),
                    'number_of_persons' => array(),
                    'service'           => array(),
                    'service_date'      => array(),
                    'service_info'      => array(),
                    'service_name'      => array(),
                    'service_price'     => array(),
                    'service_time'      => array(),
                    'staff_info'        => array(),
                    'staff_name'        => array(),
                );
                /** @var Lib\CartItem $cart_item */
                foreach ( $userData->cart->getItems() as $cart_item ) {
                    $service = $cart_item->getService();
                    $slots   = $cart_item->getSlots();
                    $service_dp = Lib\Slots\DatePoint::fromStr( $slots[0][2] )->toClientTz();

                    $data['category_name'][]     = $service->getTranslatedCategoryName();
                    $data['number_of_persons'][] = $cart_item->getNumberOfPersons();
                    $data['service_date'][]  = $service_dp->formatI18nDate();
                    $data['service_info'][]  = $service->getTranslatedInfo();
                    $data['service_name'][]  = $service->getTranslatedTitle();
                    $data['service_price'][] = Lib\Utils\Price::format( $cart_item->getServicePrice() );
                    $data['service_time'][]  = $service_dp->formatI18nTime();
                    $data['staff_info'][]    = $cart_item->getStaff()->getTranslatedInfo();
                    $data['staff_name'][]    = $cart_item->getStaff()->getTranslatedName();

                    $data = Lib\Proxy\Shared::prepareCartItemInfoText( $data, $cart_item );
                }

                list ( $total, $deposit, $due ) = $userData->cart->getInfo( $step >= 7 );  // >= step payment

                $info_text_codes = array(
                    '{amount_due}'         => '<b>' . Lib\Utils\Price::format( $due ) . '</b>',
                    '{amount_to_pay}'      => '<b>' . Lib\Utils\Price::format( $deposit ) . '</b>',
                    '{appointments_count}' => '<b>' . count( $userData->cart->getItems() ) . '</b>',
                    '{booking_number}'     => '<b>' . implode( ', ', $data['booking_number'] ) . '</b>',
                    '{category_name}'      => '<b>' . implode( ', ', $data['category_name'] ) . '</b>',
                    '{number_of_persons}'  => '<b>' . implode( ', ', $data['number_of_persons'] ) . '</b>',
                    '{service_date}'       => '<b>' . implode( ', ', $data['service_date'] ) . '</b>',
                    '{service_info}'       => '<b>' . implode( ', ', $data['service_info'] ) . '</b>',
                    '{service_name}'       => '<b>' . implode( ', ', $data['service_name'] ) . '</b>',
                    '{service_price}'      => '<b>' . implode( ', ', $data['service_price'] ) . '</b>',
                    '{service_time}'       => '<b>' . implode( ', ', $data['service_time'] ) . '</b>',
                    '{staff_info}'         => '<b>' . implode( ', ', $data['staff_info'] ) . '</b>',
                    '{staff_name}'         => '<b>' . implode( ', ', $data['staff_name'] ) . '</b>',
                    '{total_price}'        => '<b>' . Lib\Utils\Price::format( $total ) . '</b>',
                );
                if ( $step == 6 ) {
                    $info_text_codes['{login_form}'] = ! get_current_user_id()
                        ? sprintf( '<a class="bookly-js-login-show" href="#">%s</a>', __( 'Log In' ) )
                        : '';
                }
                $info_text_codes = Lib\Proxy\Shared::prepareInfoTextCodes( $info_text_codes, $data );
            }

            // Support deprecated codes [[CODE]]
            foreach ( array_keys( $info_text_codes ) as $code_key ) {
                if ( $code_key{1} == '[' ) {
                    $info_text_codes[ '{' . strtolower( substr( $code_key, 2, -2 ) ) . '}' ] = $info_text_codes[ $code_key ];
                } else {
                    $info_text_codes[ '[[' . strtoupper( substr( $code_key, 1, -1 ) ) . ']]' ] = $info_text_codes[ $code_key ];
                }
            }
        }

        return strtr( nl2br( $text ), $info_text_codes );
    }

}