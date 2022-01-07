<?php
class Hs_Common_Helper_Data extends Mage_Core_Helper_Abstract {

	/**
	 * Global constants
	 */
	const GYZS_SKU_PREFIX = 'GY1';

	const LOG_FILE_NAME = 'unmatch_idealverpakking.log';

	const UNMATCHED_IDEALPACKAGEVERPAKKING_DIR = 'unmatchedIdealverpakking';
	
	const UNMATCHED_IDEALPACKAGEVERPAKKING_FILE = 'unmatch_idealverpakking.csv';

	/*
		Get Order Pending State
	*/
	public function getPendingState() {
		return array(Mage_Sales_Model_Order::STATE_NEW, Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, Mage_Sales_Model_Order::STATE_PROCESSING, Mage_Sales_Model_Order::STATE_HOLDED, Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW);
	}

	/*
		Get Order Complete State
	*/	
	public function getCompletedState() {
		return array(Mage_Sales_Model_Order::STATE_COMPLETE, Mage_Sales_Model_Order::STATE_CLOSED);
	}

	/*
		Get Order Cancel State
	*/
	public function getCanceledState() {
		return array(Mage_Sales_Model_Order::STATE_CANCELED);
	}

	/*
		Retrive customer Id from Session
	*/
	protected function _getCustomerID(){
		return Mage::getSingleton('customer/session')->getCustomer()->getId();
	}
	
	/*
		Get Customer Order Count
	*/	
	public function getOrderCount(){
		$orderCollection = Mage::getSingleton('sales/order')->getCollection()
			->addFieldToFilter('customer_id',$this->_getCustomerID());
		
		return $orderCollection->getSize();
	}

	/*
		Get Customer Quote Count
	*/
	public function getQuoteCount(){
		$quotesCollection = Mage::getSingleton('sales/quote')->getCollection()
			->addFieldToFilter('customer_id',$this->_getCustomerID())
			->addFieldToFilter('is_cart_auto',0);

		return $quotesCollection->getSize();
	}


	/*
		Get Customer Product Review Count
	*/
	public function getProductReviewCount(){
		$reviewCollection = Mage::getSingleton('review/review')->getProductCollection()
			->addStoreFilter(Mage::app()->getStore()->getId())
			->addCustomerFilter($this->_getCustomerID());

		return $reviewCollection->getSize();
	}

	/*
		Get Allowed Countries
	*/
	public function getCountries(){
		return Mage::getSingleton('directory/country')->getResourceCollection()
			->loadByStore()
			->toOptionArray(false);
	}

	/*
		Get Page Title for PDP Page
	*/
	public function getPageTitleDescription($description){

		$_product = Mage::registry('current_product');

		if(!$_product){
			return '';
		}

		$merkStrtoupper = strtoupper($_product->getResource()->getAttribute('merk')->getFrontend()->getValue($_product));
		$merkUcfirst = ucfirst(strtolower($_product->getResource()->getAttribute('merk')->getFrontend()->getValue($_product)));
		$merk = $_product->getResource()->getAttribute('merk')->getFrontend()->getValue($_product);

		// exclude merk from title
		$excludeMerk = array('onbekend','algemeen', 'nee', 'no');

		// Title for PDP Page
		$title = '';
		$maxTitleLength = 70;
		$titleLength = '';
		$titleRemainingLength = '';
		$titleLengthEan = '';
		$eanRemainingLength = '';
		
		$title .= (!$_product->isGrouped() && !preg_match('/\b'.$merk.'\b/',$_product->getName()) && !preg_match('/\b'.$merkStrtoupper.'\b/',$_product->getName()) && !preg_match('/\b'.$merkUcfirst.'\b/',$_product->getName()) && !in_array(strtolower($merk), $excludeMerk) )?$merk." ":'';
		$title .= ($_product->isGrouped()) ? $_product->getMetaTitle(): $_product->getName();
		
		$titleLength = strlen($title);
		$titleRemainingLength = ($maxTitleLength - $titleLength);
				
		if($titleRemainingLength >= 16) {
			$eanCode = $_product->getEancode();
			$title .= ($eanCode>0)?" - " . $eanCode:'';
			$titleLengthEan = strlen($title);
			$eanRemainingLength = ($maxTitleLength - $titleLengthEan);
		}
		
		if(isset($eanRemainingLength) &&  $eanRemainingLength >= 9){			
			$title .= (!is_null($_product->getLeverancierartikelnr()))?" " . $_product->getLeverancierartikelnr():'';
		}
		
		// Meta Description for PDP Page
		$metaDesc = '';
		$metaDesc .= (!is_null($merk) && !$_product->isGrouped() && !preg_match('/\b'.$merkStrtoupper.'\b/',$_product->getName()) && !preg_match('/\b'.$merkUcfirst.'\b/',$_product->getName()) && !in_array(strtolower($merk), $excludeMerk) )?$merk." ":'';
		$metaDesc .= $_product->getName();
		$metaDesc .= " " . htmlspecialchars(strip_tags($description));
		$metaDesc .= ($eanCode>0)?" - EAN:" . $eanCode:'';
		$metaDesc .= (!is_null($_product->getLeverancierartikelnr()))?" - MPN:" . $_product->getLeverancierartikelnr():'';

		return array('title' => $title, 'metaDesc' => $metaDesc);
		
	}


