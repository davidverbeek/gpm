<?php
class Mage_Adminhtml_Model_System_Config_Source_Size
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Please select Size')),
            array('value' => 'size16', 'label'=>Mage::helper('adminhtml')->__('16 X 16')),
            array('value' => 'size20', 'label'=>Mage::helper('adminhtml')->__('20 X 20')),
            array('value' => 'size24', 'label'=>Mage::helper('adminhtml')->__('24 X 24')),
            array('value' => 'size32', 'label'=>Mage::helper('adminhtml')->__('32 X 32')),
        );
    }

}
