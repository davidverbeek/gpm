<?php

class Jaagers_Hotlist_Model_Objectmodel_Api_V2 extends Mage_Api_Model_Resource_Abstract
{

    public function testMethod($args)
    {
        
        return $args;
    
    }
    
    public function createList($customerId, $listName ) {
        
        $checkList = $this->getListByName($customerId, $listName);
        
        if($checkList['totalRecords'] > 0) {
            return 'Duplicate listname found for ' . $listName . ' and customerId ' . $customerId;
        }
        
        Mage::log('custid: ' . $customerId);
        Mage::log('listName: ' . $listName);
        
        $data = array('title'=>$listName,'customer_id'=>$customerId);
        $model = Mage::getModel('amlist/list')->setData($data);
        try {
            $insertId = $model->save()->getId();
            return $insertId;
        } catch (Exception $e){
            return $e->getMessage();
        }
        
    }
    
    public function deleteList($listId) {
        
        $list = Mage::getModel('amlist/list')->load($listId);
        try {
            $list->delete();
            return 'List with ID' . $listId . ' deleted';
        } catch (Exception $e){
            return $e->getMessage();
        }
        
    }
    
    public function updateList($listId, $items) {
        
        $list = Mage::getModel('amlist/list')->load($listId);
        
        if( is_array($list->getData()) && !count($list->getData())) {
            return false;
        }

        foreach($list->getItems() as $i) {
            $i->delete();
        }
        
        foreach($items as $i)
        {

            $duplicate = false;

            $productid = $this->getProductIdBySku($i->product_id);

            if(!$productid) {

                continue;

            }

            if(is_array($productid)) {
                foreach($productid as $p) {
                    
                    $id = $p['entity_id'];

                    $list = Mage::getModel('amlist/list')->load($listId);

                    foreach($list->getItems() as $item) {
                    
                        if($item->getData('product_id') == $id) {
                            $duplicate = true;
                        }

                    }

                }
            }

            $data = array('list_id' => $listId, 'product_id' => $id, 'qty' => $i->qty, 'user_added' => 0);
                    
            $model = Mage::getModel('amlist/item')->setData($data);
            try {
                if(!$duplicate) {
                    $insertIdItem = $model->save()->getId();
                    Mage::log('Item inserted with id = ' . $insertIdItem);
                } else {
                    Mage::Log('Duplicate entry found');
                }
            } catch (Exception $e){
                return $e->getMessage();
            }
                    
        }
        
        return true;
        
    }

    public function updateListName($listId, $listName) {

        $list = Mage::getModel('amlist/list')->load($listId);
        $list->setData('title', $listName);

        try {
            $list->save();
        } catch(Exception $e) {
            return $e->getMessage();
        }

    }
        
    public function getListByName($customerId, $listName) {
        
        $collection = Mage::getModel('amlist/list')->getCollection();
        $collection->addFieldToFilter('title', $listName);
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->load();
        $collection = $collection->toArray();
        
        return $collection;
        
    }
    
    public function getCustomerIdByDebitnumber($debitnumber)
    {
        $collection = Mage::getModel('customer/customer')->getCollection();
        $collection->addFieldToFilter('mavis_debiteurnummer', $debitnumber);
        $collection->load();
        $collection = $collection->toArray();
    
        return $collection[2]['entity_id'];
    }
    
    public function getProductIdBySku($sku)
    {
    
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addFieldToFilter('sku', $sku);
        $collection->load();
        $collection = $collection->toArray();

        if(count($collection)) {
            return $collection;
        } else {
            return false;
        }
    
    }
    
}

