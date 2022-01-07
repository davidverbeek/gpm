<?php
class Hs_Featured_Model_Adminhtml_System_Config_Source_Manualoptions extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {
 
    public function getAllOptions()
    {
        $manualoptions = array(
			array('value' => '0', 'label' => 'No'),
            array('value' => '1', 'label' => 'Gyzs'),
            array('value' => '2', 'label' => 'Transferro'),
            array('value' => '3', 'label' => 'Gyzs Warehouse')
        );
 
        return $manualoptions;
    }
}