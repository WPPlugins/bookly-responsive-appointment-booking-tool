<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * @var AB_Staff $staff
 */
?>
<li class="ab-staff-member" id="ab-list-staff-<?php echo $staff->get( 'id' ) ?>"  data-staff-id="<?php echo $staff->get( 'id' ) ?>">
    <img class="left ab-avatar" src="<?php echo plugins_url( 'backend/resources/images/default-avatar.png', AB_PATH . '/main.php' ) ?>" />
    <div class="ab-text-align"><?php echo esc_html($staff->get('full_name')) ?></div>
</li>