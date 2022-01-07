
jQuery(document).ready(function () {

    if (jQuery(".header-container").length > 0) {

        /* Scroll to bottom in Accordian Menu */
        jQuery(".navdown-arrow").click(function (e) {
            if (this.hash !== "") {
                e.preventDefault();

                var hash = this.hash;
                jQuery('#accordian > ul').animate({
                    scrollTop: jQuery(hash).offset().top
                }, 2000);
            }
        });

        /* Accordian Down arrow Enabled or Disabled */
        jQuery('#accordian > ul').bind('scroll', nav_scroll);
        function nav_scroll(e)
        {
            var elem = jQuery(e.currentTarget);
            if (elem[0].scrollHeight - elem.scrollTop() == elem.outerHeight()) {
                //jQuery(".navdown-arrow").addClass("disabled");
            } else {
                //jQuery(".navdown-arrow").removeClass("disabled");
            }
        }
    }

    function accordianmenuopen() {
        jQuery('#accordian').fadeIn().css({"left": "0px"});
        jQuery("<span class='accordianclose'></span>").insertAfter(("#accordian"));

        jQuery(".accordianclose").click(function (e) {
            e.preventDefault();
            accordianmenuclose();
        });
    }
    function accordianmenuclose() {
        jQuery('#accordian').css({"left": "-400px"}).fadeOut();
        jQuery(".accordianclose").remove();
    }
    jQuery(".grid_2.main-navigation, .menu-view-btn").click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        accordianmenuopen();
    });
    jQuery(".fixed-nav, .verticalnav > .toggle").click(function () {
        jQuery('body, html').animate({
            scrollTop: 0
        }, 500);
        accordianmenuopen();
    });
    jQuery('.main-menu-close').click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        accordianmenuclose();
    });
    jQuery('#accordian').insertAfter('.header-container');

    if (jQuery(window).width() > 767) {
        /* Accordian Height */
        function navmaxheight() {
            var accordianheight = jQuery(window).height() - jQuery(".header-container").height() - jQuery(".navdown-arrow").innerHeight();
            var accordian_innerheight = jQuery(window).height() - jQuery(".header-container").height();

            if (jQuery(".header-container").hasClass("fixed-head")) {
                var accordianheight = jQuery(window).height() - jQuery(".header-container").height() - jQuery(".navdown-arrow").innerHeight() - 14;
                var accordian_innerheight = jQuery(window).height() - jQuery(".header-container").height() - 14;
            }
        }

        navmaxheight();

        /* Header Height */
        function headerscroll() {
            var headheight = jQuery(".header-container:not('.fixed-head')").height();
            jQuery(".main-container").css("padding-top", headheight);
            jQuery(".header-container").addClass("desktop-header-fix");
        }
        ;

        jQuery(window).scroll(function () {
            headerscroll();
            navmaxheight();
        });
        jQuery(window).resize(function () {
            headerscroll();
            navmaxheight();
        });
    }

    /* Accordian, Search append in Header */
    if (jQuery(window).width() > 767) {

        jQuery(window).scroll(function () {
            var headerheight = jQuery('.header-container').height();
            searchContent = jQuery('.header-bottom .top-search');

            searchfixContent = jQuery('header .top-search');

            if (jQuery(window).scrollTop() > headerheight) {
                jQuery(searchContent).appendTo("header .grid_12");
            } else {
                jQuery(searchfixContent).appendTo(".header-bottom .row");
            }

            /* Top Offeset for third level category */
            var scroll = jQuery(window).scrollTop();
            jQuery('.level2-container').css('top', scroll);
            checkClass();
        });

        function checkClass() {
            var scroll = jQuery(window).scrollTop();
            if (jQuery('.level2-container').hasClass('gereedschap')) {
                jQuery('.level2-container.gereedschap').css('top', 0);
                jQuery('.level2-container.gereedschap .level3-container').css('top', scroll);
            }

            if (jQuery('.level3-container').hasClass('elektrisch-gereedschap')) {
                jQuery('.level3-container.elektrisch-gereedschap').css('top', -scroll);
            }
        }
    }

    /* Mobile Accordian Functionality */
    if (jQuery(window).width() <= 1024) {
        var menuhtml = jQuery(".col-left.sidebar #accordian, .main-navigation #accordian");
        jQuery(menuhtml).appendTo(".header-bottom");


        function menuresize() {
            jQuery(".header-bottom #accordian").width(jQuery(window).width());
            jQuery(".header-bottom #accordian").height(jQuery(window).height() - jQuery(".header-bottom").height() - 1);
        }
        menuresize();
        jQuery(window).resize(menuresize);
        jQuery('body').on('click', '.open-menu, li.parent > a', function (e) {
            e.preventDefault();
            var submenu = jQuery(this).parent().find("> .submenu-nav");
            e.preventDefault();
            if (submenu.is(":visible")) {
                submenu.slideUp();
                jQuery(this).removeClass('uparrow');
            } else {
                submenu.slideDown();
                jQuery(this).addClass('uparrow');
            }
        });

        jQuery(window).on('click', '.main-navigation .verticalnav', function () {
            jQuery("html, body").animate({scrollTop: 0}, 1000);

            var navmenu = jQuery(".header-bottom #accordian");
            if (navmenu.hasClass("active")) {
                navmenu.removeClass("active");
            } else {
                navmenu.addClass("active");
            }
        });
        jQuery(".main-navigation-closed .nav-close").click(function () {
            jQuery(".header-bottom #accordian").removeClass("active");
        });
    }
    if (jQuery(window).width() <= 767) {
        jQuery('.main-menu-close').click(function (e) {
            e.preventDefault();
            e.stopPropagation();
            jQuery('#accordian').css({"left": "-100%"}).fadeOut();
        });

        function cartContentbox() {
            cartContent = jQuery('header .grid_12 .cart-top-container');
            jQuery(cartContent).appendTo(".header-bottom > .row");
        };

        cartContentbox();
        jQuery(window).scroll(function () {
            cartContentbox();
        });
        jQuery(window).resize(function () {
            cartContentbox();
        });

        if (jQuery('.top-contain .customer-top span').hasClass('loggedin')) {
            jQuery('.top-contain .customer-top span').parent('a').addClass('loggedin-wrap');
        }
    }
});