	/*
		Get Customer Default Shipping Address Country Code, If Customer not Logged in then Default Country Code 
	*/
	public function getCountry(){

		$customerAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();

		if($customerAddressId) {
			$address = Mage::getModel('customer/address')->load($customerAddressId);

			$code = $address->getCountryId();
			if(isset($code) && !empty($code)) {
				return $code;
			}
		}

		return Mage::getStoreConfig('general/country/default');
	}

	/*
		Get Cart subtotal including tax for check shipping cost
	*/
	protected function _getCartSubtotal(){

		$totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();
		
		return ($totals["subtotal"]->getValue())?$totals["subtotal"]->getValue():0;
	}

	/*
		Caclulate Shipping cost for PDP page to show shipping amount
	*/
	public function calculateShippingCost(){

		// Fetch shippingrates collection from country ID.
		$result = Mage::getResourceModel('shipping/carrier_tablerate_collection')
			->addFieldToSelect('dest_country_id')
			->addFieldToSelect('condition_value')
			->addFieldToSelect('price')
			->addFieldToFilter('dest_country_id', $this->getCountry());

		if($result) {
			foreach($result as $data) {
				$costarr[$data->getConditionValue()] = $data->getPrice();
			}
			$shipmentcost = $this->closest($costarr,$this->_getCartSubtotal());
		}

		return $shipmentcost;
	}

	public function shippingCost($country) {
		$shippingCost = array(
			'NL' => array(
				'0' => '11.9835',
				'100' => '8.6778'
			),
			'BE' => array(
				'0' => '26.8595',
				'100' => '20.6612'
			),
			'LU' => array(
				'0' => '35.1240',
				'100' => '28.9256'
			)
		);
		return isset($shippingCost[$country])?$shippingCost[$country]:'';
	}

	/*
		Caclulate Shipping cost for PDP page to show shipping amount
	*/
	public function calculateTransmissionShippingCost(){
		
		return $shipmentcost = $this->closest($this->shippingCost($this->getCountry()),$this->_getCartSubtotal());
	}

	/*
		get closest value
	*/
	function closest($array, $number) {
		$_product = Mage::registry('current_product');
		$prijsfactor = Mage::helper('featured')->getPrijsfactorValue($_product);
		$_priceIncludingTax = Mage::helper('tax')->getPrice($_product, $_product->getFinalPrice() * $prijsfactor, true, null, null, null, null, false);
		
		ksort($array);
		foreach ($array as $key => $value) {
			if($_priceIncludingTax >= 100 && $key == '100.0000' ){
								$arr[] = $value;
						}else{
								if ($key <= $number) $arr[] = $value;
						}
		}
		return end($arr); // or return NULL;
	}

