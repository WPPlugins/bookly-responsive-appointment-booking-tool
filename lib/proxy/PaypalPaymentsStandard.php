<?php
namespace BooklyLite\Lib\Proxy;

use BooklyLite\Lib\Base;

/**
 * Class PaypalPaymentsStandard
 * Invoke local methods from PayPal Payments Standard add-on.
 *
 * @package BooklyLite\Lib\Proxy
 *
 * @method static array prepareToggleOptions( array $options ) returns option to enable PayPal Payments Standard
 * @see \BooklyPaypalPaymentsStandard\Lib\ProxyProviders\Local::prepareToggleOptions()
 *
 * @method static string renderSetUpOptions() prints list of options to set up PayPal Payments Standard
 * @see \BooklyPaypalPaymentsStandard\Lib\ProxyProviders\Local::renderSetUpOptions()
 *
 * @method static string renderPaymentForm( string $form_id, string $page_url ) outputs HTML form for PayPal Payments Standard.
 * @see \BooklyPaypalPaymentsStandard\Lib\ProxyProviders\Local::renderPaymentForm()
 */
abstract class PaypalPaymentsStandard extends Base\ProxyInvoker
{

}
