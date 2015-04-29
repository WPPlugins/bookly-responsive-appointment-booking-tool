<?php
/**
 * @var array $customers
 * @var array $types
 * @var array $providers
 * @var array $services
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class=ab-title><?php _e( 'Payments','ab' ) ?></div>
<div style="min-width: 800px;margin-right: 15px;">
    <div class=ab-nav-payment>
        <div class=row-fluid>
            <div id=reportrange class="pull-left ab-reportrange" style="margin-bottom: 10px">
                <i class="icon-calendar icon-large"></i>
                <span data-date="<?php echo date( 'F j, Y', strtotime( '-30 day' ) ) ?> - <?php echo date( 'F j, Y' ) ?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( '-30 day' ) ) ?> - <?php echo date_i18n( get_option( 'date_format' ) ) ?></span> <b style="margin-top: 8px;" class=caret></b>
            </div>
            <div class=pull-left>
                <select id=ab-type-filter class=selectpicker>
                    <option value="-1"><?php _e( 'All payment types', 'ab' ) ?></option>
                    <?php foreach ( $types as $type ): ?>
                        <option value="<?php echo esc_attr( $type ) ?>">
                            <?php
                            switch( $type ) {
                                case 'paypal':
                                    echo 'PayPal';
                                    break;
                                case 'authorizeNet':
                                    echo 'authorizeNet';
                                    break;
                                case 'stripe':
                                    echo 'Stripe';
                                    break;
                                default:
                                    echo __( 'Local', 'ab' );
                                    break;
                            }
                            ?>
                        </option>
                    <?php endforeach ?>
                </select>
                <select id=ab-customer-filter class=selectpicker>
                    <option value="-1"><?php _e( 'All customers', 'ab' ) ?></option>
                    <?php foreach ( $customers as $customer ): ?>
                        <option><?php echo esc_html($customer) ?></option>
                    <?php endforeach ?>
                </select>
                <select id=ab-provider-filter class=selectpicker>
                    <option value="-1"><?php _e( 'All providers', 'ab' ) ?></option>
                    <?php foreach ( $providers as $provider ): ?>
                        <option><?php echo esc_html($provider) ?></option>
                    <?php endforeach ?>
                </select>
                <select id=ab-service-filter class=selectpicker>
                    <option value="-1"><?php _e( 'All services', 'ab' ) ?></option>
                    <?php foreach ( $services as $service ): ?>
                        <option><?php echo esc_html($service) ?></option>
                    <?php endforeach ?>
                </select>
                <a id=ab-filter-submit style="margin:0 0 10px 5px;" href="#" class="btn btn-primary"><?php _e( 'Filter', 'ab' ) ?></a>
            </div>
        </div>
    </div>
    <div id=ab-alert-div class=alert style="display: none"></div>
    <table class="table table-bordered" cellspacing=0 cellpadding=0 border=0 id=ab_payments_list style="clear: both;">
        <thead>
        <tr>
            <th width=150 class="desc active" order-by=created><a href="javascript:void(0)"><?php _e( 'Date', 'ab' ) ?></a></th>
            <th width=100 order-by=type><a href="javascript:void(0)"><?php _e( 'Type', 'ab' ) ?></a></th>
            <th width=150 order-by=customer><a href="javascript:void(0)"><?php _e( 'Customer', 'ab' ) ?></a></th>
            <th width=150 order-by=provider><a href="javascript:void(0)"><?php _e( 'Provider', 'ab' ) ?></a></th>
            <th width=150 order-by=service><a href="javascript:void(0)"><?php _e( 'Service', 'ab' ) ?></a></th>
            <th width=50  order-by=total><a href="javascript:void(0)"><?php _e( 'Amount', 'ab') ?></a></th>
            <th width=50  order-by=coupon><a href="javascript:void(0)"><?php _e( 'Coupon', 'ab') ?></a></th>
            <th width=150 order-by=start_date><a href="javascript:void(0)"><?php _e( 'Appointment Date', 'ab' ) ?></a></th>
        </tr>
        </thead>
        <tbody id=ab-tb-body>
        </tbody>
    </table>
    <?php include '_alert.php'; ?>
</div>

<script type="text/javascript">
    jQuery(function($) {
        var data          = {},
            $report_range = $('#reportrange span'),
            picker_ranges = {},
            l10nRanges    = {
                response: function(start, end) {
                    return $.post(ajaxurl, {action: 'ab_l10n_ranges', start: start, end: end});
                },
                l10n: function(start, end) {
                    this.response(start, end).done(function(response) {
                        var ranges = JSON.parse(response);
                        $report_range.data('date', start.toString('MMMM d, yyyy') + ' - ' + end.toString('MMMM d, yyyy'));
                        $report_range.html(ranges.start + ' - ' + ranges.end);
                    });
                }
            };

        picker_ranges[BooklyL10n.today]      = ['today', 'today'];
        picker_ranges[BooklyL10n.yesterday]  = ['yesterday', 'yesterday'];
        picker_ranges[BooklyL10n.last_7]     = [Date.today().add({ days: -6 }), 'today'];
        picker_ranges[BooklyL10n.last_30]    = [Date.today().add({ days: -30 }), 'today','selected'];
        picker_ranges[BooklyL10n.this_month] = [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()];
        picker_ranges[BooklyL10n.last_month] = [Date.today().moveToFirstDayOfMonth().add({ months: -1 }), Date.today().moveToFirstDayOfMonth().add({ days: -1 })];

        $('.selectpicker').selectpicker({style: 'btn-info', size: 5});

        $('#reportrange').daterangepicker({ranges: picker_ranges}, function(start, end) {
            l10nRanges.l10n(start, end);
        });

    });
</script>