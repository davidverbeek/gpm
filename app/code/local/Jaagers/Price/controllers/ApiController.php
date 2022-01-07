<?php

class Jaagers_Price_ApiController extends Mage_Core_Controller_Front_Action
{
    /**
     *
     * Function: this method will return product data based on the data provided by the sku
     *
     */
    public function getProductDataV1Action()
    {


        //echo "1"; exit;
        $skus = $this->getRequest()->getParam('skus', '');
        $depterNo = Mage::app()->getRequest()->getParam('debterno', '');

        $arrSku = explode(',', $skus);

        if(count($arrSku) > 20) {
            $arrSku = array_slice($arrSku,0,20);

        }



        $apiModel = Mage::getModel('price/api_search_sli_v2');

        //product data would be an array of the data
        $productData = $apiModel->getProductData('', $arrSku, $depterNo);
        //print_r($productData);
        //exit;

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($productData));

    }


    /**
     *
     * Function: this method will return product data based on the data provided by the sku
     *
     */
    public function getProductStockHtmlV1Action()
    {
        $sku = $this->getRequest()->getParam('sku', '');
        $mode = $this->getRequest()->getParam('mode', 'grid');

        $apiModel = Mage::getModel('price/api_search_sli_v2');

        //product data would be an array of the data
        $productData = $apiModel->getStockHtml($sku, $mode);


        $this->getResponse()->setBody($productData);


    }


    /**
     *
     * Function: this method will return product price,stock and devliery time information
     *
     */
    public function getProductsDataV1Action()
    {


        $skus = $this->getRequest()->getParam('skus', '');
        $debterNo = $this->getRequest()->getParam('debterno', '');
        //product data would be an array of the data
        $arrSku = explode(',', $skus);
        if(count($arrSku) > 20) {
            $arrSku = array_slice($arrSku,0,20);

        }

        $apiModel = Mage::getModel('price/api_search_sli_v2');
        //$productData = $apiModel->getPriceInfo($arrSku, $debterNo);
        $productData = $apiModel->getDebiteurInfo($arrSku, $debterNo);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($productData));


    }
}
