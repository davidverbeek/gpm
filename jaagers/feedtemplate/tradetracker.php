<?php

echo	'<?xml version="1.0" encoding="utf-8"?>
			<rss version ="2.0" xmlns:g="http://base.google.com/ns/1.0">
				<channel>';

foreach($_products as $product) {

	if (ctype_alpha($product['sku'])) {
		continue;
	}
	$prod=Mage::getSingleton('catalog/product')->load($product['entity_id']);
	$url=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).$prod->getUrlKey();
	//$url = $product['urlpath'] .'?utm_source=vergelijkers&amp;utm_medium=cpc&amp;utm_campaign=beslist';
	//$product['urlpath'] = 'catalog/product/view/id/' . $product['entity_id'];
	//$url = $this->getUrlBasePath() . '/' . $product['urlpath'] .'?utm_source=tradetracker';

	/*Comment By helios*/
	/*if($this->trackingurl) {
		$url = $this->getTrackingUrl($url);
	}*/

	$price = ($product['price'] * 1.21);

	$stock = $this->checkStock($product['stock'], array('0', '1'));

	if($product['stock'] <= 0) {
		$levertijd = '3-7 werkdagen';
		$stock = 'out of stock';
	} else {
		$levertijd = 'Op werkdagen voor 16.30 uur besteld, morgen in huis';
		$stock = 'in stock';
	}

	if(strlen($product['prijsfactor'] > 0)) {
		$prijsfactor = $product['prijsfactor'];
	} else {
		$prijsfactor = 1;
	}
	$prijmessage='';	
	    if($product['prijsfactor']>1){
		$prijmessage=" (per ".$product['prijsfactor']." ". strtolower($prod->getData('verkoopeenheid')) .")";
	    }else{
		$prijmessage=" (per " . strtolower($prod->getData('verkoopeenheid')) .")";
	}

	if($price < 50) {
		$porto = '7.5';
	} elseif($price < 100) {
		$porto = '5.5';
	} else {
		$porto = '0';
	}

	/*
	  * Author: Helios
	  * Date : 18-Augest-2014
	  * Desc : skip product which has no Image
	*/
	if(strlen($product['image']) == 0) {
		continue;
		$product['image'] = 'image.jpg';
	}

// 	echo '
// 					<item>
// 						<g:id>' . $product['sku'] . '</g:id>
// 						<title><![CDATA[' . $product['name'] . ']]></title>
// 						<description><![CDATA[' . $product['description'] . ']]></description>
// 						<link><![CDATA['. $url . ']]></link>
// 						<g:image_link>' . $this->getUrlBasePath() . '/media/catalog/product/' . $product['image'] . '</g:image_link>
// 						<g:google_product_category><![CDATA[Hardware &gt; Gereedschap]]></g:google_product_category>
// 						<g:product_type><![CDATA[' . $product['categorypath'] . ']]></g:product_type>
// 						<g:condition>new</g:condition>
// 						<g:availability>' . $stock . '</g:availability>
// 						<hoofdcategorie><![CDATA[Bouwproducten]]></hoofdcategorie>
// 						<subcategorie><![CDATA[Afdekmateriaal]]></subcategorie>
// 						<subsubcategorie><![CDATA[Beschermfolie]]></subsubcategorie>
// 						<categoriepad><![CDATA[Bouwproducten &gt; Afdekmateriaal &gt; Beschermfolie]]></categoriepad>
// 						<price><![CDATA['. number_format($price, 2, '.', ',') .']]></price>
// 						<prijsfactorprijs><![CDATA['. $prijsfactor * $price. ']]></prijsfactorprijs>
// 						<prijsfactor><![CDATA['. $prijsfactor . ']]></prijsfactor>
// 						<porto>' . $porto . '</porto>
// 						<g:brand><![CDATA[' . $product['merk'] . ']]></g:brand>
// 						<g:mpn><![CDATA[' . $product['leverancierartikelnr'] . ']]></g:mpn>
// 						<ean><![CDATA[' . $product['eancode'] . ']]></ean>
// 					</item>';

$categories = explode('/', $product['categorypath']);
$category = isset($categories[1])? $categories[1] : '';
$subcategory = isset($categories[2])? $categories[2] : '';
$subsubcategory = isset($categories[3])? $categories[3] : '';

	echo '
					<item>
						<g:id>' . $product['sku'] . '</g:id>
						<title><![CDATA[' . $product['name'] . $prijmessage . ']]></title>
						<description><![CDATA[' . $product['description'] . ']]></description>
						<link><![CDATA['. $url . ']]></link>
						<g:image_link>' . $this->getUrlBasePath() . '/media/catalog/product/' . $product['image'] . '</g:image_link>
						<g:google_product_category><![CDATA[Hardware &gt; Gereedschap]]></g:google_product_category>
						<g:product_type><![CDATA[' . $product['categorypath'] . ']]></g:product_type>
						<g:condition>new</g:condition>
						<g:availability>' . $stock . '</g:availability>
						<hoofdcategorie><![CDATA[' . $category . ']]></hoofdcategorie>
						<subcategorie><![CDATA[' . $subcategory . ']]></subcategorie>
						<subsubcategorie><![CDATA[' . $subsubcategory . ']]></subsubcategorie>
						<categoriepad><![CDATA[' . $product['categorypath'] . ']]></categoriepad>
						<price><![CDATA['. number_format($price, 2, '.', ',') .']]></price>
						<prijsfactorprijs><![CDATA['. $prijsfactor * $price. ']]></prijsfactorprijs>
						<prijsfactor><![CDATA['. $prijsfactor . ']]></prijsfactor>
						<porto>' . $porto . '</porto>
						<g:brand><![CDATA[' . $product['merk'] . ']]></g:brand>
						<g:mpn><![CDATA[' . $product['leverancierartikelnr'] . ']]></g:mpn>
						<ean><![CDATA[' . $product['eancode'] . ']]></ean>
					</item>';

}

echo 	'		</channel>
			</rss>';

?>
