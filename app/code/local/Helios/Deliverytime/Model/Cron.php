<?php

class Helios_Deliverytime_Model_Cron {

	public function getDeliverytime(){
		$start = time();
		Mage::log('Deliverytime cron started.', null, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
		$value = Mage::getModel('deliverytime/delivery')->getDeliveryData();
		$timeConsumed = time() - $start;
		if ($value) {
			Mage::log($timeConsumed . ' - Deliverytime cron completed successfully.', null, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
			return "Cron Completed Successfully";
		} else {
			Mage::log($timeConsumed . ' - Deliverytime cron went wrong.', null, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
			return "Cron not completed";
		}
	}

	public function getDeliverytimeForNewSku(){
		$start = time();
		Mage::log('Deliverytime for new sku cron started.', null, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
		$value = Mage::getModel('deliverytime/delivery')->getDeliveryDataForNewSku();
		$timeConsumed = time() - $start;
		if ($value) {
			Mage::log($timeConsumed . ' - Deliverytime for new sku cron completed successfully.', null, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
			return "Cron Completed Successfully";
		} else {
			Mage::log($timeConsumed . ' - No new SKU Found.', null, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
			return "Cron completed";
		}
	}

	public function getStock(){
		/* $stock_api_rest_resource = Mage::getStoreConfig('deliverytime/general/stock_api_resource');
		if($stock_api_rest_resource == 1) {
			$start = time();
			Mage::log('Rest Stock update cron started.', null, Helios_Deliverytime_Helper_Data::REST_STOCK_LOG_FILE_NAME);
			Mage::getModel('deliverytime/stock')->getRestStockData();
			$timeConsumed = time() - $start;
			Mage::log($timeConsumed . ' - Rest Stock update cron completed successfully.', null, Helios_Deliverytime_Helper_Data::REST_STOCK_LOG_FILE_NAME);
		} else {
			$start = time();
			Mage::log('Stock update cron started.', null, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
			$value = Mage::getModel('deliverytime/stock')->getStockData();
			$timeConsumed = time() - $start;
			if ($value) {
				Mage::log($timeConsumed . ' - Stock update cron completed successfully.', null, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
				return "Cron Completed Successfully";
			} else {
				Mage::log($timeConsumed . ' - Stock update cron went wrong.', null, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
				return "Cron not completed";
			}
		} 
		*/
		$start = time();
		Mage::log('Stock update cron started.', null, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
		$value = Mage::getModel('deliverytime/stock')->getStockData();
		$timeConsumed = time() - $start;
		if ($value) {
			Mage::log($timeConsumed . ' - Stock update cron completed successfully.', null, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
			return "Cron Completed Successfully";
		} else {
			Mage::log($timeConsumed . ' - Stock update cron went wrong.', null, Helios_Deliverytime_Helper_Data::LOG_FILE_NAME);
			return "Cron not completed";
		}
	}


	public function getRestStock() {
		$start = time();
		Mage::log('Rest Stock daily update cron started.', null, Helios_Deliverytime_Helper_Data::REST_STOCK_LOG_FILE_NAME);
		Mage::getModel('deliverytime/stock')->getRestStockData();
		$timeConsumed = time() - $start;
		Mage::log($timeConsumed . ' - Rest Stock daily update cron completed successfully.', null, Helios_Deliverytime_Helper_Data::REST_STOCK_LOG_FILE_NAME);
	}

	public function getUpdatedRestStocks() {
		$start = time();
		Mage::log($start.' - Rest Stock update cron started.', null, Helios_Deliverytime_Helper_Data::REST_STOCK_LOG_FILE_NAME_15);
		Mage::getModel('deliverytime/stock')->getUpdatedRestStockData();
		$timeConsumed = time() - $start;
		Mage::log($timeConsumed . ' - Rest Stock update cron completed successfully.', null, Helios_Deliverytime_Helper_Data::REST_STOCK_LOG_FILE_NAME_15);
	}

	public function getRestDeliverytime(){
		$start = time();
		Mage::log('Deliverytime cron started.', null, Helios_Deliverytime_Helper_Data::REST_DELIVERY_TIME_LOG_FILE_NAME);
		Mage::getModel('deliverytime/delivery')->getRestDeliveryData();
		$timeConsumed = time() - $start;
		Mage::log($timeConsumed . ' - Deliverytime cron completed successfully.', null, Helios_Deliverytime_Helper_Data::REST_DELIVERY_TIME_LOG_FILE_NAME);
	}


}