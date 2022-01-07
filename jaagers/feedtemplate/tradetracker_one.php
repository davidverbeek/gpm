<?php

echo	'<?xml version="1.0" encoding="utf-8"?>
            <productFeed>
			';

foreach($_products as $product) {

    if (ctype_alpha($product['sku'])) {
        continue;
    }
    $prod=Mage::getSingleton('catalog/product')->load($product['entity_id']);
    $url=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).$prod->getUrlKey();


    $price = ($product['price'] * 1.21);

    $stock = $this->checkStock($product['stock'], array('0', '1'));

    if($product['stock'] <= 0) {
        $levertijd = '3-7 werkdagen';
        $stock = 'in stock';
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
        $shippingCost = '7.5';
    } elseif($price < 100) {
        $shippingCost = '5.5';
    } else {
        $shippingCost = '0';
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

    $categories = explode('/', $product['categorypath']);
    $category = isset($categories[1])? $categories[1] : '';
    $subcategory = isset($categories[2])? $categories[2] : '';
    $subsubcategory = isset($categories[3])? $categories[3] : '';

    $categoryPath=str_replace('/',' > ',substr($product['categorypath'], 1));
    echo '
					<product>
						<ID>' . $product['sku'] . '</ID>
						<SKU>GY1'.strrev($product['sku']).'</SKU>
						<name><![CDATA[' . $product['name'] .$prijmessage. ']]></name>
						<description>'. htmlentities($product['description']) .'</description>
						<shortdescription>'. htmlentities($product['short_description']) .'</shortdescription>
						<productURL><![CDATA['. $url . ']]></productURL>
						<imageURL>' . $this->getUrlBasePath() . '/media/catalog/product' . $product['image'] . '</imageURL>
						<price><![CDATA['. number_format($price, 2, '.', ',') .']]></price>
						<categoryPath><![CDATA[' . $categoryPath . ']]></categoryPath>
						<categories><![CDATA[' . $category . ']]></categories>
						<subcategories><![CDATA[' . $subcategory . ']]></subcategories>
						<subsubcategories><![CDATA[' . $subsubcategory . ']]></subsubcategories>
						<stock>' . $stock . '</stock>
						<brand><![CDATA[' . $product['merk'] . ']]></brand>
						<deliveryTime><![CDATA[' . $levertijd . ']]></deliveryTime>
						<deliveryCosts>' .$shippingCost . '</deliveryCosts>
						<prijsfactorprijs><![CDATA['. $prijsfactor * $price. ']]></prijsfactorprijs>
						<prijsfactor><![CDATA['. $prijsfactor . ']]></prijsfactor>
						<mpn><![CDATA[' . $product['leverancierartikelnr'] . ']]></mpn>
						<EAN><![CDATA[' . $product['eancode'] . ']]></EAN>
					</product>';

}

echo 	'		</productFeed>';


?>
