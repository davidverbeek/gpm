<?php
class Helios_Creports_Model_Wiretransfer extends Mage_Reports_Model_Mysql4_Order_Collection {

	function __construct() {
		parent::__construct();
		$this->setResourceModel('sales/order');
		$this->_init('sales/order','order_id');
	}

	public function setDateRange($from, $to) {
		$this->_reset();
		$this->getSelect()
			->joinLeft(array(
				'op' => $this->getTable('sales/order_payment')),
				'op.parent_id = main_table.entity_id',
				array('op.method')
			)
			->joinLeft(array(
				'ii' => 'mage_icepay_issuerdata'),
				'ii.magento_code = op.method',
				array('pm_code','magento_code')
			)
			->where("main_table.created_at BETWEEN '".$from."' AND '".$to."'")
			->where("ii.pm_code = 'wire'");  // Filter Wire transfer order
			
		return $this;
	}

	public function setStoreIds($storeIds){
		return $this;
	}

}
?>