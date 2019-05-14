<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="bookly-js-dashboard-appointments">
    <div class="row bookly-padding-bottom-lg">
        <div class="col-sm-6 col-md-3">
            <div class="row">
                <div class="col-xs-4 text-right">
                    <i class="far fa-calendar-check fa-w-14 fa-5x text-muted"></i>
                </div>
                <div class="col-xs-8">
                    <div style="font-size: 40px" class="bookly-js-approved">&nbsp;</div>
                    <span style="font-size: 20px"><a href="#" class="bookly-js-href-approved"><?php esc_html_e( 'Approved appointments', 'bookly' ) ?></a></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="row">
                <div class="col-xs-4 text-right">
                    <i class="fas fa-hourglass-half fa-w-14 fa-5x text-muted"></i>
                </div>
                <div class="col-xs-8">
                    <div style="font-size: 40px" class="bookly-js-pending">&nbsp;</div>
                    <span style="font-size: 20px"><a href="#" class="bookly-js-href-pending"><?php esc_html_e( 'Pending appointments', 'bookly' ) ?></a></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="row">
                <div class="col-xs-4 text-right">
                    <i class="far fa-calendar fa-w-14 fa-5x text-muted"></i>
                </div>
                <div class="col-xs-8">
                    <div style="font-size: 40px" class="bookly-js-total">&nbsp;</div>
                    <span style="font-size: 20px"><a href="#" class="bookly-js-href-total"><?php esc_html_e( 'Total appointments', 'bookly' ) ?></a></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="row">
                <div class="col-xs-4 text-right">
                    <i class="far fa-money-bill-alt fa-w-14 fa-5x text-muted"></i>
                </div>
                <div class="col-xs-8">
                    <div style="font-size: 40px" class="bookly-js-revenue">&nbsp;</div>
                    <span style="font-size: 20px"><a href="#" class="bookly-js-href-revenue"><?php esc_html_e( 'Revenue', 'bookly' ) ?></a></span>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-default bookly-margin-top-md">
        <div class="panel-body">
            <div>
                <canvas id="canvas" style="width:100%;height: 500px"></canvas>
            </div>
        </div>
    </div>
</div>