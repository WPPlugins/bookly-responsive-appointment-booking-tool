<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( $info_message ) : ?>
    <div class="bookly-box bookly-well">
        <div class="bookly-round bookly-margin-sm"><i class="bookly-icon-sm bookly-icon-i"></i></div>
        <div>
            <?php echo nl2br( $info_message ) ?>
        </div>
    </div>
<?php endif ?>