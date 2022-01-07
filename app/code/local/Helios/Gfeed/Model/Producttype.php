<?php
class Helios_Gfeed_Model_Producttype extends Varien_Object {
	
	/**
	 * @return array
	 */	
	public function toOptionArray() {
		return array(
			array('value' => '0', 'label'=>'Brievenbuspakket'),
			array('value' => '1', 'label'=>'Normal'),
			array('value' => '2', 'label'=>'Transmission')
		);
	}
}