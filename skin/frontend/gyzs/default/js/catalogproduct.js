jQuery(window).on("load", function () {

    var mpn = jQuery('.mobile-product-name-top').html(),
            vd = " ",
            mpi = jQuery('.mobile-product-img-box').html(),
            gar = jQuery('#garantiesservice').html(),
            pwd2 = jQuery(window).width();

    if (pwd2 <= 767)
    {
        jQuery('.mobile-product-name-top').show();
        jQuery('.mobile-product-name-top').html(mpn);
        if (!jQuery('.mobile-garanties').length)
        {
            if (jQuery('#relatedproducts').length) {
                jQuery('#relatedproducts').after('<div class="mobile-garanties"><div class="row"><div id="mobgaranties"></di></div></div>');
            } else {
                jQuery('.product-shop').after('<div class="mobile-garanties"><div class="row"><div id="mobgaranties"></di></div></div>');
            }
        }
        jQuery('#mobgaranties').html(gar);
        jQuery('#garantiesservice').hide();
        jQuery('#garantiesservice').html(vd);
        jQuery('#combi-table .related-cart-box').live("click", function () {
            jQuery(this).next().toggle();
            if (jQuery(this).hasClass('opened') == true) {
                jQuery(this).removeClass('opened');
                jQuery(this).next().show().animate({opacity: 0}, 300);
            } else if (jQuery(this).hasClass('opened') == false) {
                jQuery(this).addClass('opened');
                jQuery(this).next().show().animate({opacity: 1}, 300);
            }
        });
        jQuery('#super-product-table .related-cart-box').click(function () {
            jQuery(this).next().toggle();
            if (jQuery(this).hasClass('opened') == true) {
                jQuery(this).removeClass('opened');
                jQuery(this).css('height', 'auto');
            } else if (jQuery(this).hasClass('opened') == false) {
                jQuery(this).addClass('opened');
                var ht = jQuery(this).next().outerHeight();
                jQuery(this).css('height', ht);
            }
        });
    } else {

        jQuery('.mobile-product-name-top').hide();
        jQuery('.mobile-product-name-top').html(vd);
        jQuery('#garantiesservice').show();
        jQuery('#garantiesservice').html(gar);
        jQuery('#mobgaranties').hide();
        jQuery('#mobgaranties').html(vd);
    }
    if (pwd2 <= 1223 && pwd2 >= 768)
    {
        jQuery('.mobile-product-name-top').show();
        jQuery('.mobile-product-name-top').html(mpn);
        jQuery('#combi-table .related-cart-box').click(function () {
            if (jQuery(this).hasClass('opened') == false) {
                jQuery(this).next().show().animate({opacity: 1}, 300);
                jQuery(this).addClass('opened');
            } else if (jQuery(this).hasClass('opened') == true) {
                jQuery(this).removeClass('opened');
                jQuery(this).next().hide().animate({opacity: 0}, 300);
            }
        });
        jQuery('#super-product-table .related-cart-box').click(function () {
            if (jQuery(this).hasClass('opened') == false) {
                jQuery(this).next().show().animate({opacity: 1}, 300);
                jQuery(this).addClass('opened');
                var ht2 = jQuery(this).next().outerHeight();
                jQuery(this).css('height', ht2);
            } else if (jQuery(this).hasClass('opened') == true) {
                jQuery(this).removeClass('opened');
                jQuery(this).next().hide().animate({opacity: 0}, 300);
                jQuery(this).css('height', 'auto');
            }
        });
    }

    if (jQuery(window).width() <= 959 && jQuery(window).width() >= 768) {
        /*displaying only 5 attributes of associated products of group product in detail page*/
        jQuery('#super-product-table thead th div:nth-child(n+5)').hide();
        jQuery('#super-product-table thead th div:last-child').show();
        jQuery('#super-product-table tbody tr td .block-related-product div:nth-child(n+5)').hide();
    }
    if (jQuery(window).width() <= 767 && jQuery(window).width() >= 670) {
        /*displaying only 4 attributes of associated products of group product in detail page*/
        jQuery('#super-product-table thead th div:nth-child(n+5)').hide();
        jQuery('#super-product-table thead th div:last-child').show();
        jQuery('#super-product-table tbody tr td .block-related-product div:nth-child(n+5)').hide();
    }
    if (jQuery(window).width() <= 600) {
        /*displaying only 4 attributes of associated products of group product in detail page*/
        jQuery('#super-product-table thead th div:nth-child(n+4)').hide();
        jQuery('#super-product-table thead th div:last-child').show();
        jQuery('#super-product-table tbody tr td .block-related-product div:nth-child(n+4)').hide();
    }
    if (jQuery(window).width() <= 320) {
        /*displaying only 4 attributes of associated products of group product in detail page*/
        jQuery('#super-product-table thead th div:nth-child(n+4)').hide();
        jQuery('#super-product-table thead th div:last-child').hide();
        jQuery('#super-product-table tbody tr td .block-related-product div:nth-child(n+4)').hide();
    }

    /*product information accordion*/
    jQuery('#product-accordion dt').addClass('opened');
    jQuery('#product-accordion dt').next().show();

    jQuery('#product-accordion dt').click(function () {

        if (!jQuery(this).next().is(':visible')) {
            jQuery(this).next().slideDown().animate({opacity: 1}, 200);
            jQuery(this).addClass('opened');
            jQuery(this).removeClass('closed');
        } else {
            jQuery(this).addClass('closed');
            jQuery(this).removeClass('opened');
            jQuery(this).next().slideUp();
        }

    });
    
    jQuery('#product-accordion2 dt').addClass('opened');
    jQuery('#product-accordion2 dt').next().show();

    jQuery('#product-accordion2 dt').click(function () {
        if (!jQuery(this).next().is(':visible')) {
            jQuery(this).next().slideDown().animate({opacity: 1}, 200);
            jQuery(this).addClass('opened');
            jQuery(this).removeClass('closed');
        } else {
            jQuery(this).addClass('closed');
            jQuery(this).removeClass('opened');
            jQuery(this).next().slideUp();
        }

    });
    /*faq accordion */
    jQuery("#faq-accordian h3").click(function () {
        jQuery("#faq-accordian .faq-answer").slideUp();
        jQuery('#faq-accordian li').removeClass('active');
        /*slide down the link list below the h3 clicked - only if its closed*/
        if (!jQuery(this).next().is(":visible"))
        {
            jQuery(this).next().slideDown();
            jQuery(this).parent().addClass('active');
        }
    });
    /*add your review*/
    jQuery('#addreviewlink').click(function (e) {
        jQuery('html,body').animate({
            scrollTop: jQuery('#reviewform').position().top
        });
        e.preventDefault();
    });
    /*product reviews*/
    jQuery('.avgproreviews').click(function () {
        jQuery('#product-reviews').scrollToMe(150);
        return false;
    });
    /* dont show prev next links in recently viewed vertical slider when there are less than 3 products*/
    var liitems = jQuery('#recentlyviewed .block-content ul li').size();
    var check = liitems / 3;
    if (check <= 3) {
        jQuery('#recentlyviewed .prev').hide();
        jQuery('#recentlyviewed .next').hide();
    } else {
        jQuery('#recentlyviewed .prev').show();
        jQuery('#recentlyviewed .next').show();
    }
    var liitems = jQuery('#upsell-product-table .block-content ul li').size();
    var check = liitems / 3;
    if (check <= 3) {
        jQuery('#upsell-product-table .prev').hide();
        jQuery('#upsell-product-table .next').hide();
    } else {
        jQuery('#upsell-product-table .prev').show();
        jQuery('#upsell-product-table .next').show();
    }

    if (jQuery('body').hasClass('catalog-product-view') == true) {
        if (jQuery('#super-product-table').length > 0) {
            jQuery('body').addClass('groupedproduct');
        }
    }

    /*grouped product left hover box*/
    if (jQuery(window).width() >= 1224) {
        jQuery('#super-product-table.data-table tbody td').not(':last-child').addClass('tohover');
        jQuery('.tohover').live({
            mouseenter: function () {
                jQuery(this).parent().find('.product-detail-overlay').show();
            },
            mouseleave: function () {
                jQuery(this).parent().find('.product-detail-overlay').hide();
            }
        });
    }
    /*Product review section combined with other accordions for mobile*/
    if (jQuery(window).width() <= 767) {
        jQuery('.product-reviews-container .block-content').slideUp();
        jQuery('.product-reviews-container .block-title h2').addClass('closed');
        jQuery('.product-reviews-container .block-title h2').click(function () {
            jQuery(this).parent().next().slideToggle();
            if (jQuery(this).hasClass('closed')) {
                jQuery(this).removeClass('closed');
                jQuery(this).addClass('opened');
            } else {
                jQuery(this).removeClass('opened');
                jQuery(this).addClass('closed');
            }
        });
    }
    /*cloudzoom height*/
    var imght = jQuery('.product-img-box .product-image').outerHeight();
    jQuery('#jcl-demo .carousel').css('height', +imght - 50);

    if (jQuery(window).width() <= 767)
    {
        if (jQuery('.floatingcart').length) {
            jQuery('.floatingcart').hide();
            jQuery(window).scroll(function () {
                var mht = jQuery('.mark').height();
                var mark = jQuery('.mark').offset().top + mht;
                if (jQuery(window).scrollTop() > mark) {
                    jQuery('.floatingcart').fadeIn(100);
                } else {
                    jQuery('.floatingcart').fadeOut(100);
                }
            });
        }
    }

    /* Cache selectors scrolling tabs in product detail fixed header*/
    var lastId;
    var alheight = 0,
            topMenu = jQuery("#scrollspy"),
            /* All list items*/
            menuItems = topMenu.find("a"),
            /* Anchors corresponding to menu items*/
            scrollItems = menuItems.map(function () {
                var item = jQuery(jQuery(this).attr("href"));
                if (item.length) {
                    return item;
                }

            });
    menuItems.each(function () {
        var theirref = jQuery(this).attr('href');
        alheight = alheight + jQuery(theirref).height();
    });
    /* Bind click handler to menu items
     so we can get a fancy scroll animation*/
    menuItems.click(function (e) {
        var hrf = jQuery(this).attr('href');
        /*var negscrl = jQuery()*/
        jQuery('html, body').animate({
            scrollTop: jQuery(hrf).offset().top - 150});
        e.preventDefault();
    });
    /* Bind to scroll*/
    jQuery(window).scroll(function () {
        /* Get container scroll position*/
        var fromTop = jQuery(this).scrollTop();
        /* Get id of current scroll item*/
        var cur = scrollItems.map(function () {
            if (jQuery(this).offset().top - 180 < fromTop)
                return this;
        });
        /* Get the id of the current element*/
        cur = cur[cur.length - 1];
        var id = cur && cur.length ? cur[0].id : "";
        if (lastId !== id) {
            lastId = id;
            /* Set/remove active class*/
            menuItems
                    .parent().removeClass("active")
                    .end().filter("[href=#" + id + "]").parent().addClass("active");
        }
        if (fromTop > alheight - 700) {
            menuItems
                    .parent().removeClass("active")
                    .end().filter("[href=#" + id + "]").parent().removeClass("active");
        } else {
            menuItems
                    .parent().removeClass("active")
                    .end().filter("[href=#" + id + "]").parent().addClass("active");
        }

    });

    if (!mobile && jQuery(window).width() > 1224)
    {
        jQuery('.related-add-to-cart').live({
            mouseenter: function () {
                jQuery('.product-stock-container', this).show().animate({opacity: 1}, 200);
            },
            mouseleave: function () {
                jQuery('.product-stock-container', this).hide().animate({opacity: 0}, 200);
            }
        });
        jQuery('.block-related-product').live({
            mouseenter: function () {
                jQuery(this).children('.product-detail-overlay').show().fadeIn(3000);
            },
            mouseleave: function () {
                jQuery(this).children('.product-detail-overlay').hide().fadeOut(3000);
            }
        });
    }
    jQuery('.gyzsblog .block-content ul li .blog-title .wrdprs-title').children().addClass('wrdprs-title-link');
    jQuery('.gyzsblog .block-content ul li').live({
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
    /*in product detail page        */
    if (jQuery('.productdetail .checkstock.list').find('.green')) {
        jQuery('.productdetail .checkstock.list').children('.stock').addClass('greenstock');
    }
    jQuery('#review_field').focus(function ()
    {
        jQuery(this).animate({'height': '185px'}, 200);
        if (jQuery(this).hasClass('validation-failed') == true) {
            var fcsheight = jQuery(this).outerHeight();
            jQuery('span.frgtbar4').animate({'height': fcsheight - 2}, 200);
        }
        return false;
    });
    if (jQuery(window).width() <= 767) {
        jQuery('.floatingcart').live({
            click: function () {
                setTimeout(function () {
                    window.scrollBy(0, -100);
                }, 1000);
            }
        });
    }
});
jQuery(document).ready(function ($) {

    $('.mousetrap').live('click', function () {
        var images = [];
        images.push($('#cloud_zoom').attr('href'));
        $('a.cloud-zoom-gallery').each(function () {
            images.push($(this).attr('href'));

        });
        var imagloc = $(this).prev('a').attr('href');
        window.open(imagloc, '_blank');
        return false;
    });

    $('#leftdetail ul a[href*="#"]').click(function(event) {
    // On-page links
    if (
      location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') 
      && 
      location.hostname == this.hostname
    ) {
      // Figure out element to scroll to
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
      // Does a scroll target exist?
      if (target.length) {
        // Only prevent default if animation is actually gonna happen
        event.preventDefault();
        
        if($(window).width() > 767){
            $('html, body').animate({
                scrollTop: target.offset().top - 170 
            }, 1000 );
        }else{
            $('html, body').animate({
                scrollTop: target.offset().top - 10 
            }, 1000 );
        }      
      }
    }
  });

});
(function ($) {
  $(function () {
    $('.homeproductcarousel').slick({
      speed: 300,
      slidesToShow: 5,
      slidesToScroll: 1,
      draggable: false,
      prevArrow: '<button type="button" class="slick-prev slick-arrow fa fa-angle-left"></button>',
      nextArrow: '<button type="button" class="slick-next slick-arrow fa fa-angle-right"></button>',
      adaptiveHeight: true,
      responsive: [
        {
          breakpoint: 1025,
          settings: {
            slidesToShow: 4
          }
        },
        {
          breakpoint: 991,
          settings: {
            slidesToShow: 3
          }
        },
        {
          breakpoint: 767,
          settings: {
            slidesToShow: 1
          }
        },
        {
          breakpoint: 600,
          settings: {
            slidesToShow: 1
          }
        }
      ]
    });
  });
})(jQuery);