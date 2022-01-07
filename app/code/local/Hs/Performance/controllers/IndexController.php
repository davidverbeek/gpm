<?php

class Hs_Performance_IndexController extends Mage_Core_Controller_Front_Action
{
    public function getNewProductsAction() {
        $this->loadLayout(false);
        $block = $this->getLayout()->createBlock('catalog/product_new');
        $block->setTemplate('catalog/product/salecategoryblock.phtml');
        
        $this->getResponse()
        ->setHeader('Content-Type', 'text/html')
        ->setBody($block->toHtml());
    }

    public function getSliderAction() {
      
        echo $this->createSliderContent();
      }
  
      public function createSliderContent() {
          
          $slides = $this->getSlides();
          $getli = "";
          foreach($slides as $s) {
                  $style = $content = '';
                  $attr = 'data-img-height="0"';
                  if ( !empty($s['image']) ) {
                      $imgSize = getimagesize(Mage::getBaseDir('media') .'/'. $s['image']);
                      if ($imgSize) {
                          $attr = 'data-img-height="'.$imgSize[1].'"';
                      }
                  }
                  if ( !empty($s['slide_width']) ) {
                      $content = 'style="width:'.$s['slide_width'].'px;"';
                  }
                  $li_id = Mage::getBaseUrl('media') . $s['image'];
  
                 $getli .= "<li id=".$li_id." ".$attr."><div class='row text-".$s['slide_align']."'> <div class='content' ".$content."> <div class='border'></div> <p>".nl2br($s['slide_text'])."</p> <button class='button button_white' onclick=window.location='".$s['slide_link']."'>".$s['slide_button']."</button></div></div></li>";
                // $getli .= '<li id='.$li_id.' '.$attr.'><div class="row text-'.$s["slide_align"].'"> <div class="content" '.$content.'> <div class="border"></div> <p>'.nl2br($s["slide_text"]).'</p> <button class="button button_white" onclick="window.location=''.$s["slide_link"].'" ">'.$s["slide_button"].'</button></div></div></li>';
          }
  
          $html = '<div class="slider">
          <div id="slide-timeline" class="tmline"></div>
          <div id="flexslider" class="flexslider">
             <div class="flex-viewport">
                <ul class="slides">
                   '.$getli.'
                </ul>
             </div>
          </div>
       </div>
       ';
  
       return $html;
      }
  
      public function getSlides()
      {
          $config = Mage::getStoreConfig('shopperslideshow', Mage::app()->getStore()->getId());
          if ( $config['config']['slider'] == 'flexslider' ) {
              $model = Mage::getModel('shopperslideshow/shopperslideshow');
          } else {
              $model = Mage::getModel('shopperslideshow/shopperrevolution');
          }
          $slides = $model->getCollection()
              ->addStoreFilter(Mage::app()->getStore())
              ->addFieldToSelect('*')
              ->addFieldToFilter('status', 1)
              ->setOrder('sort_order', 'asc');
          return $slides;
      }

      // Cart Optimization Starts

