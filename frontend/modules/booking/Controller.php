<?php
namespace BooklyLite\Frontend\Modules\Booking;

use BooklyLite\Lib;
use BooklyLite\Lib\DataHolders\Booking as DataHolders;

/**
 * Class Controller
 * @package BooklyLite\Frontend\Modules\Booking
 */
class Controller extends Lib\Base\Controller
{
    /** @var  Components */
    protected $components;

    public function __construct()
    {
        parent::__construct();
        $this->components = Components::getInstance();
    }

    protected function getPermissions()
    {
        return array( '_this' => 'anonymous' );
    }

    /**
     * Render Bookly shortcode.
     *
     * @param $attributes
     * @return string
     */
    public function renderShortCode( $attributes )
    {
        global $sitepress;

        // Disable caching.
        Lib\Utils\Common::noCache();

        $assets = '';

        if ( get_option( 'bookly_gen_link_assets_method' ) == 'print' ) {
            $print_assets = ! wp_script_is( 'bookly', 'done' );
            if ( $print_assets ) {
                ob_start();

                // The styles and scripts are registered in Frontend.php
                wp_print_styles( 'bookly-intlTelInput' );
                wp_print_styles( 'bookly-ladda-min' );
                wp_print_styles( 'bookly-picker' );
                wp_print_styles( 'bookly-picker-date' );
                wp_print_styles( 'bookly-main' );

                wp_print_scripts( 'bookly-spin' );
                wp_print_scripts( 'bookly-ladda' );
                wp_print_scripts( 'bookly-picker' );
                wp_print_scripts( 'bookly-picker-date' );
                wp_print_scripts( 'bookly-hammer' );
                wp_print_scripts( 'bookly-jq-hammer' );
                wp_print_scripts( 'bookly-intlTelInput' );
                // Android animation.
                if ( stripos( strtolower( $_SERVER['HTTP_USER_AGENT'] ), 'android' ) !== false ) {
                    wp_print_scripts( 'bookly-jquery-animate-enhanced' );
                }
                Lib\Proxy\Shared::printBookingAssets();
                wp_print_scripts( 'bookly' );

                $assets = ob_get_clean();
            }
        } else {
            $print_assets = true; // to print CSS in template.
        }

        // Generate unique form id.
        $form_id = uniqid();

        // Find bookings with any of payment statuses ( PayPal, 2Checkout, PayU Latam ).
        $status = array( 'booking' => 'new' );
        foreach ( Lib\Session::getAllFormsData() as $saved_form_id => $data ) {
            if ( isset ( $data['payment'] ) ) {
                if ( ! isset ( $data['payment']['processed'] ) ) {
                    switch ( $data['payment']['status'] ) {
                        case 'success':
                        case 'processing':
                            $form_id = $saved_form_id;
                            $status = array( 'booking' => 'finished' );
                            break;
                        case 'cancelled':
                        case 'error':
                            $form_id = $saved_form_id;
                            end( $data['cart'] );
                            $status = array( 'booking' => 'cancelled', 'cart_key' => key( $data['cart'] ) );
                            break;
                    }
                    // Mark this form as processed for cases when there are more than 1 booking form on the page.
                    $data['payment']['processed'] = true;
                    Lib\Session::setFormVar( $saved_form_id, 'payment', $data['payment'] );
                }
            } elseif ( $data['last_touched'] + 30 * MINUTE_IN_SECONDS < time() ) {
                // Destroy forms older than 30 min.
                Lib\Session::destroyFormData( $saved_form_id );
            }
        }

        // Handle shortcode attributes.
        $hide_date_and_time = (bool) @$attributes['hide_date_and_time'];
        $fields_to_hide = isset ( $attributes['hide'] ) ? explode( ',', $attributes['hide'] ) : array();
        $staff_member_id = (int) ( @$_GET['staff_id'] ?: @$attributes['staff_member_id'] );

        $attrs = array(
            'location_id'            => (int) ( @$_GET['loc_id']     ?: @$attributes['location_id'] ),
            'category_id'            => (int) ( @$_GET['cat_id']     ?: @$attributes['category_id'] ),
            'service_id'             => (int) ( @$_GET['service_id'] ?: @$attributes['service_id'] ),
            'staff_member_id'        => $staff_member_id,
            'hide_categories'        => in_array( 'categories',      $fields_to_hide ) ? true : (bool) @$attributes['hide_categories'],
            'hide_services'          => in_array( 'services',        $fields_to_hide ) ? true : (bool) @$attributes['hide_services'],
            'hide_staff_members'     => ( in_array( 'staff_members', $fields_to_hide ) ? true : (bool) @$attributes['hide_staff_members'] )
                                     && ( get_option( 'bookly_app_required_employee' ) ? $staff_member_id : true ),
            'hide_date'              => $hide_date_and_time ? true : in_array( 'date',       $fields_to_hide ),
            'hide_week_days'         => $hide_date_and_time ? true : in_array( 'week_days',  $fields_to_hide ),
            'hide_time_range'        => $hide_date_and_time ? true : in_array( 'time_range', $fields_to_hide ),
            'show_number_of_persons' => (bool) @$attributes['show_number_of_persons'],
            'show_service_duration'  => (bool) get_option( 'bookly_app_service_name_with_duration' ),
            // Add-ons.
            'hide_locations'         => true,
            'hide_quantity'          => true,
        );
        // Set service step attributes for Add-ons.
        if ( Lib\Config::locationsEnabled() ) {
            $attrs['hide_locations'] = in_array( 'locations', $fields_to_hide );
        }
        if ( Lib\Config::multiplyAppointmentsEnabled() ) {
            $attrs['hide_quantity']  = in_array( 'quantity',  $fields_to_hide );
        }

        $service_part1 = (
            ! $attrs['show_number_of_persons'] &&
            $attrs['hide_categories'] &&
            $attrs['hide_services'] &&
            $attrs['service_id'] &&
            $attrs['hide_staff_members'] &&
            $attrs['hide_locations'] &&
            $attrs['hide_quantity']
        );
        $service_part2 = (
            $attrs['hide_date'] &&
            $attrs['hide_week_days'] &&
            $attrs['hide_time_range']
        );
        if ( $service_part1 && $service_part2 ) {
            // Store attributes in session for later use in Time step.
            Lib\Session::setFormVar( $form_id, 'attrs', $attrs );
            Lib\Session::setFormVar( $form_id, 'last_touched', time() );
        }
        $skip_steps = array(
            'service_part1' => (int) $service_part1,
            'service_part2' => (int) $service_part2,
            'extras' => (int) ( ! Lib\Config::serviceExtrasEnabled() ||
                $service_part1 && ! Lib\Proxy\ServiceExtras::findByServiceId( $attrs['service_id'] ) ),
            'repeat' => (int) ( ! Lib\Config::recurringAppointmentsEnabled() ),
        );
        // Prepare URL for AJAX requests.
        $ajax_url = admin_url( 'admin-ajax.php' );
        // Support WPML.
        if ( $sitepress instanceof \SitePress ) {
            $ajax_url .= ( strpos( $ajax_url, '?' ) ? '&' : '?' ) . 'lang=' . $sitepress->get_current_language();
        }
        $woocommerce_enabled = (int) Lib\Config::wooCommerceEnabled();
        $options = array(
            'intlTelInput' => array( 'enabled' => 0 ),
            'woocommerce'  => array( 'enabled' => $woocommerce_enabled, 'cart_url' => $woocommerce_enabled ? WC()->cart->get_cart_url() : '' ),
            'cart'         => array( 'enabled' => $woocommerce_enabled ? 0 : (int) Lib\Config::showStepCart() ),
        );
        if ( get_option( 'bookly_cst_phone_default_country' ) != 'disabled' ) {
            $options['intlTelInput']['enabled'] = 1;
            $options['intlTelInput']['utils']   = plugins_url( 'intlTelInput.utils.js', Lib\Plugin::getDirectory() . '/frontend/resources/js/intlTelInput.utils.js' );
            $options['intlTelInput']['country'] = get_option( 'bookly_cst_phone_default_country' );
        }
        $required = array(
            'staff' => (int) get_option( 'bookly_app_required_employee' )
        );
        if ( Lib\Config::locationsEnabled() ) {
            $required['location'] = (int) get_option( 'bookly_app_required_location' );
        }

        // Custom CSS.
        $custom_css = get_option( 'bookly_app_custom_styles' );

        return $assets . $this->render(
            'short_code',
            compact( 'attrs', 'options', 'required', 'print_assets', 'form_id', 'ajax_url', 'status', 'skip_steps', 'custom_css' ),
            false
        );
    }

