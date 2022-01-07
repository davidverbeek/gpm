<?php

class Techtwo_Gyzs_Adminhtml_UpdatecategoryController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
		$acties_id = "3098"; // live acties id changed on 30052018 - PP
		$data = $this->getRequest()->getPost();
		
		if(isset($data['product']) && !empty($data['product'])) {
			
			foreach($data['product'] as $pro_id) {
				$product = Mage::getModel('catalog/product')->load($pro_id);
				$categories = $product->getCategoryIds();
				if(!in_array($acties_id,$categories)) {
					$categories[] = $acties_id;
					$product->setCategoryIds($categories);
					$product->save();
				}
			}
		}
		$this->_redirectReferer();
	}

}
