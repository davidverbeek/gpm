jQuery(window).on("load", function () {
    /*//Read more using readmore.js*/
//    jQuery('.morecontent2').readmore({
//        speed: 1200,
//        collapsedHeight: 130,
//        startOpen: true,
//        moreLink: "<a href='#'>" + Translator.translate("Read full description") + "</a>",
//        lessLink: "<a href='#'>" + Translator.translate("Read less description") + "</a>",
//        afterToggle: function (trigger, element, expanded)
//        {
//            var ht = jQuery('header').height();
//            if (!expanded) { /*// The "Close" link was clicked*/
//                jQuery('html, body').animate({scrollTop: jQuery('#pi-desc').offset().top - ht}, 300);
//                jQuery('.morecontent2').next().removeClass('opened');
//            } else {
//                jQuery('.morecontent2').next().addClass('opened');
//            }
//        }
//    });

    jQuery('.morecontent2 p').each(function () {
        if (jQuery(this).html().length <= 0) {
            jQuery(this).addClass('emmptydes');
        }
    });

    jQuery("ul#product-attribute-specs-table li:nth-child(n+5)").hide();

    jQuery('.view-all-specs span').on({
        click: function () {
            if (jQuery(this).hasClass('allspecs')) {
                jQuery("ul#product-attribute-specs-table li:nth-child(n+5)").slideUp(800);
                jQuery(this).html(Translator.translate('View all specifications'));
                jQuery(this).removeClass('allspecs');
                jQuery('html, body').animate({scrollTop: jQuery('.specifications').offset().top - 250});
                jQuery(this).parent().removeClass('noshadow');
            } else {
                jQuery("ul#product-attribute-specs-table li:nth-child(n+5)").slideDown(1000);
                jQuery('.view-all-specs span').html(Translator.translate('View less specifications'));
                jQuery(this).addClass('allspecs');
                jQuery(this).parent().addClass('noshadow');
            }
        }
    });

    jQuery("ul#product-downloads-table li:nth-child(n+5)").hide();

    jQuery('.view-all-dwns span').on({
        click: function () {
            if (jQuery(this).hasClass('alldwns')) {
                jQuery("ul#product-downloads-table li:nth-child(n+5)").slideUp(800);
                jQuery(this).html(Translator.translate('View all downloads'));
                jQuery(this).removeClass('alldwns');
                jQuery('html, body').animate({scrollTop: jQuery('.downloads').offset().top - 250});
                jQuery(this).parent().removeClass('noshadow');

            } else {
                jQuery("ul#product-downloads-table li:nth-child(n+5)").slideDown(1000);
                jQuery('.view-all-dwns span').html(Translator.translate('View less downloads'));
                jQuery(this).addClass('alldwns');
                jQuery(this).parent().addClass('noshadow');
            }
        }
    });

    var shwcnt;
    shwcnt = jQuery('#product-attribute-specs-table li').size();

    if (shwcnt <= 4) {
        jQuery('.view-all-specs').hide();
    }

    jQuery('.details-count').html(shwcnt);
    jQuery('.spec-count').html("(" + shwcnt + ")");

    var dwncnt = jQuery('#product-downloads-table li').size();

    if (dwncnt <= 5) {
        jQuery('.view-all-dwns').hide();
    }

    jQuery('.downloads-count').html(dwncnt);
    jQuery('.dwn-count').html("(" + dwncnt + ")");

    var revcnt = jQuery('#customer-reviews li').size();
    jQuery('.rev-count').html("(" + revcnt + ")");

    jQuery(window).resize(function () {
        var wd = jQuery(window).width();
        if (wd <= 320) {
            jQuery('.productdetail .add-to-box .add-to-cart .button.btn-cart').html('<span><span>' + Translator.translate('Buy') + '</span></span>');
        } else {
            jQuery('.productdetail .add-to-box .add-to-cart .button.btn-cart').html('<span><span>' + Translator.translate('Click & Buy') + '</span></span>');
        }
    });


    var wd = jQuery(window).width();
    if (wd <= 320) {
        jQuery('.productdetail .add-to-box .add-to-cart .button.btn-cart').html('<span><span>' + Translator.translate('Buy') + '</span></span>');
    } else {
        jQuery('.productdetail .add-to-box .add-to-cart .button.btn-cart').html('<span><span>' + Translator.translate('Click & Buy') + '</span></span>');
    }
});

jQuery('#pi-desc').click(function () {
    setTimeout(desme, 1);
});

function desme() {
    jQuery('.morecontent2').readmore('destroy');
    incme();
}

function incme() {
    if (jQuery(window).width() <= 767) {
        jQuery('.descandimg').readmore({
            speed: 1200,
            collapsedHeight: 130,
            startOpen: true,
            moreLink: "<a href='#'>" + Translator.translate('Read full description') + "</a>",
            lessLink: "<a href='#'>" + Translator.translate('Read less description') + "</a>",
            afterToggle: function (trigger, element, expanded) {
                var ht = jQuery('header').height();
                if (!expanded) {
                    jQuery('html, body').animate({scrollTop: jQuery('#pi-desc').offset().top - ht}, 300);
                    jQuery('.descandimg').next().removeClass('opened');
                } else {
                    jQuery('.descandimg').next().addClass('opened');
                }
            }
        });
    } else {
        jQuery('.morecontent2').readmore({
            speed: 1200,
            collapsedHeight: 130,
            startOpen: true,
            moreLink: "<a href='#'>" + Translator.translate('Read full description') + "</a>",
            lessLink: "<a href='#'>" + Translator.translate('Read less description') + "</a>",
            afterToggle: function (trigger, element, expanded) {
                var ht = jQuery('header').height();
                if (!expanded) {
                    jQuery('html, body').animate({scrollTop: jQuery('#pi-desc').offset().top - ht}, 300);
                    jQuery('.morecontent2').next().removeClass('opened');
                } else {
                    jQuery('.morecontent2').next().addClass('opened');
                }
            }
        });
    }
}
jQuery(window).on("load", function (){
    incme();
});

//jQuery(document).ready(rating_position);
//jQuery(window).on('resize', rating_position);
//
//function rating_position() {
//	var wd = jQuery(window).width();
//	if (wd < 1025) {
//		if (jQuery('.product-name-top').prev().attr('class') == "ratings") {
//		} else {
//			jQuery('.ratings').insertBefore(".product-name-top");
//		}
//	} else {
//		jQuery('.ratings').insertBefore(".mobile-product-img-box");
//	}
//}