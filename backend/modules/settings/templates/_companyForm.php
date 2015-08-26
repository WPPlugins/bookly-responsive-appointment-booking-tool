<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'type', '_company' ) ) ?>" enctype="multipart/form-data" class="ab-settings-form">
    <table class="form-horizontal">
        <tr>
            <td>
                <label for="ab_settings_company_name"><?php _e( 'Company name', 'bookly' ) ?></label>
            </td>
            <td><input id="ab_settings_company_name" class="form-control" type="text" name="ab_settings_company_name" value="<?php echo esc_attr( get_option( 'ab_settings_company_name' ) ) ?>" reset="<?php echo esc_attr( get_option( 'ab_settings_company_name' ) ) ?>"/></td>
        </tr>
        <tr>
            <td valign="top">
                <label for="ab_settings_company_logo"><?php _e( 'Company logo', 'bookly' ) ?></label>
            </td>
            <td>
                <?php if ( get_option( 'ab_settings_company_logo_url' ) ): ?>
                    <div id="ab-show-logo">
                        <img src="<?php echo esc_attr( get_option( 'ab_settings_company_logo_url' ) ) ?>" alt="<?php echo esc_attr( __( 'Company logo', 'bookly' ) ) ?>"/>
                        <a id="ab-delete-logo" href="javascript:void(0)"><?php _e( 'Delete', 'bookly' ) ?></a>
                        <br/>
                    </div>
                <?php endif ?>
                <input name="ab_settings_company_logo" id="ab_settings_company_logo" type="file" />
            </td>
        </tr>
        <tr>
            <td valign="top">
                <label for="ab_settings_company_address"><?php _e( 'Address', 'bookly' ) ?></label>
            </td>
            <td><textarea id="ab_settings_company_address" class="form-control" rows="5" name="ab_settings_company_address"><?php echo esc_attr( get_option( 'ab_settings_company_address' ) ) ?></textarea></td>
        </tr>
        <tr>
            <td>
                <label for="ab_settings_company_phone"><?php _e( 'Phone', 'bookly' ) ?></label>
            </td>
            <td><input id="ab_settings_company_phone" class="form-control" type="text" name="ab_settings_company_phone" value="<?php echo esc_attr( get_option( 'ab_settings_company_phone' ) ) ?>" /></td>
        </tr>
        <tr>
            <td>
                <label for="ab_settings_company_website"><?php _e( 'Website', 'bookly' ) ?></label>
            </td>
            <td><input id="ab_settings_company_website" class="form-control" type="text" name="ab_settings_company_website" value="<?php echo esc_attr( get_option( 'ab_settings_company_website' ) ) ?>" /></td>
        </tr>
        <tr>
            <td></td>
            <td>
                <?php AB_Utils::submitButton() ?>
                <?php AB_Utils::resetButton( 'ab-settings-company-reset' ) ?>
            </td>
        </tr>
    </table>
</form>