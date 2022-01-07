<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Hs_Featured_FeaturedController extends Mage_Core_Controller_Front_Action
{

    /**
     * This controller is used to get product count of subcategories
     */
	public function getSubCategoriesProductCountAction()
    {
        $categoryId = $this->getRequest()->getParam('category_id',null);


        $productCount = Mage::getModel('featured/productcount')->getProductCountForSubCategories($categoryId);

        $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
        $this->getResponse()->setBody(json_encode($productCount));

    }


}
