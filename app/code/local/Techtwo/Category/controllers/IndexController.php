<?php

class Techtwo_Category_IndexController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        // define title
        $title = $this->__('Alle producten');

        $this->loadLayout(array('default', 'catalog_category_list'));

        // set title
        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            $head->setTitle($title);
        }

        // show breadcrumbs
        if (Mage::getStoreConfig('web/default/show_cms_breadcrumbs')
            && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs'))) {
                $breadcrumbs->addCrumb('home', array('label'=>Mage::helper('cms')->__('Home'), 'title'=>Mage::helper('cms')->__('Go to Home Page'), 'link'=>Mage::getBaseUrl()));
                $breadcrumbs->addCrumb('cms_page', array('label'=>$title, 'title'=>$title));
        }

        $this->renderLayout();
    }

}
