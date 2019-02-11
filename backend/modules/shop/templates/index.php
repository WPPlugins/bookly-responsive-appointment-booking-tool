<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components;
use Bookly\Lib as BooklyLib;
?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php _e( 'Addons', 'bookly' ) ?>
            </div>
            <?php Components\Support\Buttons::render( $self::pageSlug() ) ?>
        </div>
        <div class="panel panel-default bookly-main">
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-2">
                        <div class="form-group">
                            <select class="form-control bookly-js-select" id="bookly-shop-sort" data-placeholder="<?php echo esc_attr( __( 'Sort by', 'bookly' ) ) ?>">
                                <option></option>
                                <option value="sales"<?php selected( ! $has_new_items ) ?>><?php esc_html_e( 'Best Sellers', 'bookly' ) ?></option>
                                <option value="rating"><?php esc_html_e( 'Best Rated', 'bookly' ) ?></option>
                                <option value="date"<?php selected( $has_new_items ) ?>><?php esc_html_e( 'Newest Items', 'bookly' ) ?></option>
                                <option value="price_low"><?php esc_html_e( 'Price: low to high', 'bookly' ) ?></option>
                                <option value="price_high"><?php esc_html_e( 'Price: high to low', 'bookly' ) ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="panel-group">
                    <div id="bookly-shop" class="collapse"></div>
                </div>
                <div id="bookly-shop-loading" class="bookly-loading"></div>
            </div>
        </div>
    </div>
    <div id="bookly-shop-template" class="collapse">
        <div class="bookly-shop-plugin {{plugin_class}} panel panel-default">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-lg-10 col-md-9 col-xs-7">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 bookly-margin-bottom-md">
                                <div class="bookly-flexbox">
                                    <div class="bookly-flex-cell bookly-margin-bottom-md bookly-vertical-top" style="width: 1%">
                                        <a href="{{url}}" target="_blank">{{icon}}</a>
                                    </div>
                                    <div class="bookly-flex-cell bookly-vertical-top">
                                        <div class="h2 bookly-margin-top-remove bookly-margin-left-lg"><a href="{{url}}" target="_blank">{{title}}</a> <span class="badge badge-danger">{{new}}</span></div>
                                        <a class="bookly-margin-bottom-lg bookly-margin-left-lg" href="<?php echo BooklyLib\Utils\Common::prepareUrlReferrers( 'https://codecanyon.net/user/ladela/portfolio?ref=ladela', 'shop' ) ?>" target="_blank">Ladela</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8 col-md-6">{{description}}</div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-xs-5">
                        <div class="text-center">
                            <div class="h2 bookly-margin-top-remove bookly-margin-bottom-remove">{{price}}</div>
                            <div>{{sales}}</div>
                            <div class="bookly-shop-rating {{rating_class}}">{{rating}}</div>
                            <div class="bookly-margin-bottom-lg">{{reviews}}</div>
                            <div class="{{demo_url_class}}">
                                <a href="{{demo_url}}" class="btn btn-lg btn-primary bookly-margin-bottom-sm " target="_blank"><b><?php esc_html_e( 'Demo', 'bookly' ) ?></b></a>
                            </div>
                            <a href="{{url}}" class="btn btn-lg {{url_class}}" target="_blank"><b>{{url_text}}</b></a><br/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
