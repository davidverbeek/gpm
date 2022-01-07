jQuery(window).on("load", function () {

    if (jQuery(window).width() <= 767) {
        jQuery('.widetable2 , .widetable').each(function () {
            var scrollFlag = 0;
            var otop = jQuery(this).offset().top - jQuery(window).height();
            var wdth = jQuery(this).children('table').width();
            jQuery(window).scroll(function () {
                var pTop = jQuery('body').scrollTop();
                if (pTop > otop) {
                    if (scrollFlag == 0) {
                        scrollFlag++;
                        jQuery(".widetable2, .widetable").animate({
                            scrollLeft: wdth
                        }, 5000);
                    }
                }
            });
        });
    }
    jQuery('.fltr-toggle').on({
        click: function () {
            if (!jQuery(this).next().is(':visible'))
            {
                jQuery("body").css("overflow", "hidden");
                
                var filterhide = jQuery('<span class="filteroverlay"></span>');
                jQuery(".toolbar .sortby").append(filterhide);
                
                jQuery('.res-fltr').fadeIn(100).css("left", "0");
            } else {
                jQuery('.filteroverlay').fadeOut().remove();
                
                jQuery("body").css("overflow", "scroll");
                
                jQuery('.res-fltr').css("left", "-280px").fadeOut();
            }
            
        }
    });
    jQuery(document).on('click', '.filteroverlay', function (e) {
        jQuery(this).fadeOut().remove();
        jQuery('.fltr-toggle').trigger('click');
        e.preventDefault();
    });
    jQuery(document).on('click', '.filterclose', function (e) {
        jQuery('.fltr-toggle').trigger('click');
        e.preventDefault();
    });
    jQuery(".block-layered-nav dd.categoryfilter").prependTo("#narrow-by-list");
    jQuery(".block-layered-nav dt.categoryfilterdt").prependTo("#narrow-by-list");
    jQuery('.block-layered-nav #narrow-by-list2 ol li').show();
    jQuery('.block-layered-nav #narrow-by-list2 ol').next().text(Shopper.text.less);
    jQuery('.block-layered-nav #narrow-by-list2 ol').next().removeClass('viewmore');
    jQuery('.block-layered-nav #narrow-by-list2 ol').next().addClass('viewless');

    /* Layered navigation */
    var fltr = jQuery('.block.block-layered-nav.topfilter').html();
    if (jQuery(window).width() <= 959) {
        jQuery('.res-fltr').html(fltr);
        jQuery('.block.block-layered-nav.topfilter').html('');
        jQuery('.toolbar-bottom .sortby').html('');
        jQuery(".res-fltr").on("click", ".shopby_more", function (e) {
            e.preventDefault();
            if (jQuery(this).html() == Shopper.text.more) {
                jQuery(this).prev().children().show();
                jQuery(this).removeClass('viewmore');
                jQuery(this).addClass('viewless');
                jQuery(this).html(Shopper.text.less);
            } else {
                jQuery(this).prev().children('li:nth-child(n+6)').hide();
                jQuery(this).html(Shopper.text.more);
                jQuery(this).removeClass('viewless');
                jQuery(this).addClass('viewmore');
            }
            //jQuery('html, body').animate({scrollTop: jQuery(this).prev().offset().top - 100}, "slow");
        });
    }
    jQuery(window).on("resize", function () {
        if (jQuery(window).width() <= 959) {
            jQuery('.block.block-layered-nav.topfilter').html('');
            jQuery('.res-fltr').html(fltr);
        } else if (jQuery(window).width() >= 960 && jQuery(window).width() <= 1279) {
            jQuery('.block.block-layered-nav.topfilter').html(fltr);
            jQuery('.res-fltr').html('');
        }
    });
    jQuery('.layernav dt').live("click", function () {
        if (!jQuery(this).next().is(':visible'))
        {
            jQuery(this).addClass('selected');
            jQuery(this).children('span.bfrfltr').css('display', 'none');
            jQuery(this).children('span.fltrarw').css({'transform': 'rotate(360deg)', 'display': 'block'});
        } else {
            jQuery(this).children('span.bfrfltr').css('display', 'block');
            jQuery(this).removeClass('selected');
            jQuery(this).children('span.fltrarw').css('display', 'none');
        }
        jQuery(this).next().slideToggle();
    });

    /*product-list view hover*/
    var mb = jQuery(window).width();
    if (mb > 1025) {
        jQuery('#product-associated-table tbody td').not(':last-child').addClass('tohoverlist');
        if (jQuery(window).width() >= 1025) {
            jQuery('#product-associated-table tbody td.tohoverlist').live({
                mouseenter: function () {
                    jQuery(this).parent().find('.product-list-overly').show();
                },
                mouseleave: function () {
                    jQuery(this).parent().find('.product-list-overly').hide();
                }
            });
        }
        jQuery('#product-associated-table tbody td:last-child').addClass('tohoverbutton');
        jQuery('#product-associated-table .tohoverbutton').live({
            mouseenter: function () {
                jQuery('.right-overlay', this).show();
            },
            mouseleave: function () {
                jQuery('.right-overlay', this).hide();
            }
        });
        jQuery('.product-list-view-right').live({
            mouseenter: function () {
                jQuery('.simple-overlay', this).show().animate({opacity: 1}, 200);
            },
            mouseleave: function () {
                jQuery('.simple-overlay', this).hide().animate({opacity: 0}, 200);
            }
        });
    } else if (mb <= 1024) {
        /*list view stock limit on click*/
        jQuery('#product-associated-table .btn-container span').click(function () {
            if (jQuery(this).parents('.btn-container').next().is(':visible') == false) {
                jQuery(this).parents('.btn-container').next().show().animate({opacity: 1}, 200);
                jQuery(this).addClass('opened');
            } else {
                jQuery(this).parents('.btn-container').next().hide().animate({opacity: 0}, 200);
                jQuery(this).removeClass('opened');
            }
        });

        jQuery('.product-list-view-right .btn-add-cart span').click(function (e) {
            if (jQuery(this).hasClass('opened') == false) {
                jQuery(this).parent().parent().next().show().animate({opacity: 1}, 200);
                jQuery(this).addClass('opened');
            } else {
                jQuery(this).parent().parent().next().hide().animate({opacity: 0}, 200);
                jQuery(this).removeClass('opened');
            }
            return false;
        });
    }

    if (mb >= 991 && mb <= 1224) {
        jQuery('.grp-text').parents('.product-list-view-left').addClass('grouped');
        if (mb >= 768 && mb <= 1024) {
            jQuery('.product-list-details h3:nth-child(n+6)').hide();
        }
        if (mb <= 767) {
            jQuery('.product-list-details h3:nth-child(n+5)').hide();
        }
    }
    if (mb <= 1224) {
        jQuery('.grp-text').parents('.product-list-view-left').addClass('grouped');
        if (mb >= 600 && mb <= 1024) {
            jQuery('.product-list-details h3:nth-child(n+6)').hide();
        }
        if (mb <= 599) {
            jQuery('.product-list-details h3:nth-child(n+5)').hide();
        }
    }

    if (jQuery(window).width() >= 1224) {
        var ht, liht = 0
        if (jQuery('.two_columns_3').length) {
            ht = jQuery('.newsingle').height() - 2;
            jQuery('.newsingle').css({"min-height": +ht + "px"});
            /* ul height for category products*/
            jQuery('ul.products-grid.two_columns_3 li:nth-child(4n+1)').each(function () {
                liht = liht + jQuery(this).outerHeight(true);
            });
            jQuery('ul.products-grid.two_columns_3').css('min-height', liht + 'px');
        }
    }
    /*layered navigation*/
    var opnl = jQuery('.m-filter-item-list'),
            opsec = opnl.find('li.m-selected-ln-item');
    if (opsec)
    {
        jQuery(opsec).parent().parent().css('display', 'block');
        jQuery(opsec).parent().parent().prev().children('span.bfrfltr').css('display', 'none');
        jQuery(opsec).parent().parent().prev().children('span.fltrarw').css({'transform': 'rotate(360deg)', 'display': 'block'})

    }
    jQuery('#narrow-by-list dd .m-selected-filter-item').each(function ()
    {
        jQuery(this).parent().parent().parent().prev().slideDown();
        jQuery(this).children('span.bfrfltr').css('display', 'none');
        jQuery(this).children('span.fltrarw').css({'transform': 'rotate(360deg)', 'display': 'block'})
    });



});/*onload*/

