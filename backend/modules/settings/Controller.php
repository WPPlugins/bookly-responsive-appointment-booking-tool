<?php
namespace BooklyLite\Backend\Modules\Settings;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\Settings
 */
class Controller extends Lib\Base\Controller
{
    const page_slug = 'ab-settings';

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
            'module'   => array( 'js/settings.js' => array( 'jquery', 'ab-intlTelInput.min.js', 'jquery-ui-sortable' ) ),
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
            switch ( $this->getParameter( 'tab' ) ) {
                case 'payments':  // Payments form.
                    $form = new Forms\Payments();
                    break;
                case 'business_hours':  // Business hours form.
                    $form = new Forms\BusinessHours();
                    break;
                case 'purchase_code':  // Purchase Code form.
                    break;
                case 'general':  // General form.
                    $ab_settings_time_slot_length = $this->getParameter( 'ab_settings_time_slot_length' );
                    if ( in_array( $ab_settings_time_slot_length, array( 5, 10, 12, 15, 20, 30, 45, 60, 90, 120, 180, 240, 360 ) ) ) {
                        update_option( 'ab_settings_time_slot_length', $ab_settings_time_slot_length );
                    }
                    update_option( 'ab_settings_allow_staff_members_edit_profile', (int) $this->getParameter( 'ab_settings_allow_staff_members_edit_profile' ) );
                    update_option( 'ab_settings_approve_page_url',      $this->getParameter( 'ab_settings_approve_page_url' ) );
                    update_option( 'ab_settings_cancel_denied_page_url', $this->getParameter( 'ab_settings_cancel_denied_page_url' ) );
                    update_option( 'ab_settings_cancel_page_url',       $this->getParameter( 'ab_settings_cancel_page_url' ) );
                    update_option( 'ab_settings_default_appointment_status', $this->getParameter( 'ab_settings_default_appointment_status' ) );
                    update_option( 'ab_settings_final_step_url',        '' );
                    update_option( 'ab_settings_link_assets_method',    $this->getParameter( 'ab_settings_link_assets_method' ) );
                    update_option( 'ab_settings_maximum_available_days_for_booking', (int) $this->getParameter( 'ab_settings_maximum_available_days_for_booking' ) );
                    update_option( 'ab_settings_minimum_time_prior_booking', (int) $this->getParameter( 'ab_settings_minimum_time_prior_booking' ) );
                    update_option( 'ab_settings_minimum_time_prior_cancel', $this->getParameter( 'ab_settings_minimum_time_prior_cancel' ) );
                    update_option( 'ab_settings_use_client_time_zone',  (int) $this->getParameter( 'ab_settings_use_client_time_zone' ) );
                    update_option( 'ab_lite_uninstall_remove_bookly_data',  (int) $this->getParameter( 'ab_lite_uninstall_remove_bookly_data' ) );
                    $alert['success'][] = __( 'Settings saved.', 'bookly' );
                    break;
                case 'google_calendar':  // Google calendar form.
                    break;
                case 'customers':  // Customers form.
                    update_option( 'ab_settings_client_cancel_appointment_action', $this->getParameter( 'ab_settings_client_cancel_appointment_action' ) );
                    update_option( 'ab_settings_create_account',        (int) $this->getParameter( 'ab_settings_create_account' ) );
                    update_option( 'ab_settings_phone_default_country', $this->getParameter( 'ab_settings_phone_default_country' ) );
                    update_option( 'ab_sms_default_country_code',       $this->getParameter( 'ab_sms_default_country_code' ) );
                    $alert['success'][] = __( 'Settings saved.', 'bookly' );
                    break;
                case 'woocommerce':  // WooCommerce form.
                    break;
                case 'cart':  // Cart form.
                    update_option( 'ab_cart_show_columns',              $this->getParameter( 'ab_cart_show_columns', array() ) );
                    update_option( 'ab_settings_cart_notifications_combined', $this->getParameter( 'ab_settings_cart_notifications_combined' ) );
                    update_option( 'ab_settings_step_cart_enabled',     '0' );
                    $alert['success'][] = __( 'Settings saved.', 'bookly' );
                    break;
                case 'company':  // Company form.
                    update_option( 'ab_settings_company_address',       $this->getParameter( 'ab_settings_company_address' ) );
                    update_option( 'ab_settings_company_logo_attachment_id', $this->getParameter( 'ab_settings_company_logo_attachment_id' ) );
                    update_option( 'ab_settings_company_name',          $this->getParameter( 'ab_settings_company_name' ) );
                    update_option( 'ab_settings_company_phone',         $this->getParameter( 'ab_settings_company_phone' ) );
                    update_option( 'ab_settings_company_website',       $this->getParameter( 'ab_settings_company_website' ) );
                    $alert['success'][] = __( 'Settings saved.', 'bookly' );
                    break;
                default:
                    // Let add-ons save their settings.
                    $alert = apply_filters( 'bookly_save_settings', $alert, $this->getParameter( 'tab' ), $this->getPostParameters() );
            }

