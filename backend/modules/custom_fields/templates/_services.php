<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="btn-group ab--services-holder bookly-margin-top-screenxs-sm" style="display: none;">
    <button class="btn btn-default btn-block dropdown-toggle bookly-flexbox" data-toggle="dropdown">
        <div class="bookly-flex-cell">
            <i class="glyphicon glyphicon-tag bookly-margin-right-md"></i>
        </div>
        <div class="bookly-flex-cell text-left" style="width: 100%">
            <span class="ab-count"></span>
        </div>
        <div class="bookly-flex-cell"><div class="bookly-margin-left-md"><span class="caret"></span></div></div>
    </button>
    <ul class="dropdown-menu bookly-entity-selector">
        <li>
            <a class="checkbox" href="javascript:void(0)">
                <label>
                    <input type="checkbox" class="bookly-check-all-entities">
                    <?php _e( 'All services', 'bookly' ) ?>
                </label>
            </a>
        </li>
        <?php foreach ( $services as $service ) : ?>
            <li>
                <a class="checkbox" href="javascript:void(0)">
                    <label>
                        <input type="checkbox" class="bookly-js-check-entity" value="<?php echo $service['id'] ?>" data-title="<?php echo esc_attr( $service['title'] ) ?>">
                        <?php echo $service['title'] ?>
                    </label>
                </a>
            </li>
        <?php endforeach ?>
    </ul>
</div>