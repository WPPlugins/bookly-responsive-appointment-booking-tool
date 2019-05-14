import $ from 'jquery';
import {opt} from './shared.js';
import stepService from './service_step.js';
import stepPayment from './payment_step.js';
import stepComplete from './complete_step.js';

/**
 * Main Bookly function.
 *
 * @param options
 */
window.bookly = function(options) {
    opt[options.form_id] = options;

    opt[options.form_id].$container         = $('#bookly-form-' + options.form_id);
    opt[options.form_id].timeZone           = typeof Intl === 'object' ? Intl.DateTimeFormat().resolvedOptions().timeZone : undefined;
    opt[options.form_id].timeZoneOffset     = new Date().getTimezoneOffset();
    opt[options.form_id].skip_steps.service = options.skip_steps.service_part1 && options.skip_steps.service_part2;

    // initialize
    if (options.status.booking == 'finished') {
        stepComplete({form_id: options.form_id});
    } else if (options.status.booking == 'cancelled') {
        stepPayment({form_id: options.form_id});
    } else {
        stepService({form_id: options.form_id, new_chain : true});
    }
    if (options.hasOwnProperty('facebook') && options.facebook.enabled) {
        initFacebookLogin(options);
    }

    // init google places

    if (options.hasOwnProperty('google_maps') && options.google_maps.enabled) {
        var apiKey = options.google_maps.api_key,
            src = 'https://maps.googleapis.com/maps/api/js?key=' + apiKey + '&libraries=places';

        importScript(src, true);
    }
    if (options.hasOwnProperty('stripe') && options.stripe.enabled) {
        importScript('https://js.stripe.com/v3/', true);
    }
};

/**
 * Init Facebook login.
 */
function initFacebookLogin(options) {
    FB.init({
        appId : options.facebook.appId,
        status: true,
        version: 'v2.12'
    });
    FB.getLoginStatus(function(response) {
        if (response.status === 'connected') {
            options.facebook.enabled = false;
            FB.api('/me', {fields: 'id,name,first_name,last_name,email,link'}, function(userInfo) {
                $.ajax({
                    type: 'POST',
                    url: BooklyL10n.ajaxurl,
                    data: $.extend(userInfo, {
                        action      : 'bookly_pro_facebook_login',
                        csrf_token  : BooklyL10n.csrf_token,
                        form_id     : options.form_id
                    }),
                    dataType: 'json',
                    xhrFields: {withCredentials: true},
                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                    success: function (response) {}
                });
            });
        } else {
            FB.Event.subscribe('auth.statusChange', function(response) {
                if (options.facebook.onStatusChange) {
                    options.facebook.onStatusChange(response);
                }
            });
        }
    });
}

function importScript(src, async, onLoad) {
    var script = document.createElement("script");
    script.type = "text\/javascript";

    if (async !== undefined) {
        script.async = async;
    }
    if (onLoad instanceof Function) {
        script.onload = onLoad;
    }

    document.head.appendChild(script);
    script.src = src;
}