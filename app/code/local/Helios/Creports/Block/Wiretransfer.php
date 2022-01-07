<?php
class Helios_Creports_Block_Wiretransfer extends Mage_Core_Block_Template {

	public function _prepareLayout() {
		return parent::_prepareLayout();
	}

	public function getWiretransfer() {
		if (!$this->hasData('wiretransferorders')) {
			$this->setData('wiretransferorders', Mage::registry('wiretransferorders'));
		}
		return $this->getData('wiretransferorders');
	}
}