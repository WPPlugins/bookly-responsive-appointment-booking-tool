<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $custom_fields = (array) \BooklyLite\Lib\Proxy\CustomFields::getWhichHaveData();
?>
<div id="bookly-tinymce-appointment-popup" style="display: none">
    <form id="bookly-shortcode-form">
        <table>
            <tr>
                <th class="bookly-title-col"><?php _e( 'Titles', 'bookly' ) ?></th>
                <td>
                    <label><input type="checkbox" id="bookly-show-column-titles" /><?php _e( 'Yes', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <th class="bookly-title-col"><?php _e( 'Columns', 'bookly' ) ?></th>
                <td>
                    <label><input type="checkbox" data-column="category" /><?php _e( 'Category', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="service" /><?php _e( 'Service', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="staff" /><?php _e( 'Staff', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="date" /><?php _e( 'Date', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="time" /><?php _e( 'Time', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="price" /><?php _e( 'Price', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="status" /><?php _e( 'Status', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="cancel" /><?php _e( 'Cancel', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <th colspan="2"><?php _e( 'Custom Fields', 'bookly' ) ?></th>
            </tr>
            <?php foreach ( $custom_fields as $field ) : ?>
                <tr>
                    <td class="bookly-cf-col"><?php echo $field->label ?></td>
                    <td>
                        <label><input type="checkbox" data-custom_field="<?php echo $field->id ?>" /><?php _e( 'Yes', 'bookly' ) ?></label>
                    </td>
                </tr>
            <?php endforeach ?>
            <tr>
                <td></td>
                <td>
                    <input class="button button-primary" id="bookly-insert-ap-shortcode" type="submit" value="<?php esc_attr_e( 'Insert', 'bookly' ) ?>" />
                </td>
            </tr>
        </table>
    </form>
</div>

<style type="text/css">
    #bookly-shortcode-form table td { padding: 5px; vertical-align: 0; }
    #bookly-shortcode-form table th.bookly-title-col { width: 80px; }
</style>

<script type="text/javascript">
    jQuery(function ($) {
        var $add_button_appointment = $('#add-ap-appointment');
        var $insert                 = $('#bookly-insert-ap-shortcode');

        $add_button_appointment.on('click', function () {
            window.parent.tb_show(<?php echo json_encode( __( 'Add Bookly appointments list', 'bookly' ) ) ?>, this.href);
            window.setTimeout(function(){
                $('#TB_window').css({
                    'overflow-x': 'auto',
                    'overflow-y': 'hidden'
                });
            },100);
        });

        $insert.on('click', function (e) {
            e.preventDefault();

            var shortcode = '[bookly-appointments-list',
                column;

            // columns
            var columns = $('[data-column]:checked');
            if (columns.length) {
                column = [];
                $.each(columns, function() {
                    column.push($(this).data('column'));
                });
                shortcode += ' columns="' + column.join(',') + '"';
            }
            // custom_fields
            var custom_fields = $('[data-custom_field]:checked');
            if (custom_fields.length) {
                column = [];
                $.each(custom_fields, function() {
                    column.push($(this).data('custom_field'));
                });
                shortcode += ' custom_fields="' + column.join(',') + '"';
            }


            if ($('#bookly-show-column-titles:checked').length) {
                shortcode += ' show_column_titles="1"';
            }

            window.send_to_editor(shortcode + ']');
            window.parent.tb_remove();
            return false;
        });
    });
</script>