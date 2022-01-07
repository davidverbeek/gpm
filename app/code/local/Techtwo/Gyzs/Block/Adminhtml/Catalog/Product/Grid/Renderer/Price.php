<?php
class Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Price extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
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

        // Added By Parth

        // $verkoopeenheid=Mage::helper('featured')->getStockUnit($_product->getIdealeverpakking(),$unit);
        $idealeverpakking=1;

        if(!$row->getData('afwijkenidealeverpakking')){
            $idealeverpakking=str_replace(",",".",$row->getData('idealeverpakking'));
			
            //$prijsfactor=$row->getData('prijsfactor');

			//$_priceTax = $row->getPrice()*$prijsfactor;
			if($idealeverpakking>1){                    
				$_priceTax = $row->getPrice()*$idealeverpakking;
			} else {
				$_priceTax = $row->getPrice()*1;
			}
        } else {
            $_priceTax = $row->getPrice();
        }

        return Mage::helper('core')->currency($_priceTax,true,false);
    }
}
?>