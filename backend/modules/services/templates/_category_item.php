<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<li class="bookly-nav-item bookly-category-item" data-category-id="<?php echo $category['id'] ?>">
    <div class="bookly-flexbox">
        <div class="bookly-flex-cell bookly-vertical-middle" style="width: 1%">
            <i class="bookly-js-handle bookly-icon bookly-icon-draghandle bookly-margin-right-sm bookly-cursor-move" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
        </div>
        <div class="bookly-flex-cell bookly-vertical-middle">
            <span class="displayed-value"><?php echo esc_html( $category['name'] ) ?></span>
            <input class="form-control input-lg" type="text" name="name" value="<?php echo esc_attr( $category['name'] ) ?>" style="display: none"/>
        </div>
        <div class="bookly-flex-cell bookly-vertical-middle" style="width: 1%">
            <a href="#" class="glyphicon glyphicon-edit bookly-margin-horizontal-xs bookly-js-edit" title="<?php esc_attr_e( 'Edit', 'bookly' ) ?>"></a>
        </div>
        <div class="bookly-flex-cell bookly-vertical-middle" style="width: 1%">
            <a href="#" class="glyphicon glyphicon-trash text-danger bookly-js-delete" title="<?php esc_attr_e( 'Delete', 'bookly' ) ?>"></a>
        </div>
    </div>
</li>
