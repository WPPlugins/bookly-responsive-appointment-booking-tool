<?php
namespace Bookly\Backend\Modules\Appearance\Proxy;

use Bookly\Lib;

/**
 * Class Pro
 * @package Bookly\Backend\Modules\Appearance\Proxy
 *
 * @method static void renderFacebookButton() Render facebook login button on Time step.
 * @method static void renderMultipleBookingSelector() Render single/multiple booking selector on Payment step.
 * @method static void renderMultipleBookingText() Render multiple booking text option on Payment step.
 * @method static void renderPayPalPaymentOption() Render Cart step.
 * @method static void renderShowFacebookButton() Render 'Show facebook login button switcher' on Time step.
 * @method static void renderTimeZoneSwitcher() Render timezone switcher on Time step.
 * @method static void renderTimeZoneSwitcherCheckbox() Render 'Show time zone switcher' on Time step.
 * @method static void renderShowAddress() render 'Show Address Fields' on Details Step.
 * @method static void renderShowBirthday() render 'Show Birthday Fields' on Details Step.
 */
abstract class Pro extends Lib\Base\Proxy
{

}