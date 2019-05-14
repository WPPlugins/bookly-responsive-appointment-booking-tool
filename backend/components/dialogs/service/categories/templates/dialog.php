<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls;
use Bookly\Lib;
?>
<form id="bookly-service-categories-modal" class="modal fade" tabindex=-1 role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                <div class="modal-title h2"><?php esc_html_e( 'Categories', 'bookly' ) ?></div>
            </div>
            <div class="modal-body">
                <div class="form-inline bookly-margin-bottom-lg text-right">
                    <div class="form-group">
                        <?php Controls\Buttons::renderAdd( 'bookly-js-new-category', 'btn-success', esc_html__( 'Add category', 'bookly' ) ) ?>
                    </div>
                </div>
                <ul id="bookly-services-categories"></ul>
            </div>
            <div class="modal-footer">
                <?php Buttons::renderSubmit() ?>
                <?php Buttons::renderCustom( null, 'btn-lg btn-default', esc_html__( 'Close', 'bookly' ), array( 'data-dismiss' => 'modal' ) ) ?>
            </div>
        </div>
    </div>
</form>
<div class="collapse" id="bookly-new-category-template">
    <li class="form-group">
        <div class="row" style="line-height: 34px;">
            <input type="hidden" name="category_id" value="{{id}}"/>
            <div class="col-xs-1"><i class="fa fa-fw fa-lg fa-bars text-muted bookly-cursor-move bookly-js-draghandle" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i></div>
            <div class="col-xs-10"><input type="text" class="form-control" name="category_name" value="{{name}}"/></div>
            <div class="col-xs-1"><a href="#"><i class="fa fa-fw fa-trash fa-lg text-danger bookly-js-delete-category" title="<?php esc_attr_e( 'Delete', 'bookly' ) ?>"></i></a></div>
        </div>
    </li>
</div>