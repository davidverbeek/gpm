<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Hs_Multistepcheckout_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getDeliveryDay() {	
		$englishMonth=array('January','February','March','April','May','June','July','August','September','October','November','December');
		$duthcMonth=array('januari','februari','maart','april','mei','juni','juli','augustus','september','oktober','november','december');
		
		$instock=0;
		$items=Mage::registry('cart_stock_data');
		
		//print_r($items);
		
		$transferroItem = $this->isTransferroItem();
		//echo $transferroItem;
		//echo "<br>";
		foreach($items as $_item) {
			//echo $_item->Artinr;
		//echo "<br>";
			if($_item->instock==0){
				$delivaryDate[]=$_item->Levertijd;
			} else {
				$instock++;
			}
		}


		$curentDate=Mage::getModel('core/date')->date('Y-m-d H:i:s');
		$dw = date( "w",  strtotime($curentDate));
		$time=Mage::getModel('core/date')->date('H:i');
		
		//$instock = 0;
		/*$curentDate = '2020-04-12';
		$dw = '0';
		$time = '12:43';
		$time = '14:45';
		/*echo $curentDate;
		echo "<br/>";
		
		echo $dw;
		echo "<br/>";
		
		$time = '12:43';
		echo $time;
		echo "<br/>";
		echo $instock;
		echo "<br/>";
		echo count($items);
		echo "<br/>";
		echo $transferroItem;
		
		
		echo "<br/>";*/
				
		if($instock==count($items)){
				
			if($transferroItem) {
					
				$shipmentDate1 = ($dw > '0' && $dw < '4' && $time >= '14:00') ? 2 : 1; 
				$shipmentDate2 = ($dw == '4' && $time >= '14:00') ? 4 : 1; 
				$shipmentDate3 = ($dw == '5' && $time >= '14:00') ? 4 : 3; 
				$shipmentDate4 = ($dw == '6' && $time >= '14:00') ? 3 : 3; 
				$shipmentDate5 = ($dw == '0' && $time >= '14:00') ? 2 : 2; 
					
					
				if($dw>'0' && $dw<'4') {
					$shipmentDate =  $shipmentDate1;
				} elseif($dw=='4') {
					$shipmentDate = $shipmentDate2;
				} elseif($dw=='5') {
					$shipmentDate = $shipmentDate3;
				} elseif($dw=='6') {
					$shipmentDate = $shipmentDate4;
				} elseif($dw=='0') {
					$shipmentDate = $shipmentDate5;
				}
				$newDate = $this->transferroWorkingDates($shipmentDate,$curentDate);
				$message=$this->__('Expected Delivery ').date('j F',$newDate);
				$FinalMessage=$this->__('Usually ships on ').date('j F',$newDate);
				
			}elseif($time>='16:30'){
				//echo "U R IN IF";
				$finalDeliveryDate=0;
				$newDate = $this->workingDates($finalDeliveryDate,$curentDate);//date("Y-m-d",strtotime("+".$finalDeliveryDate." days", strtotime($curentData)));
				if($dw>'0' && $dw<'5') {
					$message=$this->__('Expected Delivery ').date('j F',$newDate);
					$FinalMessage=$this->__('Usually ships on ').date('j F',$newDate);
				} elseif($dw=='5') {
					$finalDeliveryDate=1;
					$newDate = $this->workingDates($finalDeliveryDate,$curentDate);//date("Y-m-d",strtotime("+".$finalDeliveryDate." days", strtotime($curentData)));
					$message=$this->__('Expected Delivery ').date('j F',$newDate);
					$FinalMessage=$this->__('Usually ships on ').date('j F',$newDate);
				} elseif($dw=='6') {
					$finalDeliveryDate=1;
					$newDate = $this->workingDates($finalDeliveryDate,$curentDate);//date("Y-m-d",strtotime("+".$finalDeliveryDate." days", strtotime($curentData)));
					$message=$this->__('Expected Delivery ').date('j F',$newDate);
					$FinalMessage=$this->__('Usually ships on ').date('j F',$newDate);
				} elseif($dw=='0') {
					$finalDeliveryDate=1;
					$newDate = $this->workingDates($finalDeliveryDate,$curentDate);//date("Y-m-d",strtotime("+".$finalDeliveryDate." days", strtotime($curentData)));
					$message=$this->__('Expected Delivery ').date('j F',$newDate);
					$FinalMessage=$this->__('Usually ships on ').date('j F',$newDate);
				}
			} elseif($time <'16:30') {
				//echo "U R IN ELSEIF";
				$finalDeliveryDate=0;
				$newDate = $this->workingDates($finalDeliveryDate,$curentDate);//date("Y-m-d",strtotime("+".$finalDeliveryDate." days", strtotime($curentData)));
				if($dw>'0' && $dw<='5') {
					$message=$this->__('Expected Delivery ').date('j F',$newDate);
					$FinalMessage=$this->__('Usually ships on ').date('j F',$newDate);
				} elseif($dw=='6') {
					$finalDeliveryDate=1;
					$newDate = $this->workingDates($finalDeliveryDate,$curentDate);//date("Y-m-d",strtotime("+".$finalDeliveryDate." days", strtotime($curentData)));
					$message=$this->__('Expected Delivery ').date('j F',$newDate);
					$FinalMessage=$this->__('Usually ships on ').date('j F',$newDate);
				} elseif($dw=='0') {
					$finalDeliveryDate=1;
					$newDate = $this->workingDates($finalDeliveryDate,$curentDate);//date("Y-m-d",strtotime("+".$finalDeliveryDate." days", strtotime($curentData)));
					$message=$this->__('Expected Delivery ').date('j F',$newDate);
					$FinalMessage=$this->__('Usually ships on ').date('j F',$newDate);
				}
			}
		}	else {
			//echo "U R IN ELSE";
		  $finalDeliveryDate = max($delivaryDate);
		  
		  if($transferroItem) {
			  //echo "U R IN TRANSFERRO OUT OF STOCK";
				$days = 21;
				$newDate = $this->deliverydateOutofstock($curentDate,$days);
				
				$message=$this->__('Expected Delivery ').$newDate;
				$FinalMessage=$this->__('Usually ships on ').$newDate;
				
					
		  } elseif($time>='16:30') {
				//echo "HELLO";
				$newDate = $this->workingDates($finalDeliveryDate,$curentDate);
				if($dw>'0' && $dw<'5') {
					$message=$this->__('Expected Delivery ').date('j F',$newDate);
					$FinalMessage=$this->__('Usually ships on ').date('j F',$newDate);
				} else {
					$message=$this->__('Expected Delivery ').date('j F',$newDate);
					$FinalMessage=$this->__('Usually ships on ').date('j F',$newDate);
				}

			} elseif($time <'16:30') {
				//echo "HELLO WORLD";
				$newDate = $this->workingDates($finalDeliveryDate,$curentDate);
				
				if($dw>'0' && $dw<'5') {
					$message=$this->__('Expected Delivery ').date('j F',$newDate);
					$FinalMessage=$this->__('Usually ships on ').date('j F',$newDate);
				} else {
					$message=$this->__('Expected Delivery ').date('j F',$newDate);
					$FinalMessage=$this->__('Usually ships on ').date('j F',$newDate);
				}
			}
		}
	   
		foreach($englishMonth as $ekey=>$_eng){
			if(strstr($message,$_eng)){
				$newMessage=str_replace($_eng,$duthcMonth[$ekey],$message);
			}
			if(strstr($FinalMessage,$_eng)){
				$newFinalMessage=str_replace($_eng,$duthcMonth[$ekey],$FinalMessage);
			}
		}

		Mage::getSingleton('checkout/session')->setExpectedDeliveryDateTime(date('Y-m-d H:i:s',$newDate));
		Mage::getSingleton('checkout/session')->setDeliveryDateTime($newFinalMessage);
		//$message=$this->__('Your order will be deliver to next day.');
		//echo  $newMessage;
		
		return $newFinalMessage;
	}
	
	public function workingDates($days,$curentDate) {

		$skipdays = array("Saturday", "Sunday");
		$timestamp=strtotime($curentDate);
		$time=Mage::getModel('core/date')->date('H:i');
		$i = 1;
		if($days=='0' && $time >='16:30')
		{
			$timestamp = strtotime("+1 day", $timestamp);
		}

		while ($days >= $i) {
			$timestamp = strtotime("+1 day", $timestamp);

			if ( (in_array(date("l", $timestamp), $skipdays)) )
			{
			  $days++;
			}
			
			$i++;
		}
		return $timestamp;
	}
	
	public function transferroWorkingDates($shipmentdate,$curentDate) {

		$timestamp=strtotime($curentDate);
		$timestamp = strtotime("+".$shipmentdate." day", $timestamp);
		
		return $timestamp;
	}
	
	public function deliverydateOutofstock($curentDate,$days) {

		//echo $curentDate;
		$d = new DateTime( $curentDate );
		$t = $d->getTimestamp();

		// loop for X days
		for($i=0; $i<$days; $i++){

		// add 1 day to timestamp
		$addDay = 86400;

		// get what day it is next day
		$nextDay = date('w', ($t+$addDay));

		// if it's Saturday or Sunday get $i-1
		if($nextDay == 0 || $nextDay == 6) {
			$i--;
		}

		// modify timestamp, add 1 day
		$t = $t+$addDay;
		}

		$d->setTimestamp($t);

		return $d->format( 'j F' );
		
		
	}
	
	/**
	* getCurrentTime()
	* To get current time according to current timezone set in database.
	*/
	public function getCurrentTime() {
		$current_timezone = Mage::getStoreConfig('general/locale/timezone');
		date_default_timezone_set($current_timezone);
		$t = time();
		$currenttime = date('H:i',$t);
		return $currenttime;
	}

	/**
	 * Name: checkSdd
	 * function : this function is used to check whether same day Delivery is available *r   not
	 *
	 */
	public function checkSdd($quote) {

		$isEnabled = Mage::getStoreConfig('samedaydelivery/general/enabled');
		if(!$isEnabled) {
			return false;
		}

		if($this->getSddFromConfig() <= 0) {
			return false;
		}
				
		
		$weekDay = Mage::getModel('core/date')->date('l');
		//do not allow same day delivery on saturday and sunday
		if($weekDay == "Saturday" or $weekDay == "Sunday") {
			return false;
		}

		$currentTime = Mage::getModel('core/date')->date('Hi');
		//do not allow same day delivery if the time is more than 12:00
		if($currentTime > "1200") {
			return false;
		}


		$shippingAddress = $quote->getShippingAddress();

		if($shippingAddress->getCountryId() == 'NL') {

			//check if any product is transmission or not
			$cart = Mage::getModel('checkout/cart')->getQuote();

			$items = $cart->getAllItems();


			foreach ($items as $item) {

				$product = $item->getProduct()->load();
				if($product->getData('tansmission') == 1 || $this->checkStock($product) < $item->getQty()) {
					return false;
				}
			}

			return true;
		}

		return false;
	}

	/***
	 * function: this function is used to check stock of the product from mavis apis
	 * name: checkStock
	 * @param #$product -> object of product model
	 */

	public function checkStock($product) {
		$params = Mage::helper('price')->buildParams($product, false, null, false, false);
		$params['bedrijfnr'] = '1';
		$params['filiaalnr'] = '1';
		$client = new Zend_Soap_Client((string)Mage::helper('price')->_config->apipath);
		$result = $client->GetVoorraad($params);
		//$result = Mage::helper('price')->getVoorraad($product);

		if($product->getData(afwijkenidealeverpakking) != 0){
			$availableqty = $result->GetVoorraadResult;
		} else{
			$availableqty = (int)($result->GetVoorraadResult / $product->getData(idealeverpakking));
		}

		return $availableqty;

	}


	/**
	 * name: getSddFromConfig
	 * function: This function is used to fetch same day delivery price from configuration
	 *
	 */
	public function getSddFromConfig() {

		$smount = Mage::getStoreConfig('samedaydelivery/general/fee');

		if(empty($smount)) {

			$smount = 0;
		}

		return $smount;

	}


	/**
	 * Name: getRemainingHours
	 * Function: This function will return Remaing Time.
	 *
	 */

	public function getRemainingHours() {

		//do not allow same day delivery if the time is more than 12:30

		$currentTime = Mage::getModel('core/date')->date('H:i');
		$lastWindowTime = '12:30';

		$time1 = strtotime($currentTime);
		$time2 = strtotime($lastWindowTime);

		$remainingHrs = ($time2 - $time1) ;

		$rH = date('H',$remainingHrs);
		$rM = date('i',$remainingHrs);

		$remainingHrs = date('H:i',$remainingHrs);

		return array('h'=>$rH,'m'=>$rM);

	}

	/**
	  *	isCountrySelectAvailable()
	  * To check whether we put dropdown on total block or just text.
	  */	
	public function isCountrySelectAvailable($route = NULL,$action = NULL) {
		if($route) {
			if($route == 'checkout') {
				return true;
			} else if($route == 'multistepcheckout' && $action == 'updatecartItem') {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	*	getSuccessOrderObj()
	* To get order object on success page.
	*/	
	public function getSuccessOrderObj($incrementId) {
		if($incrementId) {
			return Mage::getModel('sales/order')->loadByIncrementId($incrementId);	
		}
		
	}

	/**
	*	getCustomerOrderObj()
	* To get Customer Order object on success page.
	*/	
	public function getCustomerOrderObj($customerId) {
		if($customerId) {
			return Mage::getModel('customer/customer')->load($customerId);	
		}
	}

	/**
	*	isTransmissionItem()
	* To get Tranmission Item from Quote Object
	*/
	public function isTransmissionItem() {

        $quote = Mage::helper('checkout/cart')->getCart()->getQuote();
        foreach ($quote->getAllItems() as $item) {
            $product = $item->getProduct();
            $totalInclTax = Mage::getSingleton('checkout/session')->getQuote()->getGrandTotal();
            if($product->getTansmission() == '1' && $totalInclTax > 100) {
                $isTransmissionItem =  true;
                break;
            }else {
                $isTransmissionItem =  false;
            }
        }
        return $isTransmissionItem;
	}
	
	/**
	*	isTransmissionItem()
	* To get Tranmission Item from Quote Object
	*/
	public function isTransferroItem() {

        $quote = Mage::helper('checkout/cart')->getCart()->getQuote();
        foreach ($quote->getAllItems() as $item) {
            $product = $item->getProduct();
            if($product->getManualproduct() == 2 || $product->getManualproduct() == 3) {
                $isTransferroItem =  true;
                break;
            }else {
                $isTransferroItem =  false;
            }
        }
        return $isTransferroItem;
	}

}
