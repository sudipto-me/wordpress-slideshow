(function ($, window) {
    'use strict';
    $(document).ready(function () {
        new Swiper('.swiper', {
            pagination: {
                el: '.swiper-pagination',
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            scrollbar: {
                el: '.swiper-scrollbar',
            },
            slidesPerView: 1,
            spaceBetween: 10

        });
    });
})(jQuery, window);