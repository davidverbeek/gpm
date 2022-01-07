<?php
class Helios_Creports_Block_Vatopercentage extends Mage_Core_Block_Template {

	public function _prepareLayout() {
		return parent::_prepareLayout();
	}

	public function getVatopercentage() {
		if (!$this->hasData('vatopercentage')) {
			$this->setData('vatopercentage', Mage::registry('vatopercentage'));
		}
		return $this->getData('vatopercentage');
	}
}