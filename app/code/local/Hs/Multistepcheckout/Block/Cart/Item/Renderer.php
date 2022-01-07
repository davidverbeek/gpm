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
	/**
     * Get item delete url
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        if ($this->hasDeleteUrl()) {
            return $this->getData('delete_url');
        }

        if (version_compare(Mage::getVersion(), '1.9.2.3', '>=')) {
            return $this->getUrl(
                'checkout/cart/delete',
                array(
                    'id'=>$this->getItem()->getId(),
                    'form_key' => Mage::getSingleton('core/session')->getFormKey(),
                    Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->helper('core/url')->getEncodedUrl()
                )
            );
        } else {
            return $this->getUrl(
                'checkout/cart/delete',
                array(
                    'id'=>$this->getItem()->getId(),
                    Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->helper('core/url')->getEncodedUrl()
                )
            );
        }
    }

    public function checkItemStatus($_item,$qty,$sku) {

        $artikel = new stdClass();
        $artikel->sku = $sku;
        $artikel->type = '';
        $artikelen[] = $artikel;
        $onlyStatus = '';
        
        $stockstatus = $_item->getProduct()->getData('artikelstatus');

        $idealeverpakking=  str_replace(",", ".",$_item->getProduct()->getData('idealeverpakking'));
        $leverancier = $_item->getProduct()->getData('leverancier');
        $verkoopeenheid = strtolower($_item->getProduct()->getData('verkoopeenheid'));
        $afwijkenidealeverpakking = $_item->getProduct()->getData('afwijkenidealeverpakking');
        $prijsfactor = $_item->getProduct()->getData('prijsfactor');
        $id = $_item->getProduct()->getSku();

        $stockFactor='';

        if (!(int) $afwijkenidealeverpakking) {
            if($idealeverpakking>1) {
                $stockFactor=$idealeverpakking ;
            }
            $unitLabel=Mage::helper('featured')->getProductUnit($_item->getProduct()->getData('verkoopeenheid'));
            $unitLabelDisp=Mage::helper('featured')->getStockUnit($idealeverpakking,$unitLabel);
        } else {
            $stockFactor='';
            $unitLabel=Mage::helper('featured')->getProductUnit($_item->getProduct()->getData('verkoopeenheid'));
            $unitLabelDisp=Mage::helper('featured')->getStockUnit('1',$unitLabel);
        }
        
        $_incl = Mage::helper('checkout')->getPriceInclTax($_item); 
        $resultStock = Mage::helper('price/data')->getVoorraadMore($artikelen);
        // $resultDeliveryTime = Mage::helper('price/data')->getLevertijdMore($artikelen);
        // echo "<pre>";
        // print_r($resultStock);

        $finalContent = '';
        $contentMessage = '';
        $truestock = '';
        $onlyStatus = '';

        if (isset($resultStock->decimal) && !is_array($resultStock->decimal)) {

                /*** Combipac returned single result ***/

                $_resultStock = $resultStock->decimal;
                // $_resultDeliveryTime = $resultDeliveryTime->int;
                $finalresult = $this->buildResult($_resultStock, $sku, $_item);

                // print_r($finalresult);
                
                $truestock=$finalresult->VoorHH;

                $unit=Mage::helper('featured')->getProductUnit($_item->getProduct()->getData('verkoopeenheid'));
                        
                $verkoopeenheid=Mage::helper('featured')->getStockUnit($prijsfactor,$unit);
                $levertijd = $finalresult->levertijd;

                if ($qty > $truestock) {
                    $backorder = $qty - $truestock;
                    if ($truestock == 0) {
                        if ($stockstatus == 6) {

                            $finalContent='<span class="stock"> (U kunt niet meer dan ' . $truestock . ' ' .$verkoopeenheid . ' <span id="besteld-term-' . $id . '" class="besteld-info">bestellen</span>)</span>';
                            $onlyStatus = '<span class="yellow"></span>';

                        } else {
                            if($leverancier==3797) {
                                $finalContent='';
                                $onlyStatus = '';
                            } else {
                                $stockMessage=Mage::helper('featured')->getStockUnit($backorder,$unit);
                                // echo $stockMessage;
                                //var levertijd = $j('#' + id + ' .now-order').html();
                                $finalContent='<span class="yellow"></span><span class="stock">' . $backorder .  ' </span>';
                                $onlyStatus = '<span class="yellow"></span>';
                                $finalContent .= "<span class='cart-unit-price-excl'> x ".Mage::helper('checkout')->formatPrice($_item->getCalculationPrice())."</span>";    
                                $finalContent .= "<span class='cart-unit-price-incl'> ( ".Mage::helper('checkout')->formatPrice($_incl-$_item->getWeeeTaxDisposition())." ".$this->__('incl.'). ' '.$this->__('per'). ' '.$stockFactor. ' ' .$unitLabelDisp. " )</span>";   
                                if($backorder==1){
                                    $contentMessage='<span class="yellow"></span><span class="stock">' . $backorder . ' ' . $stockMessage . ' wordt besteld </span><span class="now-order">' . $levertijd . '</span>';
                                } else {
                                    $contentMessage='<span class="yellow"></span><span class="stock">' . $backorder . ' ' . $stockMessage . ' worden besteld </span><span class="now-order">' . $levertijd . '</span>';
                                }
                                // exit;
                            }
                        }
                    } else {
                        if ($stockstatus == 6) {
                            $finalContent='<span class="yellow"></span><span class="stock">slechts '.$truestock.' ' . $verkoopeenheid.' leverbaar</span><span class="now-order">Artikel verdwijnt uit assortiment</span>';
                        } else {
                            $backorderStockLabel=Mage::helper('featured')->getStockUnit($backorder,$unit);
                               
                            if ($afwijkenidealeverpakking != 0) {
                                $hstock = ' ' . $backorderStockLabel;
                            } else {
                                if($idealeverpakking!=1){
                                    $hstock = ' x ' . $idealeverpakking . ' ' . $backorderStockLabel;
                                } else {
                                    $hstock = ' ' . $backorderStockLabel;
                                }
                            }

                            if ($_item->getProduct()->getTypeId()=='grouped') {
                                $finalContent='<span class="stock">' . $this->checkQty($truestock, $idealeverpakking, $unitLabel, $afwijkenidealeverpakking,$qty) . ' </span>';
                                $finalContent .= "<span class='cart-unit-price-excl'> x ".Mage::helper('checkout')->formatPrice($_item->getCalculationPrice())."</span>";    
                                $finalContent .= "<span class='cart-unit-price-incl'> ( ".Mage::helper('checkout')->formatPrice($_incl-$_item->getWeeeTaxDisposition())." ".$this->__('incl.'). ' '.$this->__('per'). ' '.$stockFactor. ' ' .$unitLabelDisp. " )</span>";   
                            } else {
                                if($leverancier==3797  && $truestock <= 0){
                                    $finalContent='';
                                    $onlyStatus = '';
                                } else {
                                    if($backorder==1){
                                        $contentMessage='<span class="yellow"></span><span class="stock"> ' . $backorder . $hstock . ' wordt <span id="backorder-term-' . $id . '" class="backorder-info">besteld</span><span class="now-order">in +/- ' . $finalresult->levertijd . ' werkdagen geleverd</span>';
                                    } else {
                                        $contentMessage='<span class="yellow"></span><span class="stock"> ' . $backorder . $hstock . ' worden <span id="backorder-term-' . $id . '" class="backorder-info">besteld</span><span class="now-order">in +/- ' . $finalresult->levertijd . ' werkdagen geleverd</span>';
                                    }
                                    $finalContent= '<div class="backorder stock"><span class="yellow"></span><span class="stock"><div class="stockstatus"> ' . $qty . '</div>';
                                    $onlyStatus = '<span class="yellow"></span>';
                                    $finalContent .= "<span class='cart-unit-price-excl'> x ".Mage::helper('checkout')->formatPrice($_item->getCalculationPrice())."</span>";    
                                    $finalContent .= "<span class='cart-unit-price-incl'> ( ".Mage::helper('checkout')->formatPrice($_incl-$_item->getWeeeTaxDisposition())." ".$this->__('incl.'). ' '.$this->__('per'). ' '.$stockFactor. ' ' .$unitLabelDisp. " )</span>";   
                                }
                            }
                        }
                    }
                } else {
                    if($leverancier==3797 && $qty <= 0){
                        $finalContent=$finalresult->text; // Based on class
                    } else {
                        $contentMessage='<span class="stock stocklabel">' . $this->checkQty($truestock, $idealeverpakking, $unitLabel, $afwijkenidealeverpakking,$qty) . ' <span class="stocktext">op voorraad</span></span>'.$finalresult->text;
                        $finalContent=$this->calculateQty($qty, $idealeverpakking, $verkoopeenheid, $afwijkenidealeverpakking);
                        $onlyStatus = $this->calculateMobileQty($qty, $idealeverpakking, $verkoopeenheid, $afwijkenidealeverpakking);
                        $finalContent .= "<span class='cart-unit-price-excl'> x ".Mage::helper('checkout')->formatPrice($_item->getCalculationPrice())."</span>";    
                        $finalContent .= "<span class='cart-unit-price-incl'> ( ".Mage::helper('checkout')->formatPrice($_incl-$_item->getWeeeTaxDisposition())." ".$this->__('incl.'). ' '.$this->__('per'). ' '.$stockFactor. ' ' .$unitLabelDisp. " )</span>";
                       $finalContent.=$finalresult->text;
                    }
                }
        }

        $finalData['finalcontent']=$finalContent;
        $finalData['contentMsg']= $contentMessage;
        $finalData['trueStock']=$truestock;
        $finalData['onlyStatus'] = $onlyStatus;
        //return $finalContent;
        // print_r($finalData); //exit;
        return $finalData;
        
    }
    public function calculateQty($truestock, $idealeverpakking, $verkoopeenheid, $afwijkenidealeverpakking,$qty = '0') {
        if ($afwijkenidealeverpakking != 0 || $idealeverpakking == 1) { 
            if ($truestock > 0) {
                /*if ($qty <= 2) {
                    return '<span class="green"></span><span class="stock">nog maar ' . $qty . ' ' . $verkoopeenheid . ' </span>';
                } else */{
                    return '<span class="green"></span><span class="stock">' . ($truestock - $qty) .  ' </span>';
                }
            } else {
                return '<span class="yellow"></span>';
            }

            return "";

        }
        else {

            if ($truestock > 0) {
                
                /*if ($qty <= 2) {
                    return '<span class="green"></span><span class="stock">' . $qty . ' x ' . $idealeverpakking . ' ' . $verkoopeenheid . ' </span>';
                } else */{
                    //return '<span class="green"></span><span class="stock">' . $qty . ' x ' . $idealeverpakking . ' ' . $verkoopeenheid . ' </span>';
                    return '<span class="green"></span><span class="stock">' . $truestock .  ' </span>';
                }
            } else {
                return '<span class="yellow"></span>';
            }
            return "";

        }
    } 
    public function checkQty($truestock, $idealeverpakking, $stockLabel, $afwijkenidealeverpakking,$qty = '0') {
        $checkStock=$truestock-$qty;
        
        
        $verkoopeenheid=Mage::helper('featured')->getStockUnit($checkStock,$stockLabel);
        
        if ($afwijkenidealeverpakking != 0 || $idealeverpakking == 1) { 
            if ($truestock > 0) {
                /*if ($qty <= 2) {
                    return '<span class="green"></span><span class="stock">nog maar ' . $qty . ' ' . $verkoopeenheid . ' </span>';
                } else */{
                    return '<span class="green"></span><span class="stock">' . ($truestock - $qty) . ' ' . $verkoopeenheid .  ' </span>';
                }
            } else {
                return '<span class="yellow"></span>';
            }

            return "";

        }
        else {

            if ($truestock > 0) {
                
                /*if ($qty <= 2) {
                    return '<span class="green"></span><span class="stock">' . $qty . ' x ' . $idealeverpakking . ' ' . $verkoopeenheid . ' </span>';
                } else */{
                    //return '<span class="green"></span><span class="stock">' . $qty . ' x ' . $idealeverpakking . ' ' . $verkoopeenheid . ' </span>';
                    return '<span class="green"></span><span class="stock">' . $truestock . ' x ' . $idealeverpakking . ' ' . $verkoopeenheid .  ' </span>';
                }
            } else {
                return '<span class="yellow"></span>';
            }
            return "";

        }
    } 
    
    public function buildResult($result, $sku, $_item) {
        $newresult = new stdClass();
        $newresult->Artinr = $sku;
        $newresult->VoorHH = $result;
     
        $product = Mage::getModel('catalog/product')->load($_item->getProductId());
        $newresult->Levertijd = $levertijd = $product->getData('deliverytime');
        if($_item->getProduct()->getData('afwijkenidealeverpakking')!=0){
            $result=$result;
        }else{
            $idealeverpakking=$_item->getProduct()->getData('idealeverpakking');
            $result=(int)($result / $idealeverpakking);
            $newresult->VoorHH = $result;
        }
        /*out of stock product*/
        if($result <= 0) {
            if($newresult->Levertijd == 1) {
                $levertijd = 'verzending: binnen ' . $newresult->Levertijd . ' werkdagen';
            } else {
                if($newresult->Levertijd > 14) {
                    $levertijd = 'verzending: binnen 14 werkdagen';
                } else {
                    $levertijd = 'verzending: binnen ' . $newresult->Levertijd . ' werkdagen';
                }
            }

            if($_item->getProduct()->getData('leverancier')==3797){
                $newresult->text = '<span class="green"></span><span class="stock">Op voorraad bij leverancier</span><span class="now-order">Levertijd +/- 3 werkdagen</span>';
            }
            else
                $newresult->text = '<span class="stock">' . $levertijd . '</span>';

        } else { 
                /*in of stock product*/
                // $leverdatum=date_create($_item->getProduct()->getData('leverdatum'));
                // $curentDate=date_create(date("Y-m-d"));
                // $diff=date_diff($curentDate,$leverdatum);
                // if($diff->format("%R")==='+'){
                //     $levertijd= $diff->format("%a");
                // }elseif($diff->format("%R")==='-'){
                //     if($result->Levertijd == 1)
                //         $levertijd= 5;
                //     else
                //         $levertijd= $result->Levertijd;
                // }
                if($_item->getProduct()->getData('artikelstatus') == 3){
                    $newresult->text ='<span class="'. $levertijd .' now-order">u kunt niet meer dan '.$result->Voorraad.' ' .$_item->getProduct()->getData('verkoopeenheid').' bestellen</span><input type="hidden" name="levertijd" class="levertijd" value="'.$levertijd.'">';
                }else{
					$instockDeliveryText = Mage::helper('featured')->getInstockDeliveryText();
                    $newresult->text ='<span class="'. $levertijd .' now-order">'.$instockDeliveryText.'</span><input type="hidden" name="levertijd" class="levertijd" value="'.$levertijd.'">';
                }
                
        }
        $newresult->levertijd=$levertijd;
        
        return $newresult;

    }

    public function calculateMobileQty($truestock, $idealeverpakking, $verkoopeenheid, $afwijkenidealeverpakking, $qty = '0') {
        if ($afwijkenidealeverpakking != 0 || $idealeverpakking == 1) {
            if ($truestock > 0) {
                return '<span class="green"></span>';
            } else {
                return '<span class="yellow"></span>';
            }
            return "";
        } else {

            if ($truestock > 0) {
                return '<span class="green"></span>';
            } else {
                return '<span class="yellow"></span>';
            }
            return "";
        }
    }    
}
