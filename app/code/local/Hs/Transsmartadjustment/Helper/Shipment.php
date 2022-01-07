<?php

/**
 * Class        Hs_Transsmartadjustment_Helper_Shipment
 * @category    Transsmart
 * @package     Hs_Transsmartadjustment
 * @author      Hs_SJD
 * @date        06 June, 2018
 *
 */
class Hs_Transsmartadjustment_Helper_Shipment extends Transsmart_Shipping_Helper_Shipment
{
	/**
	 * Call doBookAndPrint for multiple Transsmart shipments at once.
	 *
	 * @param Mage_Sales_Model_Resource_Order_Shipment_Collection $shipmentCollection
	 * @return bool
	 * @throws Exception
	 */
	public function doMassBookAndPrint($shipmentCollection)
	{
		// group documentIds for shipments with the same QZ Host and Selected Printer.
		$groupedCalls = $this->_getMassPrintGroupedCalls($shipmentCollection, false);
		if (count($groupedCalls) == 0) {
			return;
		}

		$idsToSync = array();
		try {
			// call Transsmart API doLabel method for each group
			foreach ($groupedCalls as $_call) {
				$idsToSync += $_call['doc_ids'];
				Mage::helper('transsmartadjustment')->getApiClientUser2()->doBookAndPrint(
					$_call['doc_ids'],
					Mage::getStoreConfig(Hs_Transsmartadjustment_Helper_Data::XML_PATH_CONNECTION_USERNAME_2, 0),
					false,
					$_call['qz_host'],
					$_call['selected_printer']
				);
			}
		}
		catch (Exception $exception) {
			$this->_massSyncDocuments($shipmentCollection, $idsToSync);
			throw $exception;
		}
		$this->_massSyncDocuments($shipmentCollection, $idsToSync);

		return true;
	}
}