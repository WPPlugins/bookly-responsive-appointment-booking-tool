import $ from 'jquery';
import {opt, laddaStart, scrollTo} from './shared.js';
import stepService from './service_step.js';
import stepTime from './time_step.js';
import stepRepeat from './repeat_step.js';
import stepCart from './cart_step.js';
import stepDetails from './details_step.js';

/**
 * Extras step.
 */
export default function stepExtras(params) {
    var data = {
            action    : 'bookly_render_extras',
            csrf_token: BooklyL10n.csrf_token,
        },
        $container = opt[params.form_id].$container;
    if (opt[params.form_id].skip_steps.service && opt[params.form_id].use_client_time_zone) {
        // If Service step is skipped then we need to send time zone offset.
        data.time_zone        = opt[params.form_id].timeZone;
        data.time_zone_offset = opt[params.form_id].timeZoneOffset;
    }
    $.extend(data, params);
    $.ajax({
        url: BooklyL10n.ajaxurl,
        data: data,
        dataType: 'json',
        xhrFields: {withCredentials: true},
        crossDomain: 'withCredentials' in new XMLHttpRequest(),
        success: function (response) {
            if (response.success) {
                BooklyL10n.csrf_token = response.csrf_token;
                $container.html(response.html);
                if (params === undefined) { // Scroll when returning to the step Extras.
                    scrollTo($container);
                }
                var $next_step = $('.bookly-js-next-step', $container),
                    $back_step = $('.bookly-js-back-step', $container),
                    $goto_cart = $('.bookly-js-go-to-cart', $container),
                    $extras_items = $('.bookly-js-extras-item', $container),
                    $extras_summary = $('.bookly-js-extras-summary span', $container),
                    currency = response.currency,
                    $this,
                    $input;

                var extrasChanged = function($extras_item, quantity) {
                    var $input = $extras_item.find('input'),
                        $total = $extras_item.find('.bookly-js-extras-total-price'),
                        total_price = quantity * parseFloat($extras_item.data('price'));

                    $total.text(currency.format.replace('1', total_price.toFixed(currency.precision)));
                    $input.val(quantity);
                    $extras_item.find('.bookly-js-extras-thumb').toggleClass('bookly-extras-selected', quantity > 0);

                    // Updating summary
                    var amount = 0;
                    $extras_items.each(function (index, elem) {
                        var $this = $(this),
                            multiplier = $this.closest('.bookly-js-extras-container').data('multiplier');
                        amount += parseFloat($this.data('price')) * $this.find('input').val() * multiplier;
                    });
                    if (amount) {
                        $extras_summary.html(' + ' + currency.format.replace('1', amount.toFixed(currency.precision)));
                    } else {
                        $extras_summary.html('');
                    }
                };

                $extras_items.each(function (index, elem) {
                    var $this = $(this);
                    var $input = $this.find('input');
                    $this.find('.bookly-js-extras-thumb').on('click', function () {
                        extrasChanged($this, $input.val() > 0 ? 0 : 1);
                    });
                    $this.find('.bookly-js-count-control').on('click', function() {
                        var count = parseInt($input.val());
                        count = $(this).hasClass('bookly-js-extras-increment')
                            ? Math.min($this.data('max_quantity'), count + 1)
                            : Math.max(0, count - 1);
                        extrasChanged($this, count);
                    });
                });

                $goto_cart.on('click', function (e) {
                    e.preventDefault();
                    laddaStart(this);
                    stepCart({form_id: params.form_id, from_step : 'extras'});
                });

                $next_step.on('click', function (e) {
                    e.preventDefault();
                    laddaStart(this);
                    var extras = {};
                    $('.bookly-js-extras-container', $container).each(function () {
                        var $extras_container = $(this);
                        var chain_id = $extras_container.data('chain');
                        var chain_extras = {};
                        // Get checked extras for chain.
                        $extras_container.find('.bookly-js-extras-item').each(function (index, elem) {
                            $this = $(this);
                            $input = $this.find('input');
                            if ($input.val() > 0) {
                                chain_extras[$this.data('id')] = $input.val();
                            }
                        });
                        extras[chain_id] = JSON.stringify(chain_extras);
                    });
                    $.ajax({
                        type : 'POST',
                        url  : BooklyL10n.ajaxurl,
                        data : {
                            action     : 'bookly_session_save',
                            csrf_token : BooklyL10n.csrf_token,
                            form_id    : params.form_id,
                            extras     : extras
                        },
                        dataType: 'json',
                        xhrFields: {withCredentials: true},
                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                        success: function (response) {
                            if(opt[params.form_id].step_extras == 'before_step_time') {
                                stepTime({form_id: params.form_id, prev_step: 'extras'});
                            } else if (!opt[params.form_id].skip_steps.repeat) {
                                stepRepeat({form_id: params.form_id});
                            } else if (!opt[params.form_id].skip_steps.cart) {
                                stepCart({form_id: params.form_id, add_to_cart : true, from_step : 'time'});
                            } else {
                                stepDetails({form_id: params.form_id, add_to_cart : true});
                            }
                        }
                    });
                });
                $back_step.on('click', function (e) {
                    e.preventDefault();
                    laddaStart(this);
                    if (opt[params.form_id].step_extras == 'after_step_time' && !opt[params.form_id].no_time) {
                        stepTime({form_id: params.form_id, prev_step: 'extras'});
                    } else {
                        stepService({form_id: params.form_id});
                    }
                });
            }
        }
    });
}