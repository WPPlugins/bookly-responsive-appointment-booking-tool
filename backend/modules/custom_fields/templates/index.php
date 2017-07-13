<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php _e( 'Custom Fields', 'bookly' ) ?>
            </div>
            <?php \BooklyLite\Backend\Modules\Support\Components::getInstance()->renderButtons( $this::page_slug ) ?>
        </div>
        <div class="panel panel-default bookly-main">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4">
                        <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_custom_fields_per_service', __( 'Bind fields to services', 'bookly' ), __( 'When this setting is enabled you will be able to create service specific custom fields.', 'bookly' ) ) ?>
                    </div>
                </div>

                <hr />

                <ul id="ab-custom-fields"></ul>

                <div id="ab-add-fields">
                    <button class="btn btn-default bookly-margin-bottom-sm bookly-margin-right-sm" data-type="text-field"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Text Field', 'bookly' ) ?></button>
                    <button class="btn btn-default bookly-margin-bottom-sm bookly-margin-right-sm" data-type="textarea"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Text Area', 'bookly' ) ?></button>
                    <button class="btn btn-default bookly-margin-bottom-sm bookly-margin-right-sm" data-type="text-content"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Text Content', 'bookly' ) ?></button>
                    <button class="btn btn-default bookly-margin-bottom-sm bookly-margin-right-sm" data-type="checkboxes"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Checkbox Group', 'bookly' ) ?></button>
                    <button class="btn btn-default bookly-margin-bottom-sm bookly-margin-right-sm" data-type="radio-buttons"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Radio Button Group', 'bookly' ) ?></button>
                    <button class="btn btn-default bookly-margin-bottom-sm bookly-margin-right-sm" data-type="drop-down"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Drop Down', 'bookly' ) ?></button>
                    <button class="btn btn-default bookly-margin-bottom-sm bookly-margin-right-sm" data-type="captcha"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Captcha', 'bookly' ) ?></button>
                </div>
                <p class="help-block"><?php _e( 'HTML allowed in all texts and labels.', 'bookly' ) ?></p>

                <ul id="ab-templates" style="display:none">

                    <li data-type="textarea">
                        <div class="bookly-flexbox">
                            <div class="bookly-flex-cell">
                                <i title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>" class="bookly-js-handle bookly-icon bookly-icon-draghandle bookly-margin-right-sm bookly-cursor-move"></i>
                            </div>
                            <div class="bookly-flex-cell" style="width: 100%">
                                <p><b><?php _e( 'Text Area', 'bookly' ) ?></b><a class="ab-delete glyphicon glyphicon-trash text-danger bookly-margin-left-sm" href="#"
                                    title="<?php esc_attr_e( 'Remove field', 'bookly' ) ?>"></a></p>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <input class="ab-label form-control" type="text" value="" placeholder="<?php esc_attr_e( 'Enter a label', 'bookly' ) ?>">
                                            <label class="input-group-addon">
                                                <input class="ab-required" type="checkbox">
                                                <span class="hidden-xs"><?php _e( 'Required field', 'bookly' ) ?></span>
                                                <i class="visible-xs-inline-block glyphicon glyphicon-warning"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <?php echo $services_html ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                    </li>

                    <li data-type="text-content">
                        <div class="bookly-flexbox">
                            <div class="bookly-flex-cell">
                                <i title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>" class="bookly-js-handle bookly-icon bookly-icon-draghandle bookly-margin-right-sm bookly-cursor-move"></i>
                            </div>
                            <div class="bookly-flex-cell" style="width: 100%">
                                <p><b><?php _e( 'Text Content', 'bookly' ) ?></b><a class="ab-delete glyphicon glyphicon-trash text-danger bookly-margin-left-sm" href="#"
                                    title="<?php esc_attr_e( 'Remove field', 'bookly' ) ?>"></a></p>
                                <div class="row">
                                    <div class="col-md-8">
                                        <textarea class="ab-label form-control" type="text" rows="3"
                                                  placeholder="<?php esc_attr_e( 'Enter a content', 'bookly' ) ?>"></textarea>
                                        <input class="ab-required hidden" type="checkbox" disabled="disabled">
                                    </div>
                                    <div class="col-md-4">
                                        <?php echo $services_html ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                    </li>

                    <li data-type="text-field">
                        <div class="bookly-flexbox">
                            <div class="bookly-flex-cell">
                                <i title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>" class="bookly-js-handle bookly-icon bookly-icon-draghandle bookly-margin-right-sm bookly-cursor-move"></i>
                            </div>
                            <div class="bookly-flex-cell" style="width: 100%">
                                <p><b><?php _e( 'Text Field', 'bookly' ) ?></b><a class="ab-delete glyphicon glyphicon-trash text-danger bookly-margin-left-sm" href="#"
                                    title="<?php esc_attr_e( 'Remove field', 'bookly' ) ?>"></a></p>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <input class="ab-label form-control" type="text" value=""
                                                   placeholder="<?php esc_attr_e( 'Enter a label', 'bookly' ) ?>">
                                            <label class="input-group-addon">
                                                <input class="ab-required" type="checkbox">
                                                <span class="hidden-xs"><?php _e( 'Required field', 'bookly' ) ?></span>
                                                <i class="visible-xs-inline-block glyphicon glyphicon-warning"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <?php echo $services_html ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                    </li>

                    <li data-type="checkboxes">
                        <div class="bookly-flexbox">
                            <div class="bookly-flex-cell">
                                <i title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>" class="bookly-js-handle bookly-icon bookly-icon-draghandle bookly-margin-right-sm bookly-cursor-move"></i>
                            </div>
                            <div class="bookly-flex-cell" style="width: 100%">
                                <p><b><?php _e( 'Checkbox Group', 'bookly' ) ?></b><a class="ab-delete glyphicon glyphicon-trash text-danger bookly-margin-left-sm" href="#"
                                   title="<?php esc_attr_e( 'Remove field', 'bookly' ) ?>"></a></p>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <input class="ab-label form-control" type="text" value=""
                                                   placeholder="<?php esc_attr_e( 'Enter a label', 'bookly' ) ?>">
                                            <label class="input-group-addon">
                                                <input class="ab-required" type="checkbox">
                                                <span class="hidden-xs"><?php _e( 'Required field', 'bookly' ) ?></span>
                                                <i class="visible-xs-inline-block glyphicon glyphicon-warning"></i>
                                            </label>
                                        </div>

                                        <ul class="ab-items bookly-margin-top-sm"></ul>
                                        <button class="btn btn-sm btn-default" data-type="checkboxes-item">
                                            <i class="glyphicon glyphicon-plus"></i> <?php _e( 'Checkbox', 'bookly' ) ?>
                                        </button>
                                    </div>
                                    <div class="col-md-4">
                                        <?php echo $services_html ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                    </li>

                    <li data-type="radio-buttons">
                        <div class="bookly-flexbox">
                            <div class="bookly-flex-cell">
                                <i title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>" class="bookly-js-handle bookly-icon bookly-icon-draghandle bookly-margin-right-sm bookly-cursor-move"></i>
                            </div>
                            <div class="bookly-flex-cell" style="width: 100%">
                                <p><b><?php _e( 'Radio Button Group', 'bookly' ) ?></b><a class="ab-delete glyphicon glyphicon-trash text-danger bookly-margin-left-sm" href="#"
                                    title="<?php esc_attr_e( 'Remove field', 'bookly' ) ?>"></a></p>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <input class="ab-label form-control" type="text" value=""
                                                   placeholder="<?php esc_attr_e( 'Enter a label', 'bookly' ) ?>">
                                            <label class="input-group-addon">
                                                <input class="ab-required" type="checkbox">
                                                <span class="hidden-xs"><?php _e( 'Required field', 'bookly' ) ?></span>
                                                <i class="visible-xs-inline-block glyphicon glyphicon-warning"></i>
                                            </label>
                                        </div>

                                        <ul class="ab-items bookly-margin-top-sm"></ul>
                                        <button class="btn btn-sm btn-default" data-type="radio-buttons-item">
                                            <i class="glyphicon glyphicon-plus"></i> <?php _e( 'Radio Button', 'bookly' ) ?>
                                        </button>
                                    </div>
                                    <div class="col-md-4">
                                        <?php echo $services_html ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                    </li>

                    <li data-type="drop-down">
                        <div class="bookly-flexbox">
                            <div class="bookly-flex-cell">
                                <i title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>" class="bookly-js-handle bookly-icon bookly-icon-draghandle bookly-margin-right-sm bookly-cursor-move"></i>
                            </div>
                            <div class="bookly-flex-cell" style="width: 100%">
                                <p><b><?php _e( 'Drop Down', 'bookly' ) ?></b><a class="ab-delete glyphicon glyphicon-trash text-danger bookly-margin-left-sm" href="#"
                                    title="<?php esc_attr_e( 'Remove field', 'bookly' ) ?>"></a></p>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <input class="ab-label form-control" type="text" value=""
                                                   placeholder="<?php esc_attr_e( 'Enter a label', 'bookly' ) ?>">
                                            <label class="input-group-addon">
                                                <input class="ab-required" type="checkbox">
                                                <span class="hidden-xs"><?php _e( 'Required field', 'bookly' ) ?></span>
                                                <i class="visible-xs-inline-block glyphicon glyphicon-warning"></i>
                                            </label>
                                        </div>

                                        <ul class="ab-items bookly-margin-top-sm"></ul>
                                        <button class="btn btn-sm btn-default" data-type="drop-down-item">
                                            <i class="glyphicon glyphicon-plus"></i> <?php _e( 'Option', 'bookly' ) ?>
                                        </button>
                                    </div>
                                    <div class="col-md-4">
                                        <?php echo $services_html ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                    </li>

                    <li data-type="captcha">
                        <div class="bookly-flexbox">
                            <div class="bookly-flex-cell">
                                <i title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>" class="bookly-js-handle bookly-icon bookly-icon-draghandle bookly-margin-right-sm bookly-cursor-move"></i>
                            </div>
                            <div class="bookly-flex-cell" style="width: 100%">
                                <p><b><?php _e( 'Captcha', 'bookly' ) ?></b><a class="ab-delete glyphicon glyphicon-trash text-danger bookly-margin-left-sm" href="#"
                                    title="<?php esc_attr_e( 'Remove field', 'bookly' ) ?>"></a></p>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <input class="ab-label form-control" type="text" value=""
                                                   placeholder="<?php esc_attr_e( 'Enter a label', 'bookly' ) ?>">
                                            <label class="input-group-addon">
                                                <input class="ab-required hidden" type="checkbox">
                                                <input type="checkbox" disabled="disabled" checked="checked">
                                                <span class="hidden-xs"><?php _e( 'Required field', 'bookly' ) ?></span>
                                                <i class="visible-xs-inline-block glyphicon glyphicon-warning"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <?php echo $services_html ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                    </li>

                    <li data-type="checkboxes-item">
                        <div class="bookly-flexbox">
                            <div class="bookly-flex-cell bookly-vertical-middle">
                                <i title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>" class="bookly-js-handle bookly-icon bookly-icon-draghandle bookly-margin-right-sm bookly-cursor-move"></i>
                            </div>
                            <div class="bookly-flex-cell bookly-vertical-middle" style="width: 100%">
                                <input class="form-control" type="text" value=""
                                       placeholder="<?php esc_attr_e( 'Enter a label', 'bookly' ) ?>">
                            </div>
                            <div class="bookly-flex-cell bookly-vertical-middle">
                                <a class="ab-delete glyphicon glyphicon-trash text-danger bookly-margin-left-sm" href="#"
                                   title="<?php esc_attr_e( 'Remove item', 'bookly' ) ?>"></a>
                            </div>
                        </div>
                    </li>

                    <li data-type="radio-buttons-item">
                        <div class="bookly-flexbox">
                            <div class="bookly-flex-cell bookly-vertical-middle">
                                <i title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>" class="bookly-js-handle bookly-icon bookly-icon-draghandle bookly-margin-right-sm bookly-cursor-move"></i>
                            </div>
                            <div class="bookly-flex-cell bookly-vertical-middle" style="width: 100%">
                                <input class="form-control" type="text" value=""
                                       placeholder="<?php esc_attr_e( 'Enter a label', 'bookly' ) ?>">
                            </div>
                            <div class="bookly-flex-cell bookly-vertical-middle">
                                <a class="ab-delete glyphicon glyphicon-trash text-danger bookly-margin-left-sm" href="#"
                                   title="<?php esc_attr_e( 'Remove item', 'bookly' ) ?>"></a>
                            </div>
                        </div>
                    </li>

                    <li data-type="drop-down-item">
                        <div class="bookly-flexbox">
                            <div class="bookly-flex-cell bookly-vertical-middle">
                                <i title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>" class="bookly-js-handle bookly-icon bookly-icon-draghandle bookly-margin-right-sm bookly-cursor-move"></i>
                            </div>
                            <div class="bookly-flex-cell bookly-vertical-middle" style="width: 100%">
                                <input class="form-control" type="text" value=""
                                       placeholder="<?php esc_attr_e( 'Enter a label', 'bookly' ) ?>">
                            </div>
                            <div class="bookly-flex-cell bookly-vertical-middle">
                                <a class="ab-delete glyphicon glyphicon-trash text-danger bookly-margin-left-sm" href="#"
                                   title="<?php esc_attr_e( 'Remove item', 'bookly' ) ?>"></a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="panel-footer">
                <?php \BooklyLite\Lib\Utils\Common::submitButton( 'ajax-send-custom-fields' ) ?>
                <?php \BooklyLite\Lib\Utils\Common::resetButton() ?>
            </div>
        </div>
    </div>
</div>