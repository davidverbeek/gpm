<?php
class Techtwo_Gyzs_Block_Newhome extends Mage_Catalog_Block_Product_New
{
	public function getItemHtml ($item)
    {
		return $this->getChild('item')->setParent($this)->setProduct($item)->toHtml();
    }
}