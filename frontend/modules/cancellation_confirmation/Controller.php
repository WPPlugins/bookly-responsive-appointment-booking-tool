<?php
namespace BooklyLite\Frontend\Modules\CancellationConfirmation;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Frontend\Modules\CancellationConfirmation
 */
class Controller extends Lib\Base\Controller
{
    public function renderShortCode( $attributes )
    {
        // Disable caching.
        Lib\Utils\Common::noCache();

        // Prepare URL for AJAX requests.
        $ajax_url = admin_url( 'admin-ajax.php' );

        $token = $this->getParameter( 'bookly-appointment-token', '' );

        return $this->render( 'short_code', compact( 'ajax_url', 'token' ), false );
    }
}