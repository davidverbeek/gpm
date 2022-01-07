<?php 

echo	'<?xml version="1.0" encoding="utf-8"?>
			<rss version ="2.0" xmlns:g="http://base.google.com/ns/1.0">
				<Products>';

foreach($_products as $product) {
	
	if (ctype_alpha($product['sku'])) {
		continue;
	}
	$prod=Mage::getSingleton('catalog/product')->load($product['entity_id']);
	$product['urlpath']=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).$prod->getUrlKey();	
	$url = $product['urlpath'] .'?utm_source=vergelijkers&amp;utm_medium=cpc&amp;utm_campaign=beslist';
	//$product['urlpath'] = 'catalog/product/view/id/' . $product['entity_id'];
	//$url = $this->getUrlBasePath() . '/' . $product['urlpath'] .'}}?utm_source=vergelijkers&amp;utm_medium=cpc&amp;utm_campaign=vergelijk.nl';
	
	if($this->trackingurl) {
		$url = $this->getTrackingUrl($url);
	}
	
	if(strlen($product['prijsfactor'] > 0)) {
		$price = $product['price'] * $product['prijsfactor'];
		$prijsfactor = $product['prijsfactor'];
	} else {
		$price = $product['price'];
		$prijsfactor = 1;
	}
	
	$price = $price * 1.21;
	
	$stock = $this->checkStock($product['stock'], array('0', '1'));
	
	if($product['stock'] <= 0) {
		$levertijd = '3-7 werkdagen';
	} else {
		$levertijd = 'Op werkdagen voor 16.30 uur besteld, morgen in huis';
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
					<Product>
						<sku><![CDATA[' . $product['sku'] . ']]></sku>
						<Category><![CDATA[' . $product['categorypath'] . ']]></Category>
						<Brand><![CDATA[' . $product['merk'] . ']]></Brand>
						<ProductName><![CDATA[' . $product['name'] . ']]></ProductName>
						<Deeplink><![CDATA['. $url . ']]></Deeplink>
						<Price><![CDATA['. number_format($price, 2, '.', ',') .']]></Price>
						<Prijsfactor><![CDATA['. $prijsfactor . ']]></Prijsfactor>
						<DeliveryPeriod>' . $levertijd . '</DeliveryPeriod>
						<DeliveryCosts>' . $porto . '</DeliveryCosts>
						<ProductVendorPartNr>' . $product['leverancierartikelnr'] . '</ProductVendorPartNr>
						<ProductEAN><![CDATA[' . $product['eancode'] . ']]></ProductEAN>
						<ProductDescription><![CDATA[' . $product['short_description'] . ']]></ProductDescription>
						<OfferId>00' . $product['sku'] . '</OfferId>
						<DeeplinkPicture>' . $this->getUrlBasePath() . '/media/catalog/product/' . $product['image'] . '</DeeplinkPicture>
					</Product>';
}

echo 	'		</Products>
			</rss>';

?>