	/*
		get Brand page url
		Depends on Amasty Shop by Brand Module
	*/
	public function getBrandUrl($brand) {

		$brandUrl = '';

		$storeId = Mage::app()->getStore()->getStoreId();
		/** @var Amasty_Brands_Model_Resource_Brand_Collection $collection */
		$collection = Mage::getModel('ambrands/brand')->getCollection();

		$collection->setStoreId($storeId);

		$collection
			->addAttributeToSelect('url_key')
			->addAttributeToSelect('name')
			->addFieldToFilter('name', $brand);

			$brandData = $collection->getFirstItem();
		
		if($collection->getSize()) {

			if(Mage::getStoreConfig('ambrands/general/url_key', Mage::app()->getStore())){
				$brandUrl .= Mage::getStoreConfig('ambrands/general/url_key', Mage::app()->getStore()) . DS;
			}

			if(Mage::getStoreConfig('catalog/seo/category_url_suffix', Mage::app()->getStore())){
				$brandUrl .= $brandData->getUrlKey() . Mage::getStoreConfig('catalog/seo/category_url_suffix', Mage::app()->getStore());
			} else {
				$brandUrl .= $brandData->getUrlKey();
			}

			return $brandUrl;
		} else {
			return "";	
		}
	}

	/*
		Convert URL to CDN URL
	*/
	public function convertCdnUrl($url, $baseUrl, $cdnUrl){
		$cdnUrl = str_replace('skin/', '', $cdnUrl);

		if($baseUrl == $cdnUrl){
			return $url;
		}

		return str_replace($baseUrl, $cdnUrl, $url);
	}

	/*
		CMS Page Product Block show fixed sku 
	*/
	public function getBijenbekjesProducts(){
		$_productCollection = Mage::getModel('catalog/product')->getCollection();
		$_productCollection->addAttributeToSelect('name');
		$_productCollection->addAttributeToSelect('image');
		$_productCollection->addAttributeToSelect('small_image');
		$_productCollection->addAttributeToSelect('thumbnail');
		$_productCollection->addFieldToFilter('sku', array( 'in' => array('1091121', '1091120', '1091125', '1091127')));

		return $_productCollection;
	}

	/*
		CMS Page Product Block show fixed sku 
	*/
	public function getTrapleuninghouderProducts(){
		$_productCollection = Mage::getModel('catalog/product')->getCollection();
		$_productCollection->addAttributeToSelect('name');
		$_productCollection->addAttributeToSelect('image');
		$_productCollection->addAttributeToSelect('small_image');
		$_productCollection->addAttributeToSelect('thumbnail');
		$_productCollection->addFieldToFilter('sku', array( 'in' => array('1314696','1314697','1314688','1314687','1313095','1348218','1348220','1314695','1348222','IN00775','IN00776','IN00777')));

		return $_productCollection;
	}

	/*
		CMS Page Product Block show fixed sku 
	*/
	public function getGehoorbeschermingProducts(){
		$_productCollection = Mage::getModel('catalog/product')->getCollection();
		$_productCollection->addAttributeToSelect('name');
		$_productCollection->addAttributeToSelect('image');
		$_productCollection->addAttributeToSelect('small_image');
		$_productCollection->addAttributeToSelect('thumbnail');
		$_productCollection->addFieldToFilter('sku', array( 'in' => array('1643431','1991475')));

		return $_productCollection;
	}


/*
		CMS Page Product Block show fixed sku 
	*/
	public function getTaatsdeurscharnierenProducts(){
		$_productCollection = Mage::getResourceModel('catalog/product_collection')
		->joinField('category_id','catalog/category_product','category_id','product_id=entity_id',null,'left');

		$_productCollection->addAttributeToSelect('name');
		$_productCollection->addAttributeToSelect('image');
		$_productCollection->addAttributeToSelect('small_image');
		$_productCollection->addAttributeToSelect('thumbnail');
		$_productCollection->addFieldToFilter('category_id', array( 'in' => array('3062')));
		
		$_productCollection->getSelect()->order('position', 'asc');

		return $_productCollection;
	}
	/*
		Get Custom Filter for perticular product from any category assigned
	*/
	public function getCustomFilterForGroupProduct($categoryIds, $customFilters){

		$custom_filters = '';

		$countCategories = count($categoryIds);

		for ($i=$countCategories-1; $i >= 0; $i--) {

			$catId = $categoryIds[$i];

			if(isset($customFilters[$catId])){
				return array($customFilters [$catId], $customFilters);
			}

			$category=Mage::getSingleton('catalog/category')->load($catId);
			$custom_filters = explode(',', $category->getData('custom_filters'));
			$customFilters [$catId] = $custom_filters;

			return array($custom_filters, $customFilters);

		}

		return array($custom_filters, $customFilters);
	}

	
	/*
			get tax percent
	*/
	public function getTaxPercent($_product) {
			$taxCalculation = Mage::getModel('tax/calculation');
			$request = $taxCalculation->getRateRequest(null, null, null, null);
			$taxClassId = $_product->getTaxClassId();
			$percent = $taxCalculation->getRate($request->setProductClassId($taxClassId));

			return $percent;
	}

