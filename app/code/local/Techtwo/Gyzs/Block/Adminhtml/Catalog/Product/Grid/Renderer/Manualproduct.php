<?php
class Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Manualproduct extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
	/**
	* Removes if 'editable' function (see Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract)
	*/
	public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }

    public function _getValue(Varien_Object $row) {
        $manualproduct = $row->getData('manualproduct'); 
		
		switch ($manualproduct) {
			case 1:
				echo "Yes";
				break;
			case 2:
				echo "Transferro";
				break;
			case 3:
				echo "Gyzs Warehouse";
				break;
			default:
				echo "No";
				break;
		}
    }
}
?>