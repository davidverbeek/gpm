<?php

class Hs_Catalog_IndexController extends Mage_Core_Controller_Front_Action
{
    public function getMenuAction()
    {
        try{
            $customBlock = Mage::app()->getLayout()->createBlock('page/html_topmenu');
            $this->getResponse()->setBody($customBlock->getHtml('level-top'));
        }
        catch (Exception $e){
            Mage::log($e->getMessage());
        }
    }
}
