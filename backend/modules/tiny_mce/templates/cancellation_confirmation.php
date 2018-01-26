<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<script type="text/javascript">
    jQuery(function ($) {
        var $add_cancellation_confirmation = $('#add-cancellation-confirmation');
        $add_cancellation_confirmation.on('click', function (e) {
            e.preventDefault();
            window.send_to_editor('[bookly-cancellation-confirmation]');
            return false;
        });
    });
</script>