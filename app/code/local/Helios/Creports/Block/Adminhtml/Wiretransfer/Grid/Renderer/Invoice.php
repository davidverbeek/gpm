<?php

class Helios_Creports_Block_Adminhtml_Wiretransfer_Grid_Renderer_Invoice extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text {
	
	public function render(Varien_Object $row) {
		return $this->_getValue($row);
	}

	public function _getValue(Varien_Object $row){
		$invoiceCollection = $row->getInvoiceCollection();
		$invoiceIncrementId = array();
		foreach($invoiceCollection as $invoice):
			$invoiceIncrementId [] =  $invoice->getIncrementId();
		endforeach;
		return implode(',', $invoiceIncrementId);
	}
}
?>