<?php
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	ini_set('memory_limit', '-1');
	
	include('simple_html_dom.php');
	
	

	function scrapWebsite($url) {
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
		$price = $html->find('div[class=product__price]');	
		print_r($price);
		exit;
		if($price) {
			print_r($price);
			exit;
		}
	}	

	function scrapByEAN($record, $url){
		$obj = new stdclass;
		$ean = $record['ean'];
		$html_dom = scrapWebsite($url."8714186485668");
		return getPrice($html_dom);
	}
	
	function scrapBySKU($record, $url){
		$obj = new stdclass;
		$sku = $record['sku'];
		$html_dom = file_get_html($url."/".$sku, false);
		return $html_dom;		
	}
	function scrapByArticle($record, $url){
		$obj = new stdclass;
		$article = $record['article'];
		$html_dom = file_get_html($url."/".$article, false);
		return $html_dom;		
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
				$obj = scrapByEAN($record, $url);
			break;
			case 'SKU':
				$obj = scrapBySKU($record, $url);
			break;
			case 'ARTICLE':
				$obj = scrapByArticle($record, $url);
			break;
			default:
				$obj = scrapByAll($record, $url);
			break;
		}
		return $obj;
	}
?>