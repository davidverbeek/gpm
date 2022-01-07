(function () {
    /*ipad and iphone fix*/
    if ((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i)) || (navigator.userAgent.match(/iPad/i))) {
        jQuery("#queldoreiNav li a").on({
            click: function () {
                if (!mobile && jQuery(this).parent().hasClass('parent')) {
                    if (!jQuery(this).hasClass('touched')) {
                        jQuery('#queldoreiNav a').removeClass('touched');
                        jQuery(this).parents('li').children('a').addClass('touched');
                        return false;
                    }
                }
            }
        });
        jQuery("#nav li a").on({
            click: function () {
                if (!mobile && jQuery(this).parent().hasClass('parent')) {
                    if (!jQuery(this).hasClass('touched')) {
                        jQuery('#nav a').removeClass('touched');
                        jQuery(this).parents('li').children('a').addClass('touched');
                        return false;
                    }
                }
            }
        });
        jQuery('.header-switch, .toolbar-switch').on({
            click: function (e) {
                if (jQuery(window).width() <= 1223) {
                    jQuery('.toolbar-dropdown', this).slideToggle();
                } else {
                    jQuery(this).addClass('over');
                }
            }
        });
    }

    jQuery('.onlyformobile .search-top').click(function () {
        jQuery('html ,body').animate({scrollTop: 0}, 800);
    });

    /* cart page */
    if (jQuery(window).width() > 1024) {
        jQuery('.cartedit').live({
            mouseenter: function () {
                jQuery('.cart-update-box', this).show().animate({opacity: 1}, 200);
            },
            mouseleave: function () {
                jQuery('.cart-update-box', this).hide().animate({opacity: 0}, 200);
            }
        });
    }
    if (jQuery(window).width() <= 1024) {
        jQuery(".cartedit a").live("click", function () {
            if (!jQuery(this).parent().children('.cart-update-box').is(':visible')) {
                jQuery(this).parent().children('.cart-update-box').css('display', 'block');
                jQuery('.miscbuttons span', this).css('display', 'block');
            }
        });
        jQuery('.miscbuttons').on("click touch", function () {
            jQuery(this).parent().hide();
        });
    }
    /* cart page end

    /* About us page segments scroll*/
    jQuery('#gyzstitle').click(function () {
        jQuery('html, body').animate({scrollTop: jQuery('#aboutustitle').offset().top - 120}, 500);
        return false;
    });
    jQuery('#menucontact').click(function () {
        jQuery('html, body').animate({scrollTop: jQuery('#aboutus-info').offset().top - 50}, 500);
        return false;
    });
    jQuery('#infoform').click(function () {
        jQuery('html, body').animate({scrollTop: jQuery('.aboutcontacttitle').offset().top - 80}, 500);
        return false;
    });
    jQuery('#aboutmap').click(function () {
        jQuery('html, body').animate({scrollTop: jQuery('#googleMap').offset().top - 150}, 500);
        return false;
    });

    jQuery('.block-blog .block-content ul li .blog-title .wrdprs-title').children().addClass('wrdprs-title-link');
    jQuery('.block-blog .block-content ul li').live({
        mouseenter: function () {
            jQuery('.blog-title', this).children().children('.wrdprs-title-link').css({'background': '#5b5b5b', 'color': '#fff'});
            jQuery('.short-description', this).css({'background-color': '#f3f3f3'});
            jQuery('.short-description a', this).addClass('hover');
        },
        mouseleave: function () {
            jQuery('.blog-title', this).children().children('.wrdprs-title-link').css({'background': '#f3f3f3', 'color': '#39ae37'});
            jQuery('.short-description', this).css({'background-color': '#fff'});
            jQuery('.short-description a', this).removeClass('hover');
        }
    });

    /*scroll to top when menu icon is clicked*/
    jQuery('.fixed-nav').click(function () {
        jQuery('html ,body').animate({scrollTop: 0}, 200);
    });
    /*hover on fav product file input*/
    jQuery('#importFavoriteProductsForm input').live({
        mouseenter: function () {
            jQuery(this).prev().addClass('hovered');
        },
        mouseleave: function () {
            jQuery(this).prev().removeClass('hovered');
        }
    });
    if (!mobile) {
        jQuery('.cart-top-container').live({
            mouseenter: function () {
                jQuery('.details_after', this).hide();
            }
        });
    }
    if (jQuery(window).width() <= 350) {
        if (jQuery("#checkout-progress-state").length) {
            jQuery(".formobile").click(function () {
                var loca = jQuery(this).children().attr("href");
                if (loca) {
                    window.location.href = loca;
                }
            });
            jQuery(".checkout-progress li").click(function () {
                if (jQuery(this).children('span.formobile')) {
                    var bloca = jQuery(this).children('span.formobile').children().attr("href");
                }
                if (bloca) {
                    window.location.href = bloca;
                }
            });
        }
    }
    jQuery('.form-forgotpassword .buttons-set button.button').click(function () {
        setTimeout(showbar, 500);
    });
    function showbar() {
        if (jQuery('.frgtcheck').hasClass('validation-failed') == true) {
            jQuery('span.frgtbar').fadeIn('2000');
        }
        if (jQuery('.frgtcheck').hasClass('validation-passed') == true) {
            jQuery('span.frgtbar').fadeOut('2000');
        }
    }
    if (jQuery(window).width() >= 1224) {
        jQuery('.favorite-right').live({
            mouseenter: function () {
                jQuery('.favorite-edit-container', this).show();
            },
            mouseleave: function () {
                jQuery('.favorite-edit-container', this).hide();
            }
        });
        jQuery('.quote-edit').live({
            mouseenter: function () {
                jQuery('.quote-update-box', this).show();
            },
            mouseleave: function () {
                jQuery('.quote-update-box', this).hide();
            }
        });
    }

    if (jQuery(window).width() <= 767) {
        jQuery('.favorite-edit').on('click touch', function () {
            if (!jQuery(this).next('.favorite-edit-container').is(':visible')) {
                jQuery(this).next('.favorite-edit-container').show();
            }
        });
        jQuery('.goback').on("click touch", function () {
            jQuery(this).parent().parent().hide();
        });
    }

    /* scroll the window upwards when any cart button is cliked*/
    if (jQuery(window).width() <= 767) {
        /*scrollTop: jQuery(window).scrollTop() - 50*/

        var iOS = (navigator.userAgent.match(/(iPad|iPhone|iPod)/g) ? true : false);
        if (!iOS) {
            jQuery('.btn-cart').each(function () {
                jQuery(this).live('click', function () {
                    setTimeout(function () {
                        window.scrollBy(0, -100);
                    }, 1000);
                });
            });
        } else {
            jQuery('.btn-cart').each(function () {
                jQuery(this).live('click', function () {
                    setTimeout(function () {
                        window.scrollBy(0, -100);
                    }, 1000);
                });
            });
        }
        jQuery("#toTop").on({
            click: function () {
                jQuery('.floatingcart').hide();
            }
        });

        var ViewportHeight = jQuery(window).height();
        if (jQuery('.data-table').hasClass('grouped-items-table')) {
            var MoveTableOffset = jQuery(".widetable .data-table").offset().top;
            var MoveTableOffsetPlus = MoveTableOffset + 200 - ViewportHeight;
        }

        jQuery(window).scroll(function (event) {
            var scrollPosition = jQuery(window).scrollTop();
            if (scrollPosition >= MoveTableOffsetPlus) {
                if (jQuery(".widetable .data-table").hasClass('MoveTable')) {
                } else {
                    jQuery(".widetable .data-table").addClass("MoveTable");
                }
            }
        });
    }
})(jQuery);