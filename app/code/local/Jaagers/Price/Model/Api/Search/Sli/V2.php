<?php

class Jaagers_Price_Model_Api_Search_Sli_V2 extends Mage_Core_Model_Abstract
{

    public $_config;

    public function getProductData($apiKey = '', $skus = array(), $deptorNo = '')
    {
        //check the API key is passed or not
//        $currentApiKey = Mage::getStoreConfig('');

//        if (empty($apiKey) || $apiKey != $currentApiKey) {
//
//            return array(
//                'status' => 'error',
//                'message' => 'Invalid API key. Please enter valid API Key'
//            );
//        }


        if (empty($skus) || !is_array($skus) || count($skus) == 0) {

            return array(
                'status' => 'error',
                'message' => 'Invalid Request. Please provide Valid Skus'
            );

        }

        //getPriceInfo

        $data = array();


        //fetch stock html and append into the result
        foreach ($skus as $sku) {


            //if product is not exists then return an empty string
            $html = $this->getStockHtml($sku);

            $data[$sku] = array('product_stock_html' => $html);

        }


        $stockData = $this->getPriceInfo($data);

//        foreach ($data as $sku => $datum) {
//
//
//
//        }


        return array(

            'status' => 'success',
            'data' => $data,
            'pricedata' => $stockData
        );


    }

