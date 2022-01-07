<?php
class Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Categorie2 extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
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
        $categorie2 = $row->getData('categorie2'); 

        if ($categorie2 != ''): 
                return $categorie2;
        else :
                return 'N/A';
        endif;  
       
        
    	
    }
}
?>