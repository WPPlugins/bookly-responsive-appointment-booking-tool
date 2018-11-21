<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Modules\Payments\Proxy;
use Bookly\Lib\Utils\Price;
use Bookly\Lib\Utils\DateTime;
use Bookly\Lib\Entities;
use Bookly\Lib\Config;
/** @var array $show = ['deposit' => int, 'taxes' => int, 'gateway' => bool, 'customer_groups' => bool, 'coupons' => bool] */
?>
<?php if ( $payment ) : ?>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th width="50%"><?php _e( 'Customer', 'bookly' ) ?></th>
                    <th width="50%"><?php _e( 'Payment', 'bookly' ) ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo esc_html( $payment['customer'] ) ?></td>
                    <td>
                        <div><?php _e( 'Date', 'bookly' ) ?>: <?php echo DateTime::formatDateTime( $payment['created'] ) ?></div>
                        <div><?php _e( 'Type', 'bookly' ) ?>: <?php echo Entities\Payment::typeToString( $payment['type'] ) ?></div>
                        <div><?php _e( 'Status', 'bookly' ) ?>: <?php echo Entities\Payment::statusToString( $payment['status'] ) ?></div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th><?php _e( 'Service', 'bookly' ) ?></th>
                    <th><?php _e( 'Date', 'bookly' ) ?></th>
                    <th><?php _e( 'Provider', 'bookly' ) ?></th>
                    <?php if ( $show['deposit'] ): ?>
                        <th class="text-right"><?php _e( 'Deposit', 'bookly' ) ?></th>
                    <?php endif ?>
                    <th class="text-right"><?php _e( 'Price', 'bookly' ) ?></th>
                    <?php if ( $show['taxes'] ): ?>
                        <th class="text-right"><?php _e( 'Tax', 'bookly' ) ?></th>
                    <?php endif ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $payment['items'] as $item ) : ?>
                    <tr>
                        <td>
                            <?php if ( $item['number_of_persons'] > 1 ) echo $item['number_of_persons'] . '&nbsp;&times;&nbsp;' ?><?php echo esc_html( $item['service_name'] ) ?><?php if ( isset( $item['units'], $item['duration'] ) && $item['units'] > 1 ) echo '&nbsp;(' . DateTime::secondsToInterval( $item['units'] * $item['duration'] ) . ')' ?>
                            <?php if ( ! empty ( $item['extras'] ) ) : ?>
                                <ul class="bookly-list list-dots">
                                    <?php foreach ( $item['extras'] as $extra ) : ?>
                                        <li><?php if ( $payment['extras_multiply_nop'] && $item['number_of_persons'] > 1 ) echo $item['number_of_persons'] . '&nbsp;&times;&nbsp;' ?><?php if ( $extra['quantity'] > 1 ) echo $extra['quantity'] . '&nbsp;&times;&nbsp;' ?><?php echo esc_html( $extra['title'] ) ?></li>
                                    <?php endforeach ?>
                                </ul>
                            <?php endif ?>
                        </td>
                        <td><?php echo $item['appointment_date'] === null ? __( 'N/A', 'bookly' ) : DateTime::formatDateTime( $item['appointment_date'] ) ?></td>
                        <td><?php echo esc_html( $item['staff_name'] ) ?></td>
                        <?php if ( $show['deposit'] ) : ?>
                            <td class="text-right"><?php echo $item['deposit_format'] ?></td>
                        <?php endif ?>
                        <td class="text-right">
                            <?php $service_price = Price::format( $item['service_price'] ) ?>
                            <?php if ( $payment['from_backend'] ) : ?>
                                <?php echo $service_price ?>
                            <?php else : ?>
                                <?php if ( $item['number_of_persons'] > 1 ) $service_price = $item['number_of_persons'] . '&nbsp;&times;&nbsp' . $service_price ?>
                                <?php echo $service_price ?>
                                <ul class="bookly-list">
                                <?php foreach ( $item['extras'] as $extra ) : ?>
                                    <li>
                                        <?php printf( '%s%s%s',
                                            ( $item['number_of_persons'] > 1 && $payment['extras_multiply_nop'] ) ? $item['number_of_persons'] . '&nbsp;&times;&nbsp;' : '',
                                            ( $extra['quantity'] > 1 ) ? $extra['quantity'] . '&nbsp;&times;&nbsp;' : '',
                                            Price::format( $extra['price'] )
                                        ) ?>
                                    </li>
                                <?php endforeach ?>
                                </ul>
                            <?php endif ?>
                        </td>
                        <?php if ( $show['taxes'] ) : ?>
                            <td class="text-right"><?php echo $item['service_tax'] !== null
                                    ? sprintf( $payment['tax_in_price'] == 'included' ? '(%s)' : '%s', Price::format( $item['service_tax'] ) )
                                    : '-' ?></td>
                        <?php endif ?>
                    </tr>
                <?php endforeach ?>
            </tbody>
            <tfoot>
                <tr>
                    <th style="border-left-color: white; border-bottom-color: white;"></th>
                    <th colspan="2"><?php _e( 'Subtotal', 'bookly' ) ?></th>
                    <?php if ( $show['deposit'] ) : ?>
                        <th class="text-right"><?php echo Price::format( $payment['subtotal']['deposit'] ) ?></th>
                    <?php endif ?>
                    <th class="text-right"><?php echo Price::format( $payment['subtotal']['price'] ) ?></th>
                    <?php if ( $show['taxes'] ) : ?><th></th><?php endif ?>
                </tr>
                <?php if ( $show['coupons'] || $payment['coupon'] ) : ?>
                    <tr>
                        <th style="border-left-color: white; border-bottom-color: white;"></th>
                        <th colspan="<?php echo 2 + $show['deposit'] ?>">
                            <?php _e( 'Coupon discount', 'bookly' ) ?>
                            <?php if ( $payment['coupon'] ) : ?><div><small>(<?php echo $payment['coupon']['code'] ?>)</small></div><?php endif ?>
                        </th>
                        <th class="text-right">
                            <?php if ( $payment['coupon'] ) : ?>
                                <?php if ( $payment['coupon']['discount'] ) : ?>
                                    <div><?php echo $payment['coupon']['discount'] ?>%</div>
                                <?php endif ?>
                                <?php if ( $payment['coupon']['deduction'] ) : ?>
                                    <div><?php echo Price::format( $payment['coupon']['deduction'] ) ?></div>
                                <?php endif ?>
                            <?php else : ?>
                                <?php echo Price::format( 0 ) ?>
                            <?php endif ?>
                        </th>
                        <?php if ( $show['taxes'] ) : ?>
                            <th></th>
                        <?php endif ?>
                    </tr>
                <?php endif ?>
                <?php if ( $show['customer_groups'] || $payment['group_discount'] ) : ?>
                    <tr>
                        <th style="border-left-color:#fff;border-bottom-color:#fff;"></th>
                        <th colspan="<?php echo 2 + $show['deposit'] ?>">
                            <?php _e( 'Group discount', 'bookly' ) ?>
                        </th>
                        <th class="text-right">
                            <?php echo $payment['group_discount'] ?: Price::format( 0 ) ?>
                        </th>
                        <?php if ( $show['taxes'] ) : ?><th></th><?php endif ?>
                    </tr>
                <?php endif ?>
                <?php foreach ( $adjustments as $adjustment ) : ?>
                    <tr>
                        <th style="border-left-color:#fff;border-bottom-color:#fff;"></th>
                        <th colspan="<?php echo 2 + $show['deposit'] ?>">
                            <?php echo esc_html( $adjustment['reason'] ) ?>
                        </th>
                        <th class="text-right"><?php echo Price::format( $adjustment['amount'] ) ?></th>
                        <?php if ( $show['taxes'] ) : ?>
                            <th class="text-right"><?php echo Price::format( $adjustment['tax'] ) ?></th>
                        <?php endif ?>
                    </tr>
                <?php endforeach ?>

                <?php Proxy\Pro::renderManualAdjustmentForm( $show ) ?>

                <?php if ( $show['gateway'] || (float) $payment['price_correction'] ) : ?>
                    <tr>
                        <th style="border-left-color:#fff;border-bottom-color:#fff;"></th>
                        <th colspan="<?php echo 2 + $show['deposit'] ?>">
                            <?php echo Entities\Payment::typeToString( $payment['type'] ) ?>
                        </th>
                        <th class="text-right">
                            <?php echo Price::format( $payment['price_correction'] ) ?>
                        </th>
                        <?php if ( $show['taxes'] ) : ?>
                            <td class="text-right">-</td>
                        <?php endif ?>
                    </tr>
                <?php endif ?>
                <tr>
                    <th style="border-left-color:#fff;border-bottom-color:#fff;"></th>
                    <th colspan="<?php echo 2 + $show['deposit'] ?>"><?php _e( 'Total', 'bookly' ) ?></th>
                    <th class="text-right"><?php echo Price::format( $payment['total'] ) ?></th>
                    <?php if ( $show['taxes'] ) : ?>
                        <th class="text-right">
                            (<?php echo Price::format( $payment['tax_total'] ) ?>)
                        </th>
                    <?php endif ?>
                </tr>
                <?php if ( $payment['total'] != $payment['paid'] ) : ?>
                    <tr>
                        <th rowspan="2" style="border-left-color:#fff;border-bottom-color:#fff;"></th>
                        <th colspan="<?php echo 2 + $show['deposit'] ?>"><i><?php _e( 'Paid', 'bookly' ) ?></i></th>
                        <th class="text-right"><i><?php echo Price::format( $payment['paid'] ) ?></i></th>
                        <?php if ( $show['taxes'] ) : ?>
                            <th class="text-right"><i>(<?php echo Price::format( $payment['tax_paid'] ) ?>)</i></th>
                        <?php endif ?>
                    </tr>
                    <tr>
                        <th colspan="<?php echo 2 + $show['deposit'] ?>"><i><?php _e( 'Due', 'bookly' ) ?></i></th>
                        <th class="text-right">
                            <i><?php echo Price::format( $payment['total'] - $payment['paid'] ) ?></i>
                        </th>
                        <?php if ( $show['taxes'] ) : ?>
                            <th class="text-right"><i>(<?php echo Price::format( $payment['tax_total'] - $payment['tax_paid'] ) ?>)</i></th>
                        <?php endif ?>
                    </tr>
                <?php endif ?>
                <?php if ( Config::proActive() || ( $payment['total'] != $payment['paid'] ) ) : ?>
                    <tr>
                        <th style="border-left-color:#fff;border-bottom-color:#fff;"></th>
                        <th colspan="<?php echo 3 + $show['deposit'] + $show['taxes'] ?>" class="text-right">
                            <div class="bookly-js-details-main-controls">
                                <?php Proxy\Pro::renderManualAdjustmentButton() ?>
                                <?php if ( $payment['total'] != $payment['paid'] ) : ?>
                                <button type="button" class="btn btn-success ladda-button" id="bookly-complete-payment" data-spinner-size="40" data-style="zoom-in"><i><?php _e( 'Complete payment', 'bookly' ) ?></i></button>
                                <?php endif ?>
                            </div>
                            <div class="bookly-js-details-bind-controls collapse">
                                <?php Buttons::renderCustom( 'bookly-js-attach-payment', 'btn-success', __( 'Bind payment', 'bookly' ) ) ?>
                            </div>
                        </th>
                    </tr>
                <?php endif ?>
            </tfoot>
        </table>
    </div>
<?php endif ?>