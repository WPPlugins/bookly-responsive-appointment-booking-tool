<?php
namespace BooklyLite\Lib\Proxy;

use BooklyLite\Lib;
use BooklyLite\Backend;

/**
 * Class Shared
 * Invoke shared methods.
 *
 * @package BooklyLite\Lib\Proxy
 *
 * @method static array  adjustMinAndMaxTimes( array $times ) Prepare time_from & time_to for UserBookingData.
 * @method static array  handleRequestAction( string $bookly_action ) Handle requests with given action.
 * @method static array  prepareAppearanceCodes( array $codes ) Alter array of codes to be displayed in Bookly Appearance.
 * @method static array  prepareAppearanceOptions( array $options_to_save, array $options ) Alter array of options to be saved in Bookly Appearance.
 * @method static array  prepareCalendarAppointmentCodes( array $codes, string $participants ) Prepare codes for appointment description displayed in calendar.
 * @method static array  prepareCalendarAppointmentCodesData( array $codes, array $appointment_data, string $participants ) Prepare codes data for appointment description displayed in calendar.
 * @method static array  prepareCartItemInfoText( array $data, Lib\CartItem $cart_item ) Prepare array for replacing in Cart items
 * @method static array  prepareCartNotificationShortCodes( array $codes ) Alter array of codes to be displayed in Cart settings.
 * @method static array  prepareCaSeSt( array $result ) Prepare Categories Services Staff data
 * @method static array  prepareChainItemInfoText( array $data, Lib\ChainItem $chain_item ) Prepare array for replacing in Chain items
 * @method static array  prepareInfoTextCodes( array $info_text_codes, array $data ) Prepare array for replacing on booking steps
 * @method static array  prepareNotificationCodesList( array $codes, string $set ) Alter array of codes to be displayed in Bookly Notifications.
 * @method static void   prepareNotificationCodesForOrder( Lib\NotificationCodes $codes ) Prepare codes for replacing in notifications
 * @method static array  prepareNotificationNames( array $names ) Prepare notification names.
 * @method static array  prepareNotificationTypes( array $types ) Prepare notification types.
 * @method static array  prepareNotificationTypeIds( array $type_ids ) Prepare notification type IDs.
 * @method static array  preparePaymentOptions( array $options ) Alter payment option names before saving in Bookly Settings.
 * @method static array  preparePaymentOptionsData( array $data ) Alter and apply payment options data before saving in Bookly Settings.
 * @method static array  prepareReplaceCodes( array $codes, Lib\NotificationCodes $notification_codes, $format ) Replacing on booking steps
 * @method static Lib\NotificationCodes prepareTestNotificationCodes( Lib\NotificationCodes $codes ) Prepare codes for testing email templates
 * @method static array  prepareUpdateServiceResponse( array $response, Lib\Entities\Service $service, array $_post ) Prepare response for updated service.
 * @method static array  prepareServiceColors( array $colors, int $service_id, int $service_type ) Prepare colors for service.
 * @method static array  prepareWooCommerceShortCodes( array $codes ) Alter array of codes to be displayed in WooCommerce (Order,Cart,Checkout etc.).
 * @method static array  saveSettings( array $alert, string $tab, $_post ) Save add-on settings
 * @method static array  serviceCreated( Lib\Entities\Service $service, array $_post ) Service created
 * @method static array  prepareUpdateService( array $data ) Prepare update service settings in add-ons
 * @method static string prepareInfoMessage( string $default, Lib\UserBookingData $userData, int $step ) Prepare info message.
 * @method static string prepareStaffServiceInputClass( string $class_name ) Change css class name for inputs.
 * @method static string prepareStaffServiceLabelClass( string $class_name ) Change css class name for labels.
 * @method static void   enqueueAssetsForServices() Enqueue assets for page Services
 * @method static void   enqueueAssetsForStaffProfile() Enqueue assets for page Staff
 * @method static void   enqueueBookingAssets() Enqueue assets for booking form
 * @method static void   printBookingAssets() Print assets for booking form.
 * @method static void   renderAfterServiceList( array $service_collection ) Render content after services forms
 * @method static void   renderAppearancePaymentGatewaySelector() Render gateway selector
 * @method static void   renderAppearanceStepServiceSettings() Render checkbox settings
 * @method static void   renderAppointmentDialogCustomerList() Render content in AppointmentForm for customers
 * @method static void   renderAppointmentDialogFooter() Render buttons in appointments dialog footer.
 * @method static void   renderCartItemInfo( Lib\CartItem[] $cart_items, $cart_key, $positions, $desktop ) Render in cart extra info for CartItem
 * @method static void   renderCartSettings() Render Cart settings on Settings page
 * @method static void   renderChainItemHead() Render head for chain in step service
 * @method static void   renderChainItemTail() Render tail for chain in step service
 * @method static void   renderComponentAppointments() Render content in appointments
 * @method static void   renderComponentCalendar() Render content in calendar page
 * @method static void   renderEmailNotifications( Backend\Modules\Notifications\Forms\Notifications $form ) Render email notification(s)
 * @method static void   renderMediaButtons( string $version ) Add buttons to WordPress editor.
 * @method static void   renderPaymentGatewayForm( $form_id, $page_url ) Render gateway form on step Payment
 * @method static void   renderPaymentGatewaySelector( $form_id, array $payment ) Render gateway selector on step Payment
 * @method static void   renderPaymentGatewaySettings() Render gateway add-on payment settings
 * @method static void   renderPopUpShortCodeBooklyForm() Render controls in popup for bookly-form (build shortcode)
 * @method static void   renderPopUpShortCodeBooklyFormHead() Render controls in header popup for bookly-form (build shortcode)
 * @method static void   renderServiceForm( array $service ) Render content in service form
 * @method static void   renderServiceFormHead( array $service ) Render top content in service form
 * @method static void   renderSettingsForm() Render add-on settings form
 * @method static void   renderSettingsMenu() Render tab in settings page
 * @method static void   renderSmsNotifications( Backend\Modules\Notifications\Forms\Notifications $form ) Render SMS notification(s)
 * @method static void   renderStaffForm( Lib\Entities\Staff $staff ) Render Staff form tab details
 * @method static void   renderStaffService( int $staff_id, int $service_id, array $services_data, array $attributes = array() ) Render controls for Staff on tab services.
 * @method static void   renderStaffServices( int $staff_id ) Render Components for staff profile
 * @method static void   renderStaffServiceTail( int $staff_id, int $service_id, $attributes = array() ) Render controls for Staff on tab services.
 * @method static void   renderStaffTab( Lib\Entities\Staff $staff )
 * @method static void   renderTinyMceComponent() Render PopUp windows for WordPress editor.
 * @method static void   renderUrlSettings() Render URL settings on Settings page.
 * @method static void   serviceDeleted( int $service_id ) Service deleted
 * @method static array  updateService( array $alert, Lib\Entities\Service $service, array $_post ) Update service settings in add-ons
 * @method static void   updateStaff( array $_post ) Update staff settings in add-ons
 * @method static bool   appearanceRequiredInterfaceCreditCard( bool $required ) In case there are payment systems that request credit card information in the Details step, it will return true
 */
abstract class Shared extends Lib\Base\ProxyInvoker
{

}