    public function getDebiteurInfo($arrSku = array(), $debterNo = '')
    {

        if (count($arrSku) == 1 && $arrSku[0] == '') {
            $returnArray = array(
                'status' => 'error',
                'message' => 'Please specify skus!!'
            );

            return $returnArray;

        }

        try {


            $this->_config = Mage::getConfig()->getNode('default/combipac');
            $client = new Zend_Soap_Client((string)$this->_config->apipath);


            /*** stock apis status start **/

            $productCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('price')
                ->addAttributeToFilter('sku', array('in' => $arrSku));

            $normalPriceArray = array();

            foreach ($productCollection as $product) {
                $normalPriceArray[$product->getSku()] = $product->getFinalPrice();
            }

            //print_r($normalPriceArray);


             /*** price apis status end **/


            foreach ($arrSku as $a) {
                $artikel = new stdClass();
                $artikel->sku = $a['id'];
                $artikel->type = $a['type'];
                $artikelen[] = $artikel;

            }
            //print_r($artikelen);


            //$result = Mage::helper('price/data')->getVoorraadMore($artikelen);

            //print_r($result);
            //exit;





            /*** stock apis status end **/

            /*** price apis status **/

            $aantallen = array();
            foreach ($arrSku as $r) {
                $aantallen[] = (float)1.00;
            }


            if (count($arrSku) > 1) {

                $request = array(
                    'bedrijfnr' => 1,
                    'filiaalnr' => 0,
                    'artikelen' => $arrSku,
                    'aantallen' => $aantallen
                );


            } else {


                $request = array(
                    'bedrijfnr' => 1,
                    'filiaalnr' => 1,
                    'artikelnr' => $arrSku[0],
                    'aantal' => 1
                );
            }


            if (!empty($debterNo)) {

                $request['debiteurnr'] = $debterNo;


            } else {
                $request['debiteurnr'] = 4027100;

            }

            if (count($arrSku) > 1) {

                $priceResponse = $client->GetPrijsMore($request);
                $finalPriceResult = $priceResponse->GetPrijsMoreResult->Prijs;
            } else {
                $priceResponse = $client->GetPrijs($request);
                $finalPriceResult[] = $priceResponse->GetPrijsResult;
            }

            $finalResponse = array();
            $resultStock = $client->GetVoorraadMore($request);
            $resultDeliveryTime = $client->GetLevertijdMore($request);

            if((isset($resultStock->GetVoorraadMoreResult->decimal) && !is_array($resultStock->GetVoorraadMoreResult->decimal)) &&
                (isset($resultDeliveryTime->GetLevertijdMoreResult->int) && !is_array($resultDeliveryTime->GetLevertijdMoreResult->int))) {
                /*** Combipac returned single result ***/

                $response = new stdClass();
                $response->Artinr = $arrSku[0];
                $response->VoorHH = $resultStock->GetVoorraadMoreResult->decimal;
                $response->Levertijd = $resultDeliveryTime->GetLevertijdMoreResult->int;
                $finalResponse[] = $response;

            } elseif (isset($resultStock->GetVoorraadMoreResult->decimal) && isset($resultDeliveryTime->GetLevertijdMoreResult->int)) {
                /*** Combipac returned multiresult ***/
                $cnt = 0;
                foreach ($resultStock->GetVoorraadMoreResult->decimal as $result) {

                    $response = new stdClass();
                    $response->Artinr = $arrSku[$cnt];
                    $response->VoorHH = $result;
                    $response->Levertijd = $resultDeliveryTime->GetLevertijdMoreResult->int[$cnt];

                    $finalResponse[] = $response;
                    $cnt++;
                }

            }

            if (isset($result->VoorraadInfo) && !is_array($result->VoorraadInfo)) {

                /*** Combipac returned single result ***/

                $result = $result->VoorraadInfo;
                $result->sku = $arrSku[0];
                $finalresult[$result->sku] = $result;

            } elseif (isset($result->VoorraadInfo)) {

                /*** Combipac returned multiresult ***/

                $cnt = 0;

                foreach ($result->VoorraadInfo as $r) {

                    $r->sku = $arrSku[$cnt];
                    $finalresult[$r->sku] = $r;

                    $cnt++;
                }

            } else {

                $finalresult = array();

            }


            foreach ($finalPriceResult as $final) {

                //check if Normal price is fewer than the depterprice. if so then consider normal price
                if (empty($debterNo) && isset($normalPriceArray[$value->Artinr]) && $normalPriceArray[$final->Artinr] < $final->Verkpr) {
                    $final->Verkpr = $normalPriceArray[$final->Artinr];
                }

                $objResult = new stdClass();
                $objResult->Artinr = $final->Artinr;
                $objResult->Verkpr = number_format($final->Verkpr, 2);
                $objResult->Brvkpr = number_format($final->Brvkpr, 2);
                $objResult->Nettpr = number_format($final->Nettpr, 2);

                $objResult->Levertijd = $finalresult[$objResult->Artinr]->Levertijd;
                $objResult->Voorraad = $finalresult[$objResult->Artinr]->Voorraad;



                $finalresult[$objResult->Artinr] = $objResult;
            }

            /*** price apis status end **/


            $returnArray = array(
                'status' => 'success',
                'result' => $finalresult
            );

        } catch (Exception $e) {
            $returnArray = array(
                'status' => 'error',
                'message' => $e->getMessage()
            );

        }

        return $returnArray;
    }


