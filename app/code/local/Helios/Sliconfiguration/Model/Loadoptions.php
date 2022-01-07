<?php
class Helios_Sliconfiguration_Model_Loadoptions extends Varien_Object
{
	/**
	 * @return array
	 */
	
	public function toOptionArray()
	{
		return array(
			array('value' => '0', 'label'=>Mage::helper('adminhtml')->__('SLI Server')),
			array('value' => '1', 'label'=>Mage::helper('adminhtml')->__('GYZS Server'))
		);
	}
}