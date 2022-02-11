<?php
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	ini_set('memory_limit', '-1');
	
	include('simple_html_dom.php');
	
	function scrapWebsite_FILECONTENT($url) {
		$html = file_get_html($url);
		return $html;
	}
	
	function scrapWebsite_GETHTML($url) {
		$html = file_get_html($url);
		return $html;
	}

	function scrapWebsite_CURL($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIE, 'user_accepted_cookies=true');	
		$response = curl_exec($ch);
		curl_close($ch);
		$html = new simple_html_dom();
		$html->load($response);
		return $html;	
	}
	
	function getPrice($html) {
		$price = $html->find('span.price')[0]->plaintext;
		if(!$price) {
			$price = 0;
		}
		return $price;
	}

	function getPriceForBouwsales($html){
		$price = 0;
		if($html) {
			$price = $html->find('span[itemprop=price]')[0]->content;
		}
		return $price;
	}

	function scrapByEAN($record, $client){
		$obj = new stdclass;
		$ean = $record['ean'];
		$url = $client['url'];
		if($client['get_html']) {
			$html_dom = scrapWebsite_GETHTML($url.$ean);
		} else {
			$html_dom = scrapWebsite_CURL($url.$ean);
		}
		return getPrice($html_dom);
	}
	
	function scrapBySKU($record, $client){
		$obj = new stdclass;
		$sku = $record['sku'];
		$url = $client['url'];
		$name = $client['name'];
		if($client['get_html']) {
			$html_dom = scrapWebsite_GETHTML($url."/".$sku);
		} else {
			$html_dom = scrapWebsite_CURL($url.$sku);
		}
		if($name=="Bouwsales") {
			return getPriceForBouwsales($html_dom);
		} else {
			return getPrice($html_dom);
		}
	}
	
	function scrapByArticle($record, $client){
		$obj = new stdclass;
		$article = $record['sku'];
		$url = $client['url'];
		$name = $client['name'];
		$html_dom = "";
		if($client['get_html']) {
			$html_dom = scrapWebsite_FILECONTENT($url.$article);
		} else {
			$html_dom = scrapWebsite_CURL($url.$article);
		}
		if($name=="Bouwsales") {
			return getPriceForBouwsales($html_dom);
		} else {
			return getPrice($html_dom);
		}		
	}
	function scrapByAll($record, $url){
		$obj = new stdclass;		
		$obj = scrapByEAN($record, $url);
		if(is_null($obj)){
			$obj = scrapBySKU($record, $url);
			if(is_null($obj)){
				$obj = scrapByArticle($record, $url);
			}
		}
		return $obj;
	}
	
	function scrapData($client, $record){
		$identifier = $client['identifier'];
		$url = $client['url'];
		switch($identifier) {
			case 'EAN':
				$obj = scrapByEAN($record, $client);
			break;
			case 'SKU':
				$obj = scrapBySKU($record, $client);
			break;
			case 'ARTICLE':
				$obj = scrapByArticle($record, $client);
			break;
			default:
				$obj = scrapByAll($record, $url);
			break;
		}
		return $obj;
	}
?>