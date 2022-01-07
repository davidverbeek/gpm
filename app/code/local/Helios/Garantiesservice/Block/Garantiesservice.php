<?php

class Helios_Garantiesservice_Block_Garantiesservice extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getGarantiesservice() {
        if (!$this->hasData('garantiesservice')) {
            $this->setData('garantiesservice', Mage::registry('garantiesservice'));
        }
        return $this->getData('garantiesservice');
    }

}
