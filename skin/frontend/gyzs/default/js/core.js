$j = jQuery.noConflict();
var cdnUrl = 'https://cdn.gyzs.nl';
$j(document).ready(function () {

    $j(".bookmarkme a").click(function (e) {

        e.preventDefault();

        if (window.sidebar) { // Mozilla Firefox Bookmark
            window.sidebar.addPanel(location.href, document.title, "");
        } else if (window.external) { // IE Favorite
            window.external.AddFavorite(location.href, document.title);
        }
        else if (window.opera && window.print) { // Opera Hotlist
            this.title = document.title;
            return true;
        }
    });

    var postvar;


	
	
    /* STOCK CHECK */	
	
	var UnusedGetvoorraadCall = [ 1, "/multistepcheckout/index/login/", 2, "/customer/account/create/", 3, "/multistepcheckout/index/login/", 4, "/multistepcheckout/index/register/", 5, "/multistepcheckout/index/product/", 6, "/multistepcheckout/index/payment/",7, "/merk", 8, "/" ];
	
	var noGetvoorraadcall=jQuery.inArray(location.pathname, UnusedGetvoorraadCall);
	
	if(noGetvoorraadcall !="-1"){
	}
	else
	{
		checkStock();
		//checkStock();
	}	
	
    //checkStock();

    /* TODO, gewenste hoeveelheid checken tegen daadwerkelijke voorraad */

    function keyup(string)
    {

        if (!$j.isNumeric($j(this).val()))
            return false;
        if ($j(this).val().length <= 0)
            return false;

        var id = $j(this).parent().find('.hiddenstock').attr('title');

        if (!id) {
            id = $j('.hiddenstock').attr('title');
        }
        var truestock = parseInt($j('#stock_qty_' + id).val());

        if ($j('.' + id + ' input').length > 0)
            instockDeleveryDays = parseInt($j('.' + id + ' input').val());

        if ($j('.hover_' + id + ' input').length > 0) {
            instockDeleveryDays = parseInt($j('.hover_' + id + ' input').val());
        }
        /*
         * Author: Helios
         * Date : 22-july-2014
         * Desc : fetch out of product Status
         */

        var stockstatus = parseInt($j('#stock_qty_status_' + id).val());
        var idealeverpakking = $j('#stock_qty_ideal_' + id).val();
        var verkoopeenheid = $j('#stock_qty_verkoopeenheid_' + id).val().toLowerCase();
        var afwijkenidealeverpakking = $j('#stock_qty_afwijkenidealeverpakking_' + id).val();
        var leverancier = $j('#leverancier_' + id).val();
        var productid = $j('#hiddenproductid_' + id).val();
        var qty = parseInt($j(this).val());
        var backorder = 0;


        var stockLabel = verkoopeenheid.split("|");

        if (qty == 1)
        {
            verkoopeenheid = stockLabel[0];
        }
        else
        {
            verkoopeenheid = stockLabel[1];
        }
        if ($j(this).hasClass('grid')) {
            var url = $j('#cart_button_' + id).attr('data-url');
            if (url.search('qty') < 0) {
                url = url + 'qty/' + qty + '/';
                $j('#cart_button_' + id).attr('data-url', url);
            } else {
                var newUrl = 'qty/' + qty + '/';
                url = url.replace(url.slice(url.search('qty')), newUrl);
                $j('#cart_button_' + id).attr('data-url', url);

            }
        }
        //if (stockstatus == 6 && (truestock - qty) < 0) {
        if (stockstatus == 6) {
            $j("#cart_button_" + id).hide();
            $j(".button-cart-"+ id).hide();
        }
        else {
            $j("#cart_button_" + id).show();
            $j(".button-cart-"+ id).show();
        }
        if (qty > truestock) {
            backorder = qty - truestock;

            if (backorder == 1)
            {
                verkoopeenheid = stockLabel[0];
            }
            else
            {
                verkoopeenheid = stockLabel[1];
            }
            if (truestock == 0) {
                if (stockstatus == 6) {
                    verkoopeenheid = stockLabel[0];
                    $j('.' + id).html('<span class="stock"> (U kunt niet meer dan ' + truestock + ' ' + verkoopeenheid + ' <span id="besteld-term-' + id + '" class="besteld-info">bestellen</span>)</span>');

                } else {
                    if (leverancier == 3797) {

                        /* Added by ankita for stuck start */
                        var levertijd = $j('.' + id + ' .now-order').html();
                        if(backorder==1){
                            $j('.' + id).html('<span class="yellow"></span><span class="stock">' + backorder + ' ' + verkoopeenheid + ' wordt besteld </span>');
                        }else{
                            $j('.' + id).html('<span class="yellow"></span><span class="stock">' + backorder + ' ' + verkoopeenheid + ' worden besteld </span>');
                        }

                        if (qty == 1)
                        {
                            verkoopeenheid = stockLabel[0];
                        }
                        else
                        {
                            verkoopeenheid = stockLabel[1];
                        }
                        var hstock;
                        if (afwijkenidealeverpakking != 0) {
                            hstock = ' ' + verkoopeenheid;

                        }
                        else {
                            if(idealeverpakking!=1){
                                verkoopeenheid = stockLabel[1];
                                hstock = ' x ' + idealeverpakking + ' ' + verkoopeenheid;
                            }
                            else{
                                hstock = ' ' + verkoopeenheid;
                            }
                        }

                        $j('.stock-label-' + id).html(hstock);

                        /* Added by ankita for stuck end */
                        return false;
                    } else {
                        var testObj = $j("#super-product-table").find('.hover_' + id);
                        var levertijd = $j('.' + id + ' .now-order').html();
                        if (testObj.length >= 1) {
                            if(backorder==1){
                                $j('.hover_' + id).html('<span class="yellow"></span><span class="stock">' + backorder + ' ' + verkoopeenheid + ' wordt besteld </span>');
                            }
                            else{
                                $j('.hover_' + id).html('<span class="yellow"></span><span class="stock">' + backorder + ' ' + verkoopeenheid + ' worden besteld </span>');
                            }
                        } else {
                            if(backorder==1){
                                $j('.' + id).html('<span class="yellow"></span><span class="stock">' + backorder + ' ' + verkoopeenheid + ' wordt besteld </span>');
                            }else{
                                $j('.' + id).html('<span class="yellow"></span><span class="stock">' + backorder + ' ' + verkoopeenheid + ' worden besteld </span>');
                            }

                        }
                    }
                }
                if (qty == 1)
                {
                    verkoopeenheid = stockLabel[0];
                }
                else
                {
                    verkoopeenheid = stockLabel[1];
                }
                var hstock;
                if (afwijkenidealeverpakking != 0) {
                    hstock = ' ' + verkoopeenheid;

                }
                else {
                    if(idealeverpakking!=1){
                        verkoopeenheid = stockLabel[1];
                        hstock = ' x ' + idealeverpakking + ' ' + verkoopeenheid;
                    }
                    else{
                        hstock = ' ' + verkoopeenheid;
                    }
                }

                $j('.stock-label-' + id).html(hstock);
            } else {
                if (backorder == 1)
                {
                    verkoopeenheid = stockLabel[0];
                }
                else
                {
                    verkoopeenheid = stockLabel[1];
                }
                if (stockstatus == 6) {
                    $j('.' + id).html('<span class="yellow"></span><span class="stock">slechts ' + truestock + ' ' + verkoopeenheid + ' leverbaar</span><span class="now-order">Artikel verdwijnt uit assortiment</span>');
                }
                else {

                    var hstock;
                    if (afwijkenidealeverpakking != 0) {
                        hstock = ' ' + verkoopeenheid;
                    }
                    else {
                        //hstock = ' x ' + idealeverpakking + ' ' + verkoopeenheid;
                        if(idealeverpakking!=1){
                            verkoopeenheid = stockLabel[1];

                            hstock = ' x ' + idealeverpakking + ' ' + verkoopeenheid;
                        }
                        else{
                            hstock = ' ' + verkoopeenheid;
                        }
                    }
                    if ($j(this).hasClass('grouped')) {
                        $j('.' + id).html('<span class="stock">' + calculateQty(truestock, idealeverpakking, verkoopeenheid, afwijkenidealeverpakking) + ' </span><span class="stock yellow"> (' + backorder + hstock + ' worden <span id="yellow-backorder-term-' + id + '" class="backorder-info">besteld </span>)</span>');
                    }
                    else {
                        if (leverancier == 3797 && truestock <= 0) {
                            return false;
                        } else {
                            var testObj = $j("#super-product-table").find('.hover_' + id);
                            if (testObj.length >= 1) {
                                if(backorder==1){
                                    $j('.hover_' + id).html('<span class="yellow"></span><span class="stock"> ' + backorder + hstock + ' wordt <span id="backorder-term-' + id + '" class="yellow-backorder-info">besteld</span><span class="now-order">verzending: binnen ' + instockDeleveryDays + ' werkdagen</span>');
                                }else{
                                    $j('.hover_' + id).html('<span class="yellow"></span><span class="stock grpyellow"> ' + backorder + hstock + ' worden <span id="backorder-term-' + id + '" class="yellow-backorder-info">besteld</span><span class="now-order">verzending: binnen ' + instockDeleveryDays + ' werkdagen</span>');
                                }

                            } else {
                                if(backorder==1){
                                    $j('.' + id).html('<span class="yellow"></span><span class="stock"> ' + backorder + hstock + ' wordt <span id="backorder-term-' + id + '" class="yellow-backorder-info">besteld</span><span class="now-order">verzending: binnen ' + instockDeleveryDays + ' werkdagen</span>');
                                }
                                else{
                                    $j('.' + id).html('<span class="yellow"></span><span class="stock"> ' + backorder + hstock + ' worden <span id="backorder-term-' + id + '" class="yellow-backorder-info">besteld</span><span class="now-order">verzending: binnen ' + instockDeleveryDays + ' werkdagen</span>');
                                }

                            }
                        }

                    }
                    if (qty == 1)
                    {
                        verkoopeenheid = stockLabel[0];
                    }
                    else
                    {
                        verkoopeenheid = stockLabel[1];
                    }
                    var hstock;
                    if (afwijkenidealeverpakking != 0) {
                        hstock = ' ' + verkoopeenheid;
                    }
                    else {
                        //hstock = ' x ' + idealeverpakking + ' ' + verkoopeenheid;
                        if(idealeverpakking!=1){
                            verkoopeenheid = stockLabel[1];
                            hstock = ' x ' + idealeverpakking + ' ' + verkoopeenheid;
                        }
                        else{
                            hstock = ' ' + verkoopeenheid;
                        }
                    }

                    $j('.stock-label-' + id).html(hstock);


                }
            }
        } else if (truestock == 0) {
            if (!smalldisplay) {
                $j('#' + id).html('<span class="stock yellow">Geen voorraad</span>');
            } else {
                //$j('#' + id).html('<span class="stock yellow"><img src="/skin/frontend/gyzs/default/images/stock_yellow.png" /></span>');
                $j('#' + id).html('<span class="stock yellow"></span>');
                Tips.list.invoke('hide');
                Tips.add(id, '<p><span class="stock yellow">Geen voorraad</span></p>', {
                    showOn: 'creation',
                    hideOn: 'click',
                    style: 'slick',
                    target: true,
                    stem: true,
                    tipJoint: ['center', 'bottom']
                });
            }
        } else {
            if ($j(this).hasClass('grouped')) {
                $j('.' + id).html('<span class="stock">' + calculateQty(truestock, idealeverpakking, verkoopeenheid, afwijkenidealeverpakking) + '</span>');
            }
            else {
                if (leverancier == 3797 && truestock <= 0) {
                    return false;
                } else {
                    var hstock;
                    if (afwijkenidealeverpakking != 0) {
                        hstock = ' ' + verkoopeenheid;
                    }
                    else {
                        //hstock = ' x ' + idealeverpakking + ' ' + verkoopeenheid;
                        if(idealeverpakking!=1){
                            verkoopeenheid = stockLabel[1];

                            hstock = ' x ' + idealeverpakking + ' ' + verkoopeenheid;
                        }
                        else{
                            hstock = ' ' + verkoopeenheid;
                        }
                    }
                    $j('.stock-label-' + id).html(hstock);
                    var testObj = $j("#super-product-table").find('.hover_' + id);
//                    alert(test_obj.length);
                    //if($j('#'+id).hasClass('hover_' + id)){ alert('hi') ;
                    if (testObj.length >= 1) {
                        $j('.hover_' + id).html('<span class="stock stocklabel">' + calculateQty(truestock, idealeverpakking, stockLabel, afwijkenidealeverpakking) + ' <span class="stocktext">op voorraad</span></span><span class="now-order">' + qty + ' ' + hstock + ' direct leverbaar</span>');
                    } else {
                        $j('.' + id).html('<span class="stock stocklabel">' + calculateQty(truestock, idealeverpakking, stockLabel, afwijkenidealeverpakking) + ' <span class="stocktext">op voorraad</span></span><span class="now-order">' + qty + ' ' + hstock + ' direct leverbaar</span>');
                    }

                }
            }
        }
        $j('.group_' + id).val(qty);

    }

    $j('.qty').keyup(function () {
        keyup.call(this);

    });
    $j('.qty').live('keyup', function () {
        keyup.call(this);
        //Tips.add('backorder-term', '<p><span style="font-family: verdana,geneva; font-size: small;">Een backorder houdt in dat wij van dit artikel niet voldoende voorraad beschikbaar hebben en zelf een bestelling zullen plaatsen bij een leverancier. Bestellingen met een backorder worden pas verzonden zodra de gehele bestelling compleet is.&nbsp;Uw bestelling heeft hierdoor een langere levertijd. </span></p>', { style: 'slick', target: true, stem: true, tipJoint: [ 'center', 'bottom' ] });
    });

    $j('.truncated_description_trigger').click(function (e) {
        e.preventDefault();
        $j('.truncated_description').css('display', 'none');
        $j('.truncated_description.open').css('display', 'block');
    })

    $j('.grouped_product_link').live('click', function (e) {
        e.preventDefault();

        var id = $j(this).attr('id');

        id = id.substring(8);

        $j('.list_grouped_' + id).html('<span style="width:555px;height:100px;line-height:50px;text-align:center;vertical-align:baseline;display:block;">Bezig met laden<br /><img src="' + cdnUrl + '/skin/frontend/gyzs/default/images/ajax-loader.gif" /></span>');

        if (!$j('.list_grouped_' + id).hasClass('full')) {

            $j.post('/price/index/getgroupedproducts/', {'id': id, 'full': true}, function (output) {
                $j('.list_grouped_' + id).addClass('full');
                $j('.list_grouped_' + id).html(output);

                $j('.list_grouped_' + id).ready(function () {
                    checkStock();
                })
            });

        } else {

            $j.post('/price/index/getgroupedproducts/', {'id': id, 'full': false}, function (output) {
                $j('.list_grouped_' + id).removeClass('full');
                $j('.list_grouped_' + id).html(output);

                $j('.list_grouped_' + id).ready(function () {
                    checkStock();
                })
            });

        }

    })


    $j('.desc_popup_link').live({
        mouseover: function () {
            $j(this).find('.desc_popup').show();
        },
        mouseout: function () {
            $j(this).find('.desc_popup').hide();
        }
    });
});

