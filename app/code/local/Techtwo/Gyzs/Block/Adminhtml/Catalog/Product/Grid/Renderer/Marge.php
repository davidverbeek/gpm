<?php
class Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Marge extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
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
    	//return $row->getData('marge')? Mage::getModel('directory/currency')->format($row->getData('marge')): '';
    	
    	// return Mage::getModel('directory/currency')->format($row->getData('price')-$row->getData('cost'));

        // return Mage::helper('core')->currency($row->getData('price')-$row->getData('cost'),true,false);

        // Added By Parth

        $idealeverpakking=1;

        if(!$row->getData('afwijkenidealeverpakking')){
            $idealeverpakking=str_replace(",",".",$row->getData('idealeverpakking'));


            //$prijsfactor=$row->getData('prijsfactor');

            
            //$_marge = ($row->getData('price')-$row->getData('cost'))*$prijsfactor;
			if($idealeverpakking>1){                    
				$_marge = ($row->getData('price')-$row->getData('cost'))*$idealeverpakking;
			} else {
				$_marge = ($row->getData('price')-$row->getData('cost'))*1;
			}
        } else {
            $_marge = ($row->getData('price')-$row->getData('cost'));
        }
        return Mage::helper('core')->currency($_marge,true,false);
    }
}
?>