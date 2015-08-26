jQuery(function($) {
    $('.panel a, .panel input, .panel button').on('click', function(e){
        e.preventDefault();
        $('#lite_notice').modal('show');
    });
});