jQuery(function ($) {

    var config = {
        over: function () {
            if ($(window).width() <= 1223)
                return;
            if ($(this).hasClass('.toolbar-dropdown')) {
                $(this).parent().addClass('over');
            } else {
                $(this).addClass('over');
            }

            $('.toolbar-dropdown', this).animate({opacity: 1, height: 'toggle'}, 100);
        },
        timeout: 0, /*// number = milliseconds delay before onMouseOut*/
        out: function () {
            if (mobile)
                return;
            var that = this;
            $('.toolbar-dropdown', this).animate({opacity: 1, height: 'toggle'}, 100, function () {/*DD++ 22032018*/
                if ($(this).hasClass('.toolbar-dropdown')) {
                    $(that).parent().removeClass('over');
                } else {
                    $(that).removeClass('over');
                }
            });
        }
    };
    $('.toolbar-switch, .toolbar-switch .toolbar-dropdown').hoverIntent(config);

    var config = {
        over: function () {
            $('.back_img', this).css('opacity', 0).show().animate({opacity: 1}, 200);
        },
        timeout: 100, /*// number = milliseconds delay before onMouseOut*/
        out: function () {
            $('.back_img', this).animate({opacity: 0}, 200);
        }
    };
    $('.products-list .product-image').hoverIntent(config);

    /*//show more in layered nav*/
    if ($('.block-layered-nav').length && Shopper.shopby_num) {
        $('.block-layered-nav ol').each(function (i, v) {
            if ($('li', this).length > Shopper.shopby_num) {

                $(this).next().text(Shopper.text.more);
                var that = this;
                $('li:gt(' + (Shopper.shopby_num - 1) + ')', this).hide();
                $('.col-left, .col-right').masonry('reloadItems');
                $(this).next()
                        .css('display', 'block')
                        .click(function (e) {
                            e.preventDefault();
                            $('li:gt(' + (Shopper.shopby_num - 1) + ')', that).slideToggle();
                            if ($(this).text() === Shopper.text.more) {
                                $(this).text(Shopper.text.less);
                                $(this).removeClass('viewmore');
                                $(this).addClass('viewless');
                            } else {
                                $(this).text(Shopper.text.more);
                                $(this).removeClass('viewless');
                                $(this).addClass('viewmore');
                            }
                            $('.col-left, .col-right').masonry('reloadItems');
                            return false;
                        });
            } else {
                jQuery(this).next().hide();
            }
        });
    }
});


