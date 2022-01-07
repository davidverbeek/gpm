<?php 

echo '"id";"title";"description";"link";"image_link";"google product category";"product type";"condition";"availability";"price";"prijsfactorprijs";"prijsfactor";"porto";"brand";"mpn";"ean";' . chr(10) . chr(13);

foreach($_products as $product) {
	
	if (ctype_alpha($product['sku'])) {
		continue;
	}
	$prod=Mage::getSingleton('catalog/product')->load($product['entity_id']);
	$product['urlpath']=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).$prod->getUrlKey();	
	$url = $product['urlpath'] .'?utm_source=vergelijkers&amp;utm_medium=cpc&amp;utm_campaign=beslist';
	//$product['urlpath'] = 'catalog/product/view/id/' . $product['entity_id'];
	//$url = $this->getUrlBasePath() . '/' . $product['urlpath'] .'?utm_source=vergelijkers&amp;utm_medium=cpc&amp;utm_campaign=google%20shopping';
	
	if($this->trackingurl) {
		$url = $this->getTrackingUrl($url);
	}
	
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
	
	$description = html_entity_decode($product['short_description']);
	$description = nl2br($description);
	$description = strip_tags($description);
	$description = str_replace("\r\n",'', $description);
	
	$out = fopen('php://output', 'w');
	
	$fields = array();
	
	$fields[] = array(
					$product['sku'], 
					$description, $url, 
					$this->getUrlBasePath() . '/media/catalog/product/' . $product['image'],
					"Hardware &gt; Gereedschap;",
					$product['categorypath'],
					"new",
					$stock,
					number_format($price, 2, '.', ','),
					$prijsfactor * $price,
					$prijsfactor,
					$porto,
					$product['merk'],
					$product['leverancierartikelnr'],
					$product['eancode']
				);
	
	foreach ($fields as $field) {
		fputcsv($out, $field, ';', '"'); 
	}
 
	fclose($out);
}
?>
