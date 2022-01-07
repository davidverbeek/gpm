<?php

/**
 * Class Helios_Delivery_Model_Feed
 *
 * @copyright Copyright (c) 2019 Helios
 */

class Helios_Deliverytime_Model_Delivery extends Mage_Core_Model_Abstract {

	// global variables
	private $_helper = null;


	public function _construct() {
		parent::_construct();
		$this->_helper = Mage::helper('deliverytime');
		$this->_mavisresthelper = Mage::helper('Xelcomm_MavisRestApi');
	}

	public function getDeliveryData(){
		try {
			
			$status = 0;

			$request = $this->_helper->prepareRequest();

			Mage::log("Deliverytime Request sent to Mavis.", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
			$resultDeliveryTime = Mage::helper('price/data')->soapConnect('GetLevertijdMore', 'GetLevertijdMoreResult', $request);
			Mage::log("Deliverytime Received Data from Mavis", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);

			if(isset($resultDeliveryTime->int)){
				$status = $this->_helper->importDeliveryTime($request['entities'], $resultDeliveryTime->int);
			} else {
				Mage::log("Import Delivery time process stopped.", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
			}

			return $status;

		} catch (Exception $e) {
			Mage::log($e->getMessage(), null, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
		}
	}

	public function getDeliveryDataForNewSku(){
		try {
			
			$status = 0;

			$request = $this->_helper->prepareRequest(0, 1);

			if(!is_array($request)){
				return '';
			}

			Mage::log("Deliverytime Request sent to Mavis.", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
			$resultDeliveryTime = Mage::helper('price/data')->soapConnect('GetLevertijdMore', 'GetLevertijdMoreResult', $request);
			Mage::log("Deliverytime Received Data from Mavis", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);

			if(isset($resultDeliveryTime->int)){
				$status = $this->_helper->importDeliveryTime($request['entities'], $resultDeliveryTime->int);
			} else {
				Mage::log("Import Delivery time process stopped.", NULL, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
			}

			return $status;

		} catch (Exception $e) {
			Mage::log($e->getMessage(), null, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
		}
	}


	public function getRestDeliveryData(){	

		$mavis_products_delivery_days = $this->_mavisresthelper->getMavisProductsDeliveryDays();
		if(is_object($mavis_products_delivery_days['response_data'])) {
			$mavis_delivery_response_data = $mavis_products_delivery_days['response_data'];
			$check_products_delivery_dates_count = count($mavis_delivery_response_data->deliverydays);
			if($check_products_delivery_dates_count == 0) {
				
			$message = 'There are no updated delivery days for products:- On ('.date("Y-m-d H:i:s").'))';	

			$this->createLog($message,$mavis_products_delivery_days['token_key'],$mavis_products_delivery_days['curl_error_msg'],$mavis_products_delivery_days['request_url']);
		
			} else {
				$actual_products_delivery_updated = $this->_helper->importRestDeliveryTime($mavis_delivery_response_data->deliverydays);

				$message = 'Total products updated ('.count($actual_products_delivery_updated).'):- On ('.date("Y-m-d H:i:s").') Updated Products SKUs:-'.implode(",",$actual_products_delivery_updated).'';

				$this->createLog($message,$mavis_products_delivery_days['token_key'],$mavis_products_delivery_days['curl_error_msg'],$mavis_products_delivery_days['request_url']);
			}
		} else {
			$message = 'Issue with api/response data not available from api:- On ('.date("Y-m-d H:i:s").'))';
			$this->createLog($message,$mavis_products_delivery_days['token_key'],$mavis_products_delivery_days['curl_error_msg'],$mavis_products_delivery_days['request_url']);

			$mail_message = "<div>Response data not available for api :- ".$mavis_products_delivery_days['request_url']." on ".date("Y-m-d H:i:s")."</div>";
			
			$this->sendMailAction($mail_message);

		}
	}

	public function createLog($message,$token_data,$curl_error_message,$curl_request_url) {
		$log_array = array();
		$log_array["Message"] = $message;
		$log_array["Token_data"] = $token_data;
		$log_array["Curl Error Message"] = $curl_error_message;
		$log_array["Curl Request Url"] = $curl_request_url;
		Mage::log(print_r($log_array, true), NULL, Helios_Deliverytime_Helper_Data::REST_DELIVERY_TIME_LOG_FILE_NAME);
	}

	function sendMailAction($html){
	    $to      = 'georgestamgyzs@gmail.com,dverbeek.2019@gmail.com';
	    $subject = 'ALERT: Issue with Delivery Days API '.date("Y/m/d");
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
	        Mage::log("Mail Not Sent ON ".date("Y/m/d")." === ".print_r($e->getMessage()), NULL, Helios_Deliverytime_Helper_Data::REST_DELIVERY_TIME_LOG_FILE_NAME);
	    }
	}

}
