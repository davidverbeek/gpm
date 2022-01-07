/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Kbase
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */

openGridRow = function (event) {
    var element = Event.findElement(event, 'tr');
    if (['a', 'input', 'select', 'option'].indexOf(Event.element(event).tagName.toLowerCase()) != -1) {
        return;
    }

    if (element.title) {
        setLocation(element.title);
    }
};

initGrid = function (containerId) {
    if ($(containerId)) {
        rows = $$('#' + containerId + ' tbody tr');
        for (var row = 0; row < rows.length; row++) {
            Event.observe(rows[row], 'click', openGridRow);
        }
    }
};

jQuery(window).on('load', function () {

    if (window.location.hash)
    {
        console.log('hash in url');
        if (jQuery('body').hasClass('kbase-article-index') == false && jQuery('body').hasClass('kbase-article-article') == false && jQuery('body').hasClass('kbase-article-tag') == false)
        {
            var hashname = window.location.hash.replace('#', '').replace('/', '');
        }
    }

    /*kbase accordion*/
    if (window.location.hash)
    {
        if (jQuery('.kbase-listing').find('#' + hashname))
        {
            jQuery('#' + hashname).prev().addClass('opened');
            jQuery('#' + hashname).show();
            jQuery('#' + hashname).addClass('active');
            jQuery('dd:not(#' + hashname + ')').hide();
            if (jQuery('body').hasClass('kbase-article-index') == false && jQuery('body').hasClass('kbase-article-article') == false && jQuery('body').hasClass('kbase-article-tag') == false) {
                if (!jQuery('.kbase-listing').find('#' + hashname).length) {
                    /*return;*/
                    jQuery('dd').each(function () {
                        if (jQuery(this).attr('id') == hashname) {
                            jQuery(this).prev().addClass('opened');
                            jQuery(this).show();
                            jQuery('html, body').animate({scrollTop: jQuery(this).offset().top - 200}, 400);
                        }
                    });
                } else {
                    jQuery('html, body').animate({scrollTop: jQuery('.kbase-listing').find('#' + hashname).offset().top - 150}, 400);
                }
            }
        }
    } else {
        jQuery('#kbase_category dt:first-child').addClass('opened');
        jQuery('#kbase_category dt:first-child').next().show();
        jQuery('#kbase_category dt:first-child').next().addClass('active');
        jQuery('#kbase_category dd:not(.active)').hide();

        jQuery('#kbase_tag dt:first-child').addClass('opened');
        jQuery('#kbase_tag dt:first-child').next().show();
        jQuery('#kbase_tag dt:first-child').next().addClass('active');
        jQuery('#kbase_tag dd:not(.active)').hide();

        jQuery('#kbase_article dt:first-child').addClass('opened');
        jQuery('#kbase_article dt:first-child').next().show();
        jQuery('#kbase_article dt:first-child').next().addClass('active');
        jQuery('#kbase_article dd:not(.active)').hide();

        jQuery('#kbase_search dt:first-child').addClass('opened');
        jQuery('#kbase_search dt:first-child').next().show();
        jQuery('#kbase_search dt:first-child').next().addClass('active');
        jQuery('#kbase_search dd:not(.active)').hide();
    }
    jQuery('#kbase_category dt.kbase-listing-term').click(function ()
    {
        var self = this;
        jQuery('#kbase_category dt').removeClass('opened');
        jQuery('#kbase_category dd').slideUp(300);

        if (!jQuery(this).next().is(':visible')) {
            jQuery(this).next().slideDown(300);
            jQuery(this).addClass('opened');
        } else {
            jQuery(this).removeClass('opened');
        }
        setTimeout(function () {

            theOffset = jQuery(self).offset().top;
            jQuery('html, body').animate({scrollTop: theOffset - 200}, "slow");
        }, 1000);


    });
    jQuery('#kbase_tag dt.kbase-listing-term').click(function ()
    {
        var self = this;
        jQuery('#kbase_tag dt').removeClass('opened');
        jQuery('#kbase_tag dd').slideUp(300);
        if (!jQuery(this).next().is(':visible')) {
            jQuery(this).next().slideDown(300);
            jQuery(this).addClass('opened');
        } else {
            jQuery(this).removeClass('opened');
        }
        setTimeout(function () {

            theOffset = jQuery(self).offset().top;
            jQuery('html, body').animate({scrollTop: theOffset - 200}, "slow");
        }, 1000);

    });
    jQuery('#kbase_article dt.kbase-listing-term').click(function ()
    {
        var self = this;
        jQuery('#kbase_article dt').removeClass('opened');
        jQuery('#kbase_article dd').slideUp(300);
        if (!jQuery(this).next().is(':visible')) {
            jQuery(this).next().slideDown(300);
            jQuery(this).addClass('opened');
        } else {
            jQuery(this).removeClass('opened');
        }
        setTimeout(function () {

            theOffset = jQuery(self).offset().top;
            jQuery('html, body').animate({scrollTop: theOffset - 200}, "slow");
        }, 1000);

    });
    jQuery('#kbase_search dt.kbase-listing-term').click(function ()
    {
        var self = this;
        jQuery('#kbase_search dt').removeClass('opened');
        jQuery('#kbase_search dd').slideUp(300)
        if (!jQuery(this).next().is(':visible')) {
            jQuery(this).next().slideDown(300);
            jQuery(this).addClass('opened');
        } else {
            jQuery(this).removeClass('opened');
        }
        setTimeout(function () {

            theOffset = jQuery(self).offset().top;
            jQuery('html, body').animate({scrollTop: theOffset - 200}, "slow");
        }, 1000);

    });
    /*for other settings -start*/
    jQuery(window).resize(function () {
        if (jQuery(window).width() >= 768 && jQuery(window).width() <= 1280) {
            if (jQuery('body').hasClass('kbase-article-index') == true || jQuery('body').hasClass('kbase-article-category') == true) {
                jQuery('.kbase-main-header-container').show();
            }
        } else if (jQuery(window).width() >= 1280) {
            if (jQuery('body').hasClass('kbase-article-index') == true) {
                jQuery('.kbase-main-header-container').hide();
            }
        } else if (jQuery(window).width() < 767) {
            if (jQuery('body').hasClass('kbase-article-index') == true) {
                jQuery('.kbase-main-header-container').hide();
            }
        }
    });
    /*for other settings -end
     table desing in articles*/
    jQuery('.kbase-listing-description.std table').each(function () {
        if (jQuery(this).children('thead').length <= 0) {
            jQuery(this).children('tbody').addClass('tablesearched');
        }
    });

    /*table scroll in articles*/
    jQuery('.kbase-listing-description.std table').each(function () {
        jQuery(this).wrap("<div class='widetable'></div>");
        if (jQuery(this).children('thead').length <= 0) {
            jQuery(this).children('tbody').addClass('tablesearched');
        }
    });
    
    jQuery("#newsletter-kbase").blur();
    /*only for desktop - articles listing - start*/
    var count = 0;
    var hght = 0;
    var maxhght = 0;
    var tabletheight = 0;
    var calchght = 0
    var calchghtres = 0
    var cntr = 0;
    var cntres = 0;

    jQuery('.kbase-main .kbase-block').each(function () {
        count++;
        if (jQuery(this).height() > maxhght) {
            maxhght = jQuery(this).height();
        }
        hght = hght + jQuery(this).height();
    });

    var avghght = Math.floor(hght / count);
    var ele = count / 3;
    var eleres = Math.floor(count / 2);
    var ele1 = Math.ceil(count / 3);

    jQuery('.kbase-block:lt(' + ele1 + ')').each(function () {
        calchght = calchght + jQuery(this).height();
        cntr++;
    });
    /*counting heights of first eleres elements*/
    jQuery('.kbase-block:lt(' + eleres + ')').each(function () {
        calchghtres = calchghtres + jQuery(this).height();
        cntres++;
    });


    var finalhght = maxhght * ele;
    var finalhght2 = avghght * ele;

    var finalhghtres = maxhght * eleres;
    if (jQuery(window).width() >= 1280) {
        if (jQuery('body').hasClass('kbase-article-index') == true) {
            jQuery('.kbase.kbase-main').css({'height': finalhght + 'px'});
        }
    }
    /*only for desktop - articles listing - end*/

    jQuery(function () {
        if (jQuery(window).width() < 1280) {
            var num_cols;

            if (jQuery(window).width() >= 1279) {
                num_cols = 3;
            } else if (jQuery(window).width() > 767 && jQuery(window).width() < 1279) {
                num_cols = 2;
            } else {
                num_cols = 1;
            }
            window.addEventListener("resize", function () {
                if (jQuery(window).width() >= 1280) {
                    num_cols = 3;
                } else if (jQuery(window).width() > 767 && jQuery(window).width() < 960) {
                    num_cols = 2;
                } else {
                    num_cols = 1;
                }
            });
            container = jQuery('.kbase-main'),
                    listItem = 'div.kbase-block',
                    listClass = 'sub-kbase-container';
            container.each(function () {
                var items_per_col = new Array(),
                        items = jQuery(this).find(listItem),
                        min_items_per_col = Math.floor(items.length / num_cols),
                        difference = items.length - (min_items_per_col * num_cols);

                for (var i = 0; i < num_cols; i++) {
                    if (i < difference) {
                        items_per_col[i] = min_items_per_col + 1;
                    } else {
                        items_per_col[i] = min_items_per_col;
                    }
                }
                for (var i = 0; i < num_cols; i++) {
                    jQuery(this).append(jQuery('<div ></div>').addClass(listClass));
                    for (var j = 0; j < items_per_col[i]; j++) {
                        var pointer = 0;
                        for (var k = 0; k < i; k++) {
                            pointer += items_per_col[k];
                        }
                        jQuery(this).find('.' + listClass).last().append(items[j + pointer]);
                    }
                }
            });
        }
    });

});
