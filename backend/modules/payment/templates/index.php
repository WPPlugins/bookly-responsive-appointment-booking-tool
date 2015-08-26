<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * @var array $customers
 * @var array $types
 * @var array $providers
 * @var array $services
 */
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Payments', 'bookly' ) ?></h3>
    </div>
    <div class="panel-body">
        <div class=ab-nav-payment>
            <div class=row-fluid>
                <div id=reportrange class="ab-reportrange ab-inline-block">
                    <i class="glyphicon glyphicon-calendar"></i>
                    <span data-date="<?php echo date( 'Y-m-d', strtotime( '-30 day' ) ) ?> - <?php echo date( 'Y-m-d' ) ?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( '-30 day' ) ) ?> - <?php echo date_i18n( get_option( 'date_format' ) ) ?></span> <b style="margin-top: 8px;" class=caret></b>
                </div>
                <div class=ab-inline-block>
                    <select id=ab-type-filter class=selectpicker>
                        <option value="-1"><?php _e( 'All payment types', 'bookly' ) ?></option>
                        <?php foreach ( $types as $type ): ?>
                            <option value="<?php echo esc_attr( $type ) ?>">
                                <?php
                                switch( $type ) {
                                    case 'paypal':
                                        echo 'PayPal';              break;
                                    case 'authorizeNet':
                                        echo 'authorizeNet';        break;
                                    case 'stripe':
                                        echo 'Stripe';              break;
                                    default:
                                        _e( 'Local', 'bookly' );    break;
                                }
                                ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                    <select id=ab-customer-filter class=selectpicker>
                        <option value="-1"><?php _e( 'All customers', 'bookly' ) ?></option>
                        <?php foreach ( $customers as $customer ): ?>
                            <option><?php echo esc_html( $customer ) ?></option>
                        <?php endforeach ?>
                    </select>
                    <select id=ab-provider-filter class=selectpicker>
                        <option value="-1"><?php _e( 'All providers', 'bookly' ) ?></option>
                        <?php foreach ( $providers as $provider ): ?>
                            <option><?php echo esc_html( $provider ) ?></option>
                        <?php endforeach ?>
                    </select>
                    <select id=ab-service-filter class=selectpicker>
                        <option value="-1"><?php _e( 'All services', 'bookly' ) ?></option>
                        <?php foreach ( $services as $service ): ?>
                            <option><?php echo esc_html( $service ) ?></option>
                        <?php endforeach ?>
                    </select>
                    <a id=ab-filter-submit href="#" class="btn btn-primary"><?php _e( 'Filter', 'bookly' ) ?></a>
                </div>
            </div>
        </div>
        <div id=ab-alert-div class=alert style="display: none"></div>
        <?php include '_alert.php' ?>
        <div style="display: none" class="loading-indicator">
            <span class="ab-loader"></span>
        </div>
    </div>
</div>
<div class="modal fade" id="lite_notice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php _e('Notice', 'bookly') ?></h4>
            </div>
            <div class="modal-body">
                <?php _e('This function is disabled in the lite version of Bookly. If you find the plugin useful for your business please consider buying a licence for the full version. It costs just $46 and for this money you will get many useful functions, lifetime free update and excellent support! More information can be found here', 'bookly'); ?>: <a href="http://booking-wp-plugin.com" target="_blank">http://booking-wp-plugin.com</a>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close', 'bookly') ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript">
    jQuery(function($) {
        var data          = {},
            $report_range = $('#reportrange span'),
            picker_ranges = {};

        picker_ranges[BooklyL10n.today]      = [moment(), moment()];
        picker_ranges[BooklyL10n.yesterday]  = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
        picker_ranges[BooklyL10n.last_7]     = [moment().subtract(7, 'days'), moment()];
        picker_ranges[BooklyL10n.last_30]    = [moment().subtract(30, 'days'), moment()];
        picker_ranges[BooklyL10n.this_month] = [moment().startOf('month'), moment().endOf('month')];
        picker_ranges[BooklyL10n.last_month] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];

        $('.selectpicker').selectpicker({style: 'btn-info', size: 5});

        function ajaxData(object) {
            data['customer'] = $('#ab-customer-filter').val();
            data['provider'] = $('#ab-provider-filter').val();
            data['service']  = $('#ab-service-filter').val();
            data['range']    = $report_range.data('date'); //text();
            data['type']     = $('#ab-type-filter').val();
            data['key']      = $('#search_customers').val();


            return data;
        }

        // sort order
        $('#ab_payments_list th a').on('click', function() {
            var data = { action:'ab_sort_payments', data: ajaxData(this) };
            $('.loading-indicator').show();
            $('#ab_payments_list tbody').load(ajaxurl, data, function() {$('.loading-indicator').hide();});
        });

        $('#reportrange').daterangepicker(
            {
                startDate: moment().subtract(30, 'days'), // by default selected is "Last 30 days"
                ranges: picker_ranges,
                locale: {
                    applyLabel: BooklyL10n.apply,
                    cancelLabel: BooklyL10n.cancel,
                    fromLabel: BooklyL10n.from,
                    toLabel: BooklyL10n.to,
                    customRangeLabel: BooklyL10n.custom_range,
                    daysOfWeek: BooklyL10n.days,
                    monthNames: BooklyL10n.months,
                    firstDay: parseInt(BooklyL10n.startOfWeek),
                    format: BooklyL10n.mjsDateFormat
                }
            },
            function(start, end) {
                var format = 'YYYY-MM-DD';
                $report_range
                    .data('date', start.format(format) + ' - ' + end.format(format))
                    .html(start.format(BooklyL10n.mjsDateFormat) + ' - ' + end.format(BooklyL10n.mjsDateFormat));
            }
        );

        $('#ab-filter-submit').on('click', function() {
            $('#lite_notice').modal('show');
        });
    });
</script>