function checkStock() {
    /* STOCK CHECK */
    var data = [];
    var i = 0;

    $j('.checkstock').each(function () {
        if($j(this).hasClass('manualproduct')){
            return;
        }
        $j(this).html('<span style="width:105px;height:15px;text-align:center;vertical-align:baseline;"><img src="' + cdnUrl + '/skin/frontend/gyzs/default/images/ajax-loader.gif" /></span>');

        var subdata = {};

        subdata.id = $j(this).attr('id');

        if ($j(this).hasClass('groupedlist') || $j(this).hasClass('relatedlist')) {
            subdata.type = 'small';
        } else {
            subdata.type = '';
        }
        data.push(subdata);
    });
    $j.post('/price/index/getvoorraad/', {'data': data}, function (json) {


        if (json.length > 0) {
            $j.each(json, function (i) {

                if(json[i].Artinr=='') { return; }

                var idealeverpakking = $j('#stock_qty_ideal_' + json[i].Artinr).val(); 
                
                idealeverpakking = idealeverpakking.replace(",", ".");
                var verkoopeenheid = $j('#stock_qty_verkoopeenheid_' + json[i].Artinr).val().toLowerCase();
                var afwijkenidealeverpakking = $j('#stock_qty_afwijkenidealeverpakking_' + json[i].Artinr).val();
                var qty = json[i].VoorHH;
                var stockstatus = parseInt($j('#stock_qty_status_' + json[i].Artinr).val());
                var leverancier = $j('#leverancier_' + json[i].Artinr).val();
                var productid = $j('#hiddenproductid_' + json[i].Artinr).val();

                var stockLabel = verkoopeenheid.split("|");
                if (qty == 1)
                {
                    verkoopeenheid = stockLabel[0];
                }
                else
                {
                    verkoopeenheid = stockLabel[1];
                }
                //if (stockstatus == 6 && (qty) <= 0) {
                if (stockstatus == 6) {
                    if ($j(this).hasClass('simpleproduct')) {
                        $j("#cart_button_" + json[i].Artinr).hide();
                    }
                    else {
                        $j("#cart_button_" +  json[i].Artinr).hide();
                        $j(".button-cart-" +  json[i].Artinr).hide();
                    }
                }
                else {
                    $j("#cart_button_" +  json[i].Artinr).show();
                }

                if (!isNaN(idealeverpakking)) {
                    if (leverancier == 3797 && qty <= 0) {
                        //  $j('#' + json[i].Artinr).html(json[i].text);
                        $j('.' + json[i].Artinr).html(json[i].text); // Based on class
                    } else {

                        //$j('#' + json[i].Artinr).html(calculateQty(qty, idealeverpakking, verkoopeenheid, afwijkenidealeverpakking));
                        //$j('#' + json[i].Artinr).append(json[i].text);
                        //alert($j(this));
                        if ($j('.' + json[i].Artinr).hasClass('grouped')) {
                            // $j('.' + json[i].Artinr).html('<span class="stock">' + calculateQty(truestock, idealeverpakking, verkoopeenheid, afwijkenidealeverpakking) + '</span>');
                            var stockText=calculateQty(qty, idealeverpakking, verkoopeenheid, afwijkenidealeverpakking);
                            
                            $j('.' + json[i].Artinr).html(stockText);
                            if(stockText=='<span class="yellow"></span>')
                                $j('.' + json[i].Artinr).append(json[i].text);

                        }

                        else {
                            $j('.' + json[i].Artinr).html(calculateQty(qty, idealeverpakking, verkoopeenheid, afwijkenidealeverpakking));
                            $j('.' + json[i].Artinr).append(json[i].text);
                        }
                        if ($j('.hover_' + json[i].Artinr)) {
                            $j('.hover_' + json[i].Artinr).html(calculateQty(qty, idealeverpakking, verkoopeenheid, afwijkenidealeverpakking));
                            $j('.hover_' + json[i].Artinr).append(json[i].text);

                        }
                    }


                } else {
                    //  $j('#' + json[i].Artinr).html(json[i].text);
                    $j('.' + json[i].Artinr).html(json[i].text);
                }
                $j('#stock_qty_' + json[i].Artinr).val(qty);
                if (json[i].VoorHH > 0) {
                    $j('#checkstocklabel_' + json[i].Artinr).html('<div class="ship24-image label"></div>');
                }

            });
        } else {
            /*** No results returned, set all divs to error ***/
            $j('.checkstock').each(function () {
                if($j(this).hasClass('manualproduct') == true){
                    return;
                }
                $j(this).html('<span style="font-size:10px;color:#ccc;height:15px;">Voorraad niet beschikbaar</span>');
            });
        }
    }, 'json');

}
/*
 * Author: Helios
 * Date : 24-july-2014
 * Desc : Create a new function for calculate the Available Qty
 */
