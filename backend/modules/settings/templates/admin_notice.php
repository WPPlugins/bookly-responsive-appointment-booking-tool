<div id=ab_admin_notice class=update-nag>
    <h3>Bookly</h3>
    <p><?php _e( 'Please do not forget to specify your purchase code in Bookly <a href="admin.php?page=ab-system-settings">settings</a>. Upon providing the code you will have access to free updates of Bookly. Updates may contain functionality improvements and important security fixes.', 'ab' ) ?></p>
    <p><?php _e( '<b>Important!</b> Please be aware that if your copy of Bookly was not downloaded from Codecanyon (the only channel of Bookly distribution), you may put your website under significant risk - it is very likely that it may contain a malicious code, a trojan or a backdoor. Please consider buying a licensed copy of Bookly <a href="http://bookly.ladela.com" target="_blank">here</a>.', 'ab' ) ?></p>
    <a id="ab_dismiss" href="#"><?php _e( 'Dismiss', 'ab' ) ?></a>
</div>

<script type="text/javascript">
jQuery('a#ab_dismiss').click(function(e) {
    e.preventDefault();
    jQuery('div#ab_admin_notice').hide(300);
    jQuery.ajax({
        url  : '<?php echo admin_url('admin-ajax.php'); ?>',
        data : {action: 'ab_dismiss_admin_notice'}
    });
});
</script>