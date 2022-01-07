jQuery(window).on("load", function ()
{
    /* Hide Header on on scroll down and show on scroll up */
    if (jQuery(window).width() <= 767)
    {
        var didScroll;
        var lastScrollTop = 0;
        var delta = 1;
        var navbarHeight = jQuery('header').height();
        var navbardiv = jQuery('.header-container').height();
        jQuery(window).scroll(function (event) {
            didScroll = true;
        });
        setInterval(function () {
            if (didScroll) {
                hasScrolled();
                didScroll = false;
            }
        }, 250);
        function hasScrolled()
        {
            var st = jQuery(this).scrollTop();
            /* Make sure they scroll more than delta*/
            if (Math.abs(lastScrollTop - st) <= delta)
                return;
            /* If they scrolled down and are past the navbar, add class .nav-up.
             This is necessary so you never see what is "behind" the navbar.*/
            if (st > lastScrollTop && st > navbarHeight) {
                /* Scroll Down*/
                jQuery('header').removeClass('nav-down').addClass('nav-up');

                if (jQuery('body').hasClass('catalog-product-view')) {
                    jQuery('.floatingcart').css('top', '30px');
                }
            } else {
                /*Scroll Up*/
                if (st + jQuery(window).height() < jQuery(document).height()) {
                    jQuery('header').removeClass('nav-up').addClass('nav-down');

                }
                if (jQuery('body').hasClass('catalog-product-view')) {
                    var hdrht = jQuery('header').height();
                    jQuery('.floatingcart').css('top', +hdrht + 'px');
                }
            }

            if (st > lastScrollTop && st > navbardiv) {
                //Added by Anil
                jQuery('.header-container').removeClass('header-down').addClass('header-up');
            } else {
                /*Scroll Up*/
                if (st + jQuery(window).height() < jQuery(document).height()) {
                    //Added by Anil
                    jQuery('.header-container').removeClass('header-up').addClass('header-down');
                }
            }
            lastScrollTop = st;
        }
    }

    /*check win8 os*/
    if (navigator.userAgent.indexOf("Windows NT 6.2") != -1)
    {
        jQuery('span.now-order').css({'font-size': '15px !important'});
    }

    /*Green color for stock status in bestelling*/
    if (jQuery('.your-order-list .checkstock-cart.list').find('.green')) {
        jQuery('.your-order-list .checkstock-cart.list').children('.stock').addClass('greenstock');
    }

    /* Display error message block after update*/
    jQuery('.messages, .header-container .top-row').insertBefore('.inner-header');

    jQuery('.messages').css('display', 'block');

    if (!jQuery('ul.messages li ul').parent().hasClass('row')) {
        jQuery('ul.messages li ul').wrap('<div class="row"></div>');
    }
    jQuery('.header-container .error-msg, .header-container .note-msg, .header-container .notice-msg, .header-container .success-msg').css('display', 'block');

    if (jQuery.browser.msie)
    {
        jQuery("#newsletter").val(Translator.translate("Type here you name")).addClass('hasplaceholder');
        jQuery("#newsletter").focus(function ()
        {
            var inpf = jQuery(this).val();
            jQuery(this).addClass('hasplaceholdern');
            if (jQuery(this).val() == Translator.translate("Type here you name") || jQuery(this).val() == '') {
                jQuery(this).val(' ');
            } else {
                jQuery(this).addClass('hasplaceholdern');
                jQuery(this).val() == inpf;
            }
        });
        jQuery("#newsletter").blur(function ()
        {
            var inp = jQuery(this).val();
            if (inp == Translator.translate("Type here you name") || inp == '') {
                jQuery(this).removeClass('hasplaceholdern');
                jQuery(this).val(Translator.translate("Type here you name"));
            } else {
                jQuery(this).addClass('hasplaceholdern');
                jQuery(this).val() == inp;
            }
        });
    }
});

var sw, sh, scroll_critical,
        breakpoint = 959,
        mobile = false,
        resizeLimits = [0, 479, 767, 959, 1199, 9999],
        _resizeLimit = {};
isResize = function (limitName) {
    var current, w = jQuery(window).width();
    for (i = 0; i < resizeLimits.length; i++) {
        if (w > resizeLimits[i]) {
            current = i;
        } else {
            break;
        }
    }
    if (_resizeLimit[limitName] === undefined || current != _resizeLimit[limitName]) {
        _resizeLimit[limitName] = current;
        return true;
    }
    return false;
}

