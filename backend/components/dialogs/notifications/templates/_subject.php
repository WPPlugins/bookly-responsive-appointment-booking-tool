<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="notification_subject"><?php esc_attr_e( 'Subject', 'bookly' ) ?></label>
            <input type="text" class="form-control" id="notification_subject" name="notification[subject]" value=""/>
            <input type="hidden" name="notification[gateway]" value="email"/>
        </div>
    </div>
</div>