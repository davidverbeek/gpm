<?php 

echo	'<?xml version="1.0" encoding="utf-8"?>
			<rss version ="2.0" xmlns:g="http://base.google.com/ns/1.0">
				<products>';

foreach($_products as $product) {
	
	if (ctype_alpha($product['sku'])) {
		continue;
	}
	
	/*** Als type = null, set leverancierartikelnr as type en anders het gyzs SKU ***/
	
	if(strlen($product['type'] == 0) && strlen($product['leverancierartikelnr'] > 0)) {
		$type = $product['leverancierartikelnr'];
	} else if (strlen($product['leverancierartikelnr'] == 0)) {
		$type = $product['sku'];
	} else {
		$type = $product['type'];
	}
	$prod=Mage::getSingleton('catalog/product')->load($product['entity_id']);
	$product['urlpath']=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).$prod->getUrlKey();	
	$url = $product['urlpath'] .'?utm_source=vergelijkers&amp;utm_medium=cpc&amp;utm_campaign=beslist';
	//$product['urlpath'] = 'catalog/product/view/id/' . $product['entity_id'];
	//$url = $this->getUrlBasePath() . '/' . $product['urlpath'] .'?utm_source=vergelijkers&amp;utm_medium=cpc&amp;utm_campaign=kieskeurig.be';
	
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

        $prijmessage='';	
	if($product['prijsfactor']>1){
		$prijmessage=" (per ".$product['prijsfactor']." ". strtolower($prod->getData('verkoopeenheid')) .")";
	}else{
		$prijmessage=" (per " . strtolower($prod->getData('verkoopeenheid')) .")";
	}
	
	$price = $price * 1.21;
	
	$stock = $this->checkStock($product['stock'], array('0', '1'));
	
	if($price < 50) {
		$porto = '11.75';
	} elseif($price < 100) {
		$porto = '9.75';
	} else {
		$porto = '6.75';
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
						<producttitel><![CDATA[' . $product['name'] . $prijmessage .']]></producttitel>
						<omschrijving><![CDATA[' . $product['short_description'] . ']]></omschrijving>
						<productgroep><![CDATA[' . $product['category'] . ']]></productgroep>
						<merk><![CDATA[' . $product['merk'] . ']]></merk>
						<type><![CDATA[' . $type . ']]></type>
						<partnumber><![CDATA[' . $product['sku'] . ']]></partnumber>
						<leverancierartikelnummer><![CDATA[' . $product['leverancierartikelnr'] . ']]></leverancierartikelnummer>
						<ean-code><![CDATA[' . $product['eancode'] . ']]></ean-code>
						<prijs><![CDATA['. number_format($price, 2, '.', ',') .']]></prijs>
						<prijsfactor><![CDATA[' . $prijsfactor . ']]></prijsfactor>
						<deeplink><![CDATA[' . $url . ']]></deeplink>
						<levertijd>1-2 werkdagen</levertijd>
						<imageurl>' . $this->getUrlBasePath() . '/media/catalog/product/' . $product['image'] . '</imageurl>
						<porto>' . $porto . '</porto>
						<voorraad>' . $stock . '</voorraad>
					</product>';
}

echo 	'		</products>
			</rss>';

?>
