<?php

/**
 * Class Helios_Omniafeed_Model_Feed
 *
 * @copyright   Copyright (c) 2017 Helios
 */
class Helios_Omniafeed_Model_Potentialcount extends Mage_Core_Model_Abstract {

	// global variables
    private $_helper = null;
    private $_totalSalesYearlyData = null;
    private $_currentProductAverageSalesQty = 0;

	public function _construct(){
		parent::_construct();
		$this->_helper = Mage::helper('omniafeed');
		$this->_totalSalesYearlyData = $this->_helper->getTotalSalesYearlyData();
	}

	public function potentialCalculation() {
		
		$potentialArray = array();
		// Product Potential Calculation Starts

    $totalSalesQtyYearly = array_sum(array_column($this->_totalSalesYearlyData->getData(), 'actual_sales_year'));
    $soldSkuInYear = $this->_totalSalesYearlyData->count();
    
    // Average sales per product in QTY
    $this->_currentProductAverageSalesQty = ($soldSkuInYear) ? ($totalSalesQtyYearly / $soldSkuInYear) : 0;

		$productCollection = $this->_helper->getProductCollectionForPotentialCount();

		$updateStatus = $this->_helper->savePotentialData($productCollection, $this->_currentProductAverageSalesQty);

		return $updateStatus;

	}

}