function calculateQty(qty, idealeverpakking, stockLabel, afwijkenidealeverpakking) {

    if(stockLabel instanceof Array){
        if (qty == 1)
        {
            verkoopeenheid = stockLabel[0];
        }
        else
        {
            verkoopeenheid = stockLabel[1];
        }
    }
    else{
        verkoopeenheid= stockLabel;
    }
    if (afwijkenidealeverpakking != 0 || idealeverpakking == 1) {
        if (qty > 0) {
            if (qty <= 2) {
                return '<span class="green"></span><span class="stock">nog maar ' + qty + ' ' + verkoopeenheid + '&nbsp;</span>';
            } else {
                return '<span class="green"></span><span class="stock">' + qty + ' ' + verkoopeenheid + '&nbsp;</span>';
            }
        } else {
            return '<span class="yellow"></span>';
        }

        return "";

    }
    else {

        if (qty > 0) {
            idealeverpakking = idealeverpakking.replace(".", ",");
            if (qty <= 2) {
                return '<span class="green"></span><span class="stock">' + qty + ' x ' + idealeverpakking + ' ' + verkoopeenheid + '&nbsp;</span>';
            } else {
                return '<span class="green"></span><span class="stock">' + qty + ' x ' + idealeverpakking + ' ' + verkoopeenheid + '&nbsp;</span>';
            }
        } else {
            return '<span class="yellow"></span>';
        }
        return "";

    }


}