            if ( in_array( $this->getParameter( 'tab' ), array ( 'payments', 'business_hours' ) ) ) {
                $form->bind( $this->getPostParameters(), $_FILES );
                $form->save();

                $alert['success'][] = __( 'Settings saved.', 'bookly' );
            }
        }

        $holidays   = $this->getHolidays();
        $candidates = $this->getCandidatesBooklyProduct();

        // Check if WooCommerce cart exists.
        if ( get_option( 'ab_woocommerce_enabled' ) && class_exists( 'WooCommerce', false ) ) {
            $post = get_post( wc_get_page_id( 'cart' ) );
            if ( $post === null || $post->post_status != 'publish' ) {
                $alert['error'][] = sprintf(
                    __( 'WooCommerce cart is not set up. Follow the <a href="%s">link</a> to correct this problem.', 'bookly' ),
                    Lib\Utils\Common::escAdminUrl( 'wc-status', array( 'tab' => 'tools' ) )
                );
            }
        }
        $cart_columns = array(
            'service'  => Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_service' ),
            'date'     => __( 'Date',  'bookly' ),
            'time'     => __( 'Time',  'bookly' ),
            'employee' => Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_employee' ),
            'price'    => __( 'Price', 'bookly' ),
        );

        $cart_columns = apply_filters( 'bookly_settings_cart_columns', $cart_columns );

        wp_localize_script( 'ab-jCal.js', 'BooklyL10n',  array(
            'alert'       => $alert,
            'close'       => __( 'Close', 'bookly' ),
            'current_tab' => $current_tab,
            'days'        => array_values( $wp_locale->weekday_abbrev ),
            'months'      => array_values( $wp_locale->month ),
            'repeat'      => __( 'Repeat every year', 'bookly' ),
            'we_are_not_working' => __( 'We are not working on this day', 'bookly' ),
            'limitations' => __( '<b class="h4">This function is disabled in the Lite version of Bookly.</b><br><br>If you find the plugin useful for your business please consider buying a licence for the full version.<br>It costs just $59 and for this money you will get many useful functions, lifetime free updates and excellent support!<br>More information can be found here', 'bookly' ) . ': <a href="http://booking-wp-plugin.com" target="_blank" class="alert-link">http://booking-wp-plugin.com</a>',
        ) );

        $this->render( 'index', compact( 'holidays', 'candidates', 'cart_columns' ) );
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
        $repeat  = $this->getParameter( 'repeat' )  == 'true';

        // update or delete the event
        if ( $id ) {
            if ( $holiday ) {
                $wpdb->update( Lib\Entities\Holiday::getTableName(), array( 'repeat_event' => intval( $repeat ) ), array( 'id' => $id ), array( '%d' ) );
                $wpdb->update( Lib\Entities\Holiday::getTableName(), array( 'repeat_event' => intval( $repeat ) ), array( 'parent_id' => $id ), array( '%d' ) );
            } else {
                Lib\Entities\Holiday::query()->delete()->where( 'id', $id )->where( 'parent_id', $id, 'OR' )->execute();
            }
            // add the new event
        } elseif ( $holiday && $day ) {
            $holiday = new Lib\Entities\Holiday( array( 'date' => $day, 'repeat_event' => intval( $repeat ) ) );
            $holiday->save();
            foreach ( Lib\Entities\Staff::query()->fetchArray() as $employee ) {
                $staff_holiday = new Lib\Entities\Holiday( array( 'date' => $day, 'repeat_event' => intval( $repeat ), 'staff_id'  => $employee['id'], 'parent_id' => $holiday->get( 'id' ) ) );
                $staff_holiday->save();
            }
        }

        // and return refreshed events
        echo $this->getHolidays();
        exit;
    }

    /**
     * @return mixed|string|void
     */
    protected function getHolidays()
    {
        $collection = Lib\Entities\Holiday::query()->where( 'staff_id', null )->fetchArray();
        $holidays = array();
        if ( count( $collection ) ) {
            foreach ( $collection as $holiday ) {
                $holidays[ $holiday['id'] ] = array(
                    'm' => intval( date( 'm', strtotime( $holiday['date'] ) ) ),
                    'd' => intval( date( 'd', strtotime( $holiday['date'] ) ) ),
                );
                // If not repeated holiday, add the year
                if ( ! $holiday['repeat_event'] ) {
                    $holidays[ $holiday['id'] ]['y'] = intval( date( 'Y', strtotime( $holiday['date'] ) ) );
                }
            }
        }

        return json_encode( $holidays );
    }

    protected function getCandidatesBooklyProduct()
    {
        $goods = array( array( 'id' => 0, 'name' => __( 'Select product', 'bookly' ) ) );
        $args  = array(
            'numberposts'      => -1,
            'post_type'        => 'product',
            'suppress_filters' => true
        );
        $collection = get_posts( $args );
        foreach ( $collection as $item ) {
            $goods[] = array( 'id' => $item->ID, 'name' => $item->post_title );
        }
        wp_reset_postdata();

        return $goods;
    }

    /**
     * Ajax request to dismiss admin notice for current user.
     */
    public function executeDismissAdminNotice()
    {
        update_user_meta( get_current_user_id(), $this->getParameter( 'prefix' ) . 'dismiss_admin_notice', 1 );
    }

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     *
     * @param string $prefix
     */
    protected function registerWpActions( $prefix = '' )
    {
        parent::registerWpActions( 'wp_ajax_ab_' );
    }

}