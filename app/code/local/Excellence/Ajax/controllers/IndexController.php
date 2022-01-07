<?php
require_once 'Mage/Checkout/controllers/CartController.php';
class Excellence_Ajax_IndexController extends Mage_Checkout_CartController
{
    public function addAction()
    {
        $cart = $this->_getCart();
        $params = $this->getRequest()->getParams();
        if ($params['isAjax'] == 1) {
            $response = array();
            try {
                if (isset($params['qty'])) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $params['qty'] = $filter->filter($params['qty']);
                }

                $product = $this->_initProduct();


                $related = $this->getRequest()->getParam('related_product');

                /**
                 * Check product availability
                 */
                if (!$product) {
                    $response['status'] = 'ERROR';
                    $response['message'] = $this->__('Unable to find Product ID');
                }

                $cart->addProduct($product, $params);
                if (!empty($related)) {
                    $cart->addProductsByIds(explode(',', $related));
                }

                $cart->save();
                
                /*$_customPrice = $product->getFinalPrice();
				//echo $_customPrice; exit; 
                if($_customPrice > 0) {
                	
                	$afwijkenidealeverpakking = $product->getData('afwijkenidealeverpakking');
                	if($afwijkenidealeverpakking == 1) {
                		$priceFactor = (float)1;
                	}else{
                		$priceFactor = (float) str_replace(',','.', $product->getData('idealeverpakking'));
                	}
                    
                    if(!$priceFactor)
                        $priceFactor = (float)$product->getData('prijsfactor');
                    $item = $cart->getQuote()->getItemByProduct($product);
                    $item->setOriginalCustomPrice($product->getFinalPrice() * ($priceFactor ? $priceFactor : 1))
                        ->save();
                }*/

                $this->_getSession()->setCartWasUpdated(true);

                /**
                 * @todo remove wishlist observer processAddToCart
                 */
                Mage::dispatchEvent('checkout_cart_add_product_complete',
                    array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                );

                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('product added');
					$response['rpname'] = '<p class="product-name">
					<span>'.Mage::helper('common')->getGYZSSku($product->getSku()).'</span><br/>
					<a href="'.$product->getProductUrl().'">'.Mage::helper('core')->htmlEscape($product->getName()).'</a></p>';

					$response['status'] = 'SUCCESS';
                    $response['message'] = $message;

                    $this->loadLayout();
	                Mage::register('referrer_url', $this->_getRefererUrl());
                    $sidebar_header = $this->getLayout()->getBlock('cart_top')->toHtml();
                    $response['cart_top'] = $sidebar_header;
                }
            } catch (Mage_Core_Exception $e) {
                $msg = "";
                if ($this->_getSession()->getUseNotice(true)) {
                    $msg = $e->getMessage();
                    Mage::getSingleton('core/session')->addError($msg);

                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {

                        Mage::getSingleton('core/session')->addError($message);
                        $msg .= $message . '<br/>';
                    }
                }

                $response['status'] = 'ERROR';
                $response['message'] = $msg;
            } catch (Exception $e) {
                $response['status'] = 'ERROR';
                $response['message'] = $this->__('Cannot add the item to shopping cart.');
                Mage::getSingleton('core/session')->addError($response['message']);
                Mage::logException($e);
            }
            $this->_sendJson($response);
            return;
        } else {
            return parent::addAction();
        }
    }

    public function optionsAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        // Prepare helper and params
        $viewHelper = Mage::helper('catalog/product_view');

        $params = new Varien_Object();
        $params->setCategoryId(false);
        $params->setSpecifyOptions(false);

        // Render page
        try {
            $viewHelper->prepareAndRender($productId, $this, $params);
        } catch (Exception $e) {
            if ($e->getCode() == $viewHelper->ERR_NO_PRODUCT_LOADED) {
                if (isset($_GET['store']) && !$this->getResponse()->isRedirect()) {
                    $this->_redirect('');
                } elseif (!$this->getResponse()->isRedirect()) {
                    $this->_forward('noRoute');
                }
            } else {
                Mage::logException($e);
                $this->_forward('noRoute');
            }
        }
    }

    /**
     * send json respond
     *
     * @param array $response - response data
     */
    private function _sendJson( $response )
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody( (string) $this->getRequest()->getParam('callback') . '(' . Mage::helper('core')->jsonEncode($response) . ')' );
    }

    /**
     * Update product configuration for a cart item
     */
    public function updateItemOptionsAction()
    {
        $cart   = $this->_getCart();
        $id = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }

        if ($params['isAjax'] == 1) {
            $response = array('params' => $params);

            try {
                if (isset($params['qty'])) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $params['qty'] = $filter->filter($params['qty']);
                }

                $quoteItem = $cart->getQuote()->getItemById($id);
                if (!$quoteItem) {
                    $this->_sendJson(array(
                        'status' => 'ERROR',
                        'message' => $this->__('Quote item is not found.'),
                    ));
                    return;
                }

                $item = $cart->updateItem($id, new Varien_Object($params));
                if (is_string($item)) {
                    $this->_sendJson(array(
                        'status' => 'ERROR',
                        'message' => $item,
                    ));
                    return;
                }
                if ($item->getHasError()) {
                    Mage::throwException($item->getMessage());
                    $this->_sendJson(array(
                        'status' => 'ERROR',
                        'message' => $item->getMessage(),
                    ));
                    return;
                }

                $related = $this->getRequest()->getParam('related_product');
                if (!empty($related)) {
                    $cart->addProductsByIds(explode(',', $related));
                }

                $cart->save();

                $this->_getSession()->setCartWasUpdated(true);

                Mage::dispatchEvent('checkout_cart_update_item_complete',
                    array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                );
                if (!$this->_getSession()->getNoCartRedirect(true)) {
                    if (!$cart->getQuote()->getHasError()){
                        $response['status'] = 'SUCCESS';
                        $response['message'] = $this->__('%s was updated in your shopping cart.', Mage::helper('core')->htmlEscape($item->getProduct()->getName()));
                        $this->loadLayout();
                        Mage::register('referrer_url', $this->_getRefererUrl());
                        $sidebar_header = $this->getLayout()->getBlock('cart_top')->toHtml();
                        $response['cart_top'] = $sidebar_header;
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $msg = "";
                if ($this->_getSession()->getUseNotice(true)) {
                    $msg = $e->getMessage();
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {
                        $msg .= $message . '<br/>';
                    }
                }

                $response['status'] = 'ERROR';
                $response['message'] = $msg;
            } catch (Exception $e) {
                $response['status'] = 'ERROR';
                $response['message'] = $this->__('Cannot update the item.');
                Mage::logException($e);
            }
            $this->_sendJson($response);
            return;
        } else {
            return parent::updateItemOptionsAction();
        }

    }


    /**
     * Minicart delete action
     */
    public function ajaxDeleteAction()
    {

      // if (!$this->_validateFormKey()) {
      //   Mage::throwException('Invalid form key');
      // }
      
      $id = (int) $this->getRequest()->getParam('id');

      $result = array();
      if ($id) {
        try {
          $this->_getCart()->removeItem($id)->save();

          $result['qty'] = $this->_getCart()->getSummaryQty();

          // $this->loadLayout();
          // $result['content'] = $this->getLayout()->getBlock('minicart_head')->toHtml();

          $result['success'] = 1;
          // $result['message'] = $this->__('Item was removed successfully.');

          $message = $this->__('product removed');
                    $result['rpname'] = '<p class="product-name">Product Removed.</a></p>';

                    // $response['status'] = 'SUCCESS';
          $result['message'] = $message;

          $this->loadLayout();
            Mage::register('referrer_url', $this->_getRefererUrl());
          $sidebar_header = $this->getLayout()->getBlock('cart_top')->toHtml();
          $result['minicart_head'] = $sidebar_header;


          Mage::dispatchEvent('ajax_cart_remove_item_success', array('id' => $id));
        } catch (Exception $e) {
          $result['success'] = 0;
          $result['error'] = $this->__('Can not remove the item.');
        }
      }

      $this->getResponse()->setHeader('Content-type', 'application/json');
      $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

}
