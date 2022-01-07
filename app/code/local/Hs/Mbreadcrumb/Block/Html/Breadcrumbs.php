<?php

class Hs_Mbreadcrumb_Block_Html_Breadcrumbs extends Mage_Page_Block_Html_Breadcrumbs {

	protected $skipBreadcrumb = array('home', 'product');

	protected function rewamp() {
		if(!is_null(Mage::registry('current_category'))){
			if(Mage::registry('current_category')->getId()!=null){
				
				$tempArray = array();
				
				foreach ($this->_crumbs as $keys => $crumbs) {
					if(!in_array($keys, $this->skipBreadcrumb)){
						$tempArray[] = $crumbs;
					}
				}

				$this->_crumbs = $tempArray;
			}
		}
		return $this->_crumbs;
	}
}