	public function getShipmentIncTax($taxpercent, $tansmission){
		if($tansmission){
			$shipmentCostExclTax = $this->calculateTransmissionShippingCost();
		} else {
			$shipmentCostExclTax = $this->calculateShippingCost();
		}
			$shipmetAmount = round($shipmentCostExclTax * ($taxpercent/100),2);
			return ($shipmentCostExclTax + $shipmetAmount);
	}

	public function getGYZSSku($sku) {
		return Hs_Common_Helper_Data::GYZS_SKU_PREFIX . $sku;
	}

	public function getSkuFromGyzsSku($gyzsSku) {
		return str_replace(Hs_Common_Helper_Data::GYZS_SKU_PREFIX, '', $gyzsSku);
	}

	public function getProductInclPrice($product)	{

		//$prijsfactor = $product->getPrijsfactor();
		$prijsfactor = Mage::helper('featured')->getPrijsfactorValue($product);
		$finalPriceInclTax = Mage::helper('tax')->getPrice($product, $product->getFinalPrice() * $prijsfactor, true);
		return number_format($finalPriceInclTax, '2');
	}
	
	public function isMobile(){
		return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
	}

	public function getIcepayLogoResult($paymentMethodCode)	{
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$query = 'SELECT * FROM ' . $resource->getTableName('iceadvanced/icepay_pmdata') . ' where pm_id=' . $paymentMethodCode[1];
		$results = $readConnection->fetchRow($query);
		return $results;
	}

