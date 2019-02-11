<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Settings\Selects;
?>
<div class="panel panel-default bookly-collapse" data-slug="local">
    <div class="panel-heading">
        <i class="bookly-js-handle bookly-margin-right-sm bookly-icon bookly-icon-draghandle bookly-cursor-move ui-sortable-handle" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
        <a href="#bookly_pmt_local" class="panel-title" role="button" data-toggle="collapse">
            <?php _e( 'Service paid locally', 'bookly' ) ?>
        </a>
    </div>
    <div id="bookly_pmt_local" class="panel-collapse collapse in">
        <div class="panel-body">
            <?php Selects::renderSingle( 'bookly_pmt_local' ) ?>
        </div>
    </div>
</div>
