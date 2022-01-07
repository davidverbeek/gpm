<?php

/**
 * @category    Hs
 * @package     Hs_Pickup
 * @copyright   Copyright (c) 2019 Helios Solutions
 */
class Hs_Pickuplocation_Model_Observer
{
	
	/**
	 * Method is triggered when saving the shipping method.
	 * We use this to store the store location data.
	 *
	 * @see Mage_Checkout_OnepageController::saveShippingMethodAction()
	 * @param Varien_Event_Observer $observer
	 */
	public function checkoutControllerOnepageSaveShippingMethod($observer)
	{
		/** @var Mage_Core_Controller_Request_Http $request */
		$request = $observer->getRequest();

		/** @var Mage_Sales_Model_Quote $quote */
		$quote = $observer->getQuote();

		// remove the pickup addresses, if there are any
		Mage::helper('hs_pickuplocation/pickupaddress')->removePickupAddressFromQuote($quote);

		// check if a pickup address is required
		// totalsCollected is false here, because shipping method is updated but the totals are not collected yet.
		/* @see Mage_Checkout_OnepageController::saveShippingMethodAction */
		if (!Mage::helper('hs_pickuplocation')->isLocationSelectQuote($quote, false)) {
			// not a QLS shipping method with enabled location selector
			return;
		}

		if (!($pickupAddressData = $request->getPost('qls_pickup_address_data'))) {
			// No location data provided
			$errorMessage = Mage::helper('hs_pickuplocation')->__('A pickup location has to be selected');
			if ($request->isAjax()) {
				Mage::app()->getFrontController()->getResponse()
					->setHeader('Content-Type', 'application/json', true)
					->setBody(Mage::helper('core')->jsonEncode(array('error' => -1, 'message' => $errorMessage)))
					->sendResponse();
				exit;
			}
			return;
		}

		// base64 decode, convert Latin1 to UTF-8 and JSON decode
		$pickupAddressData = Zend_Json_Decoder::decode($pickupAddressData);
		// TODO: verify pickup address data
		Mage::helper('hs_pickuplocation/pickupaddress')
			->savePickupAddressIntoQuote($quote, $pickupAddressData['servicepoint']);
	}

	/**
	 * Method is triggered when converting the quote to the order.
	 *
	 * @see Mage_Sales_Model_Convert_Quote::toOrder()
	 * @param Varien_Event_Observer $observer
	 */
	public function salesConvertQuoteToOrder($observer)
	{
		/** @var Mage_Sales_Model_Order $order */
		$order = $observer->getOrder();

		/** @var Mage_Sales_Model_Quote $quote */
		$quote = $observer->getQuote();

		// check whether the quote uses a QLS Pickup method with a pickup address
		if (!Mage::helper('hs_pickuplocation')->isLocationSelectQuote($quote)) {
			return;
		}

		/** @var Mage_Sales_Model_Quote_Address $pickupAddress */
		if (!($pickupAddress = Mage::helper('hs_pickuplocation/pickupaddress')->getPickupAddressFromQuote($quote))) {
			// no pickup address
			Mage::throwException(Mage::helper('hs_pickuplocation')->__('A pickup location has to be selected'));
		}

		$orderAddress = Mage::getModel('sales/convert_quote')->addressToOrderAddress($pickupAddress);
		$orderAddress->setParentId($order->getId());

		$order->addAddress($orderAddress);
	}
}