	public function sendEmailForManualProduct($order)	{

		// Get Config variables
		$subject = Mage::getStoreConfig('sales_email/manualproductorder/subject', Mage::app()->getStore());
		$name = Mage::getStoreConfig('sales_email/manualproductorder/name', Mage::app()->getStore());
		$emailTo = Mage::getStoreConfig('sales_email/manualproductorder/email', Mage::app()->getStore());
		$emailFrom = Mage::getStoreConfig('trans_email/ident_sales/email', Mage::app()->getStore());
		$emailFromName = Mage::getStoreConfig('trans_email/ident_sales/name', Mage::app()->getStore());
		
		Mage::log("MANUAL PRODUCT ORDER #".$order->getIncrementId(), null, 'manualProductOrder.log');

		$subject .= " Order #" . $order->getIncrementId();

		$channel = ($order->getEffectconnect() != '') ? 'EFFECTCONNECT' : 'GYZS-WEBSHOP';
		
		$emailBody = '';
		$emailBody .= "<h4>Order Information</h4>";
		$emailBody .= "<table style='width:50%' border=1>";
		$emailBody .= "<tr>";
		$emailBody .= "<td>Order #</td>";
		$emailBody .= "<td>&nbsp;" . $order->getIncrementId() . "</td>";
		$emailBody .= "</tr>";
		$emailBody .= "<tr>";
		$emailBody .= "<td>Channel #</td>";
		$emailBody .= "<td>&nbsp;" . $channel . "</td>";
		$emailBody .= "</tr>";
		$emailBody .= "<tr>";
		$emailBody .= "<td>Billing Address</td>";
		$emailBody .= "<td>&nbsp;" . $order->getBillingAddress()->getFormated() . "</td>";
		$emailBody .= "</tr>";
		$emailBody .= "<tr>";
		$emailBody .= "<td>Shipping Address</td>";
		$emailBody .= "<td>&nbsp;" . $order->getShippingAddress()->getFormated() . "</td>";
		$emailBody .= "</tr>";
		$emailBody .= "</table>";

		$emailBody .= "<h4>Order Item(s)</h4>";
		$emailBody .= "<table style='width:50%' border=1>";

		foreach ($order->getAllItems() as $orderItem) {
			if($orderItem->getManualproduct()){
				
				$leverancierartikelnr = ($orderItem->getProduct()->getLeverancierartikelnr() != '') ? $orderItem->getProduct()->getLeverancierartikelnr() : 'N/A';
				
				$emailBody .= "<tr>";
				$emailBody .= "<td>Name</td>";
				$emailBody .= "<td>&nbsp;" . $orderItem->getName() . "</td>";
				$emailBody .= "</tr>";
				$emailBody .= "<tr>";
				$emailBody .= "<td>SKU</td>";
				$emailBody .= "<td>&nbsp;" . $orderItem->getSku() . "</td>";
				$emailBody .= "</tr>";
				$emailBody .= "<tr>";
				$emailBody .= "<td>Leveranciersartikelnr</td>";
				$emailBody .= "<td>&nbsp;" . $leverancierartikelnr . "</td>";
				$emailBody .= "</tr>";
				$emailBody .= "<tr>";
				$emailBody .= "<td>Ordered QTY</td>";
				$emailBody .= "<td>&nbsp;" . $orderItem->getQtyOrdered() . "</td>";
				$emailBody .= "</tr>";
			}
		}

		$emailBody .= "</table>";

		//Send E-Mail.
		$mail = Mage::getModel('core/email')
			->setToName($name)
			->setToEmail(explode(',', $emailTo))
			->setBody($emailBody)
			->setSubject($subject)
			->setFromEmail($emailFrom)
			->setFromName($emailFromName)
			->setType('html');

		try{
			//Confimation E-Mail Send
			$mail->send();
			Mage::log("email successfully sent to " . $emailTo, null, 'manualProductOrder.log');
		} catch(Exception $e) {
		 Mage::log($e->getMessage(), null, 'manual_product_email_error.log');
		 return false;
		}
	}
	
