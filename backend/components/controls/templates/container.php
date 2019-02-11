<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="bookly-collapse">
    <a class="h4" href="#<?php echo $id ?>" data-toggle="collapse" role="button" aria-expanded="true"><?php echo esc_html( $title ) ?></a>
    <div id="<?php echo $id ?>" class="bookly-margin-top-lg collapse in" aria-expanded="true">