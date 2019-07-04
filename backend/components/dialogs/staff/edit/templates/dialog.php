<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Lib\Utils\Common;

/** @var Bookly\Lib\Entities\Staff $staff */
?>
<form id="bookly-staff-edit-modal" class="modal fade" tabindex=-1 role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                <div class="modal-title h2"></div>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <?php if ( Common::isCurrentUserAdmin() ) : ?>
                    <?php Buttons::renderDelete( 'bookly-staff-delete', 'btn-lg pull-left bookly-js-hide-on-loading' ) ?>
                    <?php Buttons::renderCustom( null, 'btn-lg btn-danger ladda-button bookly-js-staff-archive pull-left bookly-js-hide-on-loading', esc_html__( 'Archive', 'bookly' ), array(), '<i class="fa fa-archive"></i> {caption}' ) ?>
                <?php endif ?>
                <span class="bookly-js-errors text-danger" style="max-width: 353px;display: inline-grid;"></span>
                <?php Buttons::renderCustom( null, 'btn-lg btn-success bookly-js-save bookly-js-hide-on-loading', esc_html__( 'Save', 'bookly' ) ) ?>
                <?php Buttons::renderCustom( null, 'btn-lg btn-default', esc_html__( 'Close', 'bookly' ), array( 'data-dismiss' => 'modal' ) ) ?>
            </div>
        </div>
    </div>
</form>