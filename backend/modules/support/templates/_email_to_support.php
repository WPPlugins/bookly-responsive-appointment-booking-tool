<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Support Request <?php echo site_url() ?> Bookly Lite</title>
</head>
<body>
    Bookly Lite
    <p><?php echo esc_html( $name ) ?><br /><?php echo esc_html( $email ) ?></p>
    <p><?php echo nl2br( esc_html( $msg ) ) ?></p>
    <p><?php echo esc_html( $_SERVER["HTTP_REFERER"] ) ?></p>
</body>
</html>