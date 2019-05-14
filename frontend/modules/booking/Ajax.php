<?php
namespace Bookly\Frontend\Modules\Booking;

use Bookly\Lib;
use Bookly\Frontend\Components\Booking\InfoText;
use Bookly\Frontend\Modules\Booking\Lib\Steps;
use Bookly\Frontend\Modules\Booking\Lib\Errors;

/**
 * Class Ajax
 * @package Bookly\Frontend\Modules\Booking
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * @inheritdoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'anonymous' );
    }

    /**
     * 1. Step service.
     *
     * response JSON
     */
    public static function renderService()
    {
        $response = null;
        $form_id  = self::parameter( 'form_id' );

        if ( $form_id ) {
            $userData = new Lib\UserBookingData( $form_id );
            $userData->load();

            self::_handleTimeZone( $userData );

            if ( self::hasParameter( 'new_chain' ) ) {
                $userData->resetChain();
            }

            if ( self::hasParameter( 'edit_cart_item' ) ) {
                $cart_key = self::parameter( 'edit_cart_item' );
                $userData->setEditCartKeys( array( $cart_key ) );
                $userData->setChainFromCartItem( $cart_key );
            }

            $progress_tracker = self::_prepareProgressTracker( Steps::SERVICE, $userData );
            $info_text = InfoText::prepare( Steps::SERVICE, Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_service_step' ), $userData );

            // Available days and times.
            $days_times = Lib\Config::getDaysAndTimes();
            // Prepare week days that need to be checked.
            $days_checked = $userData->getDays();
            if ( empty( $days_checked ) ) {
                // Check all available days.
                $days_checked = array_keys( $days_times['days'] );
            }
            $bounding = Lib\Config::getBoundingDaysForPickadate();

            $casest = Lib\Config::getCaSeSt();

            if ( Lib\Config::locationsActive() ) {
                $locasest = $casest['locations'];
            } else {
                $locasest = array();
            }

            $response = array(
                'success'                    => true,
                'csrf_token'                 => Lib\Utils\Common::getCsrfToken(),
                'html'                       => self::renderTemplate( '1_service', array(
                    'progress_tracker' => $progress_tracker,
                    'info_text'        => $info_text,
                    'userData'         => $userData,
                    'days'             => $days_times['days'],
                    'times'            => $days_times['times'],
                    'days_checked'     => $days_checked,
                    'show_cart_btn'    => self::_showCartButton( $userData ),
                ), false ),
                'categories'                 => $casest['categories'],
                'chain'                      => $userData->chain->getItemsData(),
                'date_max'                   => $bounding['date_max'],
                'date_min'                   => $bounding['date_min'],
                'locations'                  => $locasest,
                'services'                   => $casest['services'],
                'staff'                      => $casest['staff'],
                'services_per_location'      => (bool) Lib\Proxy\Locations::servicesPerLocationAllowed(),
                'show_ratings'               => (bool) get_option( 'bookly_ratings_app_show_on_frontend' ),
                'service_name_with_duration' => (bool) get_option( 'bookly_app_service_name_with_duration' ),
                'required'                   => array(
                    'staff'    => (int) get_option( 'bookly_app_required_employee' ),
                    'location' => (int) ( Lib\Config::locationsActive() && ( get_option( 'bookly_app_required_location' ) || get_option( 'bookly_locations_allow_services_per_location' ) ) ),
                ),
            );
            $userData->sessionSave();
        } else {
            $response = array( 'success' => false, 'error' => Errors::FORM_ID_ERROR );
        }

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 2. Step Extras.
     *
     * response JSON
     */
    public static function renderExtras()
    {
        $response = null;
        $userData = new Lib\UserBookingData( self::parameter( 'form_id' ) );
        $loaded   = $userData->load();
        if ( ! $loaded ) {
            // all previous steps are skipped
            self::_setDataForSkippedServiceStep( $userData );
            $userData->setFirstStep( Steps::EXTRAS );
            $loaded = true;
        }

        if ( $loaded ) {
            if ( self::hasParameter( 'new_chain' ) ) {
                self::_handleTimeZone( $userData );
                self::_setDataForSkippedServiceStep( $userData );
            }

            if ( self::hasParameter( 'edit_cart_item' ) ) {
                $cart_key = self::parameter( 'edit_cart_item' );
                $userData
                    ->setEditCartKeys( array( $cart_key ) )
                    ->setChainFromCartItem( $cart_key );
            }

            $progress_tracker = self::_prepareProgressTracker( Steps::EXTRAS, $userData );
            $info_text = InfoText::prepare( Steps::EXTRAS, Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_extras_step' ), $userData );
            $show_cart_btn = self::_showCartButton( $userData );

            // Prepare money format for JavaScript.
            $price     = Lib\Utils\Price::format( 1 );
            $format    = str_replace( array( '0', '.', ',' ), '', $price );
            $precision = substr_count( $price, '0' );

            $response = array(
                'success'    => true,
                'csrf_token' => Lib\Utils\Common::getCsrfToken(),
                'currency'   => array( 'format' => $format, 'precision' => $precision ),
                'html'       => Proxy\ServiceExtras::getStepHtml( $userData, $show_cart_btn, $info_text, $progress_tracker, $userData->getFirstStep() != Steps::EXTRAS ),
            );
        } else {
            $response = array( 'success' => false, 'error' => Errors::SESSION_ERROR );
        }
        $userData->sessionSave();

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 3. Step time.
     *
     * response JSON
     */
    public static function renderTime()
    {
        $response = null;
        $userData = new Lib\UserBookingData( self::parameter( 'form_id' ) );
        $loaded   = $userData->load();

        if ( ! $loaded ) {
            // all previous steps are skipped
            self::_setDataForSkippedServiceStep( $userData );
            $userData->setFirstStep( Steps::TIME );
            $loaded = true;
        }

        if ( $loaded ) {
            self::_handleTimeZone( $userData );

            if ( self::hasParameter( 'new_chain' ) ) {
                self::_setDataForSkippedServiceStep( $userData );
            }

            if ( self::hasParameter( 'edit_cart_item' ) ) {
                $cart_key = self::parameter( 'edit_cart_item' );
                $userData
                    ->setEditCartKeys( array( $cart_key ) )
                    ->setChainFromCartItem( $cart_key );
            }

            $finder = new Lib\Slots\Finder( $userData );
            if ( self::hasParameter( 'selected_date' ) ) {
                $finder->setSelectedDate( self::parameter( 'selected_date' ) );
            } else {
                $finder->setSelectedDate( $userData->getDateFrom() );
            }
            $finder->prepare()->load();

            $progress_tracker = self::_prepareProgressTracker( Steps::TIME, $userData );
            $info_text = InfoText::prepare( Steps::TIME, Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_time_step' ), $userData );

            // Render slots by groups (day or month).
            $slots = $userData->getSlots();
            $selected_date = isset ( $slots[0][2] ) ? $slots[0][2] : null;

            $slots_data = array();
            foreach ( $finder->getSlots() as $group => $group_slots ) {
                /** @var Lib\Slots\Range[] $group_slots */
                $slots_data[ $group ] = array(
                    'title' => date_i18n( ( $finder->isServiceDurationInDays() ? 'M' : 'D, M d' ), strtotime( $group ) ),
                    'slots' => array(),
                );
                foreach ( $group_slots as $slot ) {
                    $slots_data[ $group ]['slots'][] = array(
                        'data'            => $slot->buildSlotData(),
                        'time_text'       => $slot->start()->toClientTz()->formatI18n( $finder->isServiceDurationInDays() ? 'D, M d' : get_option( 'time_format' ) ),
                        'status'          => $slot->waitingListEverStarted() ? 'waiting-list' : ( $slot->fullyBooked() ? 'booked' : '' ),
                        'additional_text' => $slot->waitingListEverStarted() ? '(' . $slot->maxOnWaitingList() . ')' : ( Lib\Config::groupBookingActive() ? Proxy\GroupBooking::getTimeSlotText( $slot ) : '' ),
                    );
                }
            }

            // Set response.
            $response = array(
                'success'         => true,
                'csrf_token'      => Lib\Utils\Common::getCsrfToken(),
                'has_slots'       => ! empty ( $slots_data ),
                'has_more_slots'  => $finder->hasMoreSlots(),
                'day_one_column'  => Lib\Config::showDayPerColumn(),
                'slots_data'      => $slots_data,
                'selected_date'   => $selected_date,
                'time_slots_wide' => Lib\Config::showWideTimeSlots(),
                'show_calendar'   => Lib\Config::showCalendar(),
                'is_rtl'          => is_rtl(),
                'html'            => self::renderTemplate( '3_time', array(
                    'progress_tracker'  => $progress_tracker,
                    'info_text'         => $info_text,
                    'date'              => Lib\Config::showCalendar() ? $finder->getSelectedDateForPickadate() : null,
                    'has_slots'         => ! empty ( $slots_data ),
                    'show_cart_btn'     => self::_showCartButton( $userData ),
                    'userData'          => $userData,
                ), false ),
            );

            if ( Lib\Config::showCalendar() ) {
                $bounding = Lib\Config::getBoundingDaysForPickadate();
                $response['date_max'] = $bounding['date_max'];
                $response['date_min'] = $bounding['date_min'];
                $response['disabled_days'] = $finder->getDisabledDaysForPickadate();
            }
        } else {
            $response = array( 'success' => false, 'error' => Errors::SESSION_ERROR );
        }
        $userData->sessionSave();

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * Render next time for step Time.
     *
     * response JSON
     */
    public static function renderNextTime()
    {
        $userData = new Lib\UserBookingData( self::parameter( 'form_id' ) );

        if ( $userData->load() ) {
            $finder = new Lib\Slots\Finder( $userData );
            $finder->setLastFetchedSlot( self::parameter( 'last_slot' ) );
            $finder->prepare()->load();

            $slots = $userData->getSlots();
            $selected_date = isset ( $slots[0][2] ) ? $slots[0][2] : null;

            $slots_data = array();
            foreach ( $finder->getSlots() as $group => $group_slots ) {
                /** @var Lib\Slots\Range[] $group_slots */
                $slots_data[ $group ] = array(
                    'title' => date_i18n( ( $finder->isServiceDurationInDays() ? 'M' : 'D, M d' ), strtotime( $group ) ),
                    'slots' => array(),
                );
                foreach ( $group_slots as $slot ) {
                    $slots_data[ $group ]['slots'][] = array(
                        'data'            => $slot->buildSlotData(),
                        'time_text'       => $slot->start()->toClientTz()->formatI18n( $finder->isServiceDurationInDays() ? 'D, M d' : get_option( 'time_format' ) ),
                        'status'          => $slot->waitingListEverStarted() ? 'waiting-list' : ( $slot->fullyBooked() ? 'booked' : '' ),
                        'additional_text' => $slot->waitingListEverStarted() ? '(' . $slot->maxOnWaitingList() . ')' : ( Lib\Config::groupBookingActive() ? Proxy\GroupBooking::getTimeSlotText( $slot ) : '' ),
                    );
                }
            }

            // Set response.
            $response = array(
                'success'        => true,
                'slots_data'     => $slots_data,
                'has_slots'      => ! empty( $slots_data ),
                'has_more_slots' => $finder->hasMoreSlots(), // show/hide the next button
                'selected_date'  => $selected_date,
            );
        } else {
            $response = array( 'success' => false, 'error' => Errors::SESSION_ERROR );
        }
        $userData->sessionSave();

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 4. Step repeat.
     *
     * response JSON
     */
    public static function renderRepeat()
    {
        $form_id  = self::parameter( 'form_id' );
        $userData = new Lib\UserBookingData( $form_id );

        if ( $userData->load() ) {
            $progress_tracker = self::_prepareProgressTracker( Steps::REPEAT, $userData );
            $info_text = InfoText::prepare( Steps::REPEAT, Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_repeat_step' ), $userData );

            // Available days and times.
            $bounding  = Lib\Config::getBoundingDaysForPickadate();
            $show_cart_btn = self::_showCartButton( $userData );
            $slots    = $userData->getSlots();
            $datetime = date_create( $slots[0][2] );
            $date_min = array(
                (int) $datetime->format( 'Y' ),
                (int) $datetime->format( 'n' ) - 1,
                (int) $datetime->format( 'j' ),
            );

            $schedule = array();
            $repeat_data = $userData->getRepeatData();
            if ( $repeat_data ) {
                $until = Lib\Slots\DatePoint::fromStrInClientTz( $repeat_data['until'] );
                foreach ( $slots as $slot ) {
                    $date = Lib\Slots\DatePoint::fromStr( $slot[2] );
                    if ( $until->lt( $date ) ) {
                        $until = $date->toClientTz();
                    }
                }

                $schedule = Proxy\RecurringAppointments::buildSchedule(
                    clone $userData,
                    $slots[0][2],
                    $until->format( 'Y-m-d' ),
                    $repeat_data['repeat'],
                    $repeat_data['params'],
                    array_map( function ( $slot ) { return $slot[2]; }, $slots )
                );
            }

            $response = array(
                'success'  => true,
                'html'     => Proxy\RecurringAppointments::getStepHtml( $userData, $show_cart_btn, $info_text, $progress_tracker ),
                'date_max' => $bounding['date_max'],
                'date_min' => $date_min,
                'repeated' => (int) $userData->getRepeated(),
                'repeat_data' => $userData->getRepeatData(),
                'schedule'    => $schedule,
                'short_date_format'  => Lib\Utils\DateTime::convertFormat( 'D, M d', Lib\Utils\DateTime::FORMAT_PICKADATE ),
                'pages_warning_info' => nl2br( Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_repeat_schedule_help' ) ),
                'could_be_repeated'  => Proxy\RecurringAppointments::canBeRepeated( true, $userData ),
            );
        } else {
            $response = array( 'success' => false, 'error' => Errors::SESSION_ERROR );
        }
        $userData->sessionSave();

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 5. Step cart.
     *
     * response JSON
     */
    public static function renderCart()
    {
        $userData = new Lib\UserBookingData( self::parameter( 'form_id' ) );
        $loaded   = $userData->load();
        if ( ! $loaded ) {
            // all previous steps are skipped
            self::_setDataForSkippedServiceStep( $userData );
            $userData->setFirstStep( Steps::CART );
            $loaded = true;
        }

        if ( $loaded ) {
            $userData = Proxy\Tasks::prepareUserData( $userData );
            if ( self::hasParameter( 'add_to_cart' ) ) {
                $userData->addChainToCart();
            }
            $progress_tracker = self::_prepareProgressTracker( Steps::CART, $userData );
            $info_text        = InfoText::prepare( Steps::CART, Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_cart_step' ), $userData );

            $response = array(
                'success' => true,
                'html'    => Proxy\Cart::getStepHtml( $userData, $progress_tracker, $info_text, $userData->getFirstStep() != Steps::CART ),
            );
        } else {
            $response = array( 'success' => false, 'error' => Errors::SESSION_ERROR );
        }
        $userData->sessionSave();

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 6. Step details.
     *
     * @throws
     */
    public static function renderDetails()
    {
        $userData = new Lib\UserBookingData( self::parameter( 'form_id' ) );
        $loaded   = $userData->load();
        if ( ! $loaded ) {
            // all previous steps are skipped
            self::_setDataForSkippedServiceStep( $userData );
            $userData->setFirstStep( Steps::DETAILS );
            $loaded = true;
        }

        if ( $loaded ) {
            $userData = Proxy\Tasks::prepareUserData( $userData );
            if ( ! Lib\Config::showStepCart() ) {
                $userData->addChainToCart();
            }

            $info_text       = Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_details_step' );
            $info_text_guest = ! get_current_user_id() && ! $userData->getFacebookId()
                ? Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_details_step_guest' )
                : '';

            // Render main template.
            $html = self::renderTemplate( '6_details', array(
                'progress_tracker' => self::_prepareProgressTracker( Steps::DETAILS, $userData ),
                'info_text'        => InfoText::prepare( Steps::DETAILS, $info_text, $userData ),
                'info_text_guest'  => InfoText::prepare( Steps::DETAILS, $info_text_guest, $userData ),
                'userData'         => $userData,
                'show_back_btn'    => $userData->getFirstStep() != Steps::DETAILS,
            ), false );

            // Render additional templates.
            $html .= self::renderTemplate( '_customer_duplicate_msg', array(), false );
            if (
                ! get_current_user_id() &&
                ! $userData->getFacebookId() && (
                    Lib\Config::showLoginButton() ||
                    strpos( $info_text . $info_text_guest, '{login_form}' ) !== false
                )
            ) {
                $html .= self::renderTemplate( '_login_form', array(), false );
            }

            $woocommerce_enabled = (int) Lib\Config::wooCommerceEnabled();

            $response = array(
                'success'               => true,
                'html'                  => $html,
                'update_details_dialog' => (int) get_option( 'bookly_cst_show_update_details_dialog' ),
                'intlTelInput'          => get_option( 'bookly_cst_phone_default_country' ) != 'disabled' ? array(
                    'enabled' => 1,
                    'utils'   => is_rtl() ? '' : plugins_url( 'intlTelInput.utils.js', Lib\Plugin::getDirectory() . '/frontend/resources/js/intlTelInput.utils.js' ),
                    'country' => get_option( 'bookly_cst_phone_default_country' ),
                ) : array(
                    'enabled' => 0,
                ),
                'woocommerce'           => array(
                    'enabled'  => $woocommerce_enabled,
                    'cart_url' => $woocommerce_enabled ? wc_get_cart_url() : '',
                ),
            );
        } else {
            $response = array( 'success' => false, 'error' => Errors::SESSION_ERROR );
        }
        $userData->sessionSave();

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 7. Step payment.
     *
     * response JSON
     */
    public static function renderPayment()
    {
        $userData = new Lib\UserBookingData( self::parameter( 'form_id' ) );

        if ( $userData->load() ) {
            $payment_disabled = Lib\Config::paymentStepDisabled();
            $show_cart        = Lib\Config::showStepCart();
            if ( ! $show_cart ) {
                $userData->addChainToCart();
            }

            if ( $userData->getCouponCode() ) {
                if ( ! $userData->getCoupon()->validForCart( $userData->cart ) || ! $userData->getCoupon()->validForCustomer( $userData->getCustomer() ) ) {
                    $userData->deleteCoupon();
                }
            }

            $cart_info = $userData->cart->getInfo();

            if ( $cart_info->getTotal() <= 0 || $cart_info->getDeposit() <= 0 ) {
                $payment_disabled = true;
            }

            if ( $payment_disabled == false ) {
                $progress_tracker = self::_prepareProgressTracker( Steps::PAYMENT, $userData );

                // Prepare info texts.
                $cart_items_count = count( $userData->cart->getItems() );
                $info_text_tpl    = Lib\Utils\Common::getTranslatedOption(
                    $cart_items_count > 1
                        ? 'bookly_l10n_info_payment_step_several_apps'
                        : 'bookly_l10n_info_payment_step_single_app'
                );
                $info_text        = InfoText::prepare( Steps::PAYMENT, $info_text_tpl, $userData );

                $options = array();
                if ( Lib\Config::payLocallyEnabled() ) {
                    $options['local'] = self::renderTemplate( '_payment_local', array( 'form_id' => self::parameter( 'form_id' ) ), false );
                }
                $options = Proxy\Shared::preparePaymentOptions(
                    $options,
                    self::parameter( 'form_id' ),
                    Lib\Proxy\Shared::showPaymentSpecificPrices( false ),
                    $cart_info,
                    $userData->extractPaymentStatus()
                );
                $order = explode( ',', get_option( 'bookly_pmt_order' ) );
                $payment_options =  array();
                if ( $order ) {
                    foreach ( $order as $payment_system ) {
                        if ( array_key_exists( $payment_system, $options ) ) {
                            $payment_options[] = $options[ $payment_system ];
                        }
                    }
                }
                foreach ( $options as $slug => $data ) {
                    if ( ! $order || ! in_array( $slug, $order ) ) {
                        $payment_options[] = $data;
                    }
                }

                // Set response.
                $response = array(
                    'success'                => true,
                    'disabled'               => false,
                    'html'                   => self::renderTemplate( '7_payment', array(
                        'form_id'          => self::parameter( 'form_id' ),
                        'progress_tracker' => $progress_tracker,
                        'info_text'        => $info_text,
                        'pay_local'        => Lib\Config::payLocallyEnabled(),
                        'payment_options'  => $payment_options,
                        'page_url'         => self::parameter( 'page_url' ),
                        'userData'         => $userData,
                    ), false ),
                    'stripe_publishable_key' => get_option( 'bookly_stripe_publishable_key' ),
                );
            } else {
                $response = array(
                    'success'  => true,
                    'disabled' => true,
                );
            }
        } else {
            $response = array( 'success' => false, 'error' => Errors::SESSION_ERROR );
        }
        $userData->sessionSave();

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 8. Step done ( complete ).
     *
     * response JSON
     */
    public static function renderComplete()
    {
        $userData = new Lib\UserBookingData( self::parameter( 'form_id' ) );
        if ( $userData->load() ) {
            $progress_tracker = self::_prepareProgressTracker( Steps::DONE, $userData );
            $error = self::parameter( 'error' );
            if ( $error == 'appointments_limit_reached' ) {
                $response = array(
                    'success' => true,
                    'html'    => self::renderTemplate( '8_complete', array(
                        'progress_tracker' => $progress_tracker,
                        'info_text'        => InfoText::prepare( Steps::DONE, Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_complete_step_limit_error' ), $userData ),
                    ), false ),
                );
            } else {
                $payment = $userData->extractPaymentStatus();
                do {
                    if ( $payment ) {
                        switch ( $payment['status'] ) {
                            case 'processing':
                                $info_text = InfoText::prepare( Steps::DONE, Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_complete_step_processing' ), $userData );
                                break ( 2 );
                        }
                    }
                    $info_text = InfoText::prepare( Steps::DONE, Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_complete_step' ), $userData );
                } while ( 0 );

                $response = array(
                    'success'        => true,
                    'html'           => self::renderTemplate( '8_complete', array(
                        'progress_tracker' => $progress_tracker,
                        'info_text'        => $info_text,
                    ), false ),
                    'final_step_url' => Lib\Proxy\Pro::getFinalStepUrl(),
                );
            }
        } else {
            $response = array( 'success' => false, 'error' => Errors::SESSION_ERROR );
        }
        $userData->sessionSave();

        // Output JSON response.
        wp_send_json( $response );
    }


    /**
     * Save booking data in session.
     */
    public static function sessionSave()
    {
        $form_id = self::parameter( 'form_id' );
        $errors  = array();
        if ( $form_id ) {
            $userData = new Lib\UserBookingData( $form_id );
            $userData->load();
            $parameters = self::parameters();
            $errors = $userData->validate( $parameters );
            if ( empty ( $errors ) ) {
                if ( self::hasParameter( 'no_extras' ) ) {
                    foreach ( $parameters['chain'] as $key => &$item ) {
                        $item['extras'] = array();
                    }
                } elseif ( self::hasParameter( 'extras' ) ) {
                    $parameters['chain'] = $userData->chain->getItemsData();
                    foreach ( $parameters['chain'] as $key => &$item ) {
                        // Decode extras.
                        $item['extras'] = json_decode( $parameters['extras'][ $key ], true );
                    }
                } elseif ( self::hasParameter( 'slots' ) ) {
                    // Decode slots.
                    $parameters['slots'] = json_decode( $parameters['slots'], true );
                } elseif ( self::hasParameter( 'cart' ) ) {
                    $parameters['captcha_ids'] = json_decode( $parameters['captcha_ids'], true );
                    foreach ( $parameters['cart'] as &$service ) {
                        // Remove captcha from custom fields.
                        $custom_fields = array_filter( json_decode( $service['custom_fields'], true ), function ( $field ) use ( $parameters ) {
                            return ! in_array( $field['id'], $parameters['captcha_ids'] );
                        } );
                        // Index the array numerically.
                        $service['custom_fields'] = array_values( $custom_fields );
                    }
                    // Copy custom fields to all cart items.
                    $cart           = array();
                    $cf_per_service = Lib\Config::customFieldsPerService();
                    $merge_cf       = Lib\Config::customFieldsMergeRepeating();
                    foreach ( $userData->cart->getItems() as $cart_key => $_cart_item ) {
                        $cart[ $cart_key ] = $cf_per_service
                            ? $parameters['cart'][ $merge_cf ? $_cart_item->getService()->getId() : $cart_key ]
                            : $parameters['cart'][0];
                    }
                    $parameters['cart'] = $cart;
                }
                $userData->fillData( $parameters );
            }
            $userData->sessionSave();
        }
        $errors['success'] = empty( $errors );

        wp_send_json( $errors );
    }

    /**
     * Save cart appointments.
     */
    public static function saveAppointment()
    {
        $userData = new Lib\UserBookingData( self::parameter( 'form_id' ) );

        if ( $userData->load() ) {
            $failed_cart_key = $userData->cart->getFailedKey();
            if ( $failed_cart_key === null ) {
                $cart_info = $userData->cart->getInfo();
                $is_payment_disabled  = Lib\Config::paymentStepDisabled();
                $is_pay_locally_enabled = Lib\Config::payLocallyEnabled();
                if ( $is_payment_disabled || $is_pay_locally_enabled || $cart_info->getPayNow() <= 0 ) {
                    // Handle coupon.
                    $coupon = $userData->getCoupon();
                    if ( $coupon ) {
                        $coupon->claim()->save();
                    }
                    // Handle payment.
                    $payment = null;
                    if ( ! $is_payment_disabled ) {
                        if ( $coupon && $cart_info->getTotal() <= 0 ) {
                            // Create fake payment record for 100% discount coupons.
                            $payment = new Lib\Entities\Payment();
                            $payment
                                ->setStatus( Lib\Entities\Payment::STATUS_COMPLETED )
                                ->setPaidType( Lib\Entities\Payment::PAY_IN_FULL )
                                ->setType( Lib\Entities\Payment::TYPE_COUPON )
                                ->setTotal( 0 )
                                ->setPaid( 0 )
                                ->save();
                        } elseif ( $cart_info->getTotal() > 0 ) {
                            // Create record for local payment.
                            $payment = new Lib\Entities\Payment();
                            $payment
                                ->setStatus( Lib\Entities\Payment::STATUS_PENDING )
                                ->setPaidType( Lib\Entities\Payment::PAY_IN_FULL )
                                ->setType( Lib\Entities\Payment::TYPE_LOCAL )
                                ->setTotal( $cart_info->getTotal() )
                                ->setTax( $cart_info->getTotalTax() )
                                ->setPaid( 0 )
                                ->save();
                        }
                    }
                    // Save cart.
                    $order = $userData->save( $payment );
                    if ( $payment !== null ) {
                        $payment->setDetailsFromOrder( $order, $cart_info )->save();
                    }
                    // Send notifications.
                    Lib\Notifications\Cart\Sender::send( $order );
                    $response = array(
                        'success' => true,
                    );
                } else {
                    $response = array(
                        'success' => false,
                        'error'   => Errors::PAY_LOCALLY_NOT_AVAILABLE,
                    );
                }
            } else {
                $response = array(
                    'success'         => false,
                    'failed_cart_key' => $failed_cart_key,
                    'error'           => Errors::CART_ITEM_NOT_AVAILABLE,
                );
            }
        } else {
            $response = array( 'success' => false, 'error' => Errors::SESSION_ERROR );
        }
        $userData->sessionSave();

        wp_send_json( $response );
    }

    /**
     * Check cart.
     */
    public static function checkCart()
    {
        $userData = new Lib\UserBookingData( self::parameter( 'form_id' ) );

        if ( $userData->load() ) {
            $failed_cart_key = $userData->cart->getFailedKey();
            if ( $failed_cart_key === null ) {
                $response = array( 'success' => true );
            } else {
                $response = array(
                    'success'         => false,
                    'failed_cart_key' => $failed_cart_key,
                    'error'           => Errors::CART_ITEM_NOT_AVAILABLE,
                );
            }
        } else {
            $response = array( 'success' => false, 'error' => Errors::INVALID_GATEWAY );
        }

        wp_send_json( $response );
    }

    /**
     * Cancel Appointment using token.
     */
    public static function cancelAppointment()
    {
        $customer_appointment = new Lib\Entities\CustomerAppointment();

        $allow_cancel = true;
        if ( $customer_appointment->loadBy( array( 'token' => self::parameter( 'token' ) ) ) ) {
            $appointment = new Lib\Entities\Appointment();
            $minimum_time_prior_cancel = (int) Lib\Proxy\Pro::getMinimumTimePriorCancel();
            if ( $minimum_time_prior_cancel > 0
                 && $appointment->load( $customer_appointment->getAppointmentId() )
                 && $appointment->getStartDate() !== null
            ) {
                $allow_cancel_time = strtotime( $appointment->getStartDate() ) - $minimum_time_prior_cancel;
                if ( current_time( 'timestamp' ) > $allow_cancel_time ) {
                    $allow_cancel = false;
                }
            }
            if ( $customer_appointment->getStatus() == Lib\Entities\CustomerAppointment::STATUS_DONE ) {
                $allow_cancel = false;
            }
            if ( $allow_cancel ) {
                $customer_appointment->cancel();
            }
        }

        if ( $url = $allow_cancel ? get_option( 'bookly_url_cancel_page_url' ) : get_option( 'bookly_url_cancel_denied_page_url' ) ) {
            wp_redirect( $url );
            self::renderTemplate( 'redirection', compact( 'url' ) );
            exit;
        }

        $url = home_url();
        if ( isset ( $_SERVER['HTTP_REFERER'] ) ) {
            if ( parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_HOST ) == parse_url( $url, PHP_URL_HOST ) ) {
                // Redirect back if user came from our site.
                $url = $_SERVER['HTTP_REFERER'];
            }
        }
        wp_redirect( $url );
        self::renderTemplate( 'redirection', compact( 'url' ) );
        exit;
    }

    /**
     * Approve appointment using token.
     */
    public static function approveAppointment()
    {
        $url = get_option( 'bookly_url_approve_denied_page_url' );

        // Decode token.
        $token = Lib\Utils\Common::xorDecrypt( self::parameter( 'token' ), 'approve' );
        $ca_to_approve = new Lib\Entities\CustomerAppointment();
        if ( $ca_to_approve->loadBy( array( 'token' => $token ) ) ) {
            $item = Lib\DataHolders\Booking\Item::collect( $ca_to_approve, Lib\Proxy\CustomStatuses::prepareFreeStatuses( array(
                Lib\Entities\CustomerAppointment::STATUS_APPROVED,
                Lib\Entities\CustomerAppointment::STATUS_REJECTED,
                Lib\Entities\CustomerAppointment::STATUS_CANCELLED,
                Lib\Entities\CustomerAppointment::STATUS_DONE,
            ) ) );

            if ( $item ) {
                $success = true;
                foreach ( $item->getItems() as $simple ) {
                    $ca = $simple->getCA();
                    if ( $ca->getStatus() == Lib\Entities\CustomerAppointment::STATUS_WAITLISTED ) {
                        $info = $simple->getAppointment()->getNopInfo();
                        if ( $info['total_nop'] + $ca->getNumberOfPersons() > $info['capacity_max'] ) {
                            $success = false;
                            break;
                        }
                    }
                }
                if ( $success ) {
                    $item->setStatus( Lib\Entities\CustomerAppointment::STATUS_APPROVED );
                    foreach ( $item->getItems() as $simple ) {
                        if ( $simple->getCA()->save() ) {
                            $appointment = $simple->getAppointment();
                            // Google Calendar.
                            Lib\Proxy\Pro::syncGoogleCalendarEvent( $appointment );
                            // Outlook Calendar.
                            Lib\Proxy\OutlookCalendar::syncEvent( $appointment );
                            // Waiting list.
                            Lib\Proxy\WaitingList::handleParticipantsChange( $appointment );
                        }
                    }
                    Lib\Notifications\Booking\Sender::send( $item );
                    $url = get_option( 'bookly_url_approve_page_url' );
                }
            }
        }

        wp_redirect( $url );
        self::renderTemplate( 'redirection', compact( 'url' ) );
        exit ( 0 );
    }

    /**
     * Reject appointment using token.
     */
    public static function rejectAppointment()
    {
        $url = get_option( 'bookly_url_reject_denied_page_url' );

        // Decode token.
        $token = Lib\Utils\Common::xorDecrypt( self::parameter( 'token' ), 'reject' );
        $ca_to_reject = new Lib\Entities\CustomerAppointment();
        if ( $ca_to_reject->loadBy( array( 'token' => $token ) ) ) {
            $item = Lib\DataHolders\Booking\Item::collect( $ca_to_reject, Lib\Proxy\CustomStatuses::prepareFreeStatuses( array(
                Lib\Entities\CustomerAppointment::STATUS_REJECTED,
                Lib\Entities\CustomerAppointment::STATUS_CANCELLED,
                Lib\Entities\CustomerAppointment::STATUS_DONE,
            ) ) );

            if ( $item ) {
                $item->setStatus( Lib\Entities\CustomerAppointment::STATUS_REJECTED );
                Lib\Notifications\Booking\Sender::send( $item );

                foreach ( $item->getItems() as $simple ) {
                    if ( $simple->getCA()->save() ) {
                        $appointment = $simple->getAppointment();
                        if ( $simple->getExtras() != '[]' ) {
                            $extras_duration = $appointment->getMaxExtrasDuration();
                            if ( $appointment->getExtrasDuration() != $extras_duration ) {
                                $appointment->setExtrasDuration( $extras_duration );
                                $appointment->save();
                            }
                        }
                        // Google Calendar.
                        Lib\Proxy\Pro::syncGoogleCalendarEvent( $appointment );
                        // Outlook Calendar.
                        Lib\Proxy\OutlookCalendar::syncEvent( $appointment );
                        // Waiting list.
                        Lib\Proxy\WaitingList::handleParticipantsChange( $appointment );
                    }
                }
                $url = get_option( 'bookly_url_reject_page_url' );
            }
        }

        wp_redirect( $url );
        self::renderTemplate( 'redirection', compact( 'url' ) );
        exit ( 0 );
    }

    /**
     * Log in to WordPress in the Details step.
     */
    public static function wpUserLogin()
    {
        $response = null;
        $userData = new Lib\UserBookingData( self::parameter( 'form_id' ) );

        if ( $userData->load() ) {
            add_action( 'set_logged_in_cookie', function ( $logged_in_cookie ) {
                $_COOKIE[ LOGGED_IN_COOKIE ] = $logged_in_cookie;
            } );
            /** @var \WP_User $user */
            $user = wp_signon();
            if ( is_wp_error( $user ) ) {
                $response = array( 'success' => false, 'error' => Errors::INCORRECT_USERNAME_PASSWORD );
            } else {
                wp_set_current_user( $user->ID, $user->user_login );
                $customer = new Lib\Entities\Customer();
                if ( $customer->loadBy( array( 'wp_user_id' => $user->ID ) ) ) {
                    $user_info = array(
                        'email'              => $customer->getEmail(),
                        'full_name'          => $customer->getFullName(),
                        'first_name'         => $customer->getFirstName(),
                        'last_name'          => $customer->getLastName(),
                        'phone'              => $customer->getPhone(),
                        'country'            => $customer->getCountry(),
                        'state'              => $customer->getState(),
                        'postcode'           => $customer->getPostcode(),
                        'city'               => $customer->getCity(),
                        'street'             => $customer->getStreet(),
                        'street_number'      => $customer->getStreetNumber(),
                        'additional_address' => $customer->getAdditionalAddress(),
                        'birthday'           => $customer->getBirthday(),
                        'info_fields'        => json_decode( $customer->getInfoFields() ),
                    );
                } else {
                    $user_info  = array(
                        'email'      => $user->user_email,
                        'full_name'  => $user->display_name,
                        'first_name' => $user->user_firstname,
                        'last_name'  => $user->user_lastname,
                    );
                }
                $userData->fillData( $user_info );
                $response = array(
                    'success' => true,
                    'data'    => $user_info + array( 'csrf_token' => Lib\Utils\Common::getCsrfToken() ),
                );
            }
        } else {
            $response = array( 'success' => false, 'error' => Errors::SESSION_ERROR );
        }
        $userData->sessionSave();

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * Render progress tracker into a variable.
     *
     * @param int $step
     * @param Lib\UserBookingData $userData
     * @return string
     */
    private static function _prepareProgressTracker( $step, Lib\UserBookingData $userData )
    {
        $result = '';

        if ( get_option( 'bookly_app_show_progress_tracker' ) ) {
            $payment_disabled = Lib\Config::paymentStepDisabled();
            if ( ! $payment_disabled && $step > Steps::SERVICE ) {
                if ( $step < Steps::CART ) {
                    // step Cart.
                    // Assume that payment is disabled and check chain items.
                    // If one is incomplete or its price is more than zero then the payment step should be displayed.
                    $payment_disabled = true;
                    foreach ( $userData->chain->getItems() as $item ) {
                        if ( $item->hasPayableExtras() ) {
                            $payment_disabled = false;
                            break;
                        } else {
                            if ( $item->getService()->getType() == Lib\Entities\Service::TYPE_SIMPLE ) {
                                $staff_ids = $item->getStaffIds();
                                $staff     = null;
                                if ( count( $staff_ids ) == 1 ) {
                                    $staff = Lib\Entities\Staff::find( $staff_ids[0] );
                                }
                                if ( $staff ) {
                                    $staff_service = new Lib\Entities\StaffService();
                                    $staff_service->loadBy( array(
                                        'staff_id'   => $staff->getId(),
                                        'service_id' => $item->getService()->getId(),
                                        'location_id' => Lib\Proxy\Locations::prepareStaffLocationId( $item->getLocationId(), $staff->getId() ) ?: null,
                                    ) );
                                    if ( $staff_service->getPrice() > 0 ) {
                                        $payment_disabled = false;
                                        break;
                                    }
                                } else {
                                    $payment_disabled = false;
                                    break;
                                }
                            } else {
                                // Service::TYPE_COMPOUND
                                if ( $item->getService()->getPrice() > 0 ) {
                                    $payment_disabled = false;
                                    break;
                                }
                            }
                        }
                    }
                } else {
                    $cart_info = $userData->cart->getInfo();
                    if ( $cart_info->getTotal() == 0 || $cart_info->getDeposit() == 0 ) {
                        $payment_disabled = true;
                    }
                }
            }

            $result = self::renderTemplate( '_progress_tracker', array(
                'step' => $step,
                'show_cart' => Lib\Config::showStepCart(),
                'payment_disabled'   => $payment_disabled,
                'skip_service_step'  => Lib\Session::hasFormVar( self::parameter( 'form_id' ), 'skip_service_step' ),
                // step extras before step time
                'step_extras_active' => $step > 3 || ( $step >= 2 && self::parameter( 'action' ) == 'bookly_render_extras' )
            ), false );
        }

        return $result;
    }

    /**
     * Check if cart button should be shown.
     *
     * @param Lib\UserBookingData $userData
     * @return bool
     */
    private static function _showCartButton( Lib\UserBookingData $userData )
    {
        return Lib\Config::showStepCart() && count( $userData->cart->getItems() );
    }

    /**
     * Add data for the skipped Service step.
     *
     * @param Lib\UserBookingData $userData
     */
    private static function _setDataForSkippedServiceStep( Lib\UserBookingData $userData )
    {
        // Staff ids.
        $defaults   = Lib\Session::getFormVar( self::parameter( 'form_id' ), 'defaults' );
        $service_id = $defaults['service_id'];
        if ( $defaults['staff_id'] == 0 ) {
            $service    = Lib\Entities\Service::find( $defaults['service_id'] );
            $service_id = ( $service && $service->withSubServices() )
                ? reset( $service->getSubServices() )->getId()
                : $service_id;
            $staff_ids  = Lib\Entities\StaffService::query()
                ->where( 'service_id', $service_id )
                ->fetchCol( 'staff_id' );
        } else {
            $staff_ids = array( $defaults['staff_id'] );
        }
        // Date.
        $date_from  = Lib\Slots\DatePoint::now()->modify( Lib\Proxy\Pro::getMinimumTimePriorBooking() );
        // Days and times.
        $days_times = Lib\Config::getDaysAndTimes();
        $time_from  = key( $days_times['times'] );
        end( $days_times['times'] );

        $userData->chain->clear();
        $chain_item = new Lib\ChainItem();
        $chain_item
            ->setNumberOfPersons( 1 )
            ->setQuantity( 1 )
            ->setUnits( 1 )
            ->setServiceId( $service_id )
            ->setStaffIds( $staff_ids )
            ->setLocationId( $defaults['location_id'] ?: null );
        $userData->chain->add( $chain_item );

        $userData->fillData( array(
            'date_from'      => $date_from->toClientTz()->format( 'Y-m-d' ),
            'days'           => array_keys( $days_times['days'] ),
            'edit_cart_keys' => array(),
            'slots'          => array(),
            'time_from'      => $time_from,
            'time_to'        => key( $days_times['times'] ),
        ) );
    }

    /**
     * Handle time zone parameters.
     *
     * @param Lib\UserBookingData $userData
     */
    private static function _handleTimeZone( Lib\UserBookingData $userData )
    {
        $time_zone        = null;
        $time_zone_offset = null;  // in minutes

        if ( self::hasParameter( 'time_zone_offset' ) ) {
            // Browser values.
            $time_zone        = self::parameter( 'time_zone' );
            $time_zone_offset = self::parameter( 'time_zone_offset' );
        } else if ( self::hasParameter( 'time_zone' ) ) {
            // WordPress value.
            $time_zone = self::parameter( 'time_zone' );
            if ( preg_match( '/^UTC[+-]/', $time_zone ) ) {
                $offset           = preg_replace( '/UTC\+?/', '', $time_zone );
                $time_zone        = null;
                $time_zone_offset = - $offset * 60;
            } else {
                $time_zone_offset = - timezone_offset_get( timezone_open( $time_zone ), new \DateTime() ) / 60;
            }
        }

        if ( $time_zone !== null || $time_zone_offset !== null ) {
            // Client time zone.
            $userData
                ->setTimeZone( $time_zone )
                ->setTimeZoneOffset( $time_zone_offset )
                ->applyTimeZone();
        }
    }

    /**
     * Override parent method to exclude actions from CSRF token verification.
     *
     * @param string $action
     * @return bool
     */
    protected static function csrfTokenValid( $action = null )
    {
        $excluded_actions = array(
            'approveAppointment',
            'cancelAppointment',
            'rejectAppointment',
            'renderService',
            'renderExtras',
            'renderTime',
        );

        return in_array( $action, $excluded_actions ) || parent::csrfTokenValid( $action );
    }
}