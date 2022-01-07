<?php
class Hs_Common_Block_Catalog_Product_View_Type_Grouped extends Mage_Catalog_Block_Product_View_Type_Grouped {
	
	/*
		Override method to set order by Price
	*/
	public function getAssociatedProducts(){
		return $this->getProduct()->getTypeInstance(true)
			->getAssociatedProductCollection($this->getProduct())->setOrder('price','ASC');
	}
}
			