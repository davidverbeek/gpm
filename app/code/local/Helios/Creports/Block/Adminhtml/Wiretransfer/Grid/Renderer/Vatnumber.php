<?php

class Helios_Creports_Block_Adminhtml_Wiretransfer_Grid_Renderer_Vatnumber extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text {
	
	public function render(Varien_Object $row) {
		return $this->_getValue($row);
	}

	public function _getValue(Varien_Object $row){
		$billingAddress = $row->getBillingAddress();
		return $billingAddress->getVatId();
	}
}
?>