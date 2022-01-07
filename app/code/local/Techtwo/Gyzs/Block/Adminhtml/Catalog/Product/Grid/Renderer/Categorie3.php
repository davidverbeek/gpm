<?php
class Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Categorie3 extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
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
       $categorie3 = $row->getData('categorie3'); 

        if ($categorie3 != ''): 
                return $categorie3;
        else :
                return 'N/A';
        endif; 
       
        
    	
    }
}
?>