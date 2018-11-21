import $ from 'jquery';
import {opt, scrollTo} from './shared.js';

/**
 * Complete step.
 */
export default function stepComplete(params) {
    var data = $.extend({
            action    : 'bookly_render_complete',
            csrf_token: BooklyL10n.csrf_token,
        }, params),
        $container = opt[params.form_id].$container;
    $.ajax({
        url: BooklyL10n.ajaxurl,
        data: data,
        dataType: 'json',
        xhrFields: {withCredentials: true},
        crossDomain: 'withCredentials' in new XMLHttpRequest(),
        success: function (response) {
            if (response.success) {
                if (response.final_step_url && !data.error) {
                    document.location.href = response.final_step_url;
                } else {
                    $container.html(response.html);
                    scrollTo($container);
                }
            }
        }
    });
}