jQuery(function ($) {
    if (Shopper.totop) {
        $().UItoTop({scrollSpeed: 1000});
    }

    function header_transform() {
        window_y = $(window).scrollTop();
        if (window_y > jQuery(".header-container").height()) {
            if (!($("header").hasClass("fixed"))) {
                $("header").addClass("fixed");
                $(".header-container").addClass("fixed-head");
            }
        } else {
            if (($("header").hasClass("fixed"))) {
                $("header").removeClass("fixed");
                $(".header-container").removeClass("fixed-head");
                $(".header-wrapper").height($("header").height());
            }
        }
    }

    $(window).resize(function () {
        sw = $(window).width();
        sh = $(window).height();
        mobile = (sw > breakpoint) ? false : true;
        if (!Shopper.responsive) {
            mobile = false;
        }

        /*menu_transform*/
        if (!($("header").hasClass("fixed")))
            $(".header-wrapper").height($("header").height());
        scroll_critical = parseInt($(".header-container").height());
        if (Shopper.fixed_header)
            header_transform();
        if (!isResize('grid_header'))
            return;
//        fixGridHeight();
    });
    $(window).scroll(function () {
        if (Shopper.fixed_header)
            header_transform();
    });
    /*//cart dropdown*/
    var config = {
        over: function () {
            if (mobile)
                return;
            $('.cart-top-container .details').show().animate({opacity: 1}, 200);
        },
        timeout: 200, /* // number = milliseconds delay before onMouseOut*/
        out: function () {
            if (mobile)
                return;
            $('.cart-top-container .details').hide().animate({opacity: 0}, 200);
        }
    };
    $("div.cart-top-container").hoverIntent(config);
    /*//service dropdown*/
    var config = {
        over: function () {
            if (mobile)
                return;
            $('.service-top-container .details').animate({opacity: 1, height: 'toggle'}, 200);
        },
        timeout: 200, /*// number = milliseconds delay before onMouseOut*/
        out: function () {
            if (mobile)
                return;
            $('.service-top-container .details').animate({opacity: 0, height: 'toggle'}, 200);
        }
    };
    $("div.service-top-container").hoverIntent(config);
    /*customer dropdown*/
    var config = {
        over: function () {
            if (mobile)
                return;
            $('.customer-top-container .details').animate({opacity: 1, height: 'toggle'}, 200);
        },
        timeout: 200, /*// number = milliseconds delay before onMouseOut*/
        out: function () {
            if (mobile)
                return;
            $('.customer-top-container .details').animate({opacity: 0, height: 'toggle'}, 200);
        }
    };
    $("div.customer-top-container").hoverIntent(config);
    /*//category dropdown*/
    var config = {
        over: function () {
            if (mobile)
                return;
            $('.category-dropdown').animate({opacity: 1, height: 'toggle'}, 200);
        },
        timeout: 200,
        out: function () {
            if (mobile)
                return;
            $('.category-dropdown').animate({opacity: 0, height: 'toggle'}, 200);
        }
    };
    $("a.thcategory").hoverIntent(config);
    /*//fix grid items height*/
    function fixGridHeight() {
        $('.products-grid').each(function () {
            var items_in_row = Math.floor($(this).width() / $('li.item', this).outerWidth(true));
            var height = [], row = 0;
            $('li.item', this).each(function (i, v) {
                $('div.product-info', this).css('height', 'auto');
                var h = $('div.product-info', this).height();
                if (!height[row]) {
                    height[row] = h;
                } else if (height[row] && h > height[row]) {
                    height[row] = h;
                }
                if ((i + 1) / items_in_row == 1)
                    row++;
            });
            row = 0;
            $('li.item', this).each(function (i, v) {
                $('div.product-info', this).height(height[row]);
                if ((i + 1) / items_in_row == 1)
                    row++;
            });
        });
    }
//    fixGridHeight();
    var config = {
        over: function () {
            if ($(this).hasClass('.header-dropdown')) {
                $(this).parent().addClass('over');
            } else {
                $(this).addClass('over');
            }
            $('.header-dropdown', this).show().animate({opacity: 1}, 100);
        },
        timeout: 0, /*// number = milliseconds delay before onMouseOut*/
        out: function () {
            var that = this;
            $('.header-dropdown', this).hide().animate({opacity: 0}, 100, function () {
                if ($(this).hasClass('.header-dropdown')) {
                    $(that).parent().removeClass('over');
                } else {
                    $(that).removeClass('over');
                }
            });
        }
    };
    $('.header-switch').live({
        mouseenter: function () {
            $('.header-dropdown', this).show().animate({opacity: 1}, 100);
        },
        mouseleave: function () {
            $('.header-dropdown', this).hide().animate({opacity: 0}, 100);
        }
    });
    var wth = jQuery(window).width();
    $('.products-grid .item').live({
        mouseenter: function () {
            if (wth <= 1024) {
                return;
            }
            $(this).addClass('no-shadow');
            $('.toshow', this).show();
            $('.associatedbox', this).addClass('associatedbox-hover');
        },
        mouseleave: function () {
            if (wth <= 1024) {
                return;
            }
            $(this).removeClass('no-shadow');
            $('.toshow', this).hide();
            $('.associatedbox', this).removeClass('associatedbox-hover');
        }

    })


    if ($('body').hasClass('customer-account-login') || $('body').hasClass('customer-account-forgotpassword') || $('body').hasClass('customer-account-create')) {
        function positionFooter() {
            if (mobile)
                return;
            if (!$("#sticky-footer-push").length) {
                $(".footer-container").before('<div id="sticky-footer-push"></div>');
            }
            var docHeight = $(document.body).height() - $("#sticky-footer-push").height();
            if (docHeight < $(window).height()) {
                var diff = $(window).height() - docHeight - 5;
                $("#sticky-footer-push").height(diff);
            }
        }
        $(window).scroll(positionFooter).resize(positionFooter);
    }

    $(window).load(function () {
        if ($(".block-slideshow .block-slider").length) {
            $(".block-slideshow .block-slider")
                    .flexslider({
                        animation: "slide",
                        slideshow: true,
                        useCSS: false,
                        touch: true,
                        video: false,
                        animationLoop: true,
                        mousewheel: false,
                        keyboard: false,
                        smoothHeight: false,
                        slideshowSpeed: 7000,
                        animationSpeed: 600,
                        pauseOnAction: true,
                        pauseOnHover: true,
                        controlNav: true,
                        directionNav: false,
                        start: function (s) {
                            $('.col-left, .col-right').masonry('reloadItems');
                        }
                    });
        }
        if ($(".block-login .block-slider").length) {
            $(".block-login .block-slider")
                    .flexslider({
                        animation: "slide",
                        slideshow: false,
                        useCSS: false,
                        touch: true,
                        video: false,
                        keyboard: false,
                        animationLoop: false,
                        smoothHeight: false,
                        animationSpeed: 600,
                        controlNav: false,
                        directionNav: false,
                        start: function (s) {
                            $(window).resize();
                        }
                    });
            $('#forgot-password').click(function () {
                $(".block-login .block-slider").flexslider("next");
                return false;
            });
            $('#back-login').click(function () {
                $(".block-login .block-slider").flexslider("prev");
                return false;
            });
            if ($('body').hasClass('customer-account-forgotpassword')) {
                $('#forgot-password').click();
            }
        }
    });
    if (typeof CONFIG_REVOLUTION !== 'undefined') {
        if ($.fn.cssOriginal != undefined)   /*// CHECK IF fn.css already extended*/
            $.fn.css = $.fn.cssOriginal;
        $('.fullwidthbanner').revolution(CONFIG_REVOLUTION);
    }

    if ($('.col-left').length)
        $('.col-left').masonry({itemSelector: '.block', isResizable: true, isAnimated: true});
    if (!$('body').hasClass('checkout-onepage-index') && $('.col-right').length)
        $('.col-right').masonry({itemSelector: '.block', isResizable: true, isAnimated: true});
    $(window).load(function () {
        if ($('.col-left').length)
            $('.col-left').masonry('reloadItems');
        if (!$('body').hasClass('checkout-onepage-index') && $('.col-right').length)
            $('.col-right').masonry('reloadItems');
    });
});

