<?php
class Helios_Creports_Model_Vatopercentage extends Mage_Reports_Model_Mysql4_Order_Collection {

	function __construct() {
		parent::__construct();
		$this->setResourceModel('sales/order');
		$this->_init('sales/order','order_id');
	}

	public function setDateRange($from, $to) {
		$this->_reset();
		$this->getSelect()
			->where("main_table.created_at BETWEEN '".$from."' AND '".$to."'")
			->where("main_table.tax_amount <= 0");  // Filter Tax 0 order
			
		return $this;
	}

	public function setStoreIds($storeIds){
		return $this;
	}

}
?>