/* Tollbar Sticky */
jQuery(document).ready(function () {

    if (jQuery(window).width() <= 767) {
        var didScrollTool;
        var lastScrollTop = 0;
        var deltatool = 1;

        var headerheight = jQuery(".header-container .inner-header").height();
        var breadcrumb = jQuery(".col-main .breadcrumbs").innerHeight();
        var toolbarheight = jQuery(".category-top .toolbar .sorter").innerHeight();
        var offsettoptoolbar = headerheight + breadcrumb + toolbarheight;

        jQuery(window).scroll(function (event) {
            didScrollTool = true;
        });
        setInterval(function () {
            if (didScrollTool) {
                hasScrolledToolbar();
                didScrollTool = false;
            }
        }, 250);
        function hasScrolledToolbar() {
            var st = jQuery(this).scrollTop();
            /* Make sure they scroll more than delta*/
            if (Math.abs(lastScrollTop - st) <= deltatool)
                return;
            if (st > lastScrollTop && st > offsettoptoolbar) {
                jQuery('.category-top .toolbar').removeClass('toolbar-down').addClass('toolbar-up');

                if (jQuery(window).scrollTop() > offsettoptoolbar) {
                    if (jQuery(".category-top .toolbar").hasClass("toolbar-up")) {
                        jQuery(".category-top .toolbar").removeClass("toolbar-fixed");
                    }
                }
            } else {
                /*Scroll Up*/
                if (st + jQuery(window).height() < jQuery(document).height()) {
                    jQuery('.category-top .toolbar').addClass('toolbar-down').removeClass('toolbar-up');

                    if (jQuery(window).scrollTop() > offsettoptoolbar) {
                        if (jQuery(".category-top .toolbar").hasClass("toolbar-down")) {
                            jQuery(".category-top .toolbar").addClass("toolbar-fixed");
                        }
                    }
                    if (jQuery(window).scrollTop() < offsettoptoolbar) {
                        if (jQuery(".category-top .toolbar").hasClass("toolbar-down")) {
                            jQuery(".category-top .toolbar").removeClass("toolbar-fixed");
                        }
                    }
                }
            }
            lastScrollTop = st;
        }
    }
});