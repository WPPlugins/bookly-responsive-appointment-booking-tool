<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="bookly-form-group bookly-time-<?php echo $type ?> bookly-left">
    <span class="bookly-bold"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( $type == 'from' ? 'bookly_l10n_label_start_from' : 'bookly_l10n_label_finish_by' ) ?></span>
    <div>
        <select class="bookly-js-select-time-<?php echo $type ?>">
            <?php foreach ( $times as $key => $time ) : ?>
                <option value="<?php echo $key ?>"<?php selected( $selected == $key ) ?>><?php echo $time ?></option>
            <?php endforeach ?>
        </select>
    </div>
</div>