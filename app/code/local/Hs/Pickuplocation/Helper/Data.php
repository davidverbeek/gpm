<?php
class Hs_Pickuplocation_Helper_Data extends Mage_Core_Helper_Abstract {

	/**
	 * Check the shipping address for the given quote to see if the location selector is enabled (and thus, a pickup
	 * address is required).
	 *
	 * @param Mage_Sales_Model_Quote $quote
	 * @param bool $totalsCollected False if $quote->collectTotals() still needs to be called
	 * @return bool
	 */

	const PICKUP_METHOD_NAME = 'hs_pickuplocation_hs_pickuplocation';

	protected $_pickupUrl = 'https://embed.pakketdienstqls.nl/servicepoint/locator/';
	protected $_country = 'NL';
	protected $_color = '38ac37';

	public function isLocationSelectQuote($quote, $totalsCollected = true) {
		$enableLocationSelect = false;

		if ($shippingAddress = $quote->getShippingAddress()) {
			if($shippingAddress->getShippingMethod() == 'hs_pickuplocation_hs_pickuplocation'){
				$enableLocationSelect = true;
			}
		}

		return $enableLocationSelect;
	}

	// If Already selected Address
	public function getPickupLocationIfSelected($quote) {
		$pickupAddressData = Mage::helper('hs_pickuplocation/pickupaddress')->getPickupAddressFromQuote($quote);

		if($pickupAddressData){
			$result = $pickupAddressData->getCompany();
			$result .= '<br>';
			$result .= $pickupAddressData->getStreetFull();
			$result .= '<br>';
			$result .= $pickupAddressData->getPostcode();
			$result .= ' ';
			$result .= $pickupAddressData->getCity();
			return $result;
		}
		return '';
	}

	// ServicePoint Code 
	public function getPickupLocationCode($quote) {
		$pickupAddressData = Mage::helper('hs_pickuplocation/pickupaddress')->getPickupAddressFromQuote($quote);

		return ($pickupAddressData)?$pickupAddressData->getQlsServicepointCode():'';

	}	

	// Prepare Pickuplocation URL
	public function getPickupLocationUrl($quote) {
		if($quote->getShippingAddress()->getCountryId()){
			$this->_country = $quote->getShippingAddress()->getCountryId();
		}
		$postcode = $quote->getShippingAddress()->getPostcode();

		$url = $this->_pickupUrl . $this->_country . '?color=' . $this->_color;

		if($postcode){
			$url .= '#postalcode=' . $postcode;
		}

		return $url;
	}

}