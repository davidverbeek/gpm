<?php
 
class Jaagers_Debug_Helper_Data extends Mage_Core_Helper_Abstract
{
 
	public function setdebug($input, $clr = false) {
		
		if($clr) {
			Mage::getSingleton('core/session')->unsDebugMessages();
		}
		
		$currentdebug = Mage::getSingleton('core/session')->getDebugMessages();
		
		if(isset($input)) {
			$currentdebug[] = $input;
			Mage::getSingleton('core/session')->setDebugMessages($currentdebug);
		}

	}
	
	public function showdebug(){
 
		$outputMessage = Mage::getSingleton('core/session')->getDebugMessages();
		
		if(isset($outputMessage)) {
			Mage::getSingleton('core/session')->unsDebugMessages();
			return $outputMessage;
		}

 	}
 
}