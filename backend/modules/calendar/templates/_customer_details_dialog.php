<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use BooklyLite\Lib\Entities\CustomerAppointment;
use BooklyLite\Lib\Utils\Common;
use BooklyLite\Lib\Config;
use BooklyLite\Lib\Proxy;
?>
<div id="bookly-customer-details-dialog" class="modal fade" tabindex=-1 role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <div class="modal-title h2"><?php _e( 'Edit booking details', 'bookly' ) ?></div>
            </div>
            <form ng-hide=loading style="z-index: 1050">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="bookly-appointment-status"><?php _e( 'Status', 'bookly' ) ?></label>
                        <select class="bookly-custom-field form-control" id="bookly-appointment-status">
                            <option value="<?php echo CustomerAppointment::STATUS_PENDING ?>"><?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_PENDING ) ) ?></option>
                            <option value="<?php echo CustomerAppointment::STATUS_APPROVED ?>"><?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_APPROVED ) ) ?></option>
                            <option value="<?php echo CustomerAppointment::STATUS_CANCELLED ?>"><?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_CANCELLED ) ) ?></option>
                            <option value="<?php echo CustomerAppointment::STATUS_REJECTED ?>"><?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_REJECTED ) ) ?></option>
                            <?php if ( Config::waitingListActive() ) : ?>
                                <option value="<?php echo CustomerAppointment::STATUS_WAITLISTED ?>"><?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_WAITLISTED ) ) ?></option>
                            <?php endif ?>
                        </select>
                    </div>
                    <div class="form-group" style="display: none">
                        <select class="bookly-custom-field form-control" id="bookly-number-of-persons"></select>
                    </div>
                    <?php if ( Config::showNotes() ): ?>
                        <div class="form-group">
                            <label for="bookly-appointment-notes"><?php echo Common::getTranslatedOption( 'bookly_l10n_label_notes' ) ?></label>
                            <textarea class="bookly-custom-field form-control" id="bookly-appointment-notes"></textarea>
                        </div>
                    <?php endif ?>

                    <?php Proxy\CustomFields::renderCustomerDetails() ?>
                    <?php Proxy\ServiceExtras::renderCustomerDetails() ?>
                </div>
                <div class="modal-footer">
                    <?php Common::customButton( null, 'btn-lg btn-lg btn-success', __( 'Apply', 'bookly' ), array( 'ng-click' => 'saveCustomFields()' ) ) ?>
                    <?php Common::customButton( null, 'btn btn-lg btn-default', __( 'Cancel', 'bookly' ), array( 'data-dismiss' => 'modal' ) ) ?>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->