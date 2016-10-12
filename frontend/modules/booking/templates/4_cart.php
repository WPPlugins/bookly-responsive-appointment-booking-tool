<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    echo $progress_tracker;
?>
<div class="ab-row"><?php echo $info_text ?></div>
<div class="ab-row">
    <button class="ab-add-item ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
        <span class="ladda-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_book_more' ) ?></span>
    </button>
    <div class="ab--holder ab-label-error ab-bold"></div>
</div>
<div class="ab-cart-step">
    <div class="ab-cart ab-row">
        <table>
            <thead class="ab-desktop-version">
                <tr>
                    <?php foreach ( $columns as $position => $column ) : ?>
                        <th <?php if ( $position == $price_position ) echo 'class="ab-rtext"' ?>><?php echo $column ?></th>
                    <?php endforeach ?>
                    <th></th>
                </tr>
            </thead>
            <tbody class="ab-desktop-version">
            <?php foreach ( $cart_items as $key => $item ) : ?>
                <tr data-cart-key="<?php echo $key ?>">
                    <?php foreach ( $item as $position => $value ) : ?>
                    <td <?php if ( $position == $price_position ) echo 'class="ab-rtext"' ?>><?php echo $value ?></td>
                    <?php endforeach ?>
                    <td class="ab-rtext ab-nowrap bookly-js-actions">
                        <button class="bookly-round-button ladda-button" data-action="edit" data-style="zoom-in" data-spinner-size="30" title="<?php esc_attr_e( 'Edit', 'bookly' ) ?>"><span class="ladda-label"><img src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/edit.png' ) ?>" /></span></button>
                        <button class="bookly-round-button ladda-button" data-action="drop" data-style="zoom-in" data-spinner-size="30" title="<?php esc_attr_e( 'Remove', 'bookly' ) ?>"><span class="ladda-label"><img src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/delete.png' ) ?>" /></span></button>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
            <tbody class="ab-mobile-version">
            <?php foreach ( $cart_items as $key => $item ) : ?>
                <?php foreach ( $item as $position => $value ) : ?>
                    <tr data-cart-key="<?php echo $key ?>">
                        <th><?php echo $columns[ $position ] ?></th>
                        <td><?php echo $value ?></td>
                    </tr>
                <?php endforeach ?>
                <tr data-cart-key="<?php echo $key ?>">
                    <th></th>
                    <td class="bookly-js-actions">
                        <button class="bookly-round-button ladda-button" data-action="edit" data-style="zoom-in" data-spinner-size="30" title="<?php esc_attr_e( 'Edit', 'bookly' ) ?>"><span class="ladda-label"><img src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/edit.png' ) ?>" /></span></button>
                        <button class="bookly-round-button ladda-button" data-action="drop" data-style="zoom-in" data-spinner-size="30" title="<?php esc_attr_e( 'Remove', 'bookly' ) ?>"><span class="ladda-label"><img src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/delete.png' ) ?>" /></span></button>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
            <?php if ( $price_position != -1 ) : ?>
                <tfoot class="ab-mobile-version">
                <tr>
                    <th><?php _e( 'Total', 'bookly' ) ?>:</th>
                    <td><strong class="bookly-js-total-price"><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( $total ) ?></strong></td>
                </tr>
                <?php if ( $deposit_enabled ) : ?>
                    <tr>
                        <th><?php _e( 'Deposit', 'bookly' ) ?>:</th>
                        <td><strong class="bookly-js-total-deposit-price"><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( $deposit_total ) ?></strong></td>
                    </tr>
                <?php endif ?>
                </tfoot>
                <tfoot class="ab-desktop-version">
                <tr>
                    <td colspan="<?php echo $price_position > 0 ? $price_position : 2 ?>">
                        <strong><?php _e( 'Total', 'bookly' ) ?>:</strong>
                    <?php if ( $price_position > 0 ) : ?>
                    </td>
                    <td class="ab-rtext">
                    <?php endif ?>
                        <strong class="bookly-js-total-price"><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( $total ) ?></strong>
                    </td>
                    <td>
                        <?php if ( $deposit_enabled ) : ?>
                            <strong class="bookly-js-total-deposit-price"><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( $deposit_total ) ?></strong>
                        <?php endif ?>
                    </td>
                </tr>
                </tfoot>
            <?php endif ?>
        </table>
    </div>
</div>
<div class="ab-row ab-nav-steps">
    <button class="ab-left ab-back-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
        <span class="ladda-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_back' ) ?></span>
    </button>
    <button class="ab-right ab-next-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
        <span class="ladda-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_next' ) ?></span>
    </button>
</div>