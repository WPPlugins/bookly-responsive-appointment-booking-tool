<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'google_calendar' ) ) ?>"
      enctype="multipart/form-data" class="ab-settings-form">
    <div class="form-group">
        <h4 class="bookly-bold"><?php _e( 'Instructions', 'bookly' ) ?></h4>
        <p><?php _e( 'To find your client ID and client secret, do the following:', 'bookly' ) ?></p>
        <ol>
            <li><?php _e( 'Go to the <a href="https://console.developers.google.com/" target="_blank">Google Developers Console</a>.', 'bookly' ) ?></li>
            <li><?php _e( 'Select a project, or create a new one.', 'bookly' ) ?></li>
            <li><?php _e( 'Click in the upper left part to see a sliding sidebar. Next, click <b>API Manager</b>. In the list of APIs look for <b>Calendar API</b> and make sure it is enabled.', 'bookly' ) ?></li>
            <li><?php _e( 'In the sidebar on the left, select <b>Credentials</b>.', 'bookly' ) ?></li>
            <li><?php _e( 'Go to <b>OAuth consent screen</b> tab and give a name to the product, then click <b>Save</b>.', 'bookly' ) ?></li>
            <li><?php _e( 'Go to <b>Credentials</b> tab and in <b>New credentials</b> drop-down menu select <b>OAuth client ID</b>.', 'bookly' ) ?></li>
            <li><?php _e( 'Select <b>Web application</b> and create your project\'s OAuth 2.0 credentials by providing the necessary information. For <b>Authorized redirect URIs</b> enter the <b>Redirect URI</b> found below on this page. Click <b>Create</b>.', 'bookly' ) ?></li>
            <li><?php _e( 'In the popup window look for the <b>Client ID</b> and <b>Client secret</b>. Use them in the form below on this page.', 'bookly' ) ?></li>
            <li><?php _e( 'Go to Staff Members, select a staff member and click <b>Connect</b> which is located at the bottom of the page.', 'bookly' ) ?></li>
        </ol>
    </div>
    <div class="form-group">
        <?php \BooklyLite\Lib\Utils\Common::optionText( __( 'Client ID', 'bookly' ), 'ab_settings_google_client_id', __( 'The client ID obtained from the Developers Console', 'bookly' ) ) ?>
    </div>
    <div class="form-group">
        <?php \BooklyLite\Lib\Utils\Common::optionText( __( 'Client secret', 'bookly' ), 'ab_settings_google_client_secret', __( 'The client secret obtained from the Developers Console', 'bookly' ) ) ?>
    </div>
    <div class="form-group">
        <label for="ab_redirect_uri"><?php _e( 'Redirect URI', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Enter this URL as a redirect URI in the Developers Console', 'bookly' ) ?></p>
        <input id="ab_redirect_uri" class="form-control" type="text" readonly
               value="<?php echo \BooklyLite\Lib\Google::generateRedirectURI() ?>" onclick="this.select();"
               style="cursor: pointer;"/>
    </div>
    <div class="form-group">
        <label for="ab_settings_google_two_way_sync"><?php _e( '2 way sync', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'By default Bookly pushes new appointments and any further changes to Google Calendar. If you enable this option then Bookly will fetch events from Google Calendar and remove corresponding time slots before displaying the second step of the booking form (this may lead to a delay when users click Next at the first step).', 'bookly' ) ?></p>
        <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'ab_settings_google_two_way_sync', array( array( '0', __( 'Disabled', 'bookly' ) ), array( '1', __( 'Enabled', 'bookly' ) ) ) ) ?>
    </div>
    <div class="form-group">
        <label for="ab_settings_google_limit_events"><?php _e( 'Limit number of fetched events', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'If there is a lot of events in Google Calendar sometimes this leads to a lack of memory in PHP when Bookly tries to fetch all events. You can limit the number of fetched events here. This only works when 2 way sync is enabled.', 'bookly' ) ?></p>
        <select id="ab_settings_google_limit_events" class="form-control" name="ab_settings_google_limit_events">
            <?php foreach ( array( __( 'Disabled', 'bookly' ) => '0', 25 => 25, 50 => 50, 100 => 100, 250 => 250, 500 => 500, 1000 => 1000, 2500 => 2500 ) as $text => $limit ) : ?>
                <option value="<?php echo $limit ?>" <?php selected( get_option( 'ab_settings_google_limit_events' ), $limit ) ?> ><?php echo $text ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="form-group">
        <?php \BooklyLite\Lib\Utils\Common::optionText( __( 'Template for event title', 'bookly' ), 'ab_settings_google_event_title', __( 'Configure what information should be placed in the title of Google Calendar event. Available codes are {service_name}, {staff_name} and {client_names}.', 'bookly' ) ) ?>
    </div>

    <div class="panel-footer">
        <?php \BooklyLite\Lib\Utils\Common::submitButton( null, 'bookly-limitation' ) ?>
        <?php \BooklyLite\Lib\Utils\Common::resetButton() ?>
    </div>
</form>