    public function getPriceInfo($arrSku = array(), $debterNo = '')
    {


        if (count($arrSku) == 1 && $arrSku[0] == '') {
            $returnArray = array(
                'status' => 'error',
                'message' => 'Please specify skus!!'
            );

            return $returnArray;

        }

        try {


            $this->_config = Mage::getConfig()->getNode('default/combipac');
            $client = new Zend_Soap_Client((string)$this->_config->apipath);


            /*** stock apis status start **/

            $productCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('price')
                ->addAttributeToFilter('sku', array('in' => $arrSku));

            $normalPriceArray = array();

            foreach ($productCollection as $product) {
                $normalPriceArray[$product->getSku()] = $product->getFinalPrice();
            }


            foreach ($arrSku as $a) {

                if (!isset($normalPrice[$a])) {
                    $normalPriceArray[$a] = '';
                }
            }


            /*** price apis status end **/


            foreach ($arrSku as $a) {
                $artikel = new stdClass();
                $artikel->sku = $a['id'];
                $artikel->type = $a['type'];
                $artikelen[] = $artikel;

            }


            $result = Mage::helper('price/data')->getVoorraadMore($artikelen);

            if (isset($result->VoorraadInfo) && !is_array($result->VoorraadInfo)) {

                /*** Combipac returned single result ***/

                $result = $result->VoorraadInfo;
                $result->sku = $arrSku[0];
                $finalresult[$result->sku] = $result;

            } elseif (isset($result->VoorraadInfo)) {

                /*** Combipac returned multiresult ***/

                $cnt = 0;

                foreach ($result->VoorraadInfo as $r) {

                    $r->sku = $arrSku[$cnt];
                    $finalresult[$r->sku] = $r;

                    $cnt++;
                }

            } else {

                $finalresult = array();

            }

            /*** stock apis status end **/

            /*** price apis status **/

            $aantallen = array();
            foreach ($arrSku as $r) {
                $aantallen[] = (float)1.00;
            }


            if (count($arrSku) > 1) {

                $request = array(
                    'bedrijfnr' => 1,
                    'filiaalnr' => 0,
                    'artikelen' => $arrSku,
                    'aantallen' => $aantallen
                );


            } else {


                $request = array(
                    'bedrijfnr' => 1,
                    'filiaalnr' => 1,
                    'artikelnr' => $arrSku[0],
                    'aantal' => 1
                );
            }


            if (!empty($debterNo)) {

                $request['debiteurnr'] = $debterNo;


            } else {
                $request['debiteurnr'] = 4027100;

            }

            if (count($arrSku) > 1) {

                $priceResponse = $client->GetPrijsMore($request);
                $finalPriceResult = $priceResponse->GetPrijsMoreResult->Prijs;
            } else {
                $priceResponse = $client->GetPrijs($request);
                $finalPriceResult[] = $priceResponse->GetPrijsResult;
            }


            foreach ($finalPriceResult as $final) {

                //check if Normal price is fewer than the depterprice. if so then consider normal price
                if (empty($debterNo) && isset($normalPriceArray[$value->Artinr]) && $normalPriceArray[$final->Artinr] < $final->Verkpr) {
                    $final->Verkpr = $normalPriceArray[$final->Artinr];
                }

                $objResult = new stdClass();
                $objResult->Artinr = $final->Artinr;
                $objResult->Verkpr = number_format($final->Verkpr, 2);
                $objResult->Brvkpr = number_format($final->Brvkpr, 2);
                $objResult->Nettpr = number_format($final->Nettpr, 2);
                $objResult->Krt1pc = $final->Krt1pc;
                $objResult->Krt2pc = $final->Krt2pc;
                $objResult->Ntpsbd = $final->Ntpsbd;
                $objResult->Kortbd = $final->Kortbd;
                $objResult->Opslpc = $final->Opslpc;
                $objResult->Pryskd = $final->Pryskd;
                $objResult->Ivtsjn = $final->Ivtsjn;
                $objResult->Uitgsl = $final->Uitgsl;


                $objResult->Levertijd = $finalresult[$objResult->Artinr]->Levertijd;
                $objResult->Voorraad = $finalresult[$objResult->Artinr]->Voorraad;
                $objResult->sku = $objResult->Artinr;


                $finalresult[$objResult->Artinr] = $objResult;
            }

            /*** price apis status end **/


            $returnArray = array(
                'status' => 'success',
                'result' => $finalresult
            );

        } catch (Exception $e) {
            $returnArray = array(
                'status' => 'error',
                'message' => $e->getMessage()
            );

        }

        return $returnArray;
    }


    public function getStockHtml($product = '', $mode = 'grid')
    {
        //logic to fetch stock related information
        $returnData = array();
        //check if pass param is string then load product model otherwise treat it as a model
        if (is_string($product)) {

            $_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $product);
        }


        if ($_product !== false) {

            $_product = Mage::getModel('catalog/product')->load($_product->getId());
            $block = Mage::app()->getLayout()
                ->createBlock('jaggers_price/product_search_sli_stock', 'product.stock');

            $block->setMode($mode);
            $block->setProduct($_product);
            $html = $block->toHtml();

        }


        return $html;


    }


}