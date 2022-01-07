<?php
class Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Afnameper extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
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
        //return $row->getData('afwijkenidealeverpakking');  
		
        $unit = Mage::helper('featured')->getProductUnit($row->getData('verkoopeenheid')); 

        if (!(int) $row->getData('afwijkenidealeverpakking')): 
                $stockInfo = $row->getData('idealeverpakking'); 
				$_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$row->getData('sku'));
                echo $_product->getIdealeverpakking."".Mage::helper('featured')->getStockUnit($stockInfo, $unit);
				
        else :
				$stockInfo = 1;
                echo $stockInfo."".Mage::helper('featured')->getStockUnit($stockInfo, $unit);
        endif; 
       
        
    	
    }
}
?>