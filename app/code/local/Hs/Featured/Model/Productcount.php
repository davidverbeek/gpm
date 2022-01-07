<?php


class Hs_Featured_Model_ProductCount
{

        public function getProductCountForSubCategories($categoryId)
        {
            $productCount= array();
            $categoryModel = Mage::getModel('catalog/category')->load($categoryId);
            
            $subCategories = $categoryModel->getChildren();
            $subCategoriesArray = explode(',',$subCategories);

            $categoryCollection = Mage::getResourceModel('catalog/category_collection')
                ->addAttributeToFilter('entity_id',array('in'=>$subCategoriesArray));

            $categoryCollection->setLoadProductCount(true);

            foreach ($categoryCollection as $category) {
                $productCount[$category->getId()] = $category->getProductCount();
            }

            return $productCount;

        }
}
