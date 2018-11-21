<?php if ( ! defined( 'ABSPATH' ) )  exit; // Exit if accessed directly ?>
<div class="alert alert-info"><?php _e( 'Please take into account that not all countries by law allow custom SMS sender ID. Please check if particular country supports custom sender ID in our price list. Also please note that prices for messages with custom sender ID are usually 20% - 25% higher than normal message price.', 'bookly' ) ?></div>
<form class="form-inline bookly-margin-bottom-xlg">
    <div class="form-group">
        <label class="control-label" for="bookly-sender-id-input"><?php _e( 'Request Sender ID', 'bookly' ) ?></label> <?php if ( $sms->getSenderIdApprovalDate() ) : ?> <span class="bookly-vertical-middle"><?php _e( 'or', 'bookly' ) ?> <a href="#" id="bookly-reset-sender_id"><?php _e( 'Reset to default', 'bookly' ) ?></a></span><?php endif ?>
        <p class="help-block"><?php _e( 'Can only contain letters or digits (up to 11 characters).', 'bookly' ) ?></p>
        <input id="bookly-sender-id-input" class="form-control" type="text" maxlength="11" required="required" minlength="1" value="" />
        <button data-spinner-size="40" data-style="zoom-in" type="button" class="btn btn-success" id="bookly-request-sender_id"><span class="ladda-label"><?php _e( 'Request', 'bookly' ) ?></span><span class="ladda-spinner"></span></button>
        <button data-spinner-size="40" data-style="zoom-in" type="button" class="btn btn-danger" id="bookly-cancel-sender_id" style="display:none"><span class="ladda-label"><?php _e( 'Cancel request', 'bookly' ) ?></span><span class="ladda-spinner"></span></button>
    </div>
</form>
<table id="bookly-sender-ids" class="table table-striped" width="100%">
    <thead>
    <tr>
        <th><?php _e( 'Date', 'bookly' ) ?></th>
        <th><?php _e( 'Requested ID', 'bookly' ) ?></th>
        <th><?php _e( 'Status', 'bookly' ) ?></th>
        <th><?php _e( 'Status Date', 'bookly' ) ?></th>
    </tr>
    </thead>
</table>