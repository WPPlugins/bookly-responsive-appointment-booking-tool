<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php if ( count( $service_collection ) ) : ?>
    <table class="table table-striped" cellspacing="0" cellpadding="0" border="0" id="services_list">
        <thead>
            <tr>
                <th class="first">&nbsp;</th>
                <th><?php echo _e( 'Title', 'ab' ) ?></th>
                <th width='95'><?php echo _e( 'Duration', 'ab' ) ?></th>
                <th><?php echo _e( 'Price', 'ab' ) ?></th>
                <th width='65'><?php echo _e( 'Staff', 'ab' ) ?></th>
                <th><?php echo _e( 'Category', 'ab' ) ?></th>
                <th class="last">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ( $service_collection as $i => $service ) {
                    $row_class  = 'service-row ';
                    $row_class .= $i % 2 ? 'even' : 'odd';
                    if ( 0 == $i ) {
                        $row_class .= ' first';
                    }
                    if ( ! isset( $service_collection[$i + 1] ) ) {
                        $row_class .= ' last';
                    }
                    include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'list_item.php';
                }
            ?>
        </tbody>
    </table>
<?php endif ?>