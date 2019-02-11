<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
?>
<form class="panel panel-default bookly-js-invoice bookly-collapse">
    <div class="panel-heading" role="tab">
        <div class="checkbox bookly-margin-remove">
            <label>
                <input name="invoice[send]" value="0" class="hidden">
                <input name="invoice[send]" value="1" type="checkbox" <?php checked( $invoice['send'] ) ?>>
                <a href="#collapse_invoice" class="collapsed panel-title" role="button" data-toggle="collapse">
                    <?php esc_html_e( 'Send invoice', 'bookly' ) ?>
                </a>
            </label>
        </div>
    </div>
    <div id="collapse_invoice" class="panel-collapse collapse">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="bookly_sms_invoice_company_name"><?php esc_html_e( 'Company name', 'bookly' ) ?>*</label>
                        <input name="invoice[company_name]" type="text" class="form-control" id="bookly_sms_invoice_company_name" required value="<?php echo esc_attr( $invoice['company_name'] ) ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info bookly-flexbox" style="margin-top: 6px">
                        <div class="bookly-flex-row">
                            <div class="bookly-flex-cell" style="width:39px"><i class="alert-icon"></i></div>
                            <div class="bookly-flex-cell"><?php esc_html_e( 'Note: invoice will be sent to your PayPal email address', 'bookly' ) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="bookly_sms_invoice_company_address"><?php esc_html_e( 'Company address', 'bookly' ) ?>*</label>
                        <input name="invoice[company_address]" type="text" class="form-control" id="bookly_sms_invoice_company_address" required value="<?php echo esc_attr( $invoice['company_address'] ) ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group" style="margin-top: -6px">
                        <label class="checkbox" style="margin-left: 20px">
                            <input name="invoice[send_copy]" value="1" type="checkbox" <?php checked( $invoice['send_copy'] ) ?>><?php esc_html_e( 'Copy invoice to another email(s)', 'bookly' ) ?>
                        </label>
                        <p class="help-block"><?php esc_html_e( 'Enter one or more email addresses separated by commas.', 'bookly' ) ?></p>
                        <input name="invoice[cc]" type="text" class="form-control" value="<?php echo esc_attr( $invoice['cc'] ) ?>">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="bookly_sms_invoice_company_address_l2"><?php esc_html_e( 'Company address line 2', 'bookly' ) ?></label>
                        <input name="invoice[company_address_l2]" type="text" class="form-control" id="bookly_sms_invoice_company_address_l2" value="<?php echo esc_attr( $invoice['company_address_l2'] ) ?>">
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="bookly_sms_invoice_company_vat"><?php esc_html_e( 'VAT', 'bookly' ) ?></label>
                            <input name="invoice[company_vat]" type="text" class="form-control" id="bookly_sms_invoice_company_vat" value="<?php echo esc_attr( $invoice['company_vat'] ) ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="bookly_sms_invoice_company_code"><?php esc_html_e( 'Company code', 'bookly' ) ?></label>
                            <input name="invoice[company_code]" type="text" class="form-control" id="bookly_sms_invoice_company_code" value="<?php echo esc_attr( $invoice['company_code'] ) ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="bookly_sms_invoice_company_add_text"><?php esc_html_e( 'Additional text to include into invoice', 'bookly' ) ?></label>
                        <textarea name="invoice[company_add_text]" class="form-control" rows="4" style="height: 118px;" id="bookly_sms_invoice_company_add_text"><?php echo esc_textarea( $invoice['company_add_text'] ) ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <?php Buttons::renderCustom( null, 'btn-lg btn-success' ) ?>
        </div>
    </div>
</form>