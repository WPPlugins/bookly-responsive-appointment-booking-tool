<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo $current_url . '&type=_payments' ?>" class="ab-staff-form">
    <?php if (isset($message_p)) : ?>
    <div id="message" style="margin: 0px!important;" class="updated below-h2">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <p><?php echo $message_p ?></p>
    </div>
    <?php endif ?>
    <table class="form-horizontal">
	    <tr>
		    <td style="width: 170px;"><?php _e( 'Currency','ab' ) ?></td>
		    <td>
			    <select name="ab_paypal_currency" style="width: 200px;">
				    <?php foreach ( PayPal::getCurrencyCodes() as $code ): ?>
					    <option value="<?php echo $code ?>" <?php selected( get_option( 'ab_paypal_currency' ), $code ); ?> ><?php echo $code ?></option>
				    <?php endforeach ?>
			    </select>
		    </td>
	    </tr>
        <tr>
            <td colspan="2"><div class="ab-payments-title"><?php _e( 'Service paid locally','ab' ) ?></div></td>
        </tr>
        <tr>
            <td colspan="2">
                <select name="ab_settings_pay_locally" style="width: 200px;">
                    <?php foreach ( array( __( 'Disabled', 'ab' ) => '0', __( 'Enabled', 'ab' ) => '1' ) as $text => $mode ): ?>
                        <option value="<?php echo $mode ?>" <?php selected( get_option( 'ab_settings_pay_locally' ), $mode ); ?> ><?php echo $text ?></option>
                    <?php endforeach ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2"><div class="ab-payments-title"><?php _e( 'PayPal','ab' ) ?></div></td>
        </tr>
        <tr>
            <td colspan="2">
                <select id="ab_paypal_type" style="width: 200px;">
                    <?php foreach ( array( __( 'Disabled', 'ab' ) => 'disabled', 'PayPal Express Checkout' => 'ec' ) as $text => $mode ): ?>
                        <option value="<?php echo $mode ?>" ><?php echo $text ?></option>
                    <?php endforeach ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="submit" value="<?php _e( 'Save', 'ab' ) ?>" class="btn btn-info ab-update-button" />
                <button id="ab-payments-reset" class="ab-reset-form" type="reset"><?php _e( 'Reset', 'ab' ) ?></button>
            </td>
            <td></td>
        </tr>
    </table>
</form>

<div class="modal fade" id="light_notice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Notice</h4>
      </div>
      <div class="modal-body">
        <?php _e('This function is disabled in the light verison of Bookly. If you find the plugin useful for your business please consider buying a licence for the full version. It costs just $35 and for this money you will get many useful functions, lifetime free update and excellent support! More information can be found here'); ?>: <a href="http://bookly.ladela.com" target="_blank">http://bookly.ladela.com</a>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->