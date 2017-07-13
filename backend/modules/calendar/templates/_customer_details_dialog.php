<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use BooklyLite\Lib\Entities\CustomerAppointment;
?>
<div id="bookly-customer-details-dialog" class="modal fade" tabindex=-1 role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <div class="modal-title h2"><?php _e( 'Edit booking details', 'bookly' ) ?></div>
            </div>
            <form ng-hide=loading style="z-index: 1050">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="ab-appointment-status"><?php _e( 'Status', 'bookly' ) ?></label>
                        <select class="ab-custom-field form-control" id="ab-appointment-status">
                            <option value="<?php echo CustomerAppointment::STATUS_PENDING ?>"><?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_PENDING ) ) ?></option>
                            <option value="<?php echo CustomerAppointment::STATUS_APPROVED ?>"><?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_APPROVED ) ) ?></option>
                            <option value="<?php echo CustomerAppointment::STATUS_CANCELLED ?>"><?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_CANCELLED ) ) ?></option>
                            <option value="<?php echo CustomerAppointment::STATUS_REJECTED ?>"><?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_REJECTED ) ) ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="ab-edit-number-of-persons"><?php _e( 'Number of persons', 'bookly' ) ?></label>
                        <select class="ab-custom-field form-control" id="ab-edit-number-of-persons"></select>
                    </div>
                    <?php do_action( 'bookly_render_customer_details_dialog' ) ?>
                    <h3 class="bookly-block-head bookly-color-gray">
                        <?php _e( 'Custom Fields', 'bookly' ) ?>
                    </h3>
                    <div id="ab--custom-fields">
                        <?php foreach ( $custom_fields as $custom_field ) : ?>
                            <div class="form-group" data-type="<?php echo esc_attr( $custom_field->type )?>" data-id="<?php echo esc_attr( $custom_field->id ) ?>" data-services="<?php echo esc_attr( json_encode( $custom_field->services ) ) ?>">
                                <label for="custom_field_<?php echo esc_attr( $custom_field->id ) ?>"><?php echo $custom_field->label ?></label>
                                <div>
                                    <?php if ( $custom_field->type == 'text-field' ) : ?>
                                        <input id="custom_field_<?php echo esc_attr( $custom_field->id ) ?>" type="text" class="ab-custom-field form-control" />

                                    <?php elseif ( $custom_field->type == 'textarea' ) : ?>
                                        <textarea id="custom_field_<?php echo esc_attr( $custom_field->id ) ?>" rows="3" class="ab-custom-field form-control"></textarea>

                                    <?php elseif ( $custom_field->type == 'checkboxes' ) : ?>
                                        <?php foreach ( $custom_field->items as $item ) : ?>
                                            <div class="checkbox">
                                                <label>
                                                    <input class="ab-custom-field" type="checkbox" value="<?php echo esc_attr( $item ) ?>" />
                                                    <?php echo $item ?>
                                                </label>
                                            </div>
                                        <?php endforeach ?>

                                    <?php elseif ( $custom_field->type == 'radio-buttons' ) : ?>
                                        <?php foreach ( $custom_field->items as $item ) : ?>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="<?php echo $custom_field->id ?>" class="ab-custom-field" value="<?php echo esc_attr( $item ) ?>" />
                                                    <?php echo $item ?>
                                                </label>
                                            </div>
                                        <?php endforeach ?>

                                    <?php elseif ( $custom_field->type == 'drop-down' ) : ?>
                                        <select id="custom_field_<?php echo esc_attr( $custom_field->id ) ?>" class="ab-custom-field form-control">
                                            <option value=""></option>
                                            <?php foreach ( $custom_field->items as $item ) : ?>
                                                <option value="<?php echo esc_attr( $item ) ?>"><?php echo $item ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    <?php endif ?>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>

                    <?php if ( $extras = apply_filters( 'bookly_service_extras_find_all', array() ) ) : ?>
                        <h3 class="bookly-block-head bookly-color-gray">
                            <?php _e( 'Extras', 'bookly' ) ?>
                        </h3>
                        <div id="bookly-extras" class="bookly-flexbox">
                            <?php foreach ( $extras as $extra ) : ?>
                                <div class="bookly-flex-row service_<?php echo $extra->get( 'service_id' ) ?> bookly-margin-bottom-sm">
                                    <div class="bookly-flex-cell bookly-padding-bottom-sm" style="width:5em">
                                        <input class="extras-count form-control" data-id="<?php echo $extra->get( 'id' ) ?>" type="number" min="0" name="extra[<?php echo $extra->get( 'id' ) ?>]" value="0" />
                                    </div>
                                    <div class="bookly-flex-cell bookly-padding-bottom-sm bookly-vertical-middle">
                                        &nbsp;&times; <b><?php echo $extra->getTitle() ?></b> (<?php echo \BooklyLite\Lib\Utils\Common::formatPrice( $extra->get( 'price' ) ) ?>)
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    <?php endif ?>
                </div>
                <div class="modal-footer">
                    <input type="button" data-customer="" ng-click=saveCustomFields() class="ab-popup-save btn btn-lg btn-success" value="<?php _e( 'Apply', 'bookly' ) ?>">
                    <input type="button" class="btn btn-lg btn-default" data-dismiss=modal value="<?php _e( 'Cancel', 'bookly' ) ?>" aria-hidden=true>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->