      public function chkItemstatsAction() {
        
        $arrSku = $this->getRequest()->getParam('allsku');
        $eachitem = $this->getRequest()->getParam('item');
        
        $request = array(
        'bedrijfnr' =>  1, 
        'filiaalnr' =>  1, 
        'artikelen' =>  $arrSku
        );

		if($this->checkifApiAvailable()) {
			
			$client = new SoapClient((string)Mage::getConfig()->getNode('default/combipac')->apipath);
			$stockResponse = $client->GetVoorraadMore($request);
			$arrStocks = $stockResponse->GetVoorraadMoreResult->decimal;

			$cart_item_data = array();

			if(count($arrStocks) == 1) {
			  $narrStocks = array();
			  $narrStocks[] = $arrStocks;
			  $arrStocks = $narrStocks;
			}

			if(count($arrStocks)) {
			  foreach ($arrStocks as $k => $stock) {
			  
			   $_item = $eachitem[$arrSku[$k]];
			   $qty = $eachitem[$arrSku[$k]]["Qty"];
			   $itemsku = $arrSku[$k];

         // For Manualy product starts
         $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $itemsku);
         $manualProduct = $product->getData('manualproduct');
        
         if($manualProduct == 1) {
           $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
           $stock = $stock->getQty();
         }
         // For Manualy product ends

			   
			   $finalData = $this->itemstatuscheck($_item,$qty,$itemsku, $stock);


			   $cart_item_data[$arrSku[$k]] = $finalData;
			  }
			}
			echo json_encode($cart_item_data);
			
		} else { 

          if(count($eachitem)) {
            foreach($eachitem as $item_sku => $item_data) {
              $_item = $item_data;
              $qty = $item_data["Qty"];
              $itemsku = $item_sku;
              $stock = 1000;


              // For Manualy product starts
               $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $itemsku);
               $manualProduct = $product->getData('manualproduct');
              
               if($manualProduct == 1) {
                 $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                 $stock = $stock->getQty();
               }
               // For Manualy product ends




              $finalData = $this->itemstatuscheck($_item,$qty,$itemsku, $stock);
              $cart_item_data[$itemsku] = $finalData;
            }
          }
          echo json_encode($cart_item_data);
        }
      }
      
      public function itemstatuscheck($_item,$qty,$sku, $stock) {
        $artikel = new stdClass();
        $artikel->sku = $sku;
        $artikel->type = '';
        $artikelen[] = $artikel;
        $onlyStatus = '';
        
        $stockstatus = $_item['artikelstatus'];

        $idealeverpakking=  str_replace(",", ".", $_item['idealeverpakking']);
        $leverancier = $_item['leverancier'];
        $verkoopeenheid = strtolower($_item['verkoopeenheid']);
        $afwijkenidealeverpakking = $_item['afwijkenidealeverpakking'];
        $prijsfactor = $_item['prijsfactor'];
        $id = $_item['Sku'];

        $stockFactor='';

        if (!(int) $afwijkenidealeverpakking) {
            if($idealeverpakking>1) {
                $stockFactor=$idealeverpakking ;
            }
            $unitLabel=Mage::helper('featured')->getProductUnit($_item['verkoopeenheid']);
            $unitLabelDisp=Mage::helper('featured')->getStockUnit($idealeverpakking,$unitLabel);
        } else {
            $stockFactor='';
            $unitLabel=Mage::helper('featured')->getProductUnit($_item['verkoopeenheid']);
            $unitLabelDisp=Mage::helper('featured')->getStockUnit('1',$unitLabel);
        }
        
        $_incl = $_item["incl"]; 

        $finalContent = '';
        $contentMessage = '';
        $truestock = '';
        $onlyStatus = '';

        if (isset($stock)) {

               

                $_resultStock = $stock;
                
                $finalresult = $this->buildResult($_resultStock, $sku, $_item);

                
                
                $truestock=$finalresult->VoorHH;

                $unit=Mage::helper('featured')->getProductUnit($_item['verkoopeenheid']);
                        
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
                                $finalContent .= "<span class='cart-unit-price-excl'> x ".Mage::helper('checkout')->formatPrice($_item['CalculationPrice'])."</span>";    
                                $finalContent .= "<span class='cart-unit-price-incl'> ( ".Mage::helper('checkout')->formatPrice($_incl-$_item['WeeeTaxDisposition'])." ".$this->__('incl.'). ' '.$this->__('per'). ' '.$stockFactor. ' ' .$unitLabelDisp. " )</span>";   
                                if($backorder==1){
                                    $contentMessage='<span class="yellow"></span><span class="stock">' . $backorder . ' ' . $stockMessage . ' wordt besteld </span><span class="now-order">' . $levertijd . '</span>';
                                } else {
                                    $contentMessage='<span class="yellow"></span><span class="stock">' . $backorder . ' ' . $stockMessage . ' worden besteld </span><span class="now-order">' . $levertijd . '</span>';
                                }
                                
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

                            if ($_item['TypeId']=='grouped') {
                                $finalContent='<span class="stock">' . $this->checkQty($truestock, $idealeverpakking, $unitLabel, $afwijkenidealeverpakking,$qty) . ' </span>';
                                $finalContent .= "<span class='cart-unit-price-excl'> x ".Mage::helper('checkout')->formatPrice($_item['CalculationPrice'])."</span>";    
                                $finalContent .= "<span class='cart-unit-price-incl'> ( ".Mage::helper('checkout')->formatPrice($_incl-$_item['WeeeTaxDisposition'])." ".$this->__('incl.'). ' '.$this->__('per'). ' '.$stockFactor. ' ' .$unitLabelDisp. " )</span>";   
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
                                    $finalContent .= "<span class='cart-unit-price-excl'> x ".Mage::helper('checkout')->formatPrice($_item['CalculationPrice'])."</span>";    
                                    $finalContent .= "<span class='cart-unit-price-incl'> ( ".Mage::helper('checkout')->formatPrice($_incl-$_item['WeeeTaxDisposition'])." ".$this->__('incl.'). ' '.$this->__('per'). ' '.$stockFactor. ' ' .$unitLabelDisp. " )</span>";   
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
                        $finalContent .= "<span class='cart-unit-price-excl'> x ".Mage::helper('checkout')->formatPrice($_item['CalculationPrice'])."</span>";    
                        $finalContent .= "<span class='cart-unit-price-incl'> ( ".Mage::helper('checkout')->formatPrice($_incl-$_item['WeeeTaxDisposition'])." ".$this->__('incl.'). ' '.$this->__('per'). ' '.$stockFactor. ' ' .$unitLabelDisp. " )</span>";
                       $finalContent.=$finalresult->text;
                    }
                }
        }

        $finalData['finalcontent']=$finalContent;
        $finalData['contentMsg']= $contentMessage;
        $finalData['trueStock']=$truestock;
        $finalData['onlyStatus'] = $onlyStatus;
        
        $finalData['sku'] = $sku;
        $finalData['qty'] = $qty;

        
        return $finalData;
        
  }

      public function buildResult($result, $sku, $_item) {
        $newresult = new stdClass();
        $newresult->Artinr = $sku;
        $newresult->VoorHH = $result;
     
        $product = Mage::getModel('catalog/product')->load($_item["ProductId"]);
        $newresult->Levertijd = $levertijd = $product->getData('deliverytime');
        if($_item["afwijkenidealeverpakking"]!=0){
            $result=$result;
        }else{
            $idealeverpakking=  $_item["idealeverpakking"];
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

            if( $_item["leverancier"]==3797){
                $newresult->text = '<span class="green"></span><span class="stock">Op voorraad bij leverancier</span><span class="now-order">Levertijd +/- 3 werkdagen</span>';
            }
            else
                $newresult->text = '<span class="stock">' . $levertijd . '</span>';

        } else { 
                
                if($_item["artikelstatus"] == 3){
                    $newresult->text ='<span class="'. $levertijd .' now-order">u kunt niet meer dan '.$result->Voorraad.' ' . $_item["verkoopeenheid"].' bestellen</span><input type="hidden" name="levertijd" class="levertijd" value="'.$levertijd.'">';
                }else{
					$instockDeliveryText = Mage::helper('featured')->getInstockDeliveryText();
                    $newresult->text ='<span class="'. $levertijd .' now-order">'.$instockDeliveryText.'</span><input type="hidden" name="levertijd" class="levertijd" value="'.$levertijd.'">';
                }
                
        }
        $newresult->levertijd=$levertijd;
        
        return $newresult;

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

      public function getAjaxVooradMoreAction() {
      
      $arrSku = $this->getRequest()->getParam('allsku');
      $eachitem = $this->getRequest()->getParam('item');
      
      $request = array(
      'bedrijfnr' =>  1, 
      'filiaalnr' =>  1, 
      'artikelen' =>  $arrSku
      );

		if($this->checkifApiAvailable()) {
			  $client = new SoapClient((string)Mage::getConfig()->getNode('default/combipac')->apipath);
			  $stockResponse = $client->GetVoorraadMore($request);
			  $arrStocks = $stockResponse->GetVoorraadMoreResult->decimal;

			  $cart_item_data = array();

			   if(count($arrStocks) == 1) {
				  $narrStocks = array();
				  $narrStocks[] = $arrStocks;
				  $arrStocks = $narrStocks;
				}

			  if(count($arrStocks)) {
				foreach ($arrStocks as $k => $stock) {
				 
         // For Manualy product starts
         $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $arrSku[$k]);
         $manualProduct = $product->getData('manualproduct');
        
         if($manualProduct == 1) {
           $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
           $stock = $stock->getQty();
         }
         // For Manualy product ends




				 $finalresult = $this->buildnewResult($stock, $arrSku[$k], $eachitem[$arrSku[$k]]);
				 $cart_item_data[$arrSku[$k]]["truestock"] = $finalresult->VoorHH;
				 $cart_item_data[$arrSku[$k]]["sku"] = $arrSku[$k];
				 $cart_item_data[$arrSku[$k]]["qty"] = $eachitem[$arrSku[$k]]["Qty"];
				}
			  }
			  echo json_encode($cart_item_data);
		} else {
			if(count($eachitem)) {
				foreach($eachitem as $item_sku => $item_data) {

         $stock = 1000;  
         // For Manualy product starts
         $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $item_sku);
         $manualProduct = $product->getData('manualproduct');
        
         if($manualProduct == 1) {
           $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
           $stock = $stock->getQty();
         }
         // For Manualy product ends


				 $finalresult = $this->buildnewResult($stock, $item_sku, $item_data);
				 $cart_item_data[$item_sku]["truestock"] = $finalresult->VoorHH;
				 $cart_item_data[$item_sku]["sku"] = $item_sku;
				 $cart_item_data[$item_sku]["qty"] = $item_data["Qty"];
				}
			}
			echo json_encode($cart_item_data);
		}
    }
    
      public function buildnewResult($result, $sku, $_item) {
      $newresult = new stdClass();
      $newresult->Artinr = $sku;
      $newresult->VoorHH = $result;

      $afwijkenidealeverpakking = $_item["afwijkenidealeverpakking"];
      $idealeverpakking = $_item["idealeverpakking"];
    
      if($afwijkenidealeverpakking == 0) {
        $idealeverpakking =  $idealeverpakking;
        $result=(int)($result / $idealeverpakking);
        $newresult->VoorHH = $result;
      }

      
    return $newresult;

    }

    //Cart Optimization Ends
	
	public function checkifApiAvailable() {
      $session = Mage::getSingleton('core/session');
      
      if ($session->getApiAvailable()) {

          $handle = curl_init((string)Mage::getConfig()->getNode('default/combipac')->apipath);
          curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
          curl_setopt($handle, CURLOPT_TIMEOUT, 2);
          $curlresponse = curl_exec($handle);
          $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
      
          if ($httpCode == 0) {
                /*** We don't have a WSDL. Service is down. exit the function ***/
                $session->setApiAvailable(false);
                $session->setApiAvailableTimeOut(time());
                curl_close($handle);
                return false;
          } else {
                curl_close($handle); 
                return true;  
          }
      } else {
           return false;
      }
   }

}
