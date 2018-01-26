<?php
namespace BooklyLite\Lib\Proxy;

use BooklyLite\Lib\Base;

/**
 * Class DepositPayments
 * Invoke local methods from Deposit Payments Standard add-on.
 *
 * @package BooklyLite\Lib\Proxy
 *
 * @method static string formatDeposit( double $deposit_amount, string $deposit ) Return formatted deposit amount
 * @see \BooklyDepositPayments\Lib\ProxyProviders\Local::formatDeposit()
 *
 * @method static double|string prepareAmount( double $deposit_amount, string $deposit, int $number_of_persons ) Return deposit amount for all persons
 * @see \BooklyDepositPayments\Lib\ProxyProviders\Local::prepareAmount()
 *
 * @method static void renderStaffServiceLabel() Render column header for deposit
 * @see \BooklyDepositPayments\Lib\ProxyProviders\Local::renderStaffServiceLabel()
 */
abstract class DepositPayments extends Base\ProxyInvoker
{

}