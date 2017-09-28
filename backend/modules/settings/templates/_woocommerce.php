<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'woocommerce' ) ) ?>"
      id="woocommerce">
    <div class="form-group">
        <h4><?php _e( 'Instructions', 'bookly' ) ?></h4>
        <p>
            <?php _e( 'You need to install and activate WooCommerce plugin before using the options below.<br/><br/>Once the plugin is activated do the following steps:', 'bookly' ) ?>
        </p>
        <ol>
            <li><?php _e( 'Create a product in WooCommerce that can be placed in cart.', 'bookly' ) ?></li>
            <li><?php _e( 'In the form below enable WooCommerce option.', 'bookly' ) ?></li>
            <li><?php _e( 'Select the product that you created at step 1 in the drop down list of products.', 'bookly' ) ?></li>
            <li><?php _e( 'If needed, edit item data which will be displayed in the cart.', 'bookly' ) ?></li>
        </ol>
        <p>
            <?php _e( 'Note that once you have enabled WooCommerce option in Bookly the built-in payment methods will no longer work. All your customers will be redirected to WooCommerce cart instead of standard payment step.', 'bookly' ) ?>
        </p>
    </div>

    <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_wc_enabled', 'WooCommerce' ) ?>

    <div class="form-group">
        <label for="bookly_wc_product"><?php _e( 'Booking product', 'bookly' ) ?></label>
        <select id="bookly_wc_product" class="form-control" name="bookly_wc_product">
            <?php foreach ( $candidates as $item ) : ?>
                <option value="<?php echo $item['id'] ?>" <?php selected( get_option( 'bookly_wc_product' ), $item['id'] ) ?>>
                    <?php echo $item['name'] ?>
                </option>
            <?php endforeach ?>
        </select>
    </div>

    <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_l10n_wc_cart_info_name', __( 'Cart item data', 'bookly' ) ) ?>
    <div class="form-group">
        <textarea class="form-control" rows="8" name="bookly_l10n_wc_cart_info_value"
                  placeholder="<?php _e( 'Enter a value', 'bookly' ) ?>"><?php echo esc_textarea( get_option( 'bookly_l10n_wc_cart_info_value' ) ) ?></textarea><br/>
        <?php $this->render( '_woocommerce_codes' ) ?>
    </div>

    <div class="panel-footer">
        <?php \BooklyLite\Lib\Utils\Common::customButton( null, 'btn btn-lg btn-success bookly-limitation' ) ?>
        <?php \BooklyLite\Lib\Utils\Common::resetButton() ?>
    </div>
</form>