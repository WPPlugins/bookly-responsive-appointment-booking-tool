<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'cart' ) ) ?>">
    <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_cart_enabled', __( 'Cart', 'bookly' ), __( 'If cart is enabled then your clients will be able to book several appointments at once. Please note that WooCommerce integration must be disabled.', 'bookly' ) ) ?>
    <div class="form-group">
        <label for="bookly_cart_show_columns"><?php _e( 'Columns', 'bookly' ) ?></label><br/>
        <div class="ab-flags" id="bookly_cart_show_columns">
            <?php foreach ( (array) get_option( 'bookly_cart_show_columns' ) as $column => $attr ) : ?>
                <div class="bookly-flexbox"<?php if ( $column == 'deposit' && ! \BooklyLite\Lib\Config::isDepositPaymentsEnabled() ) : ?> style="display:none"<?php endif ?>>
                    <div class="bookly-flex-cell">
                        <i class="bookly-js-handle bookly-margin-right-sm bookly-icon bookly-icon-draghandle bookly-cursor-move" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                    </div>
                    <div class="bookly-flex-cell" style="width: 100%">
                        <div class="checkbox">
                            <label>
                                <input type="hidden" name="bookly_cart_show_columns[<?php echo $column ?>][show]" value="0">
                                <input type="checkbox"
                                       name="bookly_cart_show_columns[<?php echo $column ?>][show]"
                                       value="1" <?php checked( $attr['show'], true ) ?>>
                                <?php echo isset( $cart_columns[ $column ] ) ? $cart_columns[ $column ] : '' ?>
                            </label>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
    <div class="panel-footer">
        <?php \BooklyLite\Lib\Utils\Common::submitButton() ?>
        <?php \BooklyLite\Lib\Utils\Common::resetButton() ?>
    </div>
</form>