<?php
namespace BooklyLite\Lib\Proxy;

use BooklyLite\Lib;

/**
 * Class CustomFields
 * Invoke local methods from Custom Fields add-on.
 *
 * @package BooklyLite\Lib\Proxy
 *
 * @method static \stdClass[] getAll( $exclude = array() ) Get custom fields
 * @see \BooklyCustomFields\Lib\ProxyProviders\Local::getAll()
 *
 * @method static \stdClass[] getTranslated( $service_id = null, $translate = true, $language_code = null ) Get translated custom fields
 * @see \BooklyCustomFields\Lib\ProxyProviders\Local::getTranslated()
 *
 * @method static \stdClass[] getWhichHaveData() Get custom fields which may have data (no Captcha and Text Content)
 * @see \BooklyCustomFields\Lib\ProxyProviders\Local::getWhichHaveData()
 *
 * @method static array getForCustomerAppointment( Lib\Entities\CustomerAppointment $ca, $translate = false, $locale = null ) Get custom fields data for given customer appointment
 * @see \BooklyCustomFields\Lib\ProxyProviders\Local::getForCustomerAppointment()
 *
 * @method static string getFormatted( Lib\Entities\CustomerAppointment $ca, $format, $locale = null ) Get formatted custom fields
 * @see \BooklyCustomFields\Lib\ProxyProviders\Local::getFormatted()
 *
 * @method static array validate( array $errors, $value, $form_id, $cart_key ) Validate custom fields
 * @see \BooklyCustomFields\Lib\ProxyProviders\Local::validate()
 *
 * @method static void renderCustomerDetails() Render custom fields in customer details dialog
 * @see \BooklyCustomFields\Lib\ProxyProviders\Local::renderCustomerDetails()
 *
 * @method static void renderDetailsStep( Lib\UserBookingData $userData ) Render custom fields at Details step
 * @see \BooklyCustomFields\Lib\ProxyProviders\Local::renderDetailsStep()
 *
 * @method static void renderCustomerProfileRow( $custom_fields, $app ) Render custom fields row in customer profile
 * @see \BooklyCustomFields\Lib\ProxyProviders\Local::renderCustomerProfileRow()
 *
 * @method static void addBooklyMenuItem() Add 'Custom Fields' to Bookly menu
 * @see \BooklyCustomFields\Lib\ProxyProviders\Local::addBooklyMenuItem()
 */
abstract class CustomFields extends Lib\Base\ProxyInvoker
{

}