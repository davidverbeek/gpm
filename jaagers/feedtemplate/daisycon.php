<?php 

echo	'<?xml version="1.0" encoding="utf-8"?>
			<rss version ="2.0" xmlns:g="http://base.google.com/ns/1.0">
				<channel>';

foreach($_products as $product) {
	
	if (ctype_alpha($product['sku'])) {
		continue;
	}
	$prod=Mage::getSingleton('catalog/product')->load($product['entity_id']);
	$product['urlpath']=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).$prod->getUrlKey();	
	$url = $product['urlpath'] .'?utm_source=vergelijkers&amp;utm_medium=cpc&amp;utm_campaign=beslist';
	//$product['urlpath'] = 'catalog/product/view/id/' . $product['entity_id'];
	//$url = $this->getUrlBasePath() . '/' . $product['urlpath'] .'?utm_source=vergelijkers&amp;utm_medium=cpc&amp;utm_campaign=daisycon.nl';
	
	if($this->trackingurl) {
		$url = $this->getTrackingUrl($url);
	}
	
	if(strlen($product['type'] > 0)) {
		$type = $product['type'];
	} else {
		$type = $product['leverancierartikelnr'];
	}
	
	if(strlen($product['prijsfactor'] > 0)) {
		$price = $product['price'] * $product['prijsfactor'];
		$prijsfactor = $product['prijsfactor'];
	} else {
		$price = $product['price'];
		$prijsfactor = 1;
	}
	
	$price = $price * 1.21;
	
	if($product['stock'] <= 0) {
		$stock = 'Y';
	} else {
		$stock = 'N';
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
	
	echo '
					<product>
						<producturl><![CDATA['. $url . ']]></producturl>
						<designation><![CDATA[' . $product['name'] . ']]></designation>
						<price><![CDATA['. number_format($price, 2, '.', ',') .']]></price>
						<prijsfactor><![CDATA['. $prijsfactor. ']]></prijsfactor>
						<category><![CDATA[' . $product['categorypath'] . ']]></category>
						<image_url>' . $this->getUrlBasePath() . '/media/catalog/product/' . $product['image'] . '</image_url>
						<description><![CDATA[' . $product['short_description'] . ']]></description>
						<brand><![CDATA[' . $product['merk'] . ']]></brand>
						<merchant_id><![CDATA[' . $product['sku'] . ']]></merchant_id>
						<manufacturer_id><![CDATA[' . $product['leverancierartikelnr'] . ']]></manufacturer_id>
						<shipping_cost>' . $porto . '</shipping_cost>
						<in_stock>' . $stock . '</in_stock>
						<conditie>0</conditie>
						<upc_ean><![CDATA[' . $product['eancode'] . ']]></upc_ean>
						<product_type><![CDATA[' . $type . ']]></product_type>
					</product>';
}

echo 	'		</channel>
			</rss>';

?>
