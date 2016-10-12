<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php if ( \BooklyLite\Lib\Utils\Common::isCurrentUserAdmin() ) : ?>
                    <?php _e( 'Staff Members', 'bookly' ) ?>
                    <span class="bookly-color-gray">(<span id="bookly-staff-count"><?php echo count( $staff_members ) ?></span>)</span>
                <?php else : ?>
                    <?php _e( 'Profile', 'bookly' ) ?>
                <?php endif ?>
            </div>
        </div>

        <div class="row">
            <div id="bookly-sidebar" class="col-sm-4"
                <?php if ( ! \BooklyLite\Lib\Utils\Common::isCurrentUserAdmin() ) : ?>
                    style="display: none"
                <?php endif ?>
            >
                <ul id="bookly-staff-list" class="bookly-nav">
                    <?php foreach ( $staff_members as $staff ) : ?>
                        <?php include '_list_item.php' ?>
                    <?php endforeach ?>
                </ul>
                <?php include 'new.php' ?>
            </div>

            <div id="bookly-container-edit-staff" class="col-sm-8"></div>
        </div>
    </div>
</div>