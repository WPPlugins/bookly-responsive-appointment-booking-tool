<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="ab_custom_fields_dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title"><?php _e( 'Edit booking details', 'bookly' ) ?></h4>
            </div>
            <form class="form-horizontal" ng-hide=loading style="z-index: 1050">
            <div class="modal-body">

                    <fieldset>
                        <legend><?php _e( 'Participants', 'bookly' ) ?></legend>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="ab-edit-number-of-persons"><?php _e( 'Number of persons', 'bookly' ) ?></label>
                                <select class="ab-custom-field form-control" id="ab-edit-number-of-persons"></select>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php _e( 'Custom Fields', 'bookly' ) ?></legend>
                        <?php foreach ( json_decode( get_option( 'ab_custom_fields' ) ) as $custom_field ): ?>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="ab-formLabel"><?php echo $custom_field->label ?></label>
                                    <div class="ab-formField" data-type="<?php echo esc_attr( $custom_field->type )?>" data-id="<?php echo esc_attr( $custom_field->id ) ?>">

                                        <?php if ( $custom_field->type == 'text-field' ): ?>
                                            <input type="text" class="ab-custom-field form-control" />

                                        <?php elseif ( $custom_field->type == 'textarea' ): ?>
                                            <textarea rows="3" class="ab-custom-field form-control"></textarea>

                                        <?php elseif ( $custom_field->type == 'checkboxes' ): ?>
                                            <?php foreach ( $custom_field->items as $item ): ?>
                                                <div class="checkbox">
                                                    <label>
                                                        <input class="ab-custom-field" type="checkbox" value="<?php echo esc_attr( $item ) ?>" />
                                                        <?php echo $item ?>
                                                    </label>
                                                </div>
                                            <?php endforeach ?>

                                        <?php elseif ( $custom_field->type == 'radio-buttons' ): ?>
                                            <?php foreach ( $custom_field->items as $item ): ?>
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="<?php echo $custom_field->id ?>" class="ab-custom-field" value="<?php echo esc_attr( $item ) ?>" />
                                                        <?php echo $item ?>
                                                    </label>
                                                </div>
                                            <?php endforeach ?>

                                        <?php elseif ( $custom_field->type == 'drop-down' ): ?>
                                            <select class="ab-custom-field form-control">
                                                <option value=""></option>
                                                <?php foreach ( $custom_field->items as $item ): ?>
                                                    <option value="<?php echo esc_attr( $item ) ?>"><?php echo $item ?></option>
                                                <?php endforeach ?>
                                            </select>
                                        <?php endif ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </fieldset>

            </div>
            <div class="modal-footer">
                <input type="button" data-customer="" ng-click=saveCustomFields() class="btn btn-info ab-popup-save" value="<?php _e( 'Apply', 'bookly' ) ?>">
                <input type="button" class="ab-reset-form" data-dismiss=modal value="<?php _e( 'Cancel', 'bookly' ) ?>" aria-hidden=true>
            </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->