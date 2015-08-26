<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Custom Fields', 'bookly' ) ?></h3>
    </div>
    <div class="panel-body">
        <ul id="ab-custom-fields"></ul>

        <div id="ab-add-fields">
            <button class="button" data-type="text-field"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Text Field', 'bookly' ) ?></button>&nbsp;
            <button class="button" data-type="textarea"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Text Area', 'bookly' ) ?></button>&nbsp;
            <button class="button" data-type="checkboxes"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Checkbox Group', 'bookly' ) ?></button>&nbsp;
            <button class="button" data-type="radio-buttons"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Radio Button Group', 'bookly' ) ?></button>&nbsp;
            <button class="button" data-type="drop-down"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Drop Down', 'bookly' ) ?></button>
        </div>

        <ul id="ab-templates" style="display:none">

            <li data-type="text-field">
                <i class="ab-handle glyphicon glyphicon-align-justify" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                <h2 class="ab-field-title">
                    <?php _e( 'Text Field', 'bookly' ) ?>
                    <i class="ab-delete glyphicon glyphicon-trash" title="<?php echo esc_attr( __( 'Remove field', 'bookly' ) ) ?>"></i>
                </h2>
                <div class="input-group">
                    <input class="ab-label form-control" type="text" value="" placeholder="<?php echo esc_attr( __( 'Enter a label', 'bookly' ) ) ?>" />
                    <span class="input-group-addon">
                        <label>
                            <input class="ab-required" type="checkbox" />
                            <span><?php _e( 'Required field', 'bookly' ) ?></span>
                        </label>
                    </span>
                </div>
            </li>

            <li data-type="textarea">
                <i class="ab-handle glyphicon glyphicon-align-justify" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                <h2 class="ab-field-title">
                    <?php _e( 'Text Area', 'bookly' ) ?>
                    <i class="ab-delete glyphicon glyphicon-trash" title="<?php echo esc_attr( __( 'Remove field', 'bookly' ) ) ?>"></i>
                </h2>
                <div class="input-group">
                    <input class="ab-label form-control" type="text" value="" placeholder="<?php echo esc_attr( __( 'Enter a label', 'bookly' ) ) ?>" />
                    <span class="input-group-addon">
                        <label>
                            <input class="ab-required" type="checkbox" />
                            <span><?php _e( 'Required field', 'bookly' ) ?></span>
                        </label>
                    </span>
                </div>
            </li>

            <li data-type="checkboxes">
                <i class="ab-handle glyphicon glyphicon-align-justify" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                <h2 class="ab-field-title">
                    <?php _e( 'Checkbox Group', 'bookly' ) ?>
                    <i class="ab-delete glyphicon glyphicon-trash" title="<?php echo esc_attr( __( 'Remove field', 'bookly' ) ) ?>"></i>
                </h2>
                <div class="input-group">
                    <input class="ab-label form-control" type="text" value="" placeholder="<?php echo esc_attr( __( 'Enter a label', 'bookly' ) ) ?>" />
                    <span class="input-group-addon">
                        <label>
                            <input class="ab-required" type="checkbox" />
                            <span><?php _e( 'Required field', 'bookly' ) ?></span>
                        </label>
                    </span>
                </div>
                <ul class="ab-items"></ul>
                <button class="button" data-type="checkboxes-item"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Checkbox', 'bookly' ) ?></button>
            </li>

            <li data-type="radio-buttons">
                <i class="ab-handle glyphicon glyphicon-align-justify" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                <h2 class="ab-field-title">
                    <?php _e( 'Radio Button Group', 'bookly' ) ?>
                    <i class="ab-delete glyphicon glyphicon-trash" title="<?php echo esc_attr( __( 'Remove field', 'bookly' ) ) ?>"></i>
                </h2>
                <div class="input-group">
                    <input class="ab-label form-control" type="text" value="" placeholder="<?php echo esc_attr( __( 'Enter a label', 'bookly' ) ) ?>" />
                    <span class="input-group-addon">
                        <label>
                            <input class="ab-required" type="checkbox" />
                            <span><?php _e( 'Required field', 'bookly' ) ?></span>
                        </label>
                    </span>
                </div>
                <ul class="ab-items"></ul>
                <button class="button" data-type="radio-buttons-item"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Radio Button', 'bookly' ) ?></button>
            </li>

            <li data-type="drop-down">
                <i class="ab-handle glyphicon glyphicon-align-justify" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                <h2 class="ab-field-title">
                    <?php _e( 'Drop Down', 'bookly' ) ?>
                    <i class="ab-delete glyphicon glyphicon-trash" title="<?php echo esc_attr( __( 'Remove field', 'bookly' ) ) ?>"></i>
                </h2>
                <div class="input-group">
                    <input class="ab-label form-control" type="text" value="" placeholder="<?php echo esc_attr( __( 'Enter a label', 'bookly' ) ) ?>" />
                    <span class="input-group-addon">
                        <label>
                            <input class="ab-required" type="checkbox" />
                            <span><?php _e( 'Required field', 'bookly' ) ?></span>
                        </label>
                    </span>
                </div>
                <ul class="ab-items"></ul>
                <button class="button" data-type="drop-down-item"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Option', 'bookly' ) ?></button>
            </li>

            <li data-type="checkboxes-item">
                <i class="ab-inner-handle glyphicon glyphicon-align-justify" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                <input class="form-control ab-inline-block" type="text" value="" placeholder="<?php echo esc_attr( __( 'Enter a label', 'bookly' ) ) ?>" />
                <i class="ab-delete glyphicon glyphicon-trash" title="<?php echo esc_attr( __( 'Remove item', 'bookly' ) ) ?>"></i>
            </li>

            <li data-type="radio-buttons-item">
                <i class="ab-inner-handle glyphicon glyphicon-align-justify" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                <input class="form-control ab-inline-block" type="text" value="" placeholder="<?php echo esc_attr( __( 'Enter a label', 'bookly' ) ) ?>" />
                <i class="ab-delete glyphicon glyphicon-trash" title="<?php echo esc_attr( __( 'Remove item', 'bookly' ) ) ?>"></i>
            </li>

            <li data-type="drop-down-item">
                <i class="ab-inner-handle glyphicon glyphicon-align-justify" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                <input class="form-control ab-inline-block" type="text" value="" placeholder="<?php echo esc_attr( __( 'Enter a label', 'bookly' ) ) ?>" />
                <i class="ab-delete glyphicon glyphicon-trash" title="<?php echo esc_attr( __( 'Remove item', 'bookly' ) ) ?>"></i>
            </li>

        </ul>
    </div>
    <div class="panel-footer">
        <?php AB_Utils::submitButton( 'ajax-send-custom-fields' ) ?>
        <?php AB_Utils::resetButton() ?>
    </div>
</div>
