<?php

class Jaagers_Price_IndexController extends Mage_Core_Controller_Front_Action
{

    public function GetGroupedProductsAction()
    {
        $params = $this->_request->getParam('id');
        $_product = Mage::getModel('catalog/product')->load($params);

        echo $this->getLayout()
            ->createBlock('Mage_Core_Block_Template')
            ->setData('product', $_product)
            ->setData('limit', true)
            ->setTemplate('catalog/product/grouped_product_list.phtml')
            ->toHtml();
    }

    public function GetvoorraadAction()
    {
        $params = $this->_request->getParam('data');
        
        $finalResponse = array();
        
        if (count($params) > 0) {

            $artikelen = array();

            foreach ($params as $a) {
                $artikel = new stdClass();
                $artikel->sku = $a['id'];
                $artikel->type = $a['type'];
                $artikelen[] = $artikel;
            }

            $resultStock = Mage::helper('price/data')->getVoorraadMore($artikelen);
            // $resultDeliveryTime = Mage::helper('price/data')->getLevertijdMore($artikelen);

            if (isset($resultStock->decimal) && !is_array($resultStock->decimal)) {

                /*** Combipac returned single result ***/
                $result = $resultStock->decimal;
                $finalResponse[] = $this->_buildResult($result, $params[0]);

            } elseif (isset($resultStock->decimal)) {

                /*** Combipac returned multiresult ***/
                $cnt = 0;
                foreach ($resultStock->decimal as $result) {
                    $finalResponse[] = $this->_buildResult($result, $params[$cnt]);
                    $cnt++;
                }

            }
        }

        $this->getResponse()->setBody(json_encode($finalResponse));
    }

    private function _buildResult($result, $artikel)
    {
        $response = new stdClass();
        $response->Artinr = $artikel['id'];
        $response->VoorHH = $result;

        /*
        * Name: Helios Solutions
        * Date: 13-Augest-2014
        * Definition: Create Logic For Showing Actual Stock on FroentEnd Side.
        */
        //$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $artikel['id']);
        $productCollection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect(array('afwijkenidealeverpakking', 'idealeverpakking', 'leverancier', 'leverdatum', 'verkoopeenheid', 'deliverytime'))
            ->addAttributeToFilter('sku', $artikel);


        if (!$productCollection->count()) {
            $response->text = '';
            return $response;
        }

        $product = $productCollection->getFirstItem();
        $response->Levertijd = $levertijd = $product->getData('deliverytime');

        if ($product->getData('afwijkenidealeverpakking') == 0) {
            $idealeverpakking = $product->getData('idealeverpakking');
            $result = (int)($result / $idealeverpakking);
            $response->VoorHH = $result;
        }

        /*out of stock product*/
        if ($result <= 0) {

            if ($response->Levertijd == 1) {
                $levertijd = 'verzending: binnen ' . $response->Levertijd . ' werkdagen';
            } else {
                if ($response->Levertijd > 14) {
                    $levertijd = 'verzending: binnen 14 dagen';
                } else {
                    $levertijd = 'verzending: binnen ' . $response->Levertijd . ' werkdagen';
                }
            }

            if ($product->getData('leverancier') == 3797) {
                $response->text = '<span class="green"></span><span class="stock">Op voorraad bij leverancier</span><span class="now-order">Levertijd +/- 3 werkdagen</span>';
            } else {
                $response->text = '<span class="stock">' . $levertijd . '</span>';
            }

        } else {
            /*in of stock product*/
            $leverdatum = date_create($product->getData('leverdatum'));
            $curentDate = date_create(date("Y-m-d"));
            $diff = date_diff($curentDate, $leverdatum);

            if ($diff->format("%R") === '+') {
                $levertijd = $diff->format("%a");
            } elseif ($diff->format("%R") === '-') {
                $levertijd = $response->Levertijd;
                if ($response->Levertijd == 1) {
                    $levertijd = 5;
                }
            }
			
            $message = Mage::helper('featured')->getInstockDeliveryText();
            if ($product->getArtikelstatus() == 3) {
                $message = 'u kunt niet meer dan ' . $result . ' ' . $product->getData('verkoopeenheid') . ' bestellen';
            }

            $response->text = '<span class="stock"> ' . $this->__('In Stock') . '</span><span class="now-order">' . $message . '</span><input type="hidden" name="levertijd" class="levertijd" value="' . $levertijd . '">';
        }

        return $response;
    }

    /*public function buildResult($result) {

        $newresult = new stdClass();
        $newresult->Artinr = $result->Artinr;
        $newresult->VoorHH = $result->VoorHH;

        if($result->VoorHH < 0 || $result->VoorHH == 0 ) {
            $newresult->text = '<div class="stock red">Geen voorraad</div>';
        } elseif( $result->VoorHH > 0 && $result->VoorHH < 10 ) {
            $newresult->text = '<div class="stock orange">Beperkte voorraad</div>';
        } elseif( $result->VoorHH > 10 ) {
            $newresult->text = '<div class="stock green">op voorraad</div>';
        }

        return $newresult;

    }*/

}
