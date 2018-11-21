<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>
<div class="bookly-holidays-nav">
    <div class="input-group input-group-lg">
        <div class="input-group-btn">
            <button class="btn btn-default bookly-js-jCalBtn" data-trigger=".jCal .left" type="button">
                <i class="dashicons dashicons-arrow-left-alt2"></i>
            </button>
        </div>
        <input class="form-control text-center jcal_year" id="appendedPrependedInput"
               readonly type="text" value="">
        <div class="input-group-btn">
            <button class="btn btn-default bookly-js-jCalBtn" data-trigger=".jCal .right" type="button">
                <i class="dashicons dashicons-arrow-right-alt2"></i>
            </button>
        </div>
    </div>
</div>
<div class="bookly-js-holidays jCal-wrap bookly-margin-top-lg"></div>