// for checkout minicart sidebar view all prodcuts

jQuery(document).ready(function () {

    jQuery('ol.mini-products-list-checkout').each(function () {

        var LiN = jQuery(this).find('li').length;

        console.log("LiN " + LiN);

        if (LiN > 3) {
            jQuery('li', this).eq(2).nextAll().hide().addClass('toggleable');
            //jQuery(this).append('<li class="more">More...</li>');    
        } else {
            jQuery('.view-all-products').hide();
        }

    });

    jQuery('.view-all-products').on('click', '.more', function () {

        if (jQuery(this).hasClass('less')) {

            jQuery(this).removeClass('less');

            jQuery(this).find('.more-text').html('Bekijk alle artikelen');
            jQuery(this).find('.counter-text').show();

        } else {

            jQuery(this).addClass('less');
            jQuery(this).find('.more-text').html('Bekijk minder artikelen');
            jQuery(this).find('.counter-text').hide();

        }

        jQuery('ol.mini-products-list-checkout li.toggleable').slideToggle();

    });

    // Rating placing change in iPad and mobile
    function ratingchange() {
        if (jQuery(window).width() <= 1024) {
            var pdprarating = jQuery('.product-shop-info > .ratings');
            jQuery(pdprarating).prependTo(".product-view .product-name-top");
        } else {
            var pdprarating = jQuery('.product-view .product-name-top .ratings');
            jQuery(pdprarating).prependTo(".product-shop-info");
        }
    }
    ratingchange();
    jQuery(window).resize(function () {
        ratingchange();
    });

    // Quote mouse over functionality
    /* cart page */
    if (jQuery(window).width() > 1024) {

        jQuery('.over-qoute-wrap').live({
            mouseenter: function () {
                console.log('times count');
                jQuery('.quote-update-box', this).show().animate({opacity: 1}, 200);
            },
            mouseleave: function () {
                jQuery('.quote-update-box', this).hide().animate({opacity: 0}, 200);
            }
        });
    }

    if (jQuery(window).width() <= 1024) {
        jQuery(".over-qoute-wrap .quote-edit").live("click", function () {
            if (!jQuery(this).parent().children('.quote-update-box').is(':visible')) {
                jQuery(this).parent().children('.quote-update-box').css('display', 'block');
                jQuery('.miscbuttons span', this).css('display', 'block');
            }
        });
        jQuery('.miscbuttons').on("click touch", function () {
            jQuery(this).parent().hide();
        });
    }

    if (jQuery(window).width() > 1199) {
        setTimeout(function () {
            jQuery('#loyaltylion').parent().addClass('loyaltylion-wrapper');
            jQuery('.loyaltylion-wrapper').insertAfter('.header-container #headerwrapper header .row.clearfix');
            jQuery('.catalog-product-view #headerwrapper header .product-fixed-header').children('.loyaltylion-wrapper').hide();
        }, 5000);
    }

    // Whole product block click functionality
    if (jQuery(window).width() < 767) {
        if (jQuery('.item .item-inner .regular, .slick-slide .block-related-product, .slick-slide .rsntlyvdbasic').length > 0) {
            jQuery("body").on('click', '.item .item-inner .regular, .slick-slide .block-related-product, .slick-slide .rsntlyvdbasic', function (e) {
                e.preventDefault();
                $element = jQuery(this).find(".product-name");
                window.location = $element.attr('href');
            });
        }
    }

//    if (jQuery(window).width() < 1025) {
//        // Group Product Filter functionality
//        jQuery('.filter-link').on({
//            click: function () {
//                if (!jQuery(this).parent().next().is(':visible'))
//                {
//                    jQuery("body").css("overflow", "hidden");
//
//                    var filterhide = jQuery('<span class="filteroverlay"></span>');
//                    jQuery(".product-main-container .product-view").append(filterhide);
//
//                    jQuery('.res-fltr').fadeIn(100).css("left", "0");
//                } else {
//                    jQuery('.filteroverlay').fadeOut().remove();
//
//                    jQuery("body").css("overflow", "scroll");
//
//                    jQuery('.res-fltr').css("left", "-280px").fadeOut();
//                }
//
//            }
//        });
//        jQuery(document).on('click', '.filteroverlay', function (e) {
//            jQuery(this).fadeOut().remove();
//            jQuery('.filter-link').trigger('click');
//            e.preventDefault();
//        });
//        jQuery(document).on('click', '.filterclose', function (e) {
//            jQuery('.filter-link').trigger('click');
//            e.preventDefault();
//        });
//    }
});

// Cookie package set - PP 23052018
jQuery(document).ready(function () {

    var selectedCookie = localStorage.getItem("acceptance_cookie");
    if (selectedCookie == null) {
        selectedCookie = 'true';
    }
    document.cookie = "acceptance_cookie = " + selectedCookie + ';path=/';

    if (selectedCookie == 'false') {
        jQuery("#cookie_setting_standard").prop('checked', true);
    } else {
        jQuery("#cookie_setting_personal").prop('checked', true);
    }
});


function validateNumber(evt) {
    var theEvent = evt || window.event;
    var keyVal = theEvent.keyCode || theEvent.which;

    // Handle paste
    if (theEvent.type === 'paste') {
        key = event.clipboardData.getData('text/plain');
    } else {
        // Handle key press
        var key = theEvent.keyCode || theEvent.which;
        key = String.fromCharCode(key);
    }
    var regex = /[0-9\b\t]|\./;
    // Backspace and Tab and Enter
    if (!regex.test(key)) {
        theEvent.returnValue = false;
        if (theEvent.preventDefault)
            theEvent.preventDefault();
    }
}