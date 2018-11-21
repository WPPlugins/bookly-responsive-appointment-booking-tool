<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Modules\Staff\Proxy;
/** @var Bookly\Lib\Entities\Staff $staff */
?>
<div class="panel panel-default bookly-main">
    <div class="panel-body">
        <div class="bookly-flexbox bookly-margin-bottom-md">
            <div class="bookly-flex-cell bookly-vertical-middle" style="width: 1%">
                <div id="bookly-js-staff-avatar" class="bookly-thumb bookly-thumb-lg bookly-margin-right-lg">
                    <div class="bookly-flex-cell" style="width: 100%">
                        <div class="form-group">
                            <?php $img = wp_get_attachment_image_src( $staff->getAttachmentId(), 'thumbnail' ) ?>

                            <div class="bookly-js-image bookly-thumb bookly-thumb-lg bookly-margin-right-lg"
                                <?php echo $img ? 'style="background-image: url(' . $img[0] . '); background-size: cover;"' : '' ?>
                            >
                                <a class="dashicons dashicons-trash text-danger bookly-thumb-delete"
                                   href="javascript:void(0)"
                                   title="<?php esc_attr_e( 'Delete', 'bookly' ) ?>"
                                   <?php if ( ! $img ) : ?>style="display: none;"<?php endif ?>>
                                </a>
                                <div class="bookly-thumb-edit">
                                    <div class="bookly-pretty">
                                        <label class="bookly-pretty-indicator bookly-thumb-edit-btn">
                                            <?php _e( 'Image', 'bookly' ) ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bookly-flex-cell bookly-vertical-top"><h1 class="bookly-js-staff-name-<?php echo $staff->getId() ?>"><?php echo esc_html( $staff->getFullName() ) ?><?php Proxy\Ratings::renderStaffServiceRating( $staff->getId(), null, 'left' ) ?></h1></div>
        </div>

        <ul class="nav nav-tabs nav-justified bookly-nav-justified">
            <li class="active">
                <a id="bookly-details-tab" href="#details" data-toggle="tab">
                    <i class="bookly-icon bookly-icon-info"></i>
                    <span class="bookly-nav-tabs-title"><?php _e( 'Details', 'bookly' ) ?></span>
                </a>
            </li>
            <li>
                <a id="bookly-services-tab" href="#services" data-toggle="tab">
                    <i class="bookly-icon bookly-icon-checklist"></i>
                    <span class="bookly-nav-tabs-title"><?php _e( 'Services', 'bookly' ) ?></span>
                </a>
            </li>
            <li>
                <a id="bookly-schedule-tab" href="#schedule" data-toggle="tab">
                    <i class="bookly-icon bookly-icon-schedule"></i>
                    <span class="bookly-nav-tabs-title"><?php _e( 'Schedule', 'bookly' ) ?></span>
                </a>
            </li>
            <?php Proxy\Shared::renderStaffTab( $staff ) ?>
            <li>
                <a id="bookly-holidays-tab" href="#daysoff" data-toggle="tab">
                    <i class="bookly-icon bookly-icon-daysoff"></i>
                    <span class="bookly-nav-tabs-title"><?php _e( 'Days off', 'bookly' ) ?></span>
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div style="display: none;" class="bookly-loading"></div>

            <div class="tab-pane active" id="details">
                <div id="bookly-details-container"></div>
            </div>
            <div class="tab-pane" id="services">
                <div id="bookly-services-container" style="display: none"></div>
            </div>
            <div class="tab-pane" id="schedule">
                <div id="bookly-schedule-container" style="display: none"></div>
            </div>
            <div class="tab-pane" id="daysoff">
                <div id="bookly-holidays-container" style="display: none"></div>
            </div>
        </div>
    </div>
</div>