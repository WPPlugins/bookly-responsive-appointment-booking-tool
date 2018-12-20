<?php
namespace Bookly\Lib\Notifications\NewBooking;

use Bookly\Lib\Config;
use Bookly\Lib\DataHolders\Booking as DataHolders;
use Bookly\Lib\Entities;
use Bookly\Lib\Notifications\Base;
use Bookly\Lib\Utils;

/**
 * Class Codes
 * @package Bookly\Lib\Notifications\NewBooking
 */
class Codes extends Base\Codes
{
    public $agenda_date;
    public $amount_due;
    public $amount_paid;
    public $appointment_end;
    public $appointment_end_info;
    public $appointment_notes;
    public $appointment_schedule;
    public $appointment_schedule_c;
    public $appointment_start;
    public $appointment_start_info;
    public $appointment_token;
    public $appointment_waiting_list;
    public $booking_number;
    public $cancellation_reason;
    public $cart_info;
    public $category_name;
    public $client_email;
    public $client_address;
    public $client_first_name;
    public $client_last_name;
    public $client_name;
    public $client_phone;
    public $client_timezone;
    public $custom_fields;
    public $custom_fields_2c;
    public $deposit_value;
    public $extras;
    public $extras_total_price;
    public $files_count;
    public $google_calendar_url;
    public $invoice_date;
    public $invoice_due_date;
    public $invoice_due_days;
    public $invoice_link;
    public $invoice_number;     // payment_id
    public $location_info;
    public $location_name;
    public $new_password;
    public $new_username;
    public $next_day_agenda;
    public $next_day_agenda_extended;
    public $number_of_persons;
    public $package_life_time;
    public $package_name;
    public $package_price;
    public $package_size;
    public $payment_type;
    public $payment_status;
    public $schedule;
    public $series_token;
    public $service_duration;
    public $service_info;
    public $service_name;
    public $service_price;
    public $service_tax;
    public $service_tax_rate;
    public $site_address;
    public $staff_email;
    public $staff_info;
    public $staff_name;
    public $staff_phone;
    public $staff_photo;
    public $staff_rating_url;
    public $total_price;
    public $total_tax;

    /** @var DataHolders\Order */
    protected $order;
    /** @var DataHolders\Item */
    protected $item;
    /** @var string */
    protected $lang;
    /** @var bool */
    protected $use_client_tz;

    /**
     * Create new instance.
     *
     * @param DataHolders\Order $order
     * @return static
     */
    public static function create( DataHolders\Order $order )
    {
        $codes = new static();

        $codes->order = $order;

        $codes->client_address    = $order->getCustomer()->getAddress();
        $codes->client_email      = $order->getCustomer()->getEmail();
        $codes->client_first_name = $order->getCustomer()->getFirstName();
        $codes->client_last_name  = $order->getCustomer()->getLastName();
        $codes->client_name       = $order->getCustomer()->getFullName();
        $codes->client_phone      = $order->getCustomer()->getPhone();
        if ( $order->hasPayment() ) {
            $codes->amount_paid    = $order->getPayment()->getPaid();
            $codes->amount_due     = $order->getPayment()->getTotal() - $order->getPayment()->getPaid();
            $codes->total_price    = $order->getPayment()->getTotal();
            $codes->total_tax      = $order->getPayment()->getTax();
            $codes->invoice_number = $order->getPayment()->getId();
            $codes->payment_status = $order->getPayment()->getStatus();
            $codes->payment_type   = $order->getPayment()->getType();
        }

        Proxy\Shared::prepareCodesForOrder( $codes );

        return $codes;
    }

