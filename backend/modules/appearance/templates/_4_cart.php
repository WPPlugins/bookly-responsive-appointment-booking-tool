<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>

    <div class="ab-row">
        <span data-inputclass="input-xxlarge" data-notes="<?php echo esc_attr( $this->render( '_codes', array( 'step' => 4 ), false ) ) ?>" data-placement="bottom" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_info_cart_step' ) ) ?>" class="ab_editable" id="ab-text-info-cart" data-type="textarea"><?php echo esc_html( get_option( 'ab_appearance_text_info_cart_step' ) ) ?></span>
    </div>

    <div class="ab-row">
        <div class="ab-btn ab-add-item ab-inline-block">
            <span class="ab_editable" id="ab-text-button-book-more" data-type="text" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_button_book_more' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_button_book_more' ) ) ?></span>
        </div>
    </div>

    <div class="ab-cart-step">
        <div class="ab-cart ab-row">
            <table>
                <thead class="ab-desktop-version">
                    <tr>
                        <th data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_service' ) ) ?>" class="ab-service-list"><?php echo esc_html( get_option( 'ab_appearance_text_label_service' ) ) ?></th>
                        <th><?php _e( 'Date', 'bookly' ) ?></th>
                        <th><?php _e( 'Time', 'bookly' ) ?></th>
                        <th data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_employee' ) ) ?>" class="ab-employee-list"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_employee' ) ?></th>
                        <th><?php _e( 'Price', 'bookly' ) ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="ab-desktop-version">
                    <tr>
                        <td>Crown and Bridge</td>
                        <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatDate( strtotime( '+2 days' ) ) ?></td>
                        <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( 28800 ) ?></td>
                        <td>Nick Knight</td>
                        <td><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 350 ) ?></td>
                        <td>
                            <button class="bookly-round-button ladda-button" title="<?php esc_attr_e( 'Edit', 'bookly' ) ?>"><img src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/edit.png' ) ?>" /></button>
                            <button class="bookly-round-button ladda-button" title="<?php esc_attr_e( 'Remove', 'bookly' ) ?>"><img src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/delete.png' ) ?>" /></button>
                        </td>
                    </tr>
                    <tr>
                        <td>Teeth Whitening</td>
                        <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatDate( strtotime( '+3 days' ) ) ?></td>
                        <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( 57600 ) ?></td>
                        <td>Wayne Turner</td>
                        <td><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 400 ) ?></td>
                        <td>
                            <button class="bookly-round-button ladda-button" title="<?php esc_attr_e( 'Edit', 'bookly' ) ?>"><img src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/edit.png' ) ?>" /></button>
                            <button class="bookly-round-button ladda-button" title="<?php esc_attr_e( 'Remove', 'bookly' ) ?>"><img src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/delete.png' ) ?>" /></button>
                        </td>
                    </tr>
                </tbody>
                <tbody class="ab-mobile-version">
                    <tr>
                        <th data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_service' ) ) ?>" class="ab-service-list"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_service' ) ?></th>
                        <td>Crown and Bridge</td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Date', 'bookly' ) ?></th>
                        <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatDate( strtotime( '+2 days' ) ) ?></td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Time', 'bookly' ) ?></th>
                        <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( 28800 ) ?></td>
                    </tr>
                    <tr>
                        <th data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_employee' ) ) ?>" class="ab-employee-list"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_employee' ) ?></th>
                        <td>Nick Knight</td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Price', 'bookly' ) ?></th>
                        <td><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 350 ) ?></td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>
                            <button class="bookly-round-button ladda-button" title="<?php esc_attr_e( 'Edit', 'bookly' ) ?>"><img src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/edit.png' ) ?>" /></button>
                            <button class="bookly-round-button ladda-button" title="<?php esc_attr_e( 'Remove', 'bookly' ) ?>"><img src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/delete.png' ) ?>" /></button>
                        </td>
                    </tr>
                    <tr>
                        <th data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_service' ) ) ?>" class="ab-service-list"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_service' ) ?></th>
                        <td>Teeth Whitening</td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Date', 'bookly' ) ?></th>
                        <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatDate( strtotime( '+3 days' ) ) ?></td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Time', 'bookly' ) ?></th>
                        <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( 57600 ) ?></td>
                    </tr>
                    <tr>
                        <th data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_employee' ) ) ?>" class="ab-employee-list"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_employee' ) ?></th>
                        <td>Wayne Turner</td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Price', 'bookly' ) ?></th>
                        <td><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 400 ) ?></td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>
                            <button class="bookly-round-button ladda-button" title="<?php esc_attr_e( 'Edit', 'bookly' ) ?>"><img src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/edit.png' ) ?>" /></button>
                            <button class="bookly-round-button ladda-button" title="<?php esc_attr_e( 'Remove', 'bookly' ) ?>"><img src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/delete.png' ) ?>" /></button>
                        </td>
                    </tr>
                </tbody>
                <tfoot class="ab-desktop-version">
                    <tr>
                        <td colspan="4"><strong><?php _e( 'Total', 'bookly' ) ?>:</strong></td>
                        <td><strong class="bookly-js-total-price"><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 750 ) ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
                <tfoot class="ab-mobile-version">
                    <tr>
                        <th><strong><?php _e( 'Total', 'bookly' ) ?>:</strong></th>
                        <td><strong class="bookly-js-total-price"><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 750 ) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="ab-row ab-nav-steps">
        <div class="ab-left ab-back-step ab-btn">
            <span class="text_back ab_editable" id="ab-text-button-back" data-mirror="text_back" data-type="text" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_button_back' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_button_back' ) ) ?></span>
        </div>
        <div class="ab-right ab-next-step ab-btn">
            <span class="text_next ab_editable" id="ab-text-button-next" data-mirror="text_next" data-type="text" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_button_next' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_button_next' ) ) ?></span>
        </div>
    </div>
</div>
