<?php
class Jaagers_Projectenosc_Model_Observer {

	public function salesOrderPlaceBefore(Varien_Event_Observer $observer) {

		if(!isset($userinfo['mavis_debiteurnummer']) || !(int)$config['general']['enabled'] || !count($projects)) {
        	return;
        }

		$order 		=	$observer->getEvent()->getOrder();
		$projectId 	=	Mage::app()->getRequest()->getPost('id_projectnr');
		
		if(strlen($projectId)) {
			$order->setData('customer_projectnr', $projectId);
		}

	}

}