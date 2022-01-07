(function ($) {
    $(function () {
        $('.homeproductcarousel').slick({
            speed: 300,
            slidesToShow: 4,
            slidesToScroll: 1,
            draggable: false,
            prevArrow: '<button type="button" class="slick-prev slick-arrow fa fa-angle-left"></button>',
            nextArrow: '<button type="button" class="slick-next slick-arrow fa fa-angle-right"></button>',
            adaptiveHeight: true,
            responsive: [
                {
                    breakpoint: 1025,
                    settings: {
                        slidesToShow: 3
                    }
                },
                {
                    breakpoint: 991,
                    settings: {
                        slidesToShow: 2
                    }
                },
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 1
                    }
                }
            ]
        });
    });
})(jQuery);


jQuery(document).ready(function () {
    var acc = document.getElementsByClassName("custom-accordion");
    var i;
    for (i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function () {
            /* Toggle between adding and removing the "active" class,
             to highlight the button that controls the panel */
            this.classList.toggle("active");

            /* Toggle between hiding and showing the active panel */
            var panel = this.nextElementSibling;
            if (panel.style.display === "block") {
                panel.style.display = "none";
            } else {
                panel.style.display = "block";
            }
        });
    }
});