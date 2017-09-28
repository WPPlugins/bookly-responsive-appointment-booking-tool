<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php foreach ( $appointments as $app ) : ?>
    <?php if ( ! isset( $compound_token[ $app['compound_token'] ] ) ) :
        if ( $app['compound_token'] !== null ) {
            $compound_token[ $app['compound_token'] ] = true;
        }
        $extras_total_price = 0;
        foreach ( $app['extras'] as $extra ) {
            $extras_total_price += $extra['price'];
        }
    ?>
    <tr>
        <?php foreach ( $columns as $column ) :
            switch ( $column ) :
                case 'service' : ?>
                    <td>
                        <?php echo $app['service'] ?>
                        <?php if ( ! empty ( $app['extras'] ) ): ?>
                            <ul class="bookly-extras">
                                <?php foreach ( $app['extras'] as $extra ) : ?>
                                    <li><?php echo $extra['title'] ?></li>
                                <?php endforeach ?>
                            </ul>
                        <?php endif ?>
                    </td><?php
                    break;
                case 'date' : ?>
                    <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatDate( $app['start_date'] ) ?></td><?php
                    break;
                case 'time' : ?>
                    <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $app['start_date'] ) ?></td><?php
                    break;
                case 'price' : ?>
                    <td style="text-align:right!important;"><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( ( $app['price'] + $extras_total_price ) * $app['number_of_persons'] ) ?></td><?php
                    break;
                case 'status' : ?>
                    <td><?php echo \BooklyLite\Lib\Entities\CustomerAppointment::statusToString( $app['appointment_status'] ) ?></td><?php
                    break;
                case 'cancel' :
                    $this->render( '_custom_fields', compact( 'custom_fields', 'app' ) ); ?>
                    <td>
                    <?php if ( $app['start_date'] > current_time( 'mysql' ) ) : ?>
                        <?php if ( $allow_cancel < strtotime( $app['start_date'] ) ) : ?>
                            <?php if (
                                ( $app['appointment_status'] != \BooklyLite\Lib\Entities\CustomerAppointment::STATUS_CANCELLED )
                                && ( $app['appointment_status'] != \BooklyLite\Lib\Entities\CustomerAppointment::STATUS_REJECTED )
                            ) : ?>
                                <a class="ab-btn" style="background-color: <?php echo $color ?>" href="<?php echo esc_attr( $url_cancel . '&token=' . $app['token'] ) ?>">
                                    <span><?php _e( 'Cancel', 'bookly' ) ?></span>
                                </a>
                            <?php endif ?>
                        <?php else : ?>
                            <?php _e( 'Not allowed', 'bookly' ) ?>
                        <?php endif ?>
                    <?php else : ?>
                        <?php _e( 'Expired', 'bookly' ) ?>
                    <?php endif ?>
                    </td><?php
                    break;
                default : ?>
                    <td><?php echo $app[ $column ] ?></td>
            <?php endswitch ?>
        <?php endforeach ?>
    <?php endif ?>
    <?php if ( $with_cancel == false ) :
        $this->render( '_custom_fields', compact( 'custom_fields', 'app' ) );
    endif ?>
    </tr>
<?php endforeach ?>