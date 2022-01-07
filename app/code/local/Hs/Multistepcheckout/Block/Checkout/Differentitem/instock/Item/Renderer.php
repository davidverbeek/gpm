<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart item render block
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @method Mage_Checkout_Block_Cart_Item_Renderer setProductName(string)
 * @method Mage_Checkout_Block_Cart_Item_Renderer setDeleteUrl(string)
 */
class Hs_Multistepcheckout_Block_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer
{
    public function checkItemStatus($sku)
    {
        $artikel = new stdClass();
        $artikel->sku = $sku;
        $artikel->type = '';
        $artikelen[] = $artikel;
        
        $result = Mage::helper('price/data')->getVoorraadMore($artikelen);
            
            if(isset($result->VoorraadInfo) && !is_array($result->VoorraadInfo)) {

                /*** Combipac returned single result ***/

                $result = $result->VoorraadInfo;
                 
                $finalresult = $this->buildResult($result, $sku);

            } 
            return $finalresult;
        
    }
    public function buildResult($result, $sku) {
        $newresult = new stdClass();
        $newresult->Artinr = $sku;
        $newresult->VoorHH = $result->Voorraad;
        $newresult->Levertijd = $result->Levertijd;
     
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
        if($product->getData('afwijkenidealeverpakking')!=0){
            $result->Voorraad=$result->Voorraad;
        }else{
            $idealeverpakking=$product->getData('idealeverpakking');
            $result->Voorraad=(int)($result->Voorraad / $idealeverpakking);
            $newresult->VoorHH = $result->Voorraad;
        }
        /*out of stock product*/
        if($result->Voorraad <= 0) {
            if($result->Levertijd == 1) {
                $levertijd = 'verzending: binnen ' . $result->Levertijd . ' werkdagen';
            } else {
                if($result->Levertijd > 14) {
                    $levertijd = 'verzending: binnen 14 werkdagen';
                } else {
                    $levertijd = 'verzending: binnen ' . $result->Levertijd . ' werkdagen';
                }
            }

            if($product->getData('leverancier')==3797){
                $newresult->text = '<span class="green"></span><span class="stock">Op voorraad bij leverancier</span><span class="now-order">Levertijd +/- 3 werkdagen</span>';
            }
            else
                $newresult->text = '<span class="stock">' . $levertijd . '</span>';

        } else {
                /*in of stock product*/
                $leverdatum=date_create($product->getData('leverdatum'));
                $curentDate=date_create(date("Y-m-d"));
                $diff=date_diff($curentDate,$leverdatum);
                if($diff->format("%R")==='+'){
                    $levertijd= $diff->format("%a");
                }elseif($diff->format("%R")==='-'){
                    if($result->Levertijd == 1)
                        $levertijd= 5;
                    else
                        $levertijd= $result->Levertijd;
                }
                if($product->getArtikelstatus() == 3){
                    $newresult->text ='<span class="stock">op voorraad</span><span class="now-order">u kunt niet meer dan '.$result->Voorraad.' ' .$product->getVerkoopeenheid().' bestellen</span><input type="hidden" name="levertijd" class="levertijd" value="'.$levertijd.'">';
                }else{
					$instockDeliveryText = Mage::helper('featured')->getInstockDeliveryText();
                    $newresult->text ='<span class="stock">op voorraad</span><span class="now-order">'.$instockDeliveryText.'</span><input type="hidden" name="levertijd" class="levertijd" value="'.$levertijd.'">';
                }

        }

        return $newresult;

    }
}
