<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components;
?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <?php if ( \Bookly\Lib\Utils\Common::isCurrentUserAdmin() ) : ?>
                <div class="bookly-page-title">
                    <?php _e( 'Staff Members', 'bookly' ) ?>
                    <span class="bookly-color-gray">(<span id="bookly-staff-count"><?php echo count( $staff_members ) ?></span>)</span>
                </div>
                <?php Components\Support\Buttons::render( $self::pageSlug() ) ?>
            <?php else : ?>
                <div class="bookly-page-title">
                    <?php _e( 'Profile', 'bookly' ) ?>
                </div>
            <?php endif ?>
        </div>
        <div class="row">
            <div id="bookly-sidebar" class="col-sm-4"
                <?php if ( ! \Bookly\Lib\Utils\Common::isCurrentUserAdmin() ) : ?>
                    style="display: none"
                <?php endif ?>
            >
                <ul id="bookly-staff-list" class="bookly-nav">
                    <?php foreach ( $staff_members as $staff ) : ?>
                        <?php include '_list_item.php' ?>
                    <?php endforeach ?>
                </ul>
                <?php include '_new.php' ?>
            </div>

            <div id="bookly-container-edit-staff" class="col-sm-8"></div>
        </div>
    </div>
    <?php Components\Dialogs\Common\CascadeDelete::render() ?>
    <?php Components\Dialogs\Common\UnsavedChanges::render() ?>
</div>