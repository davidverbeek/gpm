<?php
class Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_PriceTax extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
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
        $_product = Mage::getModel('catalog/product')->load($row->getId());
        // $_priceIncludingTax = Mage::helper('tax')->getPrice($_product, $row->getFinalPrice());

        $idealeverpakking=1;

        if(!$row->getData('afwijkenidealeverpakking')){
            $idealeverpakking=str_replace(",",".",$row->getData('idealeverpakking'));

            //$prijsfactor=$row->getData('prijsfactor');

            //$temp = $row->getPrice()*$prijsfactor;
            //$_priceTax = Mage::helper('tax')->getPrice($_product, $temp, true, null, null, null, null, null, false);
            
             if($idealeverpakking>1){
				$temp = $row->getPrice()*$idealeverpakking;
				$_priceTax = Mage::helper('tax')->getPrice($_product, $temp, true, null, null, null, null, null, false);
             } else {
				$temp = $row->getPrice()*1;
				$_priceTax = Mage::helper('tax')->getPrice($_product, $temp, true, null, null, null, null, null, false);
             }
        } else {
            // $_priceTax = $row->getPrice();
            $temp = $row->getPrice();
            $_priceTax = Mage::helper('tax')->getPrice($_product, $temp, true, null, null, null, null, null, false);

        }

        return Mage::helper('core')->currency($_priceTax,true,false);
    }
}
?>