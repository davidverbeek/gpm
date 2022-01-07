<?php

class Hs_OrderFeed_Model_Cron
{
   
    /**
     *  Genrate yestersday order xml feed
     * 
     */
    public function orderFeeds()
    {
        Mage::log("Yesterday orders feed started", null, 'orderfeed.log');
        $orders = Mage::helper('orderfeed')->getOrders();
        // Create XML START
        $objXmlFeed = new DOMDocument('1.0', 'UTF-8');
        $objXmlFeed->formatOutput = true;
        $intCounter = 0;

        $ProductList = $objXmlFeed->createElement('product_list');
        $objXmlFeed->appendChild($ProductList);
        foreach ($orders as $order) {
            $attribute = Mage::getSingleton('catalog/product')->getCollection()
                ->addAttributeToFilter('sku', $order['sku'])
                ->addAttributeToSelect('prijsfactor')
                ->addAttributeToSelect('verkoopeenheid')
                ->addAttributeToSelect('idealeverpakking')
                ->addAttributeToSelect('afwijkenidealeverpakking')
                ->getFirstItem();

            // Create xml First Product Node
            $objProductNode = $objXmlFeed->createElement('product');
            $objIdNode = $objXmlFeed->createElement('sku');
            $dataSection = $objXmlFeed->createCDATASection($order['sku']);
            $objIdNode->appendChild($dataSection);
            $objProductNode->appendChild($objIdNode);

            $priceIncl = $order['price_incl_tax'];
            if(substr($priceIncl, 0,3) == '0.0'){
                $priceIncl = number_format((float)substr($order['price_incl_tax'], -3)/100,2);
            }else{
                $priceIncl = number_format($order['price_incl_tax'],2);
            }

            $objIdNode = $objXmlFeed->createElement('price_incl_tax');
            $dataSection = $objXmlFeed->createCDATASection($priceIncl);
            $objIdNode->appendChild($dataSection);
            $objProductNode->appendChild($objIdNode);

            $priceExcl = $order['base_price'];
            if(substr($priceExcl, 0,3) == '0.0'){
                $priceExcl = number_format((float)substr($order['base_price'], -3)/100,2);
            }else{
                $priceExcl = number_format($order['base_price'],2);
            }

            $objIdNode = $objXmlFeed->createElement('price_excl_vat');
            $dataSection = $objXmlFeed->createCDATASection($priceExcl);
            $objIdNode->appendChild($dataSection);
            $objProductNode->appendChild($objIdNode);

            $objIdNode = $objXmlFeed->createElement('qty_ordered');
            $dataSection = $objXmlFeed->createCDATASection(round($order['qty_ordered'], 3, PHP_ROUND_HALF_UP));
            $objIdNode->appendChild($dataSection);
            $objProductNode->appendChild($objIdNode);

            /*
            *   if afwijkenidealeverpakking == 0 
            *   Then afname_per will be idealeverpakking
            *   if afwijkenidealeverpakking is not  equal to 0 
            *   Then afname_per will be 1
            */

            $objIdNode = $objXmlFeed->createElement('afname_per');

            if($attribute['afwijkenidealeverpakking'] == 0){
                $dataSection = $objXmlFeed->createCDATASection($attribute['idealeverpakking']);
            }else{
                $dataSection = $objXmlFeed->createCDATASection('1');
            }
            
            $objIdNode->appendChild($dataSection);
            $objProductNode->appendChild($objIdNode);

            $objIdNode = $objXmlFeed->createElement('idealeverpakking');
            $dataSection = $objXmlFeed->createCDATASection($attribute['idealeverpakking']);
            $objIdNode->appendChild($dataSection);
            $objProductNode->appendChild($objIdNode);
            

            $objIdNode = $objXmlFeed->createElement('afwijkenidealeverpakking');
            $dataSection = $objXmlFeed->createCDATASection($attribute['afwijkenidealeverpakking']);
            $objIdNode->appendChild($dataSection);
            $objProductNode->appendChild($objIdNode);
            

            $objIdNode = $objXmlFeed->createElement('prijsfactor');
            $dataSection = $objXmlFeed->createCDATASection($attribute['prijsfactor']);
            $objIdNode->appendChild($dataSection);
            $objProductNode->appendChild($objIdNode);
            
            $ProductList->appendChild($objProductNode);
        }
        
        $objXmlFeed->appendChild($ProductList);
        $strXmlPath = "yesterday_order_feed.xml";
        $objXmlFeed->save($strXmlPath);
    }

