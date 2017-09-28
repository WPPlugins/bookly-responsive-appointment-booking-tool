<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $img = wp_get_attachment_image_src( $staff['attachment_id'], 'thumbnail' );
    /** @var \BooklyLite\Lib\Entities\Staff $staff */
?>
<li class="bookly-nav-item<?php if ( $active_staff_id == $staff['id'] ) : ?> active<?php endif ?>" id="bookly-staff-<?php echo $staff['id'] ?>" data-staff-id="<?php echo $staff['id'] ?>">
    <div class="bookly-flexbox">
        <div class="bookly-flex-cell bookly-vertical-middle" style="width: 1%">
            <i class="bookly-js-handle bookly-icon bookly-icon-draghandle bookly-margin-right-sm bookly-cursor-move" title="<?php _e( 'Reorder', 'bookly' ) ?>"></i>
        </div>
        <div class="bookly-flex-cell bookly-vertical-middle" style="width: 1%">
            <div class="bookly-thumb bookly-thumb-sm bookly-margin-right-lg"
                 <?php echo $img ? 'style="background-image: url(' . $img[0] . '); background-size: cover;background-position:0"' : ''  ?>
            ></div>
        </div>
        <div class="bookly-flex-cell bookly-vertical-middle">
            <?php echo esc_html( $staff['full_name'] ) ?>
        </div>
    </div>
</li>