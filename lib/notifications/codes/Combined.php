<?php
namespace Bookly\Lib\Notifications\Codes;

use Bookly\Lib\Config;
use Bookly\Lib\Proxy;
use Bookly\Lib\Utils;

/**
 * Class Codes
 * @package Bookly\Lib\Notifications\Codes
 */
class Combined extends Codes
{
    public $cart_info;

    /**
     * @inheritdoc
     */
    protected function getCodes( $format )
    {
        $cart_info_c = $cart_info = '';

        // Cart info.
        $cart_info_data = $this->cart_info;
        if ( ! empty ( $cart_info_data ) ) {
            $cart_columns = get_option( 'bookly_cart_show_columns', array() );
            if ( empty( $cart_columns ) ) {
                $cart_columns = array(
                    'service'  => array( 'show' => '1', ),
                    'date'     => array( 'show' => '1', ),
                    'time'     => array( 'show' => '1', ),
                    'employee' => array( 'show' => '1', ),
                    'price'    => array( 'show' => '1', ),
                    'deposit'  => array( 'show' => (int) Config::depositPaymentsActive() ),
                    'tax'      => array( 'show' => (int) Config::taxesActive(), ),
                );
            }
            if ( ! Proxy\Taxes::showTaxColumn() ) {
                unset( $cart_columns['tax'] );
            }
            if ( ! Config::depositPaymentsActive() ) {
                unset( $cart_columns['deposit'] );
            }
            $ths = array();
            foreach ( $cart_columns as $column => $attr ) {
                if ( $attr['show'] ) {
                    switch ( $column ) {
                        case 'service':
                            $ths[] = Utils\Common::getTranslatedOption( 'bookly_l10n_label_service' );
                            break;
                        case 'date':
                            $ths[] = __( 'Date', 'bookly' );
                            break;
                        case 'time':
                            $ths[] = __( 'Time', 'bookly' );
                            break;
                        case 'tax':
                            $ths[] = __( 'Tax', 'bookly' );
                            break;
                        case 'employee':
                            $ths[] = Utils\Common::getTranslatedOption( 'bookly_l10n_label_employee' );
                            break;
                        case 'price':
                            $ths[] = __( 'Price', 'bookly' );
                            break;
                        case 'deposit':
                            $ths[] = __( 'Deposit', 'bookly' );
                            break;
                    }
                }
            }
            $trs = array();
            foreach ( $cart_info_data as $codes ) {
                $tds = array();
                foreach ( $cart_columns as $column => $attr ) {
                    if ( $attr['show'] ) {
                        switch ( $column ) {
                            case 'service':
                                $service_name = $codes['service_name'];
                                if ( ! empty ( $codes['extras'] ) ) {
                                    $extras = '';
                                    if ( $format == 'html' ) {
                                        foreach ( $codes['extras'] as $extra ) {
                                            $extras .= '<li>' . $extra['title'] . '</li>';
                                        }
                                        $extras = '<ul>' . $extras . '</ul>';
                                    } else {
                                        foreach ( $codes['extras'] as $extra ) {
                                            $extras .= ', ' . str_replace( '&nbsp;&times;&nbsp;', ' x ', $extra['title'] );
                                        }
                                    }
                                    $service_name .= $extras;
                                }
                                $tds[] = $service_name;
                                break;
                            case 'date':
                                $tds[] = $codes['appointment_start'] === null ? __( 'N/A', 'bookly' ) : Utils\DateTime::formatDate( $codes['appointment_start'] );
                                break;
                            case 'time':
                                if ( $codes['appointment_start_info'] !== null ) {
                                    $tds[] = $codes['appointment_start_info'];
                                } else {
                                    $tds[] = $codes['appointment_start'] === null ? __( 'N/A', 'bookly' ) :  Utils\DateTime::formatTime( $codes['appointment_start'] );
                                }
                                break;
                            case 'tax':
                                $tds[] = Utils\Price::format( $codes['tax'] );
                                break;
                            case 'employee':
                                $tds[] = $codes['staff_name'];
                                break;
                            case 'price':
                                $tds[] = Utils\Price::format( $codes['appointment_price'] );
                                break;
                            case 'deposit':
                                $tds[] = $codes['deposit'];
                                break;
                        }
                    }
                }
                $tds[] = $codes['cancel_url'];
                $trs[] = $tds;
            }
            if ( $format == 'html' ) {
                $cart_info   = '<table cellspacing="1" border="1" cellpadding="5"><thead><tr><th>' . implode( '</th><th>', $ths ) . '</th></tr></thead><tbody>';
                $cart_info_c = '<table cellspacing="1" border="1" cellpadding="5"><thead><tr><th>' . implode( '</th><th>', $ths ) . '</th><th>' . __( 'Cancel', 'bookly' ) . '</th></tr></thead><tbody>';
                foreach ( $trs as $tr ) {
                    $cancel_url   = array_pop( $tr );
                    $cart_info   .= '<tr><td>' . implode( '</td><td>', $tr ) . '</td></tr>';
                    $cart_info_c .= '<tr><td>' . implode( '</td><td>', $tr ) . '</td><td><a href="' . $cancel_url . '">' . __( 'Cancel', 'bookly' ) . '</a></td></tr>';
                }
                $cart_info   .= '</tbody></table>';
                $cart_info_c .= '</tbody></table>';
            } else {
                foreach ( $trs as $tr ) {
                    $cancel_url = array_pop( $tr );
                    foreach ( $ths as $position => $column ) {
                        $cart_info   .= $column . ' ' . $tr[ $position ] . "\r\n";
                        $cart_info_c .= $column . ' ' . $tr[ $position ] . "\r\n";
                    }
                    $cart_info .= "\r\n";
                    $cart_info_c .= __( 'Cancel', 'bookly' )  . ' ' . $cancel_url . "\r\n\r\n";
                }
            }
        }
        // Codes.
        $codes = array_merge( parent::getCodes( $format ), array(
            '{cart_info}'    => $cart_info,
            '{cart_info_c}'  => $cart_info_c,
        ) );

        return $codes;
    }
}