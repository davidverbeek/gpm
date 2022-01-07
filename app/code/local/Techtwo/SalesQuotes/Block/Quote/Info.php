<?php

class Techtwo_SalesQuotes_Block_Quote_Info extends Mage_Checkout_Block_Onepage_Abstract
{
	public function getCountries()
	{
		return Mage::getResourceModel('directory/country_collection')->loadByStore();
	}

	function getAddress() {
		// if (!$this->isCustomerLoggedIn()) {
		//     return $this->getQuote()->getBillingAddress();
		// } elseif(Mage::getSingleton('customer/session')->getCustomer() && Mage::getSingleton('customer/session')->getCustomer()->getPrimaryAddress('default_billing')) {
		//     return Mage::getSingleton('customer/session')->getCustomer()->getPrimaryAddress('default_billing');
		// } else {
		//     return Mage::getModel('sales/quote_address');
		// }


		/*
		* Change by Parth
		* initially returning default billing for logged in customer 
		* change to quote's selected address istead default
		*/
		if (!$this->isCustomerLoggedIn()) {
			return $this->getQuote()->getShippingAddress();
		} else if ($this->isCustomerLoggedIn()) {
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			
			$quote = Mage::getSingleton('sales/quote')->loadByCustomer($customer->getId());

			$customerAddressId = $quote->getShippingAddress()->getCustomerAddressId();

			$customerAddress = Mage::getSingleton('customer/address')->load($customerAddressId);

			return $customerAddress;
		} else {
			return Mage::getModel('sales/quote_address');
		}
	}

	public function getFirstname()
	{
		$firstname = $this->getAddress()->getFirstname();
		if (empty($firstname) && $this->getQuote()->getCustomer()) {
			return $this->getQuote()->getCustomer()->getFirstname();
		}
		return $firstname;
	}

	public function getLastname()
	{
		$lastname = $this->getAddress()->getLastname();
		if (empty($lastname) && $this->getQuote()->getCustomer()) {
			return $this->getQuote()->getCustomer()->getLastname();
		}
		return $lastname;
	}

	public function getCustomer()
	{
		return Mage::getSingleton('customer/session')->getCustomer();
	}
	public function getCallingCode(){
		
		$countryList = Mage::getModel('directory/country')->getResourceCollection()
							->loadByStore()
							->toOptionArray(true);
		
		foreach($countryList as $_country){
			if($_country['value'])
				$countryCode[]=$_country['value'];
		}
		$newCountryCode=  implode($countryCode, ";");
		
		$url = "https://restcountries.eu/rest/v1/alpha?codes=".$newCountryCode;
				
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		$content = curl_exec( $ch );
		curl_close ( $ch );
		
		return Mage::helper('core')->jsonEncode($content);
	}
}
