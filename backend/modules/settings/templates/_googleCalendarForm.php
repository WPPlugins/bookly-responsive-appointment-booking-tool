<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'type', '_google_calendar' ) ) ?>" enctype="multipart/form-data" class="ab-settings-form">
    <table class="form-horizontal">
        <tr>
            <td colspan="3">
                <fieldset class="ab-instruction">
                    <legend><?php _e( 'Instructions', 'bookly' ) ?></legend>
                    <div>
                        <div style="margin-bottom: 10px">
                            <?php _e( 'To find your client ID and client secret, do the following:', 'bookly' ) ?>
                        </div>
                        <ol>
                            <li><?php _e( 'Go to the <a href="https://console.developers.google.com/" target="_blank">Google Developers Console</a>.', 'bookly' ) ?></li>
                            <li><?php _e( 'Select a project, or create a new one.', 'bookly' ) ?></li>
                            <li><?php _e( 'In the sidebar on the left, expand <b>APIs & auth</b>. Next, click <b>APIs</b>. In the list of APIs, make sure the status is <b>ON</b> for the Google Calendar API.', 'bookly' ) ?></li>
                            <li><?php _e( 'In the sidebar on the left, select <b>Credentials</b>.', 'bookly' ) ?></li>
                            <li><?php _e( 'Create your project\'s OAuth 2.0 credentials by clicking <b>Create new Client ID</b>, selecting <b>Web application</b>, and providing the information needed to create the credentials. For <b>AUTHORIZED REDIRECT URIS</b> enter the <b>Redirect URI</b> found below on this page.', 'bookly' ) ?></li>
                            <li><?php _e( 'Look for the <b>Client ID</b> and <b>Client secret</b> in the table associated with each of your credentials.', 'bookly' ) ?></li>
                            <li><?php _e( 'Go to Staff Members, select a staff member and click on "Connect" which is located at the bottom of the page.', 'bookly' ) ?></li>
                        </ol>
                    </div>
                </fieldset>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <div class="ab-payments-title"><?php _e( 'Google Calendar', 'bookly' ) ?></div>
            </td>
        </tr>
        <tr>
            <td>
                <label for="ab_settings_google_client_id"><?php _e( 'Client ID', 'bookly' ) ?></label>
            </td>
            <td>
                <input id="ab_settings_google_client_id" class="form-control" type="text" />
            </td>
            <td>
                <?php AB_Utils::popover( __( 'The client ID obtained from the Developers Console', 'bookly' ) ) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="ab_settings_google_client_secret"><?php _e( 'Client secret', 'bookly' ) ?></label>
            </td>
            <td>
                <input id="ab_settings_google_client_secret" class="form-control" type="text" />
            </td>
            <td>
                <?php AB_Utils::popover( __( 'The client secret obtained from the Developers Console', 'bookly' ) ) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="ab_redirect_uri"><?php _e( 'Redirect URI', 'bookly' ) ?></label>
            </td>
            <td>
                <input id="ab_redirect_uri" class="form-control" type="text" readonly value="<?php echo admin_url( 'admin.php' ) . '?page='.AB_StaffController::page_slug ?>" onclick="this.select();" style="cursor: pointer;" />
            </td>
            <td>
                <?php AB_Utils::popover( __( 'Enter this URL as a redirect URI in the Developers Console', 'bookly' ) ) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="ab_settings_google_two_way_sync"><?php _e( '2 way sync', 'bookly' ) ?></label>
            </td>
            <td>
                <?php AB_Utils::optionToggle( 'ab_settings_google_two_way_sync',  array( 'f' => array( '0', __( 'Disabled', 'bookly' ), 't' => array( '1', __( 'Enabled', 'bookly' ) ) ) ) ) ?>
            </td>
            <td>
                <?php AB_Utils::popover( __( 'By default Bookly pushes new appointments and any further changes to Google Calendar. If you enable this option then Bookly will fetch events from Google Calendar and remove corresponding time slots before displaying the second step of the booking form (this may lead to a delay when users click Next at the first step).', 'bookly' ) ) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="ab_settings_google_limit_events"><?php _e( 'Limit number of fetched events', 'bookly' ) ?></label>
            </td>
            <td>
                <select id="ab_settings_google_limit_events" class="form-control">
                    <?php foreach ( array( __( 'Disabled', 'bookly' ) => '0', 25 => 25, 50 => 50, 100 => 100, 250 => 250, 500 => 500, 1000 => 1000, 2500 => 2500 ) as $text => $limit ): ?>
                        <option value="<?php echo $limit ?>" <?php selected( get_option( 'ab_settings_google_limit_events' ), $limit ) ?> ><?php echo $text ?></option>
                    <?php endforeach ?>
                </select>
            </td>
            <td>
                <?php AB_Utils::popover( __( 'If there is a lot of events in Google Calendar sometimes this leads to a lack of memory in PHP when Bookly tries to fetch all events. You can limit the number of fetched events here. This only works when 2 way sync is enabled.', 'bookly' ) ) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="ab_settings_google_event_title"><?php _e( 'Template for event title', 'bookly' ) ?></label>
            </td>
            <td>
                <input id="ab_settings_google_event_title" class="form-control" type="text" value="[[SERVICE_NAME]]" >
            </td>
            <td>
                <?php AB_Utils::popover( __( 'Configure what information should be places in the title of Google Calendar event. Available codes are [[SERVICE_NAME]], [[STAFF_NAME]] and [[CLIENT_NAMES]].', 'bookly' ) ) ?>
            </td>
        </tr>

        <tr>
            <td></td>
            <td>
                <?php AB_Utils::submitButton() ?>
                <?php AB_Utils::resetButton() ?>
            </td>
            <td></td>
        </tr>
    </table>
</form>
