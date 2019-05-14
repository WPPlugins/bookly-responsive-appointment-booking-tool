jQuery(function ($) {
    'use strict';

    var $modal        = $('#bookly-create-service-modal'),
        $serviceTitle = $('#bookly-new-service-title', $modal),
        $serviceType  = $('#bookly-new-service-type', $modal),
        $saveBtn      = $('.bookly-js-save', $modal),
        $servicesList = $('#services-list')
    ;

    function format(option) {
        return option.id && option.element.dataset.icon ? '<i class="fa fa-fw ' + option.element.dataset.icon + '"></i> ' + option.text : option.text;
    }


    $serviceType.select2({
        minimumResultsForSearch: -1,
        width                  : '100%',
        theme                  : 'bootstrap',
        allowClear             : false,
        templateResult         : format,
        templateSelection      : format,
        escapeMarkup           : function (m) {
            return m;
        }
    });
    $modal.on('shown.bs.modal', function () {
        $serviceTitle.focus();
    });
    $saveBtn.on('click', function (e) {
        e.preventDefault();
        var ladda = Ladda.create(this),
            data  = $modal.serializeArray();
        ladda.start();
        $.post(ajaxurl, data, function (response) {
            if (response.success) {
                $servicesList.DataTable().ajax.reload();
                $serviceTitle.val('');
                $serviceType.val('simple').trigger('change');
                $modal.modal('hide');
            } else {
                booklyAlert({error: [response.data.message]});
            }
            ladda.stop();
        });
    });
});