<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'company' ) ) ?>">
    <div class="row">
        <div class="col-xs-3 col-lg-2">
            <div class="bookly-flexbox">
                <div id="bookly-js-logo" class="bookly-thumb bookly-thumb-lg bookly-margin-right-lg">
                    <input type="hidden" name="bookly_co_logo_attachment_id" data-default="<?php form_option( 'bookly_co_logo_attachment_id' ) ?>"
                           value="<?php form_option( 'bookly_co_logo_attachment_id' ) ?>">
                    <div class="bookly-flex-cell">
                        <div class="form-group">
                            <?php $img = wp_get_attachment_image_src( get_option( 'bookly_co_logo_attachment_id' ), 'thumbnail' ) ?>
                            <div class="bookly-js-image bookly-thumb bookly-thumb-lg bookly-margin-right-lg"
                                 data-style="<?php echo $img ? 'background-image: url(' . $img[0] . '); background-size: cover;' : '' ?>"
                                <?php echo $img ? 'style="background-image: url(' . $img[0] . '); background-size: cover;"' : '' ?>
                            >
                                <a class="dashicons dashicons-trash text-danger bookly-thumb-delete"
                                   href="javascript:void(0)"
                                   title="<?php _e( 'Delete', 'bookly' ) ?>"
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
        </div>
        <div class="col-xs-9 col-lg-10">
            <div class="bookly-flex-cell bookly-vertical-middle">
                <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_co_name', __( 'Company name', 'bookly' ) ) ?>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="bookly_co_address"><?php _e( 'Address', 'bookly' ) ?></label>
        <textarea id="bookly_co_address" class="form-control" rows="5"
                  name="bookly_co_address"><?php form_option( 'bookly_co_address' ) ?></textarea>
    </div>
    <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_co_phone', __( 'Phone', 'bookly' ) ) ?>
    <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_co_website', __( 'Website', 'bookly' ) ) ?>

    <div class="panel-footer">
        <?php \BooklyLite\Lib\Utils\Common::submitButton() ?>
        <?php \BooklyLite\Lib\Utils\Common::resetButton( 'bookly-company-reset' ) ?>
    </div>
</form>