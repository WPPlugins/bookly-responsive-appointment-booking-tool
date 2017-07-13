<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $subtotal = 0;
    $subtotal_deposit = 0;
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
                    <td><?php echo $payment['customer'] ?></td>
                    <td>
                        <div><?php _e( 'Date', 'bookly' ) ?>: <?php echo \BooklyLite\Lib\Utils\DateTime::formatDateTime( $payment['created'] ) ?></div>
                        <div><?php _e( 'Type', 'bookly' ) ?>: <?php echo \BooklyLite\Lib\Entities\Payment::typeToString( $payment['type'] ) ?></div>
                        <div><?php _e( 'Status', 'bookly' ) ?>: <?php echo \BooklyLite\Lib\Entities\Payment::statusToString( $payment['status'] ) ?></div>
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
                    <?php if ( $deposit_enabled ): ?>
                        <th class="text-right"><?php _e( 'Deposit', 'bookly' ) ?></th>
                    <?php endif ?>
                    <th class="text-right"><?php _e( 'Price', 'bookly' ) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $items as $item ) :
                    $extras_price = 0; ?>
                    <tr>
                        <td>
                            <?php if ( $item['number_of_persons'] > 1 ) echo $item['number_of_persons'] . '&nbsp;&times;&nbsp;'  ?><?php echo $item['service_name'] ?>
                            <?php if ( ! empty ( $item['extras'] ) ) : ?>
                                <ul class="bookly-list list-dots">
                                    <?php foreach ( $item['extras'] as $extra ) : ?>
                                        <li><?php if ( $extra['quantity'] > 1 ) echo $extra['quantity'] . '&nbsp;&times;&nbsp;' ?><?php echo $extra['title'] ?></li>
                                        <?php $extras_price += $extra['price'] * $extra['quantity'] ?>
                                    <?php endforeach ?>
                                </ul>
                            <?php endif ?>
                        </td>
                        <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatDateTime( $item['appointment_date'] ) ?></td>
                        <td><?php echo $item['staff_name'] ?></td>
                        <?php $deposit = apply_filters( 'bookly_deposit_payments_get_deposit_amount', $item['number_of_persons'] * ( $item['service_price'] + $extras_price ), $item['deposit'], $item['number_of_persons'] ) ?>
                        <?php if ( $deposit_enabled ) : ?>
                            <td class="text-right"><?php echo apply_filters( 'bookly_deposit_payments_format_deposit', $deposit, $item['deposit'] ) ?></td>
                        <?php endif ?>
                        <td class="text-right">
                            <?php $service_price = \BooklyLite\Lib\Utils\Common::formatPrice( $item['service_price'] ) ?>
                            <?php if ( $item['number_of_persons'] > 1 ) $service_price = $item['number_of_persons'] . '&nbsp;&times;&nbsp' . $service_price ?>
                            <?php echo $service_price ?>
                            <ul class="bookly-list">
                            <?php foreach ( $item['extras'] as $extra ) : ?>
                                <li>
                                    <?php printf( '%s%s%s',
                                        ( $item['number_of_persons'] > 1 ) ? $item['number_of_persons'] . '&nbsp;&times;&nbsp;' : '',
                                        ( $extra['quantity'] > 1 ) ? $extra['quantity'] . '&nbsp;&times;&nbsp;' : '',
                                        \BooklyLite\Lib\Utils\Common::formatPrice( $extra['price'] )
                                    ) ?>
                                </li>
                                <?php $subtotal += $item['number_of_persons'] * $extra['price'] * $extra['quantity'] ?>
                            <?php endforeach ?>
                            </ul>
                        </td>
                    </tr>
                    <?php $subtotal += $item['number_of_persons'] * $item['service_price'] ?>
                    <?php $subtotal_deposit += $deposit ?>
                <?php endforeach ?>
            </tbody>
            <tfoot>
                <tr>
                    <th rowspan="3" style="border-left-color: white; border-bottom-color: white;"></th>
                    <th colspan="2"><?php _e( 'Subtotal', 'bookly' ) ?></th>
                    <?php if ( $deposit_enabled ) : ?>
                        <th class="text-right"><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( $subtotal_deposit ) ?></th>
                    <?php endif ?>
                    <th class="text-right"><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( $subtotal ) ?></th>
                </tr>
                <tr>
                    <th colspan="<?php echo 2 + (int) $deposit_enabled ?>">
                        <?php _e( 'Discount', 'bookly' ) ?>
                        <?php if ( $payment['coupon'] ) : ?><div><small>(<?php echo $payment['coupon']['code'] ?>)</small></div><?php endif ?>
                    </th>
                    <th class="text-right">
                        <?php if ( $payment['coupon'] ) : ?>
                            <?php if ( $payment['coupon']['discount'] ) : ?>
                                <div>-<?php echo $payment['coupon']['discount'] ?>%</div>
                            <?php endif ?>
                            <?php if ( $payment['coupon']['deduction'] ) : ?>
                                <div><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( - $payment['coupon']['deduction'] ) ?></div>
                            <?php endif ?>
                        <?php else : ?>
                            <?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 0 ) ?>
                        <?php endif ?>
                    </th>
                </tr>
                <tr>
                    <th colspan="<?php echo 2 + (int) $deposit_enabled ?>"><?php _e( 'Total', 'bookly' ) ?></th>
                    <th class="text-right"><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( $payment['total'] ) ?></th>
                </tr>
                <?php if ( $payment['total'] != $payment['paid'] ) : ?>
                    <tr>
                        <td rowspan="2" style="border-left-color:#fff;border-bottom-color:#fff;"></td>
                        <td colspan="<?php echo 2 + (int) $deposit_enabled ?>"><i><?php _e( 'Paid', 'bookly' ) ?></i></td>
                        <td class="text-right"><i><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( $payment['paid'] ) ?></i></td>
                    </tr>
                    <tr>
                        <td colspan="<?php echo 2 + (int) $deposit_enabled ?>"><i><?php _e( 'Due', 'bookly' ) ?></i></td>
                        <td class="text-right"><i><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( $payment['total'] - $payment['paid'] ) ?></i></td>
                    </tr>
                    <tr>
                        <td style="border-left-color:#fff;border-bottom-color:#fff;"></td>
                        <td colspan="<?php echo 3 + (int) $deposit_enabled ?>" class="text-right"><button type="button" class="btn btn-success ladda-button" id="bookly-complete-payment" data-spinner-size="40" data-style="zoom-in"><i><?php _e( 'Complete payment', 'bookly' ) ?></i></button></td>
                    </tr>
                <?php endif ?>
            </tfoot>
        </table>
    </div>
<?php endif ?>