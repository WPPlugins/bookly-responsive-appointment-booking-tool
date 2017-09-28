<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>

    <div class="bookly-box">
        <span data-notes="<?php echo esc_attr( $this->render( '_codes', array( 'step' => 5 ), false ) ) ?>" data-placement="bottom" data-default="<?php form_option( 'bookly_l10n_info_cart_step' ) ?>" class="bookly-editable" id="bookly_l10n_info_cart_step" data-type="textarea"><?php echo esc_html( get_option( 'bookly_l10n_info_cart_step' ) ) ?></span>
    </div>

    <div class="bookly-box">
        <div class="ab-btn ab-add-item ab-inline-block">
            <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_button_book_more', ) ) ?>
        </div>
    </div>

    <div class="ab-cart-step">
        <div class="ab-cart bookly-box">
            <table>
                <thead class="ab-desktop-version">
                    <tr>
                        <th data-default="<?php form_option( 'bookly_l10n_label_service' ) ?>" class="ab-service-list"><?php echo esc_html( get_option( 'bookly_l10n_label_service' ) ) ?></th>
                        <th><?php _e( 'Date', 'bookly' ) ?></th>
                        <th><?php _e( 'Time', 'bookly' ) ?></th>
                        <th data-default="<?php form_option( 'bookly_l10n_label_employee' ) ?>" class="ab-employee-list"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_employee' ) ?></th>
                        <th><?php _e( 'Price', 'bookly' ) ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="ab-desktop-version">
                    <tr>
                        <td>Crown and Bridge</td>
                        <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatDate( '+2 days' ) ?></td>
                        <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( 28800 ) ?></td>
                        <td>Nick Knight</td>
                        <td><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 350 ) ?></td>
                        <td>
                            <button class="bookly-round" title="<?php esc_attr_e( 'Edit', 'bookly' ) ?>"><i class="bookly-icon-sm bookly-icon-edit"></i></button>
                            <button class="bookly-round" title="<?php esc_attr_e( 'Remove', 'bookly' ) ?>"><i class="bookly-icon-sm bookly-icon-drop"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>Teeth Whitening</td>
                        <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatDate( '+3 days' ) ?></td>
                        <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( 57600 ) ?></td>
                        <td>Wayne Turner</td>
                        <td><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 400 ) ?></td>
                        <td>
                            <button class="bookly-round" title="<?php esc_attr_e( 'Edit', 'bookly' ) ?>"><i class="bookly-icon-sm bookly-icon-edit"></i></button>
                            <button class="bookly-round" title="<?php esc_attr_e( 'Remove', 'bookly' ) ?>"><i class="bookly-icon-sm bookly-icon-drop"></i></button>
                        </td>
                    </tr>
                </tbody>
                <tbody class="ab-mobile-version">
                    <tr>
                        <th data-default="<?php form_option( 'bookly_l10n_label_service' ) ?>" class="ab-service-list"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_service' ) ?></th>
                        <td>Crown and Bridge</td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Date', 'bookly' ) ?></th>
                        <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatDate( '+2 days' ) ?></td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Time', 'bookly' ) ?></th>
                        <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( 28800 ) ?></td>
                    </tr>
                    <tr>
                        <th data-default="<?php form_option( 'bookly_l10n_label_employee' ) ?>" class="ab-employee-list"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_employee' ) ?></th>
                        <td>Nick Knight</td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Price', 'bookly' ) ?></th>
                        <td><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 350 ) ?></td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>
                            <button class="bookly-round" title="<?php esc_attr_e( 'Edit', 'bookly' ) ?>"><i class="bookly-icon-sm bookly-icon-edit"></i></button>
                            <button class="bookly-round" title="<?php esc_attr_e( 'Remove', 'bookly' ) ?>"><i class="bookly-icon-sm bookly-icon-drop"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <th data-default="<?php form_option( 'bookly_l10n_label_service' ) ?>" class="ab-service-list"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_service' ) ?></th>
                        <td>Teeth Whitening</td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Date', 'bookly' ) ?></th>
                        <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatDate( '+3 days' ) ?></td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Time', 'bookly' ) ?></th>
                        <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( 57600 ) ?></td>
                    </tr>
                    <tr>
                        <th data-default="<?php form_option( 'bookly_l10n_label_employee' ) ?>" class="ab-employee-list"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_employee' ) ?></th>
                        <td>Wayne Turner</td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Price', 'bookly' ) ?></th>
                        <td><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 400 ) ?></td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>
                            <button class="bookly-round" title="<?php esc_attr_e( 'Edit', 'bookly' ) ?>"><i class="bookly-icon-sm bookly-icon-edit"></i></button>
                            <button class="bookly-round" title="<?php esc_attr_e( 'Remove', 'bookly' ) ?>"><i class="bookly-icon-sm bookly-icon-drop"></i></button>
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

    <?php do_action( 'bookly_recurring_appointments_render_editable_message_info' ) ?>

    <div class="bookly-box bookly-nav-steps">
        <div class="bookly-back-step ab-btn">
            <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_button_back' ), 'bookly-js-text-back' ) ?>
        </div>
        <div class="bookly-next-step ab-btn">
            <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_button_next' ), 'bookly-js-text-next' ) ?>
        </div>
    </div>
</div>