    public function monthorderFeeds(){
        Mage::log("Past 30 days orders feed started", null, 'orderfeed.log');
        $newdate = date("Y-m-d");
        for ($i=30; $i > 0; $i--) { 
            $resultSet =  Mage::helper('orderfeed')->getDateOrders($newdate);

            $objXmlFeed = new DOMDocument('1.0', 'UTF-8');
            $objXmlFeed->formatOutput = true;

            // Create xml Product List Node
            $ProductList = $objXmlFeed->createElement('product_list');

            foreach ($resultSet as $order) {
                $attribute = Mage::getSingleton('catalog/product')->getCollection()
                ->addAttributeToFilter('sku', $order['sku'])
                ->addAttributeToSelect('prijsfactor')
                ->addAttributeToSelect('verkoopeenheid')
                ->addAttributeToSelect('idealeverpakking')
                ->addAttributeToSelect('afwijkenidealeverpakking')
                ->getFirstItem();

                // Create xml First Product Node
                $objProductNode = $objXmlFeed->createElement('product');
                $objIdNode = $objXmlFeed->createElement('sku');
                $dataSection = $objXmlFeed->createCDATASection($order['sku']);
                $objIdNode->appendChild($dataSection);
                $objProductNode->appendChild($objIdNode);

                $priceIncl = $order['price_incl_tax'];
                if(substr($priceIncl, 0,3) == '0.0'){
                    $priceIncl = number_format((float)substr($order['price_incl_tax'], -3)/100,2);
                }else{
                    $priceIncl = number_format($order['price_incl_tax'],2);
                }

                $objIdNode = $objXmlFeed->createElement('price_incl_tax');
                $dataSection = $objXmlFeed->createCDATASection($priceIncl);
                $objIdNode->appendChild($dataSection);
                $objProductNode->appendChild($objIdNode);

                $priceExcl = $order['base_price'];
                if(substr($priceExcl, 0,3) == '0.0'){
                    $priceExcl = number_format((float)substr($order['base_price'], -3)/100,2);
                }else{
                    $priceExcl = number_format($order['base_price'],2);
                }

                $objIdNode = $objXmlFeed->createElement('price_excl_vat');
                $dataSection = $objXmlFeed->createCDATASection($priceExcl);
                $objIdNode->appendChild($dataSection);
                $objProductNode->appendChild($objIdNode);

                $objIdNode = $objXmlFeed->createElement('qty_ordered');
                $dataSection = $objXmlFeed->createCDATASection(round($order['qty_ordered'], 3, PHP_ROUND_HALF_UP));
                $objIdNode->appendChild($dataSection);
                $objProductNode->appendChild($objIdNode);

                /*
                *   if afwijkenidealeverpakking == 0 
                *   Then afname_per will be idealeverpakking
                *   if afwijkenidealeverpakking is not  equal to 0 
                *   Then afname_per will be 1
                */

                $objIdNode = $objXmlFeed->createElement('afname_per');

                if($attribute['afwijkenidealeverpakking'] == 0){
                    $dataSection = $objXmlFeed->createCDATASection($attribute['idealeverpakking']);
                }else{
                    $dataSection = $objXmlFeed->createCDATASection('1');
                }
                
                $objIdNode->appendChild($dataSection);
                $objProductNode->appendChild($objIdNode);

                $objIdNode = $objXmlFeed->createElement('idealeverpakking');
                $dataSection = $objXmlFeed->createCDATASection($attribute['idealeverpakking']);
                $objIdNode->appendChild($dataSection);
                $objProductNode->appendChild($objIdNode);
                

                $objIdNode = $objXmlFeed->createElement('afwijkenidealeverpakking');
                $dataSection = $objXmlFeed->createCDATASection($attribute['afwijkenidealeverpakking']);
                $objIdNode->appendChild($dataSection);
                $objProductNode->appendChild($objIdNode);
                

                $objIdNode = $objXmlFeed->createElement('prijsfactor');
                $dataSection = $objXmlFeed->createCDATASection($attribute['prijsfactor']);
                $objIdNode->appendChild($dataSection);
                $objProductNode->appendChild($objIdNode);
                
                $ProductList->appendChild($objProductNode);
            }
            $objXmlFeed->appendChild($ProductList);
            $strXmlPath = "monthwise/".$newdate.".xml";
            $objXmlFeed->save($strXmlPath);
            $newdate = date('Y-m-d',(strtotime ( '-1 day' , strtotime ( $newdate) ) ));
        }
    }

}
