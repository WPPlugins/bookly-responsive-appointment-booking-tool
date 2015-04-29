<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo add_query_arg( 'type', '_company' ) ?>" enctype="multipart/form-data" class="ab-staff-form">

    <?php if (isset($message_c)) : ?>
        <div id="message" style="margin: 0px!important;" class="updated below-h2">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <p><?php echo $message_c ?></p>
        </div>
    <?php endif ?>

    <table class="form-horizontal">
        <tr>
            <td><?php _e('Company name','ab') ?></td>
            <td><input type="text" size="33" name="ab_settings_company_name" value="<?php echo get_option('ab_settings_company_name') ?>" reset="<?php echo get_option('ab_settings_company_name') ?>"/></td>
        </tr>
        <tr>
            <td valign="top"><?php _e('Company logo','ab') ?></td>
            <td>
                <?php if ( get_option( 'ab_settings_company_logo_url' ) ): ?>
                    <div id="ab-show-logo">
                        <img src="<?php echo get_option( 'ab_settings_company_logo_url' ) ?>" alt="<?php _e( 'Company logo','ab' ) ?>"/>
                        <a id="ab-delete-logo" href="javascript:void(0)"><?php _e( 'Delete','ab' ) ?></a>
                        <br/>
                    </div>
                <?php endif ?>
                <input name="ab_settings_company_logo" id="ab_settings_company_logo" type="file" />
            </td>
        </tr>
        <tr>
            <td valign="top"><?php _e('Address','ab') ?></td>
            <td><textarea cols="32" rows="5" name="ab_settings_company_address"><?php echo get_option('ab_settings_company_address') ?></textarea></td>
        </tr>
        <tr>
            <td><?php _e('Phone','ab') ?></td>
            <td><input type="text" size="33" name="ab_settings_company_phone" value="<?php echo get_option('ab_settings_company_phone') ?>" /></td>
        </tr>
        <tr>
            <td><?php _e('Website','ab') ?></td>
            <td><input type="text" size="33" name="ab_settings_company_website" value="<?php echo get_option('ab_settings_company_website') ?>" /></td>
        </tr>
        <tr>
            <td></td>
            <td>
                <input type="submit" value="<?php _e( 'Save', 'ab' ) ?>" class="btn btn-info ab-update-button" />
                <button id="ab-settings-company-reset" class="ab-reset-form" type="reset"><?php _e( ' Reset ', 'ab' ) ?></button>
            </td>
        </tr>
    </table>
</form>