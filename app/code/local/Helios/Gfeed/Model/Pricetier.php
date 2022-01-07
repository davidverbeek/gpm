<?php
class Helios_Gfeed_Model_Pricetier extends Varien_Object {
	
	/**
	 * @return array
	 */	
	public function toOptionArray() {
		return array(
			array('value' => '0', 'label'=>'0 - 25'),
			array('value' => '1', 'label'=>'25 - 50'),
			array('value' => '2', 'label'=>'50 - 75'),
			array('value' => '3', 'label'=>'100 and above')
		);
	}
}