(function ($, window) {
    'use strict';
    $('document').ready(function () {
        $('.remove_image').on('click', function (e) {
            e.preventDefault();
            let position = $(this).closest('.single-image').data('option_id');
            let image_data = {
                action: "slideshow_image_remove",
                position: position
            }
            jQuery.post(wp_slideshow_admin_ajax.ajax_url, image_data, function (response) {
                if (response.data.success) {
                    $(".wp_img_sortable").html( response.data.html );
                }
            });
        });

        $('#all_img_sortable').sortable({
            connectWith: ".wp_img_sortable",
            update: function (e, ui) {
                let new_positions = $(this).sortable('toArray').toString();
                let image_data = {
                    action: "slideshow_image_rearrange",
                    positions: new_positions
                }
                jQuery.post(wp_slideshow_admin_ajax.ajax_url, image_data, function (response) {
                    if (response.data.success) {
                        $(".wp_img_sortable").html( response.data.html );
                    }
                });


            }
        }).disableSelection();
    });
})(jQuery, window);