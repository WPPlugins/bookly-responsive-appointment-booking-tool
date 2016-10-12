<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $color = get_option( 'ab_appearance_color', '#f4662f' );
    $compound_tokens = array();
    $custom_fields = isset( $attributes['custom_fields'] ) ? explode( ',', $attributes['custom_fields'] ) : array();
    $columns = isset( $attributes['columns'] ) ? explode( ',', $attributes['columns'] ) : array();
    $with_cancel = in_array( 'cancel', $columns );
?>
<?php if ( is_user_logged_in() ) : ?>
    <div class="bookly-customer-appointment-list">
        <h2><?php _e( 'Appointments', 'bookly' ) ?></h2>
        <?php if ( ! empty( $columns ) || ! empty( $custom_fields ) ) : ?>
            <table class="ab-appointments-table" data-columns="<?php echo esc_attr( json_encode( $columns ) ) ?>" data-custom_fields="<?php echo esc_attr( implode(',', $custom_fields ) ) ?>" data-page="0">
                <?php if ( isset( $attributes['show_column_titles'] ) && $attributes['show_column_titles'] ) : ?>
                    <thead>
                        <tr>
                            <?php foreach ( $columns as $column ) : ?>
                                <?php if ( $column != 'cancel' ) : ?>
                                    <th><?php echo $titles[ $column ] ?></th>
                                <?php endif ?>
                            <?php endforeach ?>
                            <?php foreach ( $custom_fields as $column ) : ?>
                                <th><?php if ( isset( $titles[ $column ] ) ) echo $titles[ $column ] ?></th>
                            <?php endforeach ?>
                            <?php if ( $with_cancel ) : ?>
                                <th><?php echo $titles['cancel'] ?></th>
                            <?php endif ?>
                        </tr>
                    </thead>
                <?php endif ?>
                <?php if ( empty( $appointments ) ) : ?>
                    <tr class="bookly--no-appointments"><td colspan="<?php echo count( $columns ) + count( $custom_fields ) ?>"><?php _e( 'No appointments found.', 'bookly' ) ?></td></tr>
                <?php else : ?>
                    <?php include '_row.php' ?>
                <?php endif ?>
            </table>
            <?php if( $more ) : ?>
                <button class="ab-btn ab--show-past ab-inline-block ab-right" style="background: <?php echo $color ?>!important; width: auto" data-spinner-size="40" data-style="zoom-in">
                    <span class="ab_label"><?php _e( 'Show past appointments', 'bookly' ) ?></span>
                </button>
            <?php endif ?>
        <?php endif ?>
    </div>

    <script type="text/javascript">
        (function (win, fn) {
            var done = false, top = true,
                doc = win.document,
                root = doc.documentElement,
                modern = doc.addEventListener,
                add = modern ? 'addEventListener' : 'attachEvent',
                rem = modern ? 'removeEventListener' : 'detachEvent',
                pre = modern ? '' : 'on',
                init = function(e) {
                    if (e.type == 'readystatechange') if (doc.readyState != 'complete') return;
                    (e.type == 'load' ? win : doc)[rem](pre + e.type, init, false);
                    if (!done) { done = true; fn.call(win, e.type || e); }
                },
                poll = function() {
                    try { root.doScroll('left'); } catch(e) { setTimeout(poll, 50); return; }
                    init('poll');
                };
            if (doc.readyState == 'complete') fn.call(win, 'lazy');
            else {
                if (!modern) if (root.doScroll) {
                    try { top = !win.frameElement; } catch(e) { }
                    if (top) poll();
                }
                doc[add](pre + 'DOMContentLoaded', init, false);
                doc[add](pre + 'readystatechange', init, false);
                win[add](pre + 'load', init, false);
            }
        })(window, function() {
            window.booklyCustomerProfile({
                ajaxurl : <?php echo json_encode( $ajax_url ) ?>
            });
        });
    </script>
<?php else : ?>
    <?php wp_login_form() ?>
<?php endif ?>