	public function sendEmailForHigherCostAtBol($order)	{

		// Get Config variables
		$subject = "ORDER CHECKEN: hogere kosten dan inkomsten";
		$name = Mage::getStoreConfig('sales_email/manualproductorder/name', Mage::app()->getStore());
		$emailTo = Mage::getStoreConfig('sales_email/manualproductorder/email', Mage::app()->getStore());
		$emailFrom = Mage::getStoreConfig('trans_email/ident_sales/email', Mage::app()->getStore());
		$emailFromName = Mage::getStoreConfig('trans_email/ident_sales/name', Mage::app()->getStore());
		
		Mage::log("ORDER CONTAINS HIGHER COST AT BOL.COM #".$order->getIncrementId(), null, 'higerCostAtBolOrder.log');

		$subject .= " Order #" . $order->getIncrementId();

		$emailBody = '';
		$emailBody .= "<h4>Order Information</h4>";
		$emailBody .= "<table style='width:50%' border=1>";
		$emailBody .= "<tr>";
		$emailBody .= "<td>Order #</td>";
		$emailBody .= "<td>&nbsp;" . $order->getIncrementId() . "</td>";
		$emailBody .= "</tr>";
		$emailBody .= "</table>";

		$emailBody .= "<h4>Order Item(s)</h4>";
		$emailBody .= "<table style='width:50%' border=1>";

		foreach ($order->getAllItems() as $orderItem) {
			
				$idealeverpakking = $orderItem->getIdealeverpakking();
				$qty_ordered = $orderItem->getQtyOrdered();
				$base_cost = $orderItem->getBaseCost();
				$final_cost = (($qty_ordered * $idealeverpakking) * $base_cost);
				$row_total = $orderItem->getRowTotalInclTax();
							
				$higerCost = ($final_cost > $row_total) ? 1 : 0;
				
				if($higerCost){
					
					$emailBody .= "<tr>";
					$emailBody .= "<td>Name</td>";
					$emailBody .= "<td>&nbsp;" . $orderItem->getName() . "</td>";
					$emailBody .= "</tr>";
					$emailBody .= "<tr>";
					$emailBody .= "<td>SKU</td>";
					$emailBody .= "<td>&nbsp;" . $orderItem->getSku() . "</td>";
					$emailBody .= "</tr>";
					$emailBody .= "<tr>";
					$emailBody .= "<td>Cost</td>";
					$emailBody .= "<td>&nbsp;" . $final_cost . "</td>";
					$emailBody .= "</tr>";
					$emailBody .= "<tr>";
					$emailBody .= "<td>Row Total(Inc.Tax)</td>";
					$emailBody .= "<td>&nbsp;" . $row_total . "</td>";
					$emailBody .= "</tr>";
					
				}
			
		}

		$emailBody .= "</table>";

		//Send E-Mail.
		$mail = Mage::getModel('core/email')
			->setToName($name)
			->setToEmail(explode(',', $emailTo))
			->setBody($emailBody)
			->setSubject($subject)
			->setFromEmail($emailFrom)
			->setFromName($emailFromName)
			->setType('html');

		try{
			//Confimation E-Mail Send
			$mail->send();
			Mage::log("email successfully sent to " . $emailTo, null, 'higerCostAtBolOrder.log');
		} catch(Exception $e) {
		 Mage::log($e->getMessage(), null, 'higerCostAtBolOrder_email_error.log');
		 return false;
		}
	}

	// get connection based on requirment
	protected function _getConnection($type = 'core_read') {
		return Mage::getSingleton('core/resource')->getConnection($type);
	}

	/**
	 * get magento table name
	 *
	 * @param $tableName
	 *
	 * @return string
	 */
	public function _getTableName($tableName) {
		return Mage::getSingleton('core/resource')->getTableName($tableName);
	}

	/**
	 * Get product collection for all products sales data by product sku
	 *
	 * @return Object
	 */
	public function getProductCollectionForUnmatchIdealverpakking() {
		try {
			//Check if the directory already exists.
			if(!is_dir(Mage::getBaseDir('var') . DS . Hs_Common_Helper_Data::UNMATCHED_IDEALPACKAGEVERPAKKING_DIR)){
				mkdir(Mage::getBaseDir('var') . DS . Hs_Common_Helper_Data::UNMATCHED_IDEALPACKAGEVERPAKKING_DIR, 0755, true);
			}

			$file = fopen(Mage::getBaseDir('var') . DS . Hs_Common_Helper_Data::UNMATCHED_IDEALPACKAGEVERPAKKING_DIR . DS . Hs_Common_Helper_Data::UNMATCHED_IDEALPACKAGEVERPAKKING_FILE, 'w');

			fputcsv($file, array('sku', 'afwijkenidealeverpakking', 'idealeverpakking', 'verpakkingseanhoeveelheid'));

			// idealeverpakking Attribute Id
			$idealeverpakking = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'idealeverpakking');

			// afwijkenidealeverpakking Attribute Id
			$afwijkenidealeverpakking = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'afwijkenidealeverpakking');

