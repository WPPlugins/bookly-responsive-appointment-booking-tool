<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php foreach ( $appointments as $app ) : ?>
    <?php if ( ! isset( $compound_token[ $app['compound_token'] ] ) ) : ?>
        <?php $app['compound_token'] !== null && $compound_token[ $app['compound_token'] ] = true;
        $extras_total_price = 0;
        $app = apply_filters( 'bookly_appointment_data', $app, true );
        if ( is_array( $app['extras'] ) ) {
            foreach ( $app['extras'] as $extra ) {
                $extras_total_price += $extra['price'];
            }
        } ?>
        <tr>
        <?php foreach ( $columns as $column ) :
            switch ( $column ) :
                case 'service': ?>
                    <td style="text-align: left">
                    <table class="bookly-extras">
                        <tbody>
                        <tr>
                            <td colspan="3" style="text-align: left"><?php echo $app['service'] ?></td>
                        </tr>
                        <?php if ( is_array( $app['extras'] ) ) : ?>
                            <?php foreach ( $app['extras'] as $position => $extra ) : ?>
                                <tr>
                                    <td>&bull;</td>
                                    <td>
                                        <?php echo $extra['title'] ?>
                                    </td>
                                    <td style="text-align: right"> <?php echo \BooklyLite\Lib\Utils\Common::formatPrice( $extra['price'] ) ?></td>
                                </tr>
                            <?php endforeach ?>
                        <?php endif ?>
                        </tbody>
                    </table>
                    </td><?php
                    break;
                case 'date': ?>
                    <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatDate( $app['start_date'] ) ?></td><?php
                    break;
                case 'time': ?>
                    <td><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $app['start_date'] ) ?></td><?php
                    break;
                case 'price': ?>
                    <td><?php echo \BooklyLite\Lib\Utils\Common::formatPrice( ( $app['price'] + $extras_total_price ) * $app['number_of_persons'] ) ?></td><?php
                    break;
                case 'status': ?>
                    <td><?php echo \BooklyLite\Lib\Entities\CustomerAppointment::statusToString( $app['appointment_status'] ) ?></td><?php
                    break;
                case 'cancel':
                    $this->render( '_custom_fields', compact( 'custom_fields', 'app' ) ); ?>
                    <td>
                    <?php if ( $app['start_date'] > current_time( 'mysql' ) ) : ?>
                        <?php if( $allow_cancel < strtotime( $app['start_date'] ) ) : ?>
                            <?php if ( $app['appointment_status'] != \BooklyLite\Lib\Entities\CustomerAppointment::STATUS_CANCELLED ) : ?>
                                <a class="ab-btn" style="background-color: <?php echo $color ?>" href="<?php echo esc_attr( $url_cancel . '&token=' . $app['token'] ) ?>">
                                    <span class="ab_label"><?php _e( 'Cancel', 'bookly' ) ?></span>
                                </a>
                            <?php else : ?>✓<?php endif ?>
                        <?php else : ?>
                            <span class="ab_label"><?php _e( 'Not allowed', 'bookly' ) ?></span>
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