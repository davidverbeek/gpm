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
	//$url = $this->getUrlBasePath() . '/' . $product['urlpath'] .'?utm_source=vergelijkers&amp;utm_medium=cpc&amp;utm_campaign=prijsvergelijk.nl';
	
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
					<item>
						<categorie><![CDATA[' . $product['categorypath'] . ']]></categorie>
						<merk><![CDATA[' . $product['merk'] . ']]></merk>
						<type><![CDATA[' . $product['type'] . ']]></type>
						<naam><![CDATA[' . $product['name'] . ']]></naam>
						<skuGYZS><![CDATA[' . $product['sku'] . ']]></skuGYZS>
						<sku><![CDATA[' . $product['leverancierartikelnr'] . ']]></sku>
						<ean><![CDATA[' . $product['eancode'] . ']]></ean>
						<prijs><![CDATA['. number_format($price, 2, '.', ',') .']]></prijs>
						<prijsfactor><![CDATA['. $prijsfactor . ']]></prijsfactor>
						<verzendkosten>' . $porto . '</verzendkosten>
						<producturl><![CDATA['. $url . ']]></producturl>
						<imageurl>' . $this->getUrlBasePath() . '/media/catalog/product/' . $product['image'] . '</imageurl>
						<omschrijving><![CDATA[' . $product['description'] . ']]></omschrijving>						
					</item>';
}

echo 	'		</channel>
			</rss>';

?>