<?php
class Helios_Gfeed_Model_Pricetype extends Varien_Object {
	
	/**
	 * @return array
	 */	
	public function toOptionArray() {
		return array(
			array('value' => '0', 'label'=>'Minimum Price'),
			array('value' => '1', 'label'=>'Desire Price')
		);
	}
}