			// verpakkingseanhoeveelheid_ Attribute Id
			$verpakkingseanhoeveelheid_ = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'verpakkingseanhoeveelheid_');


			$_productCollection = Mage::getResourceModel('catalog/product_collection')
			->addAttributeToSelect(array('sku'));

			$_productCollection->getSelect()

				// Join idealeverpakking from EAV
				->joinLeft(array('eav_varchar' => $this->_getTableName('catalog_product_entity_varchar')), 'e.entity_id = eav_varchar.entity_id and eav_varchar.attribute_id=' . $idealeverpakking, array('idealeverpakking' => 'value'))
				
				// Join afwijkenidealeverpakking from EAV
				->joinLeft(array('eav_varchar_' => $this->_getTableName('catalog_product_entity_varchar')), 'e.entity_id = eav_varchar_.entity_id and eav_varchar_.attribute_id=' . $afwijkenidealeverpakking, array('afwijkenidealeverpakking' => 'value'))

				// Join verpakkingseanhoeveelheid_ from EAV
				->joinLeft(array('eav_int' => $this->_getTableName('catalog_product_entity_int')), 'e.entity_id = eav_int.entity_id and eav_int.attribute_id=' . $verpakkingseanhoeveelheid_, array('verpakkingseanhoeveelheid_' => 'value'))

				// Join verpakkingseanhoeveelheid_ with option values
				->joinLeft(array('eav_int_option_value' => $this->_getTableName('eav_attribute_option_value')), 'eav_int.value = eav_int_option_value.option_id and eav_int.attribute_id=' . $verpakkingseanhoeveelheid_, array('verpakkingseanhoeveelheid_value' => 'value'))

				->where("e.sku <> '' and e.type_id='simple'")
				->where("eav_varchar_.value = 0")
				->where("eav_int_option_value.value != eav_varchar.value");


			foreach ($_productCollection as $product) {
				$tempSKUArray = array();
				$tempSKUArray['sku'] = $product->getSku();
				$tempSKUArray['afwijkenidealeverpakking'] = $product->getAfwijkenidealeverpakking();
				$tempSKUArray['idealeverpakking'] = $product->getIdealeverpakking();
				$tempSKUArray['verpakkingseanhoeveelheid'] = $product->getVerpakkingseanhoeveelheidValue();
				fputcsv($file, $tempSKUArray);
			}

			fclose($file);
			
			$status = $this->sendEmailForUnmatched();

			return $status;

		} catch (Exception $e) {
			Mage::log($e->getMessage(), null, Hs_Common_Helper_Data::LOG_FILE_NAME);
		}
	}

	protected function sendEmailForUnmatched($value=''){
		// Get Config variables
		$subject = 'Unmatched idealeverpakking';
		$name = Mage::getStoreConfig('sales_email/manualproductorder/name', Mage::app()->getStore());
		$emailTo = Mage::getStoreConfig('sales_email/manualproductorder/email', Mage::app()->getStore());
		$emailFrom = Mage::getStoreConfig('trans_email/ident_sales/email', Mage::app()->getStore());
		$emailFromName = Mage::getStoreConfig('trans_email/ident_sales/name', Mage::app()->getStore());

		$emailBody = 'Unmatched idealeverpakking, please check attachement.';
		//Send E-Mail.
		$mail = new Zend_Mail('utf-8');
		$mail->addTo(explode(',', $emailTo))	
			->setBodyHtml($emailBody)
			->setSubject($subject)
			->setFrom($emailFrom, $emailFromName);

		$file = Mage::getBaseDir('var') . DS . Hs_Common_Helper_Data::UNMATCHED_IDEALPACKAGEVERPAKKING_DIR . DS . Hs_Common_Helper_Data::UNMATCHED_IDEALPACKAGEVERPAKKING_FILE;
		$attachment = file_get_contents($file);

		$mail->createAttachment(
			$attachment,
			Zend_Mime::TYPE_OCTETSTREAM,
			Zend_Mime::DISPOSITION_ATTACHMENT,
			Zend_Mime::ENCODING_BASE64,
			Hs_Common_Helper_Data::UNMATCHED_IDEALPACKAGEVERPAKKING_FILE
		);

		try{
			//Confimation E-Mail Send
			$mail->send();
			Mage::log("email successfully sent to " . $emailTo, null, Hs_Common_Helper_Data::LOG_FILE_NAME);
			return true;
		} catch(Exception $e) {
		 Mage::log($e->getMessage(), null, Hs_Common_Helper_Data::LOG_FILE_NAME);
		 return false;
		}
	}

}
	 