    /**
     * 1. Step service.
     *
     * response JSON
     */
    public function executeRenderService()
    {
        $response = null;
        $form_id  = $this->getParameter( 'form_id' );

        if ( $form_id ) {
            $userData = new Lib\UserBookingData( $form_id );
            $userData->load();

            if ( $this->hasParameter( 'new_chain' ) ) {
                $userData->resetChain();
            }

            if ( $this->hasParameter( 'edit_cart_item' ) ) {
                $cart_key = $this->getParameter( 'edit_cart_item' );
                $userData->setEditCartKeys( array( $cart_key ) );
                $userData->setChainFromCartItem( $cart_key );
            }

            if ( Lib\Config::useClientTimeZone() ) {
                // Client time zone.
                $userData
                    ->setTimeZone( $this->getParameter( 'time_zone' ) )
                    ->setTimeZoneOffset( $this->getParameter( 'time_zone_offset' ) )
                    ->applyTimeZone()
                    ->setDateFrom(
                        Lib\Slots\DatePoint::now()
                            ->modify( Lib\Config::getMinimumTimePriorBooking() )
                            ->toClientTz()
                            ->format( 'Y-m-d' )
                    );
            }

            $progress_tracker = $this->_prepareProgressTracker( 1, $userData );
            $info_text = $this->components->prepareInfoText( 1, Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_service_step' ), $userData );

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

            if ( class_exists( '\BooklyLocations\Lib\Plugin', false ) ) {
                $locasest = $casest['locations'];
            } else {
                $locasest = array();
            }

            $response = array(
                'success'    => true,
                'csrf_token' => Lib\Utils\Common::getCsrfToken(),
                'html'       => $this->render( '1_service', array(
                    'progress_tracker' => $progress_tracker,
                    'info_text'        => $info_text,
                    'userData'         => $userData,
                    'days'             => $days_times['days'],
                    'times'            => $days_times['times'],
                    'days_checked'     => $days_checked,
                    'show_cart_btn'    => $this->_showCartButton( $userData )
                ), false ),
                'categories' => $casest['categories'],
                'chain'      => $userData->chain->getItemsData(),
                'date_max'   => $bounding['date_max'],
                'date_min'   => $bounding['date_min'],
                'locations'  => $locasest,
                'services'   => $casest['services'],
                'staff'      => $casest['staff'],
            );
        } else {
            $response = array( 'success' => false, 'error_code' => 2, 'error' => __( 'Form ID error.', 'bookly' ) );
        }

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 2. Step Extras.
     *
     * response JSON
     */
    public function executeRenderExtras()
    {
        $response = null;
        $userData = new Lib\UserBookingData( $this->getParameter( 'form_id' ) );
        $loaded   = $userData->load();
        if ( ! $loaded && Lib\Session::hasFormVar( $this->getParameter( 'form_id' ), 'attrs' ) ) {
            $loaded = true;
        }

        if ( $loaded ) {
            if ( $this->hasParameter( 'new_chain' ) ) {
                $this->_setDataForSkippedServiceStep( $userData );
            }

            if ( $this->hasParameter( 'edit_cart_item' ) ) {
                $cart_key = $this->getParameter( 'edit_cart_item' );
                $userData
                    ->setEditCartKeys( array( $cart_key ) )
                    ->setChainFromCartItem( $cart_key );
            }

            $progress_tracker = $this->_prepareProgressTracker( 2, $userData );
            $info_text = $this->components->prepareInfoText( 2, Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_extras_step' ), $userData );
            $show_cart_btn = $this->_showCartButton( $userData );

            // Prepare money format for JavaScript.
            $price     = Lib\Utils\Price::format( 1 );
            $format    = str_replace( array( '0', '.', ',' ), '', $price );
            $precision = substr_count( $price, '0' );

            $response = array(
                'success'       => true,
                'csrf_token'    => Lib\Utils\Common::getCsrfToken(),
                'currency'      => array( 'format' => $format, 'precision' => $precision ),
                'html'          => Lib\Proxy\ServiceExtras::getStepHtml( $userData, $show_cart_btn, $info_text, $progress_tracker ),
            );
        } else {
            $response = array( 'success' => false, 'error_code' => 1, 'error' => __( 'Session error.', 'bookly' ) );
        }

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 3. Step time.
     *
     * response JSON
     */
    public function executeRenderTime()
    {
        $response = null;
        $userData = new Lib\UserBookingData( $this->getParameter( 'form_id' ) );
        $loaded   = $userData->load();

        if ( ! $loaded && Lib\Session::hasFormVar( $this->getParameter( 'form_id' ), 'attrs' ) ) {
            $loaded = true;
        }

        if ( $loaded ) {
            if ( $this->hasParameter( 'new_chain' ) ) {
                $this->_setDataForSkippedServiceStep( $userData );
            }

            if ( $this->hasParameter( 'edit_cart_item' ) ) {
                $cart_key = $this->getParameter( 'edit_cart_item' );
                $userData
                    ->setEditCartKeys( array( $cart_key ) )
                    ->setChainFromCartItem( $cart_key );
            }

            $finder = new Lib\Slots\Finder( $userData );
            if ( $this->hasParameter( 'selected_date' ) ) {
                $finder->setSelectedDate( $this->getParameter( 'selected_date' ) );
            } else {
                $finder->setSelectedDate( $userData->getDateFrom() );
            }
            $finder->prepare()->load();

            $progress_tracker = $this->_prepareProgressTracker( 3, $userData );
            $info_text = $this->components->prepareInfoText( 3, Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_time_step' ), $userData );

            // Render slots by groups (day or month).
            $slots = $userData->getSlots();
            $selected_date = isset ( $slots[0][2] ) ? $slots[0][2] : null;
            $slots = array();
            foreach ( $finder->getSlots() as $group => $group_slots ) {
                $slots[ $group ] = preg_replace( '/>\s+</', '><', $this->render( '_time_slots', array(
                    'group' => $group,
                    'slots' => $group_slots,
                    'duration_in_days' => $finder->isServiceDurationInDays(),
                    'selected_date' => $selected_date,
                ), false ) );
            }

            // Set response.
            $response = array(
                'success'        => true,
                'csrf_token'     => Lib\Utils\Common::getCsrfToken(),
                'has_slots'      => ! empty ( $slots ),
                'has_more_slots' => $finder->hasMoreSlots(),
                'day_one_column' => Lib\Config::showDayPerColumn(),
                'slots'          => $slots,
                'html'           => $this->render( '3_time', array(
                    'progress_tracker' => $progress_tracker,
                    'info_text'        => $info_text,
                    'date'             => Lib\Config::showCalendar() ? $finder->getSelectedDateForPickadate() : null,
                    'has_slots'        => ! empty ( $slots ),
                    'show_cart_btn'    => $this->_showCartButton( $userData )
                ), false ),
            );

            if ( Lib\Config::showCalendar() ) {
                $bounding = Lib\Config::getBoundingDaysForPickadate();
                $response['date_max'] = $bounding['date_max'];
                $response['date_min'] = $bounding['date_min'];
                $response['disabled_days'] = $finder->getDisabledDaysForPickadate();
            }
        } else {
            $response = array( 'success' => false, 'error_code' => 1, 'error' => __( 'Session error.', 'bookly' ) );
        }

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * Render next time for step Time.
     *
     * response JSON
     */
    public function executeRenderNextTime()
    {
        $userData = new Lib\UserBookingData( $this->getParameter( 'form_id' ) );

        if ( $userData->load() ) {
            $finder = new Lib\Slots\Finder( $userData );
            $finder->setLastFetchedSlot( $this->getParameter( 'last_slot' ) );
            $finder->prepare()->load();

            $slots = $userData->getSlots();
            $selected_date = isset ( $slots[0][2] ) ? $slots[0][2] : null;
            $html = '';
            foreach ( $finder->getSlots() as $group => $group_slots ) {
                $html .= $this->render( '_time_slots', array(
                    'group' => $group,
                    'slots' => $group_slots,
                    'duration_in_days' => $finder->isServiceDurationInDays(),
                    'selected_date' => $selected_date,
                ), false );
            }

            // Set response.
            $response = array(
                'success'        => true,
                'html'           => preg_replace( '/>\s+</', '><', $html ),
                'has_slots'      => $html != '',
                'has_more_slots' => $finder->hasMoreSlots(), // show/hide the next button
            );
        } else {
            $response = array( 'success' => false, 'error_code' => 1, 'error' => __( 'Session error.', 'bookly' ) );
        }

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 4. Step repeat.
     *
     * response JSON
     */
    public function executeRenderRepeat()
    {
        $form_id = $this->getParameter( 'form_id' );
        $userData = new Lib\UserBookingData( $form_id );

        if ( $userData->load() ) {
            $progress_tracker = $this->_prepareProgressTracker( 4, $userData );
            $info_text = $this->components->prepareInfoText( 4, Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_repeat_step' ), $userData );

            // Available days and times.
            $bounding  = Lib\Config::getBoundingDaysForPickadate();
            $show_cart_btn = $this->_showCartButton( $userData );
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

                $schedule = Lib\Proxy\RecurringAppointments::buildSchedule(
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
                'html'     => Lib\Proxy\RecurringAppointments::getStepHtml( $userData, $show_cart_btn, $info_text, $progress_tracker ),
                'date_max' => $bounding['date_max'],
                'date_min' => $date_min,
                'repeated' => (int) $userData->getRepeated(),
                'repeat_data' => $userData->getRepeatData(),
                'schedule'    => $schedule,
                'short_date_format'  => Lib\Utils\DateTime::convertFormat( 'D, M d', Lib\Utils\DateTime::FORMAT_PICKADATE ),
                'pages_warning_info' => nl2br( Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_repeat_schedule_help' ) ),
                'could_be_repeated'  => Lib\Proxy\RecurringAppointments::couldBeRepeated( true, $userData ),
            );
        } else {
            $response = array( 'success' => false, 'error_code' => 1, 'error' => __( 'Session error.', 'bookly' ) );
        }

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 5. Step cart.
     *
     * response JSON
     */
    public function executeRenderCart()
    {
        $userData = new Lib\UserBookingData( $this->getParameter( 'form_id' ) );
        $deposit = array( 'show' => false, );

        if ( $userData->load() ) {
            if ( $this->hasParameter( 'add_to_cart' ) ) {
                $userData->addChainToCart();
            }
            $progress_tracker = $this->_prepareProgressTracker( 5, $userData );
            $info_text        = $this->components->prepareInfoText( 5, Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_cart_step' ), $userData );
            $items_data       = array();
            $cart_columns     = get_option( 'bookly_cart_show_columns', array() );
            foreach ( $userData->cart->getItems() as $cart_key => $cart_item ) {
                if ( Lib\Proxy\RecurringAppointments::hideChildAppointments( false, $cart_item ) ) {
                    continue;
                }
                $nop_prefix = ( $cart_item->getNumberOfPersons() > 1 ? '<i class="bookly-icon-user"></i>' . $cart_item->getNumberOfPersons() . ' &times; ' : '' );
                $slots      = $cart_item->getSlots();
                $service_dp = Lib\Slots\DatePoint::fromStr( $slots[0][2] )->toClientTz();

                foreach ( $cart_columns as $column => $attr ) {
                    if ( $attr['show'] ) {
                        switch ( $column ) {
                            case 'service':
                                $items_data[ $cart_key ][] = $cart_item->getService()->getTranslatedTitle();
                                break;
                            case 'date':
                                $items_data[ $cart_key ][] = $service_dp->formatI18nDate();;
                                break;
                            case 'time':
                                if ( $cart_item->getService()->getDuration() < DAY_IN_SECONDS ) {
                                    $items_data[ $cart_key ][] = $service_dp->formatI18nTime();
                                } else {
                                    $items_data[ $cart_key ][] = '';
                                }
                                break;
                            case 'employee':
                                $items_data[ $cart_key ][] = $cart_item->getStaff()->getTranslatedName();
                                break;
                            case 'price':
                                if ( $cart_item->getNumberOfPersons() > 1 ) {
                                    $items_data[ $cart_key ][] = $nop_prefix . Lib\Utils\Price::format( $cart_item->getServicePrice() - $cart_item->getExtrasAmount() ) . ' = ' . Lib\Utils\Price::format( ( $cart_item->getServicePrice() - $cart_item->getExtrasAmount() ) * $cart_item->getNumberOfPersons() );
                                } else {
                                    $items_data[ $cart_key ][] = Lib\Utils\Price::format( $cart_item->getServicePrice() - $cart_item->getExtrasAmount() );
                                }
                                break;
                            case 'deposit':
                                if ( Lib\Config::depositPaymentsEnabled() ) {
                                    $items_data[ $cart_key ][] = Lib\Proxy\DepositPayments::formatDeposit( $cart_item->getDepositPrice(), $cart_item->getDeposit() );
                                    $deposit['show'] = true;
                                }
                                break;
                        }
                    }
                }
            }

            $columns  = array();
            $position = 0;
            $positions = array();
            foreach ( $cart_columns as $column => $attr ) {
                if ( $attr['show'] ) {
                    if ( $column != 'deposit' || $deposit['show'] ) {
                        $positions[ $column ] = $position;
                    }
                    switch ( $column ) {
                        case 'service':
                            $columns[] = Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_service' );
                            $position ++;
                            break;
                        case 'date':
                            $columns[] = __( 'Date', 'bookly' );
                            $position ++;
                            break;
                        case 'time':
                            $columns[] = __( 'Time', 'bookly' );
                            $position ++;
                            break;
                        case 'employee':
                            $columns[] = Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_employee' );
                            $position ++;
                            break;
                        case 'price':
                            $columns[] = __( 'Price', 'bookly' );
                            $position ++;
                            break;
                        case 'deposit':
                            if ( $deposit['show'] ) {
                                $columns[] = __( 'Deposit', 'bookly' );
                                $position ++;
                            }
                            break;
                    }
                }
            }
            list( $total, $amount_to_pay ) = $userData->cart->getInfo( false );   // without coupon
            $deposit['to_pay'] = $amount_to_pay;
            $response = array(
                'success' => true,
                'html'    => $this->render( '5_cart', array(
                    'progress_tracker'  => $progress_tracker,
                    'info_text'         => $info_text,
                    'items_data'        => $items_data,
                    'columns'           => $columns,
                    'deposit'           => $deposit,
                    'positions'         => $positions,
                    'total'             => $total,
                    'cart_items'        => $userData->cart->getItems(),
                    'info_message'      => Lib\Proxy\Shared::prepareInfoMessage( '', $userData, 5 ),
                ), false ),
            );
        } else {
            $response = array( 'success' => false, 'error_code' => 1, 'error' => __( 'Session error.', 'bookly' ) );
        }

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 6. Step details.
     *
     * response JSON
     */
    public function executeRenderDetails()
    {
        $form_id  = $this->getParameter( 'form_id' );
        $userData = new Lib\UserBookingData( $form_id );

        if ( $userData->load() ) {
            if ( ! Lib\Config::showStepCart() ) {
                $userData->addChainToCart();
            }

            $info_text       = Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_details_step' );
            $info_text_guest = ! get_current_user_id() ? Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_details_step_guest' ) : '';

            // Render main template.
            $html = $this->render( '6_details', array(
                'progress_tracker' => $this->_prepareProgressTracker( 6, $userData ),
                'info_text'        => $this->components->prepareInfoText( 6, $info_text, $userData ),
                'info_text_guest'  => $this->components->prepareInfoText( 6, $info_text_guest, $userData ),
                'userData'         => $userData,
                'info_message'     => Lib\Proxy\Shared::prepareInfoMessage( '', $userData, 6 ),
            ), false );

            // Render additional templates.
            $html .= $this->render( '_customer_duplicate_msg', array(), false );
            if (
                ! get_current_user_id() && (
                    get_option( 'bookly_app_show_login_button' ) ||
                    strpos( $info_text . $info_text_guest, '{login_form}' ) !== false
                )
            ) {
                $html .= $this->render( '_login_form', array(), false );
            }

            $response = array(
                'success' => true,
                'html'    => $html,
            );
        } else {
            $response = array( 'success' => false, 'error_code' => 1, 'error' => __( 'Session error.', 'bookly' ) );
        }

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 7. Step payment.
     *
     * response JSON
     */
    public function executeRenderPayment()
    {
        $userData = new Lib\UserBookingData( $this->getParameter( 'form_id' ) );

        if ( $userData->load() ) {
            $payment_disabled = Lib\Config::paymentStepDisabled();
            $show_cart        = Lib\Config::showStepCart();
            if ( ! $show_cart ) {
                $userData->addChainToCart();
            }
            list ( , $deposit ) = $userData->cart->getInfo();
            if ( $deposit <= 0 ) {
                $payment_disabled = true;
            }

            if ( $payment_disabled == false ) {
                $progress_tracker = $this->_prepareProgressTracker( 7, $userData );

                // Prepare info texts.
                $cart_items_count = count( $userData->cart->getItems() );
                $info_text_tpl    = Lib\Utils\Common::getTranslatedOption(
                    $cart_items_count > 1
                        ? 'bookly_l10n_info_payment_step_several_apps'
                        : 'bookly_l10n_info_payment_step_single_app'
                );
                $info_text        = $this->components->prepareInfoText( 7, $info_text_tpl, $userData );

                // Set response.
                $response = array(
                    'success'  => true,
                    'disabled' => false,
                    'html'     => $this->render( '7_payment', array(
                        'form_id'           => $this->getParameter( 'form_id' ),
                        'progress_tracker'  => $progress_tracker,
                        'info_text'         => $info_text,
                        'coupon_html'       => Lib\Proxy\Coupons::getPaymentStepHtml( $userData ),
                        'payment'           => $userData->extractPaymentStatus(),
                        'pay_local'         => Lib\Config::payLocallyEnabled(),
                        'pay_paypal'        => get_option( 'bookly_paypal_enabled' ),
                        'url_cards_image'   => plugins_url( 'frontend/resources/images/cards.png', Lib\Plugin::getMainFile() ),
                        'page_url'          => $this->getParameter( 'page_url' ),
                        'info_message'      => Lib\Proxy\Shared::prepareInfoMessage( '', $userData, 7 ),
                    ), false )
                );
            } else {
                $response = array(
                    'success'  => true,
                    'disabled' => true,
                );
            }
        } else {
            $response = array( 'success' => false, 'error_code' => 1, 'error' => __( 'Session error.', 'bookly' ) );
        }

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 8. Step done ( complete ).
     *
     * response JSON
     */
    public function executeRenderComplete()
    {
        $userData = new Lib\UserBookingData( $this->getParameter( 'form_id' ) );
        if ( $userData->load() ) {
            $progress_tracker = $this->_prepareProgressTracker( 8, $userData );
            $error = $this->getParameter( 'error' );
            if ( $error == 'appointments_limit_reached' ) {
                $response = array(
                    'success' => true,
                    'html'    => $this->render( '8_complete', array(
                        'progress_tracker' => $progress_tracker,
                        'info_text'        => $this->components->prepareInfoText( 8, Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_complete_step_limit_error' ), $userData ),
                    ), false ),
                );
            } else {
                $payment = $userData->extractPaymentStatus();
                do {
                    if ( $payment ) {
                        switch ( $payment['status'] ) {
                            case 'processing':
                                $info_text = $this->components->prepareInfoText( 8, Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_complete_step_processing' ), $userData );
                                break ( 2 );
                        }
                    }
                    $info_text = $this->components->prepareInfoText( 8, Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_complete_step' ), $userData );
                } while ( 0 );

                $response = array(
                    'success' => true,
                    'html'    => $this->render( '8_complete', array(
                        'progress_tracker' => $progress_tracker,
                        'info_text'        => $info_text,
                    ), false ),
                );
            }
        } else {
            $response = array( 'success' => false, 'error_code' => 1, 'error' => __( 'Session error.', 'bookly' ) );
        }

        // Output JSON response.
        wp_send_json( $response );
    }


    /**
     * Save booking data in session.
     */
    public function executeSessionSave()
    {
        $form_id = $this->getParameter( 'form_id' );
        $errors  = array();
        if ( $form_id ) {
            $userData = new Lib\UserBookingData( $form_id );
            $userData->load();
            $parameters = $this->getParameters();
            $errors = $userData->validate( $parameters );
            if ( empty ( $errors ) ) {
                if ( $this->hasParameter( 'extras' ) ) {
                    $parameters['chain'] = $userData->chain->getItemsData();
                    foreach ( $parameters['chain'] as $key => &$item ) {
                        // Decode extras.
                        $item['extras'] = json_decode( $parameters['extras'][ $key ], true );
                    }
                } elseif ( $this->hasParameter( 'slots' ) ) {
                    // Decode slots.
                    $parameters['slots'] = json_decode( $parameters['slots'], true );
                } elseif ( $this->hasParameter( 'cart' ) ) {
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
        }
        $errors['success'] = empty( $errors );
        wp_send_json( $errors );
    }

    /**
     * Save cart appointments.
     */
    public function executeSaveAppointment()
    {
        $userData = new Lib\UserBookingData( $this->getParameter( 'form_id' ) );

        if ( $userData->load() ) {
            $failed_cart_key = $userData->cart->getFailedKey();
            if ( $failed_cart_key === null ) {
                list( $total, $deposit ) = $userData->cart->getInfo();
                $is_payment_disabled  = Lib\Config::paymentStepDisabled();
                $is_pay_locally_enabled = Lib\Config::payLocallyEnabled();
                if ( $is_payment_disabled || $is_pay_locally_enabled || $deposit <= 0 ) {
                    // Handle coupon.
                    $coupon = $userData->getCoupon();
                    if ( $coupon ) {
                        $coupon->claim()->save();
                    }
                    // Handle payment.
                    $payment = null;
                    if ( ! $is_payment_disabled ) {
                        $payment = new Lib\Entities\Payment();
                        $payment
                            ->setStatus( Lib\Entities\Payment::STATUS_COMPLETED )
                            ->setPaidType( Lib\Entities\Payment::PAY_IN_FULL )
                            ->setCreated( current_time( 'mysql' ) );
                        if ( $coupon && $deposit <= 0 ) {
                            // Create fake payment record for 100% discount coupons.
                            $payment
                                ->setType( Lib\Entities\Payment::TYPE_COUPON )
                                ->setTotal( 0 )
                                ->setPaid( 0 )
                                ->save();
                        } elseif ( $is_pay_locally_enabled && $deposit > 0 ) {
                            // Create record for local payment.
                            $payment
                                ->setType( Lib\Entities\Payment::TYPE_LOCAL )
                                ->setTotal( $total )
                                ->setPaid( 0 )
                                ->setStatus( Lib\Entities\Payment::STATUS_PENDING )
                                ->save();
                        }
                    }
                    // Save cart.
                    $order = $userData->save( $payment );
                    // Send notifications.
                    Lib\NotificationSender::sendFromCart( $order );
                    if ( $payment !== null ) {
                        $payment->setDetails( $order, $coupon )->save();
                    }
                    $response = array(
                        'success' => true,
                    );
                } else {
                    $response = array(
                        'success'    => false,
                        'error_code' => 4,
                        'error'      => __( 'Pay locally is not available.', 'bookly' ),
                    );
                }
            } else {
                $response = array(
                    'success'         => false,
                    'failed_cart_key' => $failed_cart_key,
                    'error_code'      => 3,
                    'error'           => Lib\Utils\Common::getTranslatedOption( Lib\Config::showStepCart() ? 'bookly_l10n_step_cart_slot_not_available' : 'bookly_l10n_step_time_slot_not_available' ),
                );
            }
        } else {
            $response = array( 'success' => false, 'error_code' => 1, 'error' => __( 'Session error.', 'bookly' ) );
        }

        wp_send_json( $response );
    }

    /**
     * Save cart items as pending appointments.
     */
    public function executeSavePendingAppointment()
    {
        if (
            Lib\Config::payuLatamEnabled() ||
            get_option( 'bookly_paypal_enabled' ) == Lib\Payment\PayPal::TYPE_PAYMENTS_STANDARD
        ) {
            $userData = new Lib\UserBookingData( $this->getParameter( 'form_id' ) );
            if ( $userData->load() ) {
                $failed_cart_key = $userData->cart->getFailedKey();
                if ( $failed_cart_key === null ) {
                    $coupon = $userData->getCoupon();
                    if ( $coupon ) {
                        $coupon->claim();
                        $coupon->save();
                    }
                    list ( $total, $deposit ) = $userData->cart->getInfo();
                    $payment = new Lib\Entities\Payment();
                    $payment
                        ->setType( $this->getParameter( 'payment_type' ) )
                        ->setStatus( Lib\Entities\Payment::STATUS_PENDING )
                        ->setTotal( $total )
                        ->setPaid( $deposit )
                        ->setCreated( current_time( 'mysql' ) )
                        ->save();
                    $payment_id = $payment->getId();
                    $order = $userData->save( $payment );
                    $payment->setDetails( $order, $coupon )->save();
                    $response = array(
                        'success'    => true,
                        'payment_id' => $payment_id,
                    );
                } else {
                    $response = array(
                        'success'         => false,
                        'failed_cart_key' => $failed_cart_key,
                        'error_code'      => 3,
                        'error'           => Lib\Utils\Common::getTranslatedOption( Lib\Config::showStepCart() ? 'bookly_l10n_step_cart_slot_not_available' : 'bookly_l10n_step_time_slot_not_available' ),
                    );
                }
            } else {
                $response = array( 'success' => false, 'error_code' => 1, 'error' => __( 'Session error.', 'bookly' ) );
            }
        } else {
            $response = array( 'success' => false, 'error_code' => 5, 'error' => __( 'Invalid gateway.', 'bookly' ) );
        }

        wp_send_json( $response );
    }

    public function executeCheckCart()
    {
        $userData = new Lib\UserBookingData( $this->getParameter( 'form_id' ) );

        if ( $userData->load() ) {
            $failed_cart_key = $userData->cart->getFailedKey();
            if ( $failed_cart_key === null ) {
                $response = array( 'success' => true );
            } else {
                $response = array(
                    'success'         => false,
                    'failed_cart_key' => $failed_cart_key,
                    'error_code'      => 3,
                    'error'           => Lib\Config::showStepCart()
                        ? Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_step_time_slot_not_available' )
                        : Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_step_cart_slot_not_available' )
                );
            }
        } else {
            $response = array( 'success' => false, 'error_code' => 5, 'error' => __( 'Invalid gateway.', 'bookly' ) );
        }

        wp_send_json( $response );
    }

    /**
     * Cancel Appointment using token.
     */
    public function executeCancelAppointment()
    {
        $customer_appointment = new Lib\Entities\CustomerAppointment();

        $allow_cancel = true;
        if ( $customer_appointment->loadBy( array( 'token' => $this->getParameter( 'token' ) ) ) ) {
            $appointment = new Lib\Entities\Appointment();
            $minimum_time_prior_cancel = (int) get_option( 'bookly_gen_min_time_prior_cancel', 0 );
            if ( $minimum_time_prior_cancel > 0
                 && $appointment->load( $customer_appointment->getAppointmentId() )
            ) {
                $allow_cancel_time = strtotime( $appointment->getStartDate() ) - $minimum_time_prior_cancel * HOUR_IN_SECONDS;
                if ( current_time( 'timestamp' ) > $allow_cancel_time ) {
                    $allow_cancel = false;
                }
            }
            if ( $allow_cancel ) {
                $customer_appointment->cancel();
            }
        }

        if ( $url = $allow_cancel ? get_option( 'bookly_url_cancel_page_url' ) : get_option( 'bookly_url_cancel_denied_page_url' ) ) {
            wp_redirect( $url );
            $this->render( 'redirection', compact( 'url' ) );
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
        $this->render( 'redirection', compact( 'url' ) );
        exit;
    }

    /**
     * Approve appointment using token.
     */
    public function executeApproveAppointment()
    {
        $url = get_option( 'bookly_url_approve_denied_page_url' );

        // Decode token.
        $token = Lib\Utils\Common::xorDecrypt( $this->getParameter( 'token' ), 'approve' );
        $ca_to_approve = new Lib\Entities\CustomerAppointment();
        if ( $ca_to_approve->loadBy( array( 'token' => $token ) ) ) {
            $success = true;
            $updates = array();
            /** @var Lib\Entities\CustomerAppointment[] $ca_list */
            if ( $ca_to_approve->getCompoundToken() != '' ) {
                $ca_list = Lib\Entities\CustomerAppointment::query()
                    ->where( 'compound_token', $ca_to_approve->getCompoundToken() )
                    ->find();
            } else {
                $ca_list = array( $ca_to_approve );
            }
            // Check that all items can be switched to approved.
            foreach ( $ca_list as $ca ) {
                $ca_status = $ca->getStatus();
                if ( $ca_status != Lib\Entities\CustomerAppointment::STATUS_APPROVED ) {
                    if ( $ca_status != Lib\Entities\CustomerAppointment::STATUS_CANCELLED &&
                        $ca_status != Lib\Entities\CustomerAppointment::STATUS_REJECTED ) {
                        $appointment = new Lib\Entities\Appointment();
                        $appointment->load( $ca->getAppointmentId() );
                        if ( $ca_status == Lib\Entities\CustomerAppointment::STATUS_WAITLISTED ) {
                            $info = $appointment->getNopInfo();
                            if ( $info['total_nop'] + $ca->getNumberOfPersons() > $info['capacity_max'] ) {
                                $success = false;
                                break;
                            }
                        }
                        $updates[] = array( $ca, $appointment );
                    } else {
                        $success = false;
                        break;
                    }
                }
            }

            if ( $success ) {
                foreach ( $updates as $update ) {
                    /** @var Lib\Entities\CustomerAppointment $ca */
                    /** @var Lib\Entities\Appointment $appointment */
                    list ( $ca, $appointment ) = $update;
                    $ca->setStatus( Lib\Entities\CustomerAppointment::STATUS_APPROVED )->save();
                    $appointment->handleGoogleCalendar();
                }

                if ( ! empty ( $updates ) ) {
                    $ca_to_approve->setStatus( Lib\Entities\CustomerAppointment::STATUS_APPROVED );
                    Lib\NotificationSender::sendSingle( DataHolders\Simple::create( $ca_to_approve ) );
                }

                $url = get_option( 'bookly_url_approve_page_url' );
            }
        }

        wp_redirect( $url );
        $this->render( 'redirection', compact( 'url' ) );
        exit ( 0 );
    }

    /**
     * Reject appointment using token.
     */
    public function executeRejectAppointment()
    {
        $url = get_option( 'bookly_url_reject_denied_page_url' );

        // Decode token.
        $token = Lib\Utils\Common::xorDecrypt( $this->getParameter( 'token' ), 'reject' );
        $ca_to_reject = new Lib\Entities\CustomerAppointment();
        if ( $ca_to_reject->loadBy( array( 'token' => $token ) ) ) {
            $updates = array();
            /** @var Lib\Entities\CustomerAppointment[] $ca_list */
            if ( $ca_to_reject->getCompoundToken() != '' ) {
                $ca_list = Lib\Entities\CustomerAppointment::query()
                    ->where( 'compound_token', $ca_to_reject->getCompoundToken() )
                    ->find();
            } else {
                $ca_list = array( $ca_to_reject );
            }
            // Check that all items can be switched to rejected.
            foreach ( $ca_list as $ca ) {
                $ca_status = $ca->getStatus();
                if ( $ca_status != Lib\Entities\CustomerAppointment::STATUS_REJECTED &&
                    $ca_status != Lib\Entities\CustomerAppointment::STATUS_CANCELLED ) {
                    $appointment = new Lib\Entities\Appointment();
                    $appointment->load( $ca->getAppointmentId() );
                    $updates[] = array( $ca, $appointment );
                }
            }

            foreach ( $updates as $update ) {
                /** @var Lib\Entities\CustomerAppointment $ca */
                /** @var Lib\Entities\Appointment $appointment */
                list ( $ca, $appointment ) = $update;
                $ca->setStatus( Lib\Entities\CustomerAppointment::STATUS_REJECTED )->save();
                $appointment->handleGoogleCalendar();
            }

            if ( ! empty ( $updates ) ) {
                $ca_to_reject->setStatus( Lib\Entities\CustomerAppointment::STATUS_REJECTED );
                Lib\NotificationSender::sendSingle( DataHolders\Simple::create( $ca_to_reject ) );
                $url = get_option( 'bookly_url_reject_page_url' );
            }
        }

        wp_redirect( $url );
        $this->render( 'redirection', compact( 'url' ) );
        exit ( 0 );
    }

    /**
     * Log in to WordPress in the Details step.
     */
    public function executeWpUserLogin()
    {
        $response = null;
        $userData = new Lib\UserBookingData( $this->getParameter( 'form_id' ) );

        if ( $userData->load() ) {
            add_action( 'set_logged_in_cookie', function ( $logged_in_cookie ) {
                $_COOKIE[ LOGGED_IN_COOKIE ] = $logged_in_cookie;
            } );
            /** @var \WP_User $user */
            $user = wp_signon();
            if ( is_wp_error( $user ) ) {
                $response = array( 'success' => false, 'error_code' => 8, 'error' => __( 'Incorrect username or password.' ) );
            } else {
                wp_set_current_user( $user->ID, $user->user_login );
                $customer = new Lib\Entities\Customer();
                if ( $customer->loadBy( array( 'wp_user_id' => $user->ID ) ) ) {
                    $user_info = array(
                        'email'      => $customer->getEmail(),
                        'full_name'  => $customer->getFullName(),
                        'first_name' => $customer->getFirstName(),
                        'last_name'  => $customer->getLastName(),
                        'phone'      => $customer->getPhone(),
                        'csrf_token' => Lib\Utils\Common::getCsrfToken(),
                    );
                } else {
                    $user_info  = array(
                        'email'      => $user->user_email,
                        'full_name'  => $user->display_name,
                        'first_name' => $user->user_firstname,
                        'last_name'  => $user->user_lastname,
                        'csrf_token' => Lib\Utils\Common::getCsrfToken(),
                    );
                }
                $userData->fillData( $user_info );
                $response = array(
                    'success' => true,
                    'data'    => $user_info,
                );
            }
        } else {
            $response = array( 'success' => false, 'error_code' => 1, 'error' => __( 'Session error.', 'bookly' ) );
        }

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * Drop cart item.
     */
    public function executeCartDropItem()
    {
        $userData = new Lib\UserBookingData( $this->getParameter( 'form_id' ) );
        $total = $deposit = 0;
        if ( $userData->load() ) {
            $cart_key       = $this->getParameter( 'cart_key' );
            $edit_cart_keys = $userData->getEditCartKeys();

            $userData->cart->drop( $cart_key );
            if ( ( $idx = array_search( $cart_key, $edit_cart_keys) ) !== false ) {
                unset ( $edit_cart_keys[ $idx ] );
                $userData->setEditCartKeys( $edit_cart_keys );
            }

            list( $total, $deposit ) = $userData->cart->getInfo();
        }
        wp_send_json_success(
            array(
                'total_price' => Lib\Utils\Price::format( $total ),
                'total_deposit_price' => Lib\Utils\Price::format( $deposit )
            )
        );
    }

    /**
     * Render progress tracker into a variable.
     *
     * @param int $step
     * @param Lib\UserBookingData $userData
     * @return string
     */
    private function _prepareProgressTracker( $step, Lib\UserBookingData $userData )
    {
        $result = '';

        if ( get_option( 'bookly_app_show_progress_tracker' ) ) {
            $payment_disabled = Lib\Config::paymentStepDisabled();
            if ( ! $payment_disabled && $step > 1 ) {
                if ( $step < 5 ) {  // step Cart.
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
                                    $staff = Lib\Entities\Staff::find( 1 );
                                }
                                if ( $staff ) {
                                    $staff_service = new Lib\Entities\StaffService();
                                    $staff_service->loadBy( array(
                                        'staff_id'   => $staff->getId(),
                                        'service_id' => $item->getService()->getId(),
                                    ) );
                                    if ( $staff_service->getPrice() > 0 ) {
                                        $payment_disabled = false;
                                        break;
                                    }
                                } else {
                                    $payment_disabled = false;
                                    break;
                                }
                            } else {    // Service::TYPE_COMPOUND
                                if ( $item->getService()->getPrice() > 0 ) {
                                    $payment_disabled = false;
                                    break;
                                }
                            }
                        }
                    }
                } else {
                    list( , $deposit ) = $userData->cart->getInfo( true );
                    if ( $deposit == 0 ) {
                        $payment_disabled = true;
                    }
                }
            }

            $result = $this->render( '_progress_tracker', array(
                'step' => $step,
                'show_cart' => Lib\Config::showStepCart(),
                'payment_disabled' => $payment_disabled,
                'skip_service_step' => Lib\Session::hasFormVar( $this->getParameter( 'form_id' ), 'attrs' )
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
    private function _showCartButton( Lib\UserBookingData $userData )
    {
        return Lib\Config::showStepCart() && count( $userData->cart->getItems() );
    }

    /**
     * Add data for the skipped Service step.
     *
     * @param Lib\UserBookingData $userData
     */
    private function _setDataForSkippedServiceStep( Lib\UserBookingData $userData )
    {
        // Staff ids.
        $attrs = Lib\Session::getFormVar( $this->getParameter( 'form_id' ), 'attrs' );
        if ( $attrs['staff_member_id'] == 0 ) {
            $staff_ids = array_map( function ( $staff ) { return $staff['id']; }, Lib\Entities\StaffService::query()
                ->select( 'staff_id AS id' )
                ->where( 'service_id', $attrs['service_id'] )
                ->fetchArray()
            );
        } else {
            $staff_ids = array( $attrs['staff_member_id'] );
        }
        // Date.
        $date_from = Lib\Slots\DatePoint::now()->modify( Lib\Config::getMinimumTimePriorBooking() );
        if ( Lib\Config::useClientTimeZone() ) {
            // Client time zone.
            $userData
                ->setTimeZone( $this->getParameter( 'time_zone' ) )
                ->setTimeZoneOffset( $this->getParameter( 'time_zone_offset' ) )
                ->applyTimeZone();
            $date_from = $date_from->toClientTz();
        }
        // Days and times.
        $days_times = Lib\Config::getDaysAndTimes();
        $time_from  = key( $days_times['times'] );
        end( $days_times['times'] );

        $userData->chain->clear();
        $chain_item = new Lib\ChainItem();
        $chain_item
            ->setNumberOfPersons( 1 )
            ->setQuantity( 1 )
            ->setServiceId( $attrs['service_id'] )
            ->setStaffIds( $staff_ids )
            ->setLocationId( $attrs['location_id'] ?: null );
        $userData->chain->add( $chain_item );

        $userData->fillData( array(
            'date_from'      => $date_from->format( 'Y-m-d' ),
            'days'           => array_keys( $days_times['days'] ),
            'edit_cart_keys' => array(),
            'slots'          => array(),
            'time_from'      => $time_from,
            'time_to'        => key( $days_times['times'] ),
        ) );
    }

    /**
     * Override parent method to register 'wp_ajax_nopriv_' actions too.
     *
     * @param bool $with_nopriv
     */
    protected function registerWpAjaxActions( $with_nopriv = false )
    {
        parent::registerWpAjaxActions( true );
    }

    /**
     * Override parent method to exclude actions from CSRF token verification.
     *
     * @param string $action
     * @return bool
     */
    protected function csrfTokenValid( $action = null )
    {
        $excluded_actions = array(
            'executeApproveAppointment',
            'executeCancelAppointment',
            'executeRejectAppointment',
            'executeRenderService',
            'executeRenderExtras',
            'executeRenderTime',
        );

        return in_array( $action, $excluded_actions ) || parent::csrfTokenValid( $action );
    }
}