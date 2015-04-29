<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<li class="ab-staff-member" id="ab-list-staff-<?php echo $staff->get( 'id' ) ?>"  data-staff-id="<?php echo $staff->get( 'id' ) ?>">
    <img class="left ab-avatar" src="<?php echo plugins_url( 'resources/images/default-avatar.png', dirname(__FILE__).'/../../../AB_Backend.php' ) ?>" />
    <div class="ab-text-align"><?php esc_html_e( $staff->get( 'full_name' ) ) ?></div>
</li>