$j = jQuery.noConflict();

// to use cdn url for loading image
var CDN_URL = 'https://cdn.gyzs.nl';

$j(document).ready(function() {
//alert("asd");
    $j(".bookmarkme a").click(function(e) {

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

    $j('#search').keyup(function(event) {
		
		if (event.which == 13) {
            event.preventDefault();
            return false;   
        }

        $j('#customsearchbutton').attr('disabled', 'disabled'); /* DISABLE BUTTON UNTIL RETURN FROM SEARCH */

        if (postvar) {
            postvar.abort();
        }

        var searchval = $j(this).val();

        postvar = $j.post('/jaagers/search.php?mode=json', {searchterm: searchval}, function(data) {

            if (data != null && data.length!=0) {

                var html = '<table class="main_search_result_table"><tr class="search_result_header"><td class="img">&nbsp;</td><td class="artikelnr"><strong>Artikelnr</strong></td><td class="description"><strong>Productnaam</strong></td><td class="artikelnr"><strong>Lev.Art.nr</strong></td><td class="category"><strong>Productgroep</strong></td></tr>';
                html += '<tr class="nohover"><td colspan="4" style="height:10px;"></td></tr>';
                var catcnt = 0;

                $j.each(data.results, function(i, item) {
                    html += '<tr><td class="img">' + item.productimg + '</td><td class="artikelnr">' + item.skuurl + '</td><td class="description">' + item.producturl + '</td><td class="artikelnr">' + item.leverancierartikelnrurl + '</td><td class="category">' + item.categoryurl + '</td></tr>';
                });

                html += '</table>';

                html += '<hr />';

                var merkcnt = 0;
                $j.each(data.merken, function() {
                    merkcnt++;
                });

                if (merkcnt > 0) {
                    var tableclass = 'half';
                }

                html += '<table class="' + tableclass + '"><tr class="search_result_header"><td class="img">&nbsp;</td><td class="artikelnr"><strong>Categorie</strong></td><td class="artikelnr">&nbsp;</td><td class="category"><strong>Aantal resultaten</strong></td></tr>';

                $j.each(data.cats, function(i, item) {
                    if (catcnt < 5) {
                        html += '<tr><td class="img">&nbsp;</td><td class="description">' + item.caturl + '</td><td class="artikelnr">&nbsp;</td><td class="category">' + item.count + '</td></tr>';
                    }
                    catcnt++;
                });

                html += '</table>';

                if (merkcnt > 0) {

                    merkcnt = 0;

                    html += '<table class="' + tableclass + ' borderleft" style="min-height:180px;display:block;"><tr class="search_result_header"><td class="img">&nbsp;</td><td class="artikelnr"><strong>Merknaam</strong></td><td class="artikelnr">&nbsp;</td><td class="category"><strong>Aantal resultaten</strong></td></tr>';

                    $j.each(data.merken, function(i, item) {
                        if (merkcnt < 5) {
                            html += '<tr><td class="img">&nbsp;</td><td class="description">' + item.merkurl + '</td><td class="artikelnr">&nbsp;</td><td class="category">' + item.count + '</td></tr>';
                        }
                        merkcnt++;
                    });

                    html += '</table>';

                }

                $j('#searchvalueq').val(searchval)
                $j('#ids').val(data.ids);
                $j('#searchvalue').val(searchval);

                $j('#topCartMask').show();
                $j('.form-search').css('z-index', '100');
                $j('#customsearch_autocomplete').html(html);
                $j('#customsearch_autocomplete').removeClass('hidden');
                $j('#customsearch_autocomplete').addClass('visible');

                /*setZindex(Array('#leftcats', 'div.dimblock', '.overlaymask' ,'.form-search'), 5000);*/
                $j('.overlaymask').stop().css('display', 'block').animate({"opacity": "1"}, 300);

                setTimeout(function() {
                    $j('#customsearchbutton').removeAttr('disabled');
                }, 1000); /* ENABLE SEARCH BUTTON */

            } else {

                $j('#ids').val(null);

                $j('#topCartMask').hide();
                /*setZindex(Array('#leftcats', 'div.dimblock', '.overlaymask'), 0);*/
                $j('.overlaymask').stop().css('display', 'block').animate({"opacity": "0"}, 300);
                $j('#customsearch_autocomplete').removeClass('visible');
                $j('#customsearch_autocomplete').addClass('hidden');

                setTimeout(function() {
                    $j('#customsearchbutton').removeAttr('disabled');
                }, 1000); /* ENABLE SEARCH BUTTON */
            }

        }, "json")

    });

    $j('#search_mini_form').submit(function(e) {
        e.preventDefault();

        var defaultAction = $j('#search_mini_form').attr('action');
        var newAction = defaultAction + '?q=' + $j('#search').val();

        $j('#idsubmit').attr('action', newAction);

        $j('#idsubmit').submit();
    });

    $j(".merklink").live("click", function() {
        //alert($j(this).attr('id'));
        $j('#searchmerk').val($j(this).attr('id'));

        var defaultAction = $j('#search_mini_form').attr('action');
        var newAction = defaultAction + '?q=' + $j('#searchvalueq').val();

        $j('#idsubmit').attr('action', newAction);
        $j('#idsubmit').submit();
    });

    $j('html').click(function() {
        /*setZindex(Array('#leftcats', '.overlaymask'), -1);*/
        $j('.form-search').css('z-index', '0');
        $j('#topCartMask').hide();
        $j('#customsearch_autocomplete').removeClass('visible');
        $j('#customsearch_autocomplete').addClass('hidden');
    });

    $j('.debuglink').live('hover', function() {
        $j(this).next('.debugtogglediv').toggle();
    });

    $j("#search").focus(function(srcc) {
        if ($j(this).val() == $j(this)[0].title)
        {
            $j(this).removeClass("defaultTextActive");
            $j(this).val("");
        }
    });

    $j("#search").blur(function() {
        if ($j(this).val() == "")
        {
            $j(this).addClass("defaultTextActive");
            $j(this).val($j(this)[0].title);
        }
    });

    $j("#search").blur();

    /* STOCK CHECK */

    checkStock();

    /* TODO, gewenste hoeveelheid checken tegen daadwerkelijke voorraad */

    $j('.qty').keyup(function() {
        //alert("h")
		if ($j(this).hasClass('grid')){
			return false;
		}
        else if ($j(this).hasClass('grouped')) {
            var id = $j(this).parent('td').parent('tr').find('.hiddenstock').attr('title');
        } else if ($j(this).hasClass('groupedlist')) {
            var smalldisplay = true;
            var id = $j(this).parent('form').attr('class');
			
        } else if ($j(this).hasClass('relatedlist')) {
            var smalldisplay = true;
            var id = $j(this).parent('div').parent('div').find('.hiddenstock').attr('title');
        } else {
            var id = $j(this).parent().find('.hiddenstock').attr('title');
            if (!id) {
                id = $j('.hiddenstock').attr('title');
            }

        }

        var truestock = parseInt($j('#stock_qty_' + id).val());

        /*
         * Author: Helios
         * Date : 22-july-2014
         * Desc : fetch out of product Status
         */
        //alert("val fn" + $j('#stock_qty_status_' + id).val());
       // alert("value" + $j('#stock_qty_status_' + id).value);
        
        //alert("hiii");
        var stockstatus = parseInt($j('#stock_qty_status_' + id).val());
        var idealeverpakking = $j('#stock_qty_ideal_' + id).val();
        //console.log('hi');
        //alert(idealeverpakking);
        //alert('#stock_qty_verkoopeenheid_' + id);
		var verkoopeenheid = $j('#stock_qty_verkoopeenheid_' + id).val().toLowerCase();
                //alert(verkoopeenheid);
		var afwijkenidealeverpakking = $j('#stock_qty_afwijkenidealeverpakking_' + id).val();
		var leverancier = $j('#leverancier_' + id).val();		
		var productid = $j('#hiddenproductid_' + id).val();
        var qty = parseInt($j(this).val());
        var backorder = 0;
        var backorderMsg = $j('#backorderMsg').html();
		var besteldMsg = $j('#besteldMsg').html();
		
		if(leverancier==3797){
			return false;
		}
                //alert(stockstatus)
		if(stockstatus==3 && (truestock-qty)<0){
			if($j(this).hasClass('simpleproduct')){
				$j("#cart_button_"+id).hide();
				
			}
			else{			
				$j("#cart_button_"+productid).hide();
				$j(".btn-cart").hide();
			}			
		}
		else{			
			$j("#cart_button_"+productid).show();					
		}
        if (backorderMsg.length <= 0)
        {
            backorderMsg = '<p><span style="font-family: verdana,geneva; font-size: small;">Een backorder houdt in dat wij van dit artikel niet voldoende voorraad beschikbaar hebben en zelf een bestelling zullen plaatsen bij een leverancier. Bestellingen met een backorder worden pas verzonden zodra de gehele bestelling compleet is.&nbsp;Uw bestelling heeft hierdoor een langere levertijd. </span></p>';
        }		
        if (qty > truestock) {
			
            backorder = qty - truestock;				
            if (truestock == 0) {				
                if (!smalldisplay){
					if(stockstatus==3){
						$j('#' + id).html('<span class="stock orange"> (U kunt niet meer dan ' + truestock + ' ' + verkoopeenheid + ' <span id="besteld-term-' + id + '" class="besteld-info">bestellen <img src="' + CDN_URL + '/skin/frontend/gyzs/default/images/questionmark.png" /></span>)</span>');
						Tips.list.invoke('hide');
						Tips.add('besteld-term-' + id, besteldMsg, {style: 'slick', target: true, stem: true, tipJoint: ['center', 'bottom']});
					}else{
						$j('#' + id).html('<span class="stock orange">' + truestock +" " +calculateQty(truestock,idealeverpakking,verkoopeenheid,afwijkenidealeverpakking) + ' op voorraad</span><span class="stock orange"> (' + backorder + ' '+ verkoopeenheid +' worden <span id="backorder-term-' + id + '" class="backorder-info">besteld <img src="' + CDN_URL + '/skin/frontend/gyzs/default/images/questionmark.png" /></span>)</span>');					
						Tips.list.invoke('hide');
						Tips.add('backorder-term-' + id, backorderMsg, {style: 'slick', target: true, stem: true, tipJoint: ['center', 'bottom']});
					}
                } else {					
                    //$j('#' + id).html('<span class="stock orange"><img src="/skin/frontend/gyzs/default/images/stock_orange.png" title="Geen voorraad"/></span>');
                    $j('#' + id).html('<span class="stock orange" title="Geen voorraad"></span>');
                    Tips.list.invoke('hide');
                    //Tips.add('backorder-term-' + id, backorderMsg, {style: 'slick', target: true, stem: true, tipJoint: ['center', 'bottom']});
					var hstock;
					if(afwijkenidealeverpakking!=0){
						hstock=' '+verkoopeenheid;
					}
					else{
						hstock=' x ' +idealeverpakking+' '+verkoopeenheid;
					}
					Tips.add(id, '<span class="stock green">' +  truestock +" "+ calculateQty(truestock,idealeverpakking,verkoopeenheid,afwijkenidealeverpakking) + ' op voorraad</span><span class="stock orange"> (' + backorder + hstock + ' worden besteld</span>)</span>', {showOn: 'creation', hideOn: 'click', style: 'slick', target: true, stem: true, tipJoint: ['center', 'bottom']});
                }
            } else {				
                if (!smalldisplay) {
                    if(stockstatus==3){					
                       // if($j(this).hasClass('grouped')){
                           // $j('#' + id).html('<span class="stock green">' + calculateQty(truestock,idealeverpakking,verkoopeenheid,afwijkenidealeverpakking) + ' op voorraad</span><span class="stock orange"> (U kunt niet meer dan ' + truestock + ' ' + verkoopeenheid + ' bestellen)</span>');							
                     //   }
                      //  else{											
                            $j('#' + id).html('<span class="stock green">' + calculateQty(truestock,idealeverpakking,verkoopeenheid,afwijkenidealeverpakking) + ' op voorraad</span><span class="stock orange"> (U kunt niet meer dan ' + truestock + ' ' + verkoopeenheid + ' <span id="besteld-term-' + id + '" class="besteld-info">bestellen <img src="' + CDN_URL + '/skin/frontend/gyzs/default/images/questionmark.png" /></span>)</span>');
							Tips.list.invoke('hide');
							Tips.add('besteld-term-' + id, besteldMsg, {style: 'slick', target: true, stem: true, tipJoint: ['center', 'bottom']});
                        //}

                    }
                    else{
							var hstock;
							if(afwijkenidealeverpakking!=0){
								hstock=' '+verkoopeenheid;
							}
							else{
								hstock=' x ' +idealeverpakking+' '+verkoopeenheid;
							}
						if($j(this).hasClass('grouped')){
							 $j('#' + id).html('<span class="stock green">' + calculateQty(truestock,idealeverpakking,verkoopeenheid,afwijkenidealeverpakking) + ' </span><span class="stock orange"> (' + backorder +hstock+ ' worden <span id="backorder-term-' + id + '" class="backorder-info">besteld <img src="' + CDN_URL + '/skin/frontend/gyzs/default/images/questionmark.png" /></span>)</span>');
						}
						else{
							
							 $j('#' + id).html('<span class="stock green">' + calculateQty(truestock,idealeverpakking,verkoopeenheid,afwijkenidealeverpakking) + ' op voorraad</span><span class="stock orange"> (' + backorder + hstock + ' worden <span id="backorder-term-' + id + '" class="backorder-info">besteld <img src="' + CDN_URL + '/skin/frontend/gyzs/default/images/questionmark.png" /></span>)</span>');
						}
                       
                    }


                    Tips.list.invoke('hide');
                    Tips.add('backorder-term-' + id, backorderMsg, {style: 'slick', target: true, stem: true, tipJoint: ['center', 'bottom']});
                } else {
                    //$j('#' + id).html('<span class="stock green"><img src="/skin/frontend/gyzs/default/images/stock_orange.png" /></span>');
                    if(stockstatus==3){	
                        $j('#' + id).html('<span class="stock green"></span>');						
                        Tips.list.invoke('hide');
                        Tips.add(id, '<p><span class="stock green">' + calculateQty(truestock,idealeverpakking,verkoopeenheid,afwijkenidealeverpakking) + ' op voorraad</span><span class="stock orange"> (U kunt niet meer dan ' + truestock + ' ' + verkoopeenheid + ' bestellen)</span></p>', {showOn: 'creation', hideOn: 'click', style: 'slick', target: true, stem: true, tipJoint: ['center', 'bottom']});						
                    }
                    else{
                        $j('#' + id).html('<span class="stock green"></span>');
                        Tips.list.invoke('hide');
						var hstock;
							if(afwijkenidealeverpakking!=0){
								hstock=' '+verkoopeenheid;
							}
							else{
								hstock=' x ' +idealeverpakking+' '+verkoopeenheid;
							}
                        Tips.add(id, '<p><span class="stock green">' + calculateQty(truestock,idealeverpakking,verkoopeenheid,afwijkenidealeverpakking) + ' op voorraad</span><span class="stock orange"> (' + backorder + hstock +  ' worden besteld)</span></p>', {showOn: 'creation', hideOn: 'click', style: 'slick', target: true, stem: true, tipJoint: ['center', 'bottom']});
                    }

                }
            }
        } else if (truestock == 0){			
            if (!smalldisplay) {
                $j('#' + id).html('<span class="stock orange">Geen voorraad</span>');
            } else {
                //$j('#' + id).html('<span class="stock orange"><img src="/skin/frontend/gyzs/default/images/stock_orange.png" /></span>');
                $j('#' + id).html('<span class="stock orange"></span>');
                Tips.list.invoke('hide');
                Tips.add(id, '<p><span class="stock orange">Geen voorraad</span></p>', {showOn: 'creation', hideOn: 'click', style: 'slick', target: true, stem: true, tipJoint: ['center', 'bottom']});
            }
        } else {								
            if (!smalldisplay) {
				if($j(this).hasClass('grouped')){
					$j('#' + id).html('<span class="stock green">'+calculateQty(truestock,idealeverpakking,verkoopeenheid,afwijkenidealeverpakking)+'</span>');
				}
				else{
					$j('#' + id).html('<span class="stock green">'+calculateQty(truestock,idealeverpakking,verkoopeenheid,afwijkenidealeverpakking)+' op voorraad</span>');
				}
            } else {
                //$j('#' + id).html('<span class="stock green"><img src="/skin/frontend/gyzs/default/images/stock_green.png" /></span>');
                $j('#' + id).html('<span class="stock green"></span>');
                Tips.list.invoke('hide');
                Tips.add(id, '<p><span class="stock green">op voorraad</span></p>', {showOn: 'creation', hideOn: 'click', style: 'slick', target: true, stem: true, tipJoint: ['center', 'bottom']});
            }
        }
    });

    $j('.qty').live('keyup', function() {
        //Tips.add('backorder-term', '<p><span style="font-family: verdana,geneva; font-size: small;">Een backorder houdt in dat wij van dit artikel niet voldoende voorraad beschikbaar hebben en zelf een bestelling zullen plaatsen bij een leverancier. Bestellingen met een backorder worden pas verzonden zodra de gehele bestelling compleet is.&nbsp;Uw bestelling heeft hierdoor een langere levertijd. </span></p>', { style: 'slick', target: true, stem: true, tipJoint: [ 'center', 'bottom' ] });
    })

    $j('.truncated_description_trigger').click(function(e) {
        e.preventDefault();
        $j('.truncated_description').css('display', 'none');
        $j('.truncated_description.open').css('display', 'block');
    })

    $j('.grouped_product_link').live('click', function(e) {
        e.preventDefault();

        var id = $j(this).attr('id');

        id = id.substring(8);

        $j('.list_grouped_' + id).html('<span style="width:555px;height:100px;line-height:50px;text-align:center;vertical-align:baseline;display:block;">Bezig met laden<br /><img src="' + CDN_URL + '/skin/frontend/gyzs/default/images/ajax-loader.gif" /></span>');

        if (!$j('.list_grouped_' + id).hasClass('full')) {

            $j.post('/price/index/getgroupedproducts/', {'id': id, 'full': true}, function(output) {
                $j('.list_grouped_' + id).addClass('full');
                $j('.list_grouped_' + id).html(output);

                $j('.list_grouped_' + id).ready(function() {
                    checkStock();
                })
            });

        } else {

            $j.post('/price/index/getgroupedproducts/', {'id': id, 'full': false}, function(output) {
                $j('.list_grouped_' + id).removeClass('full');
                $j('.list_grouped_' + id).html(output);

                $j('.list_grouped_' + id).ready(function() {
                    checkStock();
                })
            });

        }

    })

    $j(".desc").dotdotdot();

    $j('.desc_popup_link').live({
        mouseover: function() {
            $j(this).find('.desc_popup').show();
        },
        mouseout: function() {
            $j(this).find('.desc_popup').hide();
        }});
});

function checkStock() {
    /* STOCK CHECK */
	
    var data = [];
    var i = 0;

    $j('.checkstock').each(function() {
        $j(this).html('<span style="width:105px;height:15px;text-align:center;vertical-align:baseline;"><img src="' + CDN_URL + '/skin/frontend/gyzs/default/images/ajax-loader.gif" /></span>');

        var subdata = {};

        subdata.id = $j(this).attr('id');

        if ($j(this).hasClass('groupedlist') || $j(this).hasClass('relatedlist')) {
            subdata.type = 'small';			
        } else {
            subdata.type = '';
        }			
        data.push(subdata);
    });
	 
    $j.post('/price/index/getvoorraad/', {'data': data}, function(json) {		
        if (json.length > 0) {
            $j.each(json, function(i) {					
                var idealeverpakking =$j('#stock_qty_ideal_' + json[i].Artinr).val();				
				idealeverpakking=idealeverpakking.replace(",", ".");
				var verkoopeenheid = $j('#stock_qty_verkoopeenheid_' + json[i].Artinr).val().toLowerCase();
				var afwijkenidealeverpakking = $j('#stock_qty_afwijkenidealeverpakking_' + json[i].Artinr).val();		
				var qty=json[i].VoorHH;									
				var stockstatus = parseInt($j('#stock_qty_status_' + json[i].Artinr).val());
				var productid = $j('#hiddenproductid_' + json[i].Artinr).val();
				
				if(stockstatus==3 && (qty)<=0){
					if($j(this).hasClass('simpleproduct')){
						$j("#cart_button_"+json[i].Artinr).hide();	
					}
					else{			
						$j("#cart_button_"+productid).hide();
						$j(".btn-cart").hide();
					}			
				}
				else{			
					$j("#cart_button_"+productid).show();					
				}
				/*if(afwijkenidealeverpakking!=0){
					var qty=json[i].VoorHH;
				}
				else{			
				alert(json[i].VoorHH);
					var qty=parseInt(json[i].VoorHH/idealeverpakking);
				}*/
                if(!isNaN(idealeverpakking))
				{		
//$j('.availability .checkstock').hasClass('list') || 				
					/*if($j('.availability .checkstock').hasClass('relatedlist')){
						$j('.product_list_foot #' + json[i].Artinr+ ' .stock').html(actualStock);
					}					
					else */if($j('.availability .checkstock').hasClass('groupedlist')){					
						$j('#' + json[i].Artinr).html(json[i].text);						
						$j('#list' + json[i].Artinr).html(json[i].text);
						var actualStock=calculateQty(qty,idealeverpakking,verkoopeenheid,afwijkenidealeverpakking);							
						if(actualStock!=""){							
							$j('#list' + json[i].Artinr+ ' .stock').html(actualStock);
							$j('#' + json[i].Artinr+'.list').html(actualStock);
							$j('#' + json[i].Artinr+'.list').append(json[i].text);
						}
						else{							
							$j('#list' + json[i].Artinr+ ' .stock').text($j('#'+json[i].Artinr+' .stock').attr("title"));
							
						}
						$j('#' + json[i].Artinr + ".groupedlist .stock").html('');

						}
					else if($j('.availability .checkstock').hasClass('grouped')){
							$j('#' + json[i].Artinr).html(calculateQty(qty,idealeverpakking,verkoopeenheid,afwijkenidealeverpakking));
							if(json[i].text.indexOf("green")<0){
								$j('#' + json[i].Artinr).append(json[i].text);
							}							
					}
					else{						
						$j('#' + json[i].Artinr).html(calculateQty(qty,idealeverpakking,verkoopeenheid,afwijkenidealeverpakking));
						$j('#' + json[i].Artinr).append(json[i].text);	
						$j('#' + json[i].Artinr+'.relatedlist').html(json[i].text);	
					}
				}else{						
					$j('#' + json[i].Artinr).html(json[i].text);
				}				
				$j('#stock_qty_' + json[i].Artinr).val(qty);
                if (json[i].VoorHH > 0) {
                    $j('#checkstocklabel_' + json[i].Artinr).html('<div class="ship24-image label"></div>');
                }

            });
        } else {
            /*** No results returned, set all divs to error ***/
            $j('.checkstock').each(function() {
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
function calculateQty(qty,idealeverpakking,verkoopeenheid,afwijkenidealeverpakking) {			
	if(afwijkenidealeverpakking!=0 || idealeverpakking==1){			
		if(qty>0){
			if(qty<=2){
				return '<span class="stock green">nog maar '+qty+' '+verkoopeenheid+' </span>';		
			}else{
				return '<span class="stock green">'+qty+' '+verkoopeenheid+' </span>';		
			}
			
			
		}
		
		return "";
			
	}
	else{		
		if(qty>0){		
			idealeverpakking=idealeverpakking.replace(".", ",");
			if(qty<=2){
				return '<span class="stock green">'+qty+' x '+idealeverpakking+' '+verkoopeenheid+' </span>';
			}else{
				return '<span class="stock green">'+qty+' x '+idealeverpakking+' '+verkoopeenheid+' </span>';
			}
			
		}
		return "";
				
	}
   
    
}