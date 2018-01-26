<?php
namespace BooklyLite\Backend\Modules\Settings;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\Settings
 */
class Controller extends Lib\Base\Controller
{
    const page_slug = 'bookly-settings';

    public function index()
    {
        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        wp_enqueue_media();
        $this->enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css' ),
            'backend'  => array( 'bootstrap/css/bootstrap-theme.min.css', )
        ) );

        $this->enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/jCal.js'  => array( 'jquery' ),
                'js/alert.js' => array( 'jquery' ),
            ),
            'module'   => array( 'js/settings.js' => array( 'jquery', 'bookly-intlTelInput.min.js', 'jquery-ui-sortable' ) ),
            'frontend' => array(
                'js/intlTelInput.min.js' => array( 'jquery' ),
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            )
        ) );

        $current_tab = $this->hasParameter( 'tab' ) ? $this->getParameter( 'tab' ) : 'general';
        $alert = array( 'success' => array(), 'error' => array() );

        // Save the settings.
        if ( ! empty ( $_POST ) ) {
            if ( $this->csrfTokenValid() ) {
                switch ( $this->getParameter( 'tab' ) ) {
                    case 'calendar':  // Calendar form.
                        update_option( 'bookly_cal_one_participant',   $this->getParameter( 'bookly_cal_one_participant' ) );
                        update_option( 'bookly_cal_many_participants', $this->getParameter( 'bookly_cal_many_participants' ) );
                        $alert['success'][] = __( 'Settings saved.', 'bookly' );
                        break;
                    case 'payments':  // Payments form.
                        $form = new Forms\Payments();
                        break;
                    case 'business_hours':  // Business hours form.
                        $form = new Forms\BusinessHours();
                        break;
                    case 'purchase_code':  // Purchase Code form.
                        break;
                    case 'general':  // General form.
                        $bookly_gen_time_slot_length = $this->getParameter( 'bookly_gen_time_slot_length' );
                        if ( in_array( $bookly_gen_time_slot_length, array( 5, 10, 12, 15, 20, 30, 45, 60, 90, 120, 180, 240, 360 ) ) ) {
                            update_option( 'bookly_gen_time_slot_length', $bookly_gen_time_slot_length );
                        }
                        update_option( 'bookly_gen_lite_uninstall_remove_bookly_data', (int) $this->getParameter( 'bookly_gen_lite_uninstall_remove_bookly_data' ) );
                        update_option( 'bookly_gen_service_duration_as_slot_length', (int) $this->getParameter( 'bookly_gen_service_duration_as_slot_length' ) );
                        update_option( 'bookly_gen_allow_staff_edit_profile', (int) $this->getParameter( 'bookly_gen_allow_staff_edit_profile' ) );
                        update_option( 'bookly_gen_default_appointment_status', $this->getParameter( 'bookly_gen_default_appointment_status' ) );
                        update_option( 'bookly_gen_link_assets_method', $this->getParameter( 'bookly_gen_link_assets_method' ) );
                        update_option( 'bookly_gen_max_days_for_booking', (int) $this->getParameter( 'bookly_gen_max_days_for_booking' ) );
                        update_option( 'bookly_gen_min_time_prior_booking', $this->getParameter( 'bookly_gen_min_time_prior_booking' ) );
                        update_option( 'bookly_gen_min_time_prior_cancel', $this->getParameter( 'bookly_gen_min_time_prior_cancel' ) );
                        update_option( 'bookly_gen_use_client_time_zone', (int) $this->getParameter( 'bookly_gen_use_client_time_zone' ) );
                        update_option( 'bookly_gen_collect_stats', $this->getParameter( 'bookly_gen_collect_stats' ) );
                        $alert['success'][] = __( 'Settings saved.', 'bookly' );
                        break;
                    case 'url': // URL settings form.
                        update_option( 'bookly_url_approve_page_url', $this->getParameter( 'bookly_url_approve_page_url' ) );
                        update_option( 'bookly_url_approve_denied_page_url', $this->getParameter( 'bookly_url_approve_denied_page_url' ) );
                        update_option( 'bookly_url_cancel_page_url', $this->getParameter( 'bookly_url_cancel_page_url' ) );
                        update_option( 'bookly_url_cancel_denied_page_url', $this->getParameter( 'bookly_url_cancel_denied_page_url' ) );
                        update_option( 'bookly_url_cancel_confirm_page_url', $this->getParameter( 'bookly_url_cancel_confirm_page_url' ) );
                        update_option( 'bookly_url_reject_denied_page_url', $this->getParameter( 'bookly_url_reject_denied_page_url' ) );
                        update_option( 'bookly_url_reject_page_url', $this->getParameter( 'bookly_url_reject_page_url' ) );
                        update_option( 'bookly_url_final_step_url', '' );
                        $alert['success'][] = __( 'Settings saved.', 'bookly' );
                        break;
                    case 'google_calendar':  // Google calendar form.
                        update_option( 'bookly_gc_client_id',     '' );
                        $alert['success'][] = __( 'Settings saved.', 'bookly' );
                        break;
                    case 'customers':  // Customers form.
                        update_option( 'bookly_cst_cancel_action',              $this->getParameter( 'bookly_cst_cancel_action' ) );
                        update_option( 'bookly_cst_combined_notifications',     $this->getParameter( 'bookly_cst_combined_notifications' ) );
                        update_option( 'bookly_cst_create_account',             $this->getParameter( 'bookly_cst_create_account' ) );
                        update_option( 'bookly_cst_default_country_code',       $this->getParameter( 'bookly_cst_default_country_code' ) );
                        update_option( 'bookly_cst_new_account_role',           $this->getParameter( 'bookly_cst_new_account_role' ) );
                        update_option( 'bookly_cst_phone_default_country',      $this->getParameter( 'bookly_cst_phone_default_country' ) );
                        update_option( 'bookly_cst_remember_in_cookie',         $this->getParameter( 'bookly_cst_remember_in_cookie' ) );
                        update_option( 'bookly_cst_show_update_details_dialog', $this->getParameter( 'bookly_cst_show_update_details_dialog' ) );
                        $alert['success'][] = __( 'Settings saved.', 'bookly' );
                        break;
                    case 'woo_commerce':  // WooCommerce form.
                        update_option( 'bookly_wc_enabled', '0' );
                        $alert['success'][] = __( 'Settings saved.', 'bookly' );
                        break;
                    case 'cart':  // Cart form.
                        update_option( 'bookly_cart_show_columns', $this->getParameter( 'bookly_cart_show_columns', array() ) );
                        update_option( 'bookly_cart_enabled', '0' );
                        $alert['success'][] = __( 'Settings saved.', 'bookly' );
                        if ( get_option( 'bookly_wc_enabled' ) && $this->getParameter( 'bookly_cart_enabled' ) ) {
                            $alert['error'][] = sprintf( __( 'To use the cart, disable integration with WooCommerce <a href="%s">here</a>.', 'bookly' ), Lib\Utils\Common::escAdminUrl( self::page_slug, array( 'tab' => 'woocommerce' ) ) );
                        }
                        break;
                    case 'company':  // Company form.
                        update_option( 'bookly_co_address', $this->getParameter( 'bookly_co_address' ) );
                        update_option( 'bookly_co_logo_attachment_id', $this->getParameter( 'bookly_co_logo_attachment_id' ) );
                        update_option( 'bookly_co_name',    $this->getParameter( 'bookly_co_name' ) );
                        update_option( 'bookly_co_phone',   $this->getParameter( 'bookly_co_phone' ) );
                        update_option( 'bookly_co_website', $this->getParameter( 'bookly_co_website' ) );
                        $alert['success'][] = __( 'Settings saved.', 'bookly' );
                        break;
                }

                // Let Add-ons save their settings.
                $alert = Lib\Proxy\Shared::saveSettings( $alert, $this->getParameter( 'tab' ), $this->getPostParameters() );

                if ( in_array( $this->getParameter( 'tab' ), array( 'payments', 'business_hours' ) ) ) {
                    $form->bind( $this->getPostParameters(), $_FILES );
                    $form->save();

                    $alert['success'][] = __( 'Settings saved.', 'bookly' );
                }
            }
        }

        $candidates = $this->getCandidatesBooklyProduct();

        // Check if WooCommerce cart exists.
        if ( get_option( 'bookly_wc_enabled' ) && class_exists( 'WooCommerce', false ) ) {
            $post = get_post( wc_get_page_id( 'cart' ) );
            if ( $post === null || $post->post_status != 'publish' ) {
                $alert['error'][] = sprintf(
                    __( 'WooCommerce cart is not set up. Follow the <a href="%s">link</a> to correct this problem.', 'bookly' ),
                    Lib\Utils\Common::escAdminUrl( 'wc-status', array( 'tab' => 'tools' ) )
                );
            }
        }
        $cart_columns = array(
            'service'  => Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_service' ),
            'date'     => __( 'Date',  'bookly' ),
            'time'     => __( 'Time',  'bookly' ),
            'employee' => Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_employee' ),
            'price'    => __( 'Price', 'bookly' ),
            'deposit'  => __( 'Deposit', 'bookly' ),
        );

        wp_localize_script( 'bookly-jCal.js', 'BooklyL10n',  array(
            'alert'              => $alert,
            'current_tab'        => $current_tab,
            'csrf_token'         => Lib\Utils\Common::getCsrfToken(),
            'default_country'    => get_option( 'bookly_cst_phone_default_country' ),
            'holidays'           => $this->getHolidays(),
            'loading_img'        => plugins_url( 'bookly-responsive-appointment-booking-tool/backend/resources/images/loading.gif' ),
            'start_of_week'      => get_option( 'start_of_week' ),
            'days'               => array_values( $wp_locale->weekday_abbrev ),
            'months'             => array_values( $wp_locale->month ),
            'close'              => __( 'Close', 'bookly' ),
            'repeat'             => __( 'Repeat every year', 'bookly' ),
            'we_are_not_working' => __( 'We are not working on this day', 'bookly' ),
            'sample_price'       => number_format_i18n( 10, 3 ),
            'limitations'        => __( '<b class="h4">This function is not available in the Lite version of Bookly.</b><br><br>To get access to all Bookly features, lifetime free updates and 24/7 support, please upgrade to the Standard version of Bookly.<br>For more information visit', 'bookly' ) . ' <a href="http://booking-wp-plugin.com" target="_blank" class="alert-link">http://booking-wp-plugin.com</a>',
        ) );
        $values = array(
            'bookly_gc_limit_events' => array( array( '0', __( 'Disabled', 'bookly' ) ), array( 25, 25 ), array( 50, 50 ), array( 100, 100 ), array( 250, 250 ), array( 500, 500 ), array( 1000, 1000 ), array( 2500, 2500 ) ),
            'bookly_gen_min_time_prior_booking' => array( array( '0', __( 'Disabled', 'bookly' ) ) ),
            'bookly_gen_min_time_prior_cancel'  => array( array( '0', __( 'Disabled', 'bookly' ) ) ),
        );
        $wp_roles = new \WP_Roles();
        foreach ( $wp_roles->get_names() as $role => $name ) {
            $values['bookly_cst_new_account_role'][] = array( $role, $name );
        }
        foreach ( array( 5, 10, 12, 15, 20, 30, 45, 60, 90, 120, 180, 240, 360 ) as $duration ) {
            $values['bookly_gen_time_slot_length'][] = array( $duration, Lib\Utils\DateTime::secondsToInterval( $duration * MINUTE_IN_SECONDS ) );
        }
        foreach ( array_merge( array( 0.5 ), range( 1, 12 ), range( 24, 144, 24 ), range( 168, 672, 168 ) ) as $hour ) {
            $values['bookly_gen_min_time_prior_booking'][] = array( $hour, Lib\Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) );
        }
        foreach ( array_merge( array( 1 ), range( 2, 12, 2 ), range( 24, 168, 24 ) ) as $hour ) {
            $values['bookly_gen_min_time_prior_cancel'][] = array( $hour, Lib\Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) );
        }

        $this->render( 'index', compact( 'candidates', 'cart_columns', 'values' ) );
    }

    /**
     * Ajax request for Holidays calendar
     */
    public function executeSettingsHoliday()
    {
        global $wpdb;

        $id      = $this->getParameter( 'id',  false );
        $day     = $this->getParameter( 'day', false );
        $holiday = $this->getParameter( 'holiday' ) == 'true';
        $repeat  = (int) ( $this->getParameter( 'repeat' ) == 'true' );

        // update or delete the event
        if ( $id ) {
            if ( $holiday ) {
                $wpdb->update( Lib\Entities\Holiday::getTableName(), array( 'repeat_event' => $repeat ), array( 'id' => $id ), array( '%d' ) );
                $wpdb->update( Lib\Entities\Holiday::getTableName(), array( 'repeat_event' => $repeat ), array( 'parent_id' => $id ), array( '%d' ) );
            } else {
                Lib\Entities\Holiday::query()->delete()->where( 'id', $id )->where( 'parent_id', $id, 'OR' )->execute();
            }
            // add the new event
        } elseif ( $holiday && $day ) {
            $holiday = new Lib\Entities\Holiday( );
            $holiday
                ->setDate( $day )
                ->setRepeatEvent( $repeat )
                ->save();
            foreach ( Lib\Entities\Staff::query()->fetchArray() as $employee ) {
                $staff_holiday = new Lib\Entities\Holiday();
                $staff_holiday
                    ->setDate( $day)
                    ->setRepeatEvent( $repeat )
                    ->setStaffId( $employee['id'] )
                    ->setParent( $holiday )
                    ->save();
            }
        }

        // and return refreshed events
        echo json_encode( $this->getHolidays() );
        exit;
    }

    /**
     * Dismiss collect stats notice.
     */
    public function executeDismissCollectStatsNotice()
    {
        update_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_collect_stats_notice', 1 );
        update_option( 'bookly_gen_collect_stats', '0' );

        wp_send_json_success();
    }

    public function executeAllowCollectStats()
    {
        update_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_collect_stats_notice', 1 );
        update_option( 'bookly_gen_collect_stats', '1' );

        wp_send_json_success();
    }


    /**
     * @return string
     */
    protected function getHolidays()
    {
        $collection = Lib\Entities\Holiday::query()->where( 'staff_id', null )->fetchArray();
        $holidays = array();
        if ( count( $collection ) ) {
            foreach ( $collection as $holiday ) {
                $holidays[ $holiday['id'] ] = array(
                    'm' => (int) date( 'm', strtotime( $holiday['date'] ) ),
                    'd' => (int) date( 'd', strtotime( $holiday['date'] ) ),
                );
                // If not repeated holiday, add the year
                if ( ! $holiday['repeat_event'] ) {
                    $holidays[ $holiday['id'] ]['y'] = (int) date( 'Y', strtotime( $holiday['date'] ) );
                }
            }
        }

        return $holidays;
    }

    /**
     * @return array
     */
    protected function getCandidatesBooklyProduct()
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        $goods    = array( array( 'id' => 0, 'name' => __( 'Select product', 'bookly' ) ) );
        $query    = 'SELECT ID, post_title FROM ' . $wpdb->posts . ' WHERE post_type = \'product\' AND post_status = \'publish\' ORDER BY post_title';
        $products = $wpdb->get_results( $query );

        foreach ( $products as $product ) {
            $goods[] = array( 'id' => $product->ID, 'name' => $product->post_title );
        }

        return $goods;
    }
}