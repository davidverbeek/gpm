<?php

/**
 * Class Helios_Delivery_Model_Stock
 *
 * @copyright Copyright (c) 2019 Helios
 */

class Helios_Deliverytime_Model_Stock extends Mage_Core_Model_Abstract {

	// global variables
	private $_helper = null;


	public function _construct() {
		parent::_construct();
		$this->_helper = Mage::helper('deliverytime');

		$this->_mavisresthelper = Mage::helper('Xelcomm_MavisRestApi');
	}

	public function getStockData(){
		try {
			
			$status = 0;

			$requestArray = $this->_helper->prepareRequest(1);
			foreach ($requestArray as $request) {

				Mage::log("Stock Request sent to Mavis.", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
				$resultStock = Mage::helper('price/data')->soapConnect('GetVoorraadMore', 'GetVoorraadMoreResult', $request);
				Mage::log("Stock Received Data from Mavis", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
				
				if(isset($resultStock->decimal)){
					$status = $this->_helper->importStock($request['entities'], $resultStock->decimal);

				} else {
					Mage::log(print_r($request['artikelen'], true), NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
					Mage::log("Import Stock process stopped.", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
				}
			}

			if($status){
				$this->_helper->reindexCatalogInventory();
			}
			
			return $status;

		} catch (Exception $e) {
			Mage::log($e->getMessage(), null, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
		}
	}

	public function getRestStockData() {

		$all_chunk_products = $this->_helper->prepareRestRequest();

		//$break_chunks = 1;
		foreach($all_chunk_products as $requestparams) {
			echo $requestparams['mavis_request_url']."<br>";

			
			$mavis_stock_data = $this->_mavisresthelper->getMavisMultipleProductsPriceAndStock($requestparams['mavis_request_url']);

			if(count($mavis_stock_data['response_data'])) {
				$this->_helper->importRestStocks($mavis_stock_data['response_data'],$requestparams['chunked_products_data']);
			} else {
				Mage::log('Chunk Products not imported:- Request URL:-'.$requestparams['mavis_request_url'].'==== Token Info:-'.$mavis_stock_data['token_key'].'==== CURL Error Message:-'.$mavis_stock_data['curl_error_msg'], null, Helios_Deliverytime_Helper_Data::REST_STOCK_LOG_FILE_NAME);
				break;
			}
			
			//if($break_chunks == 2) { break; } 
			//$break_chunks++;
		}

	}


	public function getUpdatedRestStockData() {

		$get_date_time_15_minutes_earlier_from_current_UTC = date('Y-m-d H:i:s',strtotime("-15 minutes",strtotime(gmdate("Y-m-d H:i:s"))));
		$current_utc_date_time = gmdate("Y-m-d H:i:s");
		$prepare_request = str_replace(" ", "T", $get_date_time_15_minutes_earlier_from_current_UTC);
		
		$mavis_updated_stock_data = $this->_mavisresthelper->getMavisUpdatedStocksDataFromSpecificDateTime($prepare_request);

		if(is_object($mavis_updated_stock_data['response_data'])) {
			$mavis_response_data = $mavis_updated_stock_data['response_data'];
			$check_availability = count($mavis_response_data->availability);
			if($check_availability == 0) {
				
				$message = 'There are no updated products:- From ('.$prepare_request.' - '.$current_utc_date_time.' (Request Url :- '. $mavis_updated_stock_data['request_url'] .'';
				
				$this->createLog($message,$mavis_updated_stock_data['token_key'],$mavis_updated_stock_data['curl_error_msg'],$mavis_updated_stock_data['request_url'],Helios_Deliverytime_Helper_Data::REST_STOCK_LOG_FILE_NAME_15);

			} else {
				
				$products_updated = $mavis_response_data->availability[0]->branches[0]->productAvailibility;

				$actual_products_updated = $this->_helper->updateRestStocks($products_updated);

				$message = 'Total products updated ('.count($actual_products_updated).'):- From ('.$prepare_request.' - '.$current_utc_date_time.' (Request Url :- '. $mavis_updated_stock_data['request_url'] .' Updated Products SKUs:- '.implode(",",$actual_products_updated).'';

				$this->createLog($message,$mavis_updated_stock_data['token_key'],$mavis_updated_stock_data['curl_error_msg'],$mavis_updated_stock_data['request_url'],Helios_Deliverytime_Helper_Data::REST_STOCK_LOG_FILE_NAME_15);	

			}	
		} else {
			

			$message = 'Issue with api/response data not available from api:- From ('.$prepare_request.' - '.$current_utc_date_time.' (Request Url :- '. $mavis_updated_stock_data['request_url'] .'';

			$this->createLog($message,$mavis_updated_stock_data['token_key'],$mavis_updated_stock_data['curl_error_msg'],$mavis_updated_stock_data['request_url'],Helios_Deliverytime_Helper_Data::REST_STOCK_LOG_FILE_NAME_15);

			$mail_message = "<div>Response data not available for api :- ".$mavis_updated_stock_data['request_url']." from ".$prepare_request." - ".$current_utc_date_time."</div>";
			
			$this->sendMailAction($mail_message);

			
		}
	}


	public function createLog($message,$token_data,$curl_error_message,$curl_request_url,$file_name) {
		$log_array = array();
		$log_array["Message"] = $message;
		$log_array["Token_data"] = $token_data;
		$log_array["Curl Error Message"] = $curl_error_message;
		$log_array["Curl Request Url"] = $curl_request_url;
		Mage::log(print_r($log_array, true), NULL, $file_name);
	}

	function sendMailAction($html){
	    $to      = 'georgestamgyzs@gmail.com,dverbeek.2019@gmail.com';
	    $subject = 'ALERT: Issue with 15 mins stock update API '.date("Y/m/d H:i:s");
	    $message = $html;
	    $headers = 'From: webmaster@gyzs.nl' . "\r\n" .
	        'Reply-To: webmaster@gyzs.nl' . "\r\n" .
	        'MIME-Version: 1.0' . "\r\n" .
	        'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
	        'X-Mailer: PHP/' . phpversion();
	    try {
	        mail($to, $subject, $message, $headers);
	    }
	    catch (Exception $e) {
	        Mage::log("Mail Not Sent ON ".date("Y/m/d")." === ".print_r($e->getMessage()), NULL, Helios_Deliverytime_Helper_Data::REST_STOCK_LOG_FILE_NAME_15);
	    }
	}


}


