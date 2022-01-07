<?php

class Gyzs_Blog_Block_Adminhtml_Blog_Edit_Tab_Categories extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories {

	protected $_categoryIds;
  protected $_selectedNodes = null;
	
	public function __construct() {
    parent::__construct();
		//$this->setTemplate('blog/categories.phtml');
	}
	
	public function getCategoryIds()
	{
            //$blog=$this->getBlog();
            //$blogId=$blog->getId();
            $blogId=$this->getRequest()->getParam('id');
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $connection->beginTransaction();
            
            $tableName=Mage::getSingleton('core/resource')->getTableName('wordpress_association');
            
            $select = $connection->select()
                                ->from($tableName, array('*')) 
                                ->where('wordpress_object_id=?',$blogId)              
                                ->where('type_id=?','3');
            $rowArray =$connection->fetchAll($select);  
            foreach($rowArray as $_row){
               $category[]=$_row['object_id'];
                       
            }
            array_unique($category);
            //$categoryIds=implode(",",$category);
            
            return $category;
	}
	
	public function isReadonly()
	{
			return false;
	}
  
	
	public function getIdsString()
	{
			return implode(',', $this->getCategoryIds());
	}
		
	public function getBlog() {
            
		return Mage::registry('blog_data');
	}
}