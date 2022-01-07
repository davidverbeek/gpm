jQuery(window).on("load", function(){
	var counter = 0;
	var productData = [];
	jQuery( ".product-info" ).each(function() {
		var product = {};
		product.name = jQuery( this ).find( ".product-name" ).text();
		product.id = jQuery( this ).find( ".product-sku span" ).text();
		//product.id = product.id.replace("GY1", "").reverse();
		product.price = jQuery.trim(jQuery( this ).find( ".price-including-tax span.price" ).text().replace('€', '').replace(',', '.'));
		product.list = 'Product List';
		product.position = counter;
		productData.push(product);
		counter++;
	});
	dataLayer.push({
		'ecommerce': {
			'currencyCode': 'EUR',
			'actionField': {'list': 'Product List'},
			'impressions': productData
		}
	});
});

/**
* Call this function when a user clicks on a product link. This function uses the event
* callback datalayer variable to handle navigation after the ecommerce data has been sent
* to Google Analytics.
* @param {Object} productObj An object representing a product.
*/
jQuery( ".product-image" ).on( "click", function() {
	event.preventDefault();
	var url = jQuery( this ).attr('href');
	if(typeof url !== "undefined"){
		var productname = jQuery( this ).next().find( ".product-name" ).text();
		var productid = jQuery( this ).next().find( ".product-sku span" ).text();
		var productprice = jQuery.trim(jQuery( this ).next().find( ".price-including-tax span.price" ).text().replace('€', '').replace(',', '.'));
		var productlist = 'Product List';
		if(productname != ''){
			dataLayer.push({
				'event': 'productClick',
				'ecommerce': {
					'currencyCode': 'EUR',
					'click': {
						'actionField': {'list': 'Product List'},
						'products': [{
							'name': productname,
							'id': productid,
							'price': productprice,
							'list': productlist
						}]
					 }
				},
			 	'eventCallback': function() {
					document.location = url;
				}
			});
		}
		document.location = url;
	}
});

/**
 * Call this function when a user clicks on a product link. This function uses the event
 * callback datalayer variable to handle navigation after the ecommerce data has been sent
 * to Google Analytics.
 * @param {Object} productObj An object representing a product.
 */
jQuery( ".oproduct-image" ).on( "click", function() {
	event.preventDefault();
	var url = jQuery( this ).find( ".product-image" ).attr('href');
	if(typeof url !== "undefined"){
		var productname = jQuery( this ).next().find( ".product-name a" ).text();
		var productid = jQuery( this ).next().find( ".product-sku span" ).text();
		var productlist = 'Product List';
		if(productname != ''){
			dataLayer.push({
				'event': 'productClick',
				'ecommerce': {
					'currencyCode': 'EUR',
					'click': {
						'actionField': {'list': 'Product List'},
						'products': [{
							'name': productname,
							'id': productid,
							'list': productlist
						}]
					 }
				},
				'eventCallback': function() {
					document.location = url;
				}
			});
		}
		document.location = url;
	}
});

/**
* Call this function when a user clicks on a product link. This function uses the event
* callback datalayer variable to handle navigation after the ecommerce data has been sent
* to Google Analytics.
* @param {Object} productObj An object representing a product.
*/
jQuery( ".product-name" ).on( "click", function( event ) {
	event.preventDefault();
	var url = jQuery( this ).attr('href');
	if(typeof url !== "undefined"){
		var productname = jQuery.trim(jQuery( this ).text());
		var productid = jQuery.trim(jQuery( this ).prev().text());
		var productprice = jQuery.trim(jQuery( this ).next().find( ".price-including-tax span.price" ).text().replace('€', '').replace(',', '.'));
		var productlist = 'Product List';
		if(productname != ''){
			dataLayer.push({
				'event': 'productClick',
				'ecommerce': {
					'currencyCode': 'EUR',
					'click': {
						'actionField': {'list': 'Product List'},
						'products': [{
							'name': productname,
							'id': productid,
							'price': productprice,
							'list': productlist
						}]
					 }
				},
				'eventCallback': function() {
					document.location = url;
				}
			});
		}
		document.location = url;
	}
});

jQuery( ".btn-cart.ajax-cart" ).on( "click", function( event ) {
	/* home page */
	var item = jQuery( this ).closest( ".item-inner" );
	var sku = item.find(' .product-sku span ').text();
	var name = item.find(' .product-name ').text();
	var priceex = jQuery.trim(item.find(' .price-excluding-tax .price ').text().replace('€', '').replace(',', '.'));
	var pricein = jQuery.trim(item.find(' .price-including-tax .price ').text().replace('€', '').replace(',', '.'));
	var qty = item.find(' .input-text.qty  ').val();
	var productlist = 'Product List';
	if(name != ''){
		// Measure adding a product to a shopping cart by using an 'add' actionFieldObject
		// and a list of productFieldObjects.
		dataLayer.push({
			'event': 'addToCart',
			'ecommerce': {
				'currencyCode': 'EUR',
				'add': {
					'actionField': {'list': 'Product List'},
					'products': [{                        //  adding a product to a shopping cart.
						'name': name,
						'id': sku,
						'price': pricein,
						'list': productlist,
						'quantity': qty
					}]
				}
			}
		});
	}
});
jQuery( ".button.btn-cart" ).on( "click", function( event ) {
	/* product detail page */
	var item = jQuery( this ).closest( "#prodmain" );
	var sku = "GY1" + item.find(' .productsku').text().split('').reverse().join('');
	var name = item.find(' .product-name h1').text();
	var priceex = item.find(' .price-excluding-tax .price ').text().replace('€', '').replace(',', '.');
	var pricein = item.find(' .price-including-tax .price ').text().replace('€', '').replace(',', '.');
	var qty = item.find(' .input-text.qty  ').val();
	var productlist = 'Product List';
	if(name != ''){
		// Measure adding a product to a shopping cart by using an 'add' actionFieldObject
		// and a list of productFieldObjects.
		dataLayer.push({
			'event': 'addToCart',
			'ecommerce': {
				'currencyCode': 'EUR',
				'add': {
					'actionField': {'list': 'Product List'},
					'products': [{ //  adding a product to a shopping cart.
						'name': name,
						'id': sku,
						'price': pricein,
						'list': productlist,
						'quantity': qty
					}]
				}
			}
		});
	}
});

