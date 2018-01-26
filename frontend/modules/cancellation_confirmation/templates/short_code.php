<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $color = get_option( 'bookly_app_color', '#f4662f' );
?>
<div id="bookly-tbs" class="bookly-js-cancellation-confirmation">
    <div class="bookly-js-cancellation-confirmation-buttons">
        <a href="<?php echo admin_url( 'admin-ajax.php?action=bookly_cancel_appointment&token=' . $token ) ?>" class="bookly-btn bookly-left bookly-inline-block" style="background: <?php echo $color ?>!important; width: auto; margin-right: 12px" data-spinner-size="40" data-style="zoom-in">
            <span><?php _e( 'Confirm', 'bookly' ) ?></span>
        </a>
        <a href="#" class="bookly-js-cancellation-confirmation-no bookly-btn bookly-inline-block bookly-left bookly-margin-left-md" style="background: <?php echo $color ?>!important; width: auto" data-spinner-size="40" data-style="zoom-in">
            <span><?php _e( 'Cancel', 'bookly' ) ?></span>
        </a>
    </div>
    <div class="bookly-js-cancellation-confirmation-message bookly-row" style="display: none">
        <p class="bookly-bold">
            <?php _e( 'Thank you for being with us', 'bookly' ) ?>
        </p>
    </div>
</div>
<script type="text/javascript">
    var links = document.getElementsByClassName('bookly-js-cancellation-confirmation-no');
    for (var i = 0; i < links.length; i++) {
        if (links[i].onclick == undefined) {
            links[i].onclick = function (e) {
                e.preventDefault();
                var container = this.closest('.bookly-js-cancellation-confirmation'),
                    buttons = container.getElementsByClassName('bookly-js-cancellation-confirmation-buttons')[0],
                    message = container.getElementsByClassName('bookly-js-cancellation-confirmation-message')[0];
                buttons.style.display = 'none';
                message.style.display = 'inline';
            };
        }
    }
</script>