    /**
     * Prepare codes for given order item.
     *
     * @param DataHolders\Item $item
     * @param string $lang
     * @param bool $use_client_tz
     */
    public function prepareForItem( DataHolders\Item $item, $lang, $use_client_tz )
    {
        if (
            $this->item === $item &&
            $this->lang == $lang &&
            ( $this->use_client_tz == $use_client_tz || $item->getCA()->getTimeZoneOffset() === null )
        ) {
            return;
        }

        $this->item = $item;
        $this->lang = $lang;
        $this->use_client_tz = $use_client_tz;

        $staff_photo = wp_get_attachment_image_src( $item->getStaff()->getAttachmentId(), 'full' );

        $this->appointment_end        = $this->tz( $item->getTotalEnd()->format( 'Y-m-d H:i:s' ) );
        $this->appointment_end_info   = $item->getService()->getEndTimeInfo();
        $this->appointment_notes      = $item->getCA()->getNotes();
        $this->appointment_start      = $this->tz( $item->getAppointment()->getStartDate() );
        $this->appointment_start_info = $item->getService()->getStartTimeInfo();
        $this->appointment_token      = $item->getCA()->getToken();
        $this->booking_number         = $item->getAppointment()->getId();
        $this->category_name          = $item->getService()->getTranslatedCategoryName();
        $this->client_timezone        = $item->getCA()->getTimeZone() ?: (
            $item->getCA()->getTimeZoneOffset() !== null
                ? 'UTC' . Utils\DateTime::guessTimeZone( - $item->getCA()->getTimeZoneOffset() * 60 )
                : ''
        );
        $this->number_of_persons      = $item->getCA()->getNumberOfPersons();
        $this->service_duration       = $item->getServiceDuration();
        $this->service_info           = $item->getService()->getTranslatedInfo();
        $this->service_name           = $item->getService()->getTranslatedTitle();
        $this->service_price          = $item->getServicePrice();
        $this->staff_email            = $item->getStaff()->getEmail();
        $this->staff_info             = $item->getStaff()->getTranslatedInfo();
        $this->staff_name             = $item->getStaff()->getTranslatedName();
        $this->staff_phone            = $item->getStaff()->getPhone();
        $this->staff_photo            = $staff_photo ? $staff_photo[0] : '';
        if ( ! $this->order->hasPayment() ) {
            $this->total_price        = $item->getTotalPrice();
            $this->total_tax          = $item->getTax();
            if ( Config::taxesActive() && get_option( 'bookly_taxes_in_price' ) == 'excluded' ) {
                $this->total_price += $this->total_tax;
            }
        }

        Proxy\Shared::prepareCodesForItem( $this );
    }

