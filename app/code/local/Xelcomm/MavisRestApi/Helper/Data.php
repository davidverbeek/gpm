<?php

class Xelcomm_MavisRestApi_Helper_Data extends Mage_Core_Helper_Abstract
{
	
	const TOKEN_FILE_PATH = 'rest_token.txt';

	public function GetApiAccountTokenUrl()
	{
	    return "http://62.12.1.67:4451/api/Account/Token";
	}

	public function GetMultipleProductsStockUrl()
	{
	   return "http://62.12.1.67:4451/api/Product/PricesAndStock";
	}

	public function GetProuductStockAvailabilityUrl() {
		return "http://62.12.1.67:4451/api/Product/ProductAvailability";
	}

	public function GetProductDeliveryDaysUrl() {
		return "http://62.12.1.67:4451/api/Product/ProductDeliveryDays";
	}

	public function SetCustomSessionForApiToken($token){
        $tokenfile = fopen(Xelcomm_MavisRestApi_Helper_Data::TOKEN_FILE_PATH, "w");
        fwrite($tokenfile, json_encode($token));
    }
 
    public function GetCustomSessionForApiToken(){
        $tokenfile = fopen(Xelcomm_MavisRestApi_Helper_Data::TOKEN_FILE_PATH, "r");
        $file_token_data = fread($tokenfile,filesize(Xelcomm_MavisRestApi_Helper_Data::TOKEN_FILE_PATH));
        $sess_data = json_decode($file_token_data,true);
        return $sess_data;
    }
 
   
    public function getMavisAuthenticationKey() {
        $getApiTokenData = $this->GetCustomSessionForApiToken();
        if(isset($getApiTokenData)) {
            $session_token_data = $this->GetCustomSessionForApiToken();
            $time_diff_secs = strtotime($session_token_data["expiration"]) - time();
            $time_diff_hrs = $time_diff_secs/3600;
            
            if($time_diff_hrs > 0) {
                return $session_token_data["token"]."|||".$time_diff_hrs;
            } else {
                $token_info = $this->generateApiToken();
                return $token_info["token"]."|||8";
            }
        }
        else {  
            $token_info = $this->generateApiToken();
            return $token_info["token"]."|||8";
        }
    }

    public function generateApiToken() {
        if(!empty($this->GetApiAccountTokenUrl())) {   	
	    	$apiURL = $this->GetApiAccountTokenUrl();
	    	$credentials = array("username" => "pstam", "password" => "stam2011");
	        $json_credentials = json_encode($credentials);
	        $ch = curl_init($apiURL);
	        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_credentials);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Content-Length: ".strlen($json_credentials)));
	        $token = curl_exec($ch);
	        $token = json_decode($token);
	        //$this->UnsetCustomSessionForApiToken();
	        $this->SetCustomSessionForApiToken($token); 
	        return $this->GetCustomSessionForApiToken();
        } 
    }

    public function getMavisMultipleProductsPriceAndStock($products_sku) {
        $token_data = $this->getMavisAuthenticationKey();
        $token_info = explode("|||", $token_data); 
        if(strlen($token_info[0]) != 0) { 
        $headers = array("Authorization: Bearer ".$token_info[0]);
        $params = "storeId=99&CompanyId=1&BranchId=1&CustomerId=400005&";
        $final_params = $params.$products_sku;   
	         if(!empty($this->GetMultipleProductsStockUrl())) {	
	        	$requestUrl = $this->GetMultipleProductsStockUrl()."?".$final_params;
	        	$getresponse = array();
	        	$ch = curl_init($requestUrl);
		        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		        curl_setopt($ch, CURLOPT_FAILONERROR, true);
		        $result = curl_exec($ch);
		        if (curl_errno($ch)) {
	    			$getresponse['curl_error_msg'] = curl_error($ch);
				}
				curl_close($ch);

		        $getresponse["raw_response"] = htmlentities($result);
		        $result =  json_decode($result);
		        $getresponse["response_data"] = $result;
		        $getresponse["token_key"] = $token_info[0]."===".$token_info[1];

		        return $getresponse;
	        }
        }  else {
        	$getresponse = array();
        	$getresponse['curl_error_msg'] = "Token cannot be generated";
        	return $getresponse;
        } 
    }

    public function getMavisUpdatedStocksDataFromSpecificDateTime($fromdatetime) {
    	$token_data = $this->getMavisAuthenticationKey();
        $token_info = explode("|||", $token_data); 
        if(strlen($token_info[0]) != 0) { 
        $headers = array("Authorization: Bearer ".$token_info[0]);
        $params = "storeId=99&fromDate=".$fromdatetime."";
             if(!empty($this->GetProuductStockAvailabilityUrl())) {	
	        	$requestUrl = $this->GetProuductStockAvailabilityUrl()."?".$params;
	        	$getresponse = array();
	        	$ch = curl_init($requestUrl);
		        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		        curl_setopt($ch, CURLOPT_FAILONERROR, true);
		        $result = curl_exec($ch);
		        if (curl_errno($ch)) {
	    			$getresponse['curl_error_msg'] = curl_error($ch);
				}
				
		        $getresponse["raw_response"] = htmlentities($result);
		        $result =  json_decode($result);
		        $getresponse["response_data"] = $result;
		        $getresponse["token_key"] = $token_info[0]."===".$token_info[1];
		        $getresponse["request_url"] = $requestUrl;
		        
		        return $getresponse;
	        }
        }  else {
        	$getresponse = array();
        	$getresponse['curl_error_msg'] = "Token cannot be generated";
        	return $getresponse;
        }
    }


    public function getMavisProductsDeliveryDays() {
    	$token_data = $this->getMavisAuthenticationKey();
        $token_info = explode("|||", $token_data); 
        if(strlen($token_info[0]) != 0) { 
        $headers = array("Authorization: Bearer ".$token_info[0]);
        $params = "storeId=99";
             if(!empty($this->GetProductDeliveryDaysUrl())) {	
	        	$requestUrl = $this->GetProductDeliveryDaysUrl()."?".$params;
	        	$getresponse = array();
	        	$ch = curl_init($requestUrl);
		        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		        curl_setopt($ch, CURLOPT_FAILONERROR, true);
		        $result = curl_exec($ch);
		        if (curl_errno($ch)) {
	    			$getresponse['curl_error_msg'] = curl_error($ch);
				}
				
		        $getresponse["raw_response"] = htmlentities($result);
		        $result =  json_decode($result);
		        $getresponse["response_data"] = $result;
		        $getresponse["token_key"] = $token_info[0]."===".$token_info[1];
		        $getresponse["request_url"] = $requestUrl;
		        
		        return $getresponse;
	        }
        }  else {
        	$getresponse = array();
        	$getresponse['curl_error_msg'] = "Token cannot be generated";
        	return $getresponse;
        }
    }





}
	 