<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Modules\Services\Proxy;
use Bookly\Lib;
?>
<form id="bookly-create-service-modal" class="modal fade" tabindex=-1 role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                <div class="modal-title h2"><?php esc_html_e( 'Create service', 'bookly' ) ?></div>
            </div>
            <div class="modal-body">
                <div class="form-group bookly-margin-bottom-md">
                    <div class="form-field form-required">
                        <label for="bookly-new-service-title"><?php _e( 'Title', 'bookly' ) ?></label>
                        <input class="form-control bookly-js-new-service-title" id="bookly-new-service-title" name="title" type="text">
                    </div>
                </div>
                <?php if ( count( $service_types = Proxy\Shared::prepareServiceTypes( array( Lib\Entities\Service::TYPE_SIMPLE => ucfirst( Lib\Entities\Service::TYPE_SIMPLE ) ) ) ) > 1 ) : ?>
                    <div class="form-group bookly-margin-bottom-md">
                        <div class="form-field form-required">
                            <label for="bookly-new-service-type"><?php _e( 'Type', 'bookly' ) ?></label>
                            <select class="form-control bookly-js-new-service-type" id="bookly-new-service-type" name="type">
                                <?php foreach ( $service_types as $type => $title ): ?>
                                    <option data-icon="<?php echo esc_attr( $type_icons[ $type ] ) ?>" value="<?php echo $type ?>"><?php echo $title ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                <?php endif ?>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="action" value="bookly_create_service"/>
                <?php Inputs::renderCsrf() ?>
                <?php Buttons::renderSubmit( null, 'bookly-js-save', esc_html__( 'Create service', 'bookly' ) ) ?>
                <?php Buttons::renderCustom( null, 'btn-lg btn-default', esc_html__( 'Close', 'bookly' ), array( 'data-dismiss' => 'modal' ) ) ?>
            </div>
        </div>
    </div>
</form>