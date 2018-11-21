jQuery(function($) {
    $('.collapse').collapse('hide');

    $('#bookly_import_file').change(function() {
        if($(this).val()) {
            $('#bookly_import').submit();
        }
    });
});