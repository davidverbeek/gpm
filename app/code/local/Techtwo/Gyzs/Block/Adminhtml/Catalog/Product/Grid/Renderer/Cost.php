<?php
class Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Cost extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
	/**
	* Removes if 'editable' function (see Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract)
	*/
	public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }

    public function _getValue(Varien_Object $row)
    {
        // return $row->getData('cost')? Mage::getModel('directory/currency')->format($row->getData('cost')): '';

        // return $row->getData('cost')? Mage::helper('core')->currency($row->getData('cost'),true,false): '';


        // Added By Parth

        $idealeverpakking=1;

        if(!$row->getData('afwijkenidealeverpakking')){
            $idealeverpakking=str_replace(",",".",$row->getData('idealeverpakking'));
            //$prijsfactor=$row->getData('prijsfactor');

            
            //$_cost = $row->getData('cost')*$prijsfactor;
			if($idealeverpakking>1){                    
				$_cost = $row->getData('cost')*$idealeverpakking;
			} else {
				$_cost = $row->getData('cost')*1;
			}
        } else {
            $_cost = $row->getData('cost');
        }

        return Mage::helper('core')->currency($_cost,true,false);

        
    }
}
?>