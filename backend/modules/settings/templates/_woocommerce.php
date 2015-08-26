<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form enctype="multipart/form-data" method="post" action="<?php echo esc_url( add_query_arg( 'type', '_woocommerce' ) ) ?>" class="ab-settings-form" id="woocommerce">
    <table class="form-horizontal">
        <tr>
            <td colspan="2">
                <fieldset class="ab-instruction">
                    <legend><?php _e( 'Instructions', 'bookly' ) ?></legend>
                    <div>
                        <div style="margin-bottom: 10px">
                            <?php _e( 'You need to install and activate WooCommerce plugin before using the options below.<br/><br/>Once the plugin is activated do the following steps:', 'bookly' ) ?>
                        </div>
                        <ol>
                            <li><?php _e( 'Create a product in WooCommerce that can be placed in cart.', 'bookly' ) ?></li>
                            <li><?php _e( 'In the form below enable WooCommerce option.', 'bookly' ) ?></li>
                            <li><?php _e( 'Select the product that you created at step 1 in the drop down list of products.', 'bookly' ) ?></li>
                            <li><?php _e( 'If needed, edit item data which will be displayed in the cart.', 'bookly' ) ?></li>
                        </ol>
                        <div style="margin-top: 10px">
                            <?php _e( 'Note that once you have enabled WooCommerce option in Bookly the built-in payment methods will no longer work. All your customers will be redirected to WooCommerce cart instead of standard payment step.', 'bookly' ) ?>
                        </div>
                    </div>
                </fieldset>
            </td>
        </tr>
        <tr>
            <td colspan="2"><div class="ab-payments-title">WooCommerce</div></td>
        </tr>
        <tr>
            <td>
                <?php AB_Utils::optionToggle( 'ab_woocommerce' ) ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?php _e( 'Booking product', 'bookly' ) ?>
                <select class="form-control" name="ab_woocommerce_product">
                    <?php foreach ( $candidates as $item ) : ?>
                        <option value="<?php echo $item['id'] ?>" <?php selected( get_option( 'ab_woocommerce_product' ), $item['id'] ); ?>>
                            <?php echo $item['name'] ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2"><div class="ab-payments-title"><?php _e( 'Cart item data', 'bookly' ) ?></td>
        </tr>
        <tr>
            <td colspan="2"><input class="form-control" type="text" name="ab_woocommerce_cart_info_name" value="<?php echo esc_attr( get_option( 'ab_woocommerce_cart_info_name' ) ) ?>" placeholder="<?php echo esc_attr( __( 'Enter a name', 'bookly' ) ) ?>" /></td>
        </tr>
        <tr>
            <td colspan="2">
                <textarea class="form-control" rows="8" name="ab_woocommerce_cart_info_value" style="width: 100%" placeholder="<?php _e( 'Enter a value', 'bookly' ) ?>"><?php echo esc_textarea( get_option( 'ab_woocommerce_cart_info_value' ) ) ?></textarea>
            </td>
        </tr>
        <tr>
            <td>
                <div class="ab-codes">
                    <table>
                        <tr><td><input value="[[APPOINTMENT_DATE]]" readonly="readonly" onclick="this.select()"> - <?php _e('date of appointment', 'bookly') ?></td></tr>
                        <tr><td><input value="[[APPOINTMENT_TIME]]" readonly="readonly" onclick="this.select()"> - <?php _e('time of appointment', 'bookly') ?></td></tr>
                        <tr><td><input value="[[CATEGORY_NAME]]" readonly="readonly" onclick="this.select()"> - <?php _e('name of category', 'bookly') ?></td></tr>
                        <tr><td><input value="[[SERVICE_NAME]]" readonly="readonly" onclick="this.select()"> - <?php _e('name of service', 'bookly') ?></td></tr>
                        <tr><td><input value="[[SERVICE_PRICE]]" readonly="readonly" onclick="this.select()"> - <?php _e('price of service', 'bookly') ?></td></tr>
                        <tr><td><input value="[[STAFF_NAME]]" readonly="readonly" onclick="this.select()"> - <?php _e('name of staff', 'bookly') ?></td></tr>
                    </table>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?php AB_Utils::submitButton() ?>
                <?php AB_Utils::resetButton() ?>
            </td>
        </tr>
    </table>
</form>