    /**
     * @inheritdoc
     */
    protected function getReplaceCodes( $format )
    {
        $replace_codes = parent::getReplaceCodes( $format );

        // Prepare data.
        $staff_photo  = '';
        if ( $format == 'html' ) {
            if ( $this->staff_photo != '' ) {
                // Staff photo as <img> tag.
                $staff_photo = sprintf(
                    '<img src="%s" alt="%s" />',
                    esc_attr( $this->staff_photo ),
                    esc_attr( $this->staff_name )
                );
            }
        }
        $cancel_appointment_confirm_url = get_option( 'bookly_url_cancel_confirm_page_url' );
        $cancel_appointment_confirm_url = $this->appointment_token
            ? add_query_arg( 'bookly-appointment-token', $this->appointment_token, $cancel_appointment_confirm_url )
            : '';

        // Add replace codes.
        $replace_codes += array(
            '{agenda_date}'                    => $this->agenda_date ? Utils\DateTime::formatDate( $this->agenda_date ) : '',
            '{amount_due}'                     => Utils\Price::format( $this->amount_due ),
            '{amount_paid}'                    => Utils\Price::format( $this->amount_paid ),
            '{appointment_date}'               => $this->appointment_start === null ? __( 'N/A', 'bookly' ) : Utils\DateTime::formatDate( $this->appointment_start ),
            '{appointment_time}'               => $this->appointment_start === null ? __( 'N/A', 'bookly' ) : ( $this->service_duration < DAY_IN_SECONDS ? Utils\DateTime::formatTime( $this->appointment_start ) : $this->appointment_start_info ),
            '{appointment_end_date}'           => $this->appointment_start === null ? __( 'N/A', 'bookly' ) : Utils\DateTime::formatDate( $this->appointment_end ),
            '{appointment_end_time}'           => $this->appointment_start === null ? __( 'N/A', 'bookly' ) : ( $this->service_duration < DAY_IN_SECONDS ? Utils\DateTime::formatTime( $this->appointment_end ) : $this->appointment_end_info ),
            '{appointment_notes}'              => $format == 'html' ? nl2br( $this->appointment_notes ) : $this->appointment_notes,
            '{approve_appointment_url}'        => $this->appointment_token ? admin_url( 'admin-ajax.php?action=bookly_approve_appointment&token=' . urlencode( Utils\Common::xorEncrypt( $this->appointment_token, 'approve' ) ) ) : '',
            '{booking_number}'                 => $this->booking_number,
            '{cancel_appointment_url}'         => $this->appointment_token ? admin_url( 'admin-ajax.php?action=bookly_cancel_appointment&token=' . $this->appointment_token ) : '',
            '{cancel_appointment_confirm_url}' => $cancel_appointment_confirm_url,
            '{category_name}'                  => $this->category_name,
            '{client_email}'                   => $this->client_email,
            '{client_address}'                 => $this->client_address,
            '{client_name}'                    => $this->client_name,
            '{client_first_name}'              => $this->client_first_name,
            '{client_last_name}'               => $this->client_last_name,
            '{client_phone}'                   => $this->client_phone,
            '{client_timezone}'                => $this->client_timezone,
            '{google_calendar_url}'            => sprintf( 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=%s&dates=%s/%s&details=%s',
                urlencode( $this->service_name ),
                date( 'Ymd\THis', strtotime( $this->appointment_start ) ),
                date( 'Ymd\THis', strtotime( $this->appointment_end ) ),
                urlencode( sprintf( "%s\n%s", $this->service_name, $this->staff_name ) )
            ),
            '{new_password}'                   => $this->new_password,
            '{new_username}'                   => $this->new_username,
            '{next_day_agenda}'                => $this->next_day_agenda,
            '{next_day_agenda_extended}'       => $this->next_day_agenda_extended,
            '{number_of_persons}'              => $this->number_of_persons,
            '{payment_type}'                   => Entities\Payment::typeToString( $this->payment_type ),
            '{payment_status}'                 => Entities\Payment::statusToString( $this->payment_status ),
            '{reject_appointment_url}'         => $this->appointment_token ? admin_url( 'admin-ajax.php?action=bookly_reject_appointment&token=' . urlencode( Utils\Common::xorEncrypt( $this->appointment_token, 'reject' ) ) ) : '',
            '{service_info}'                   => $format == 'html' ? nl2br( $this->service_info ) : $this->service_info,
            '{service_name}'                   => $this->service_name,
            '{service_price}'                  => Utils\Price::format( $this->service_price ),
            '{service_duration}'               => $this->appointment_start === null ? __( 'N/A', 'bookly' ) : Utils\DateTime::secondsToInterval( $this->service_duration ),
            '{site_address}'                   => $this->site_address,
            '{staff_email}'                    => $this->staff_email,
            '{staff_info}'                     => $format == 'html' ? nl2br( $this->staff_info ) : $this->staff_info,
            '{staff_name}'                     => $this->staff_name,
            '{staff_phone}'                    => $this->staff_phone,
            '{staff_photo}'                    => $staff_photo,
            '{tomorrow_date}'                  => Utils\DateTime::formatDate( date_create( current_time( 'mysql' ) )->modify( '+1 day' )->format( 'Y-m-d' ) ),
            '{total_price}'                    => Utils\Price::format( $this->total_price ),
            '{total_tax}'                      => Utils\Price::format( $this->total_tax ),
            '{total_price_no_tax}'             => Utils\Price::format( $this->total_price - $this->total_tax ),
            '{cancellation_reason}'            => $this->cancellation_reason,
        );
        $replace_codes['{cancel_appointment}'] = $format == 'html'
            ? sprintf( '<a href="%1$s">%1$s</a>', $replace_codes['{cancel_appointment_url}'] )
            : $replace_codes['{cancel_appointment_url}'];

        return Proxy\Shared::prepareReplaceCodes( $replace_codes, $this, $format );
    }

    /**
     * Get order.
     *
     * @return DataHolders\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Get item.
     *
     * @return DataHolders\Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Apply client time zone to given datetime string in WP time zone if use_client_tz is true.
     *
     * @param string $datetime
     * @return mixed
     */
    public function tz( $datetime )
    {
        if ( $this->use_client_tz && $datetime != '' ) {
            $time_zone        = $this->item->getCA()->getTimeZone();
            $time_zone_offset = $this->item->getCA()->getTimeZoneOffset();

            if ( $time_zone !== null ) {
                $datetime = date_create( $datetime . ' ' . Config::getWPTimeZone() );
                return date_format( date_timestamp_set( date_create( $time_zone ), $datetime->getTimestamp() ), 'Y-m-d H:i:s' );
            } else if ( $time_zone_offset !== null ) {
                return Utils\DateTime::applyTimeZoneOffset( $datetime, $time_zone_offset );
            }
        }

        return $datetime;
    }
}