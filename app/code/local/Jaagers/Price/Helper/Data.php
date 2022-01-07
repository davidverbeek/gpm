<?php
/**
 * Jaagers BV
 *
 * @category    Jaagers
 * @package     Jaagers_Price
 * @copyright   Copyright (c) 2012 Jaagers BV. (http://www.jaagers.com)
 * @license     All rights reserved. May not be reproduced without prior written authorisation.
 */

class Jaagers_Price_Helper_Data extends Mage_Core_Helper_Abstract {

	public $_config;
	public $_userinfo;

	public function __construct() {

		$this->_config = Mage::getConfig()->getNode('default/combipac');

		$userinfo = Mage::helper('customer')->getCustomer()->getData();

		if(isset($userinfo['mavis_debiteurnummer'])) {
			$this->_userinfo = $userinfo;
			$this->_userinfo['bedrijfnr'] = 1;
			$this->_userinfo['filiaalnr'] = 1;
		} else {
			$this->_userinfo = Mage::getConfig()->getNode('default/combipac/default_user');
		}

		/* check if "api not available" timeout is set, calculate diff and set availability to true to enable recheck. */

		$session = Mage::getSingleton('core/session');

		if(isset($_GET['forceapicheck']) && $_GET['forceapicheck'] == 'true') {
			$session->setApiAvailable(true);
		}

		if(!$session->getApiAvailable() && strlen($session->getApiAvailableTimeOut() > 0)) {
			$difference = time() - $session->getApiAvailableTimeOut();
			if($difference > 600) {
				$session->setApiAvailable(true);
			}
		} else {
			$session->setApiAvailable(true);
		}

	}

	/**
	 * Check if CombiPac Price result exists in cache
	 *
	 * @param Mage_Eav_Model_Entity_Collection_Abstract|array $collection
	 * @return array $result
	 */

	public function checkCache($collection, $multiple = false) {

		$ttl = (int)$this->_config->cache_ttl;

		$model = Mage::getModel('price/price');


		if($multiple) {

			/* if collection contains multiple products */

			$arr = array();

			foreach($collection as $c) {
				$arr[] = $c->getData('sku');
			}

			$new_collection = $model->getCollection()
									->addFieldToFilter('Artinr', array('in' => $arr))
									->addFieldToFilter('debiteurnr', (string)$this->_userinfo['mavis_debiteurnummer']);

			$new_collection->getSelect()->where('date > DATE_SUB(NOW(), INTERVAL ' . $ttl . ' HOUR)');

		} else {
			/* if collection contains single product */

			$new_collection = $model->getCollection()
									->addFieldToFilter('Artinr', $collection->sku)
									->addFieldToFilter('debiteurnr', (string)$this->_userinfo['mavis_debiteurnummer']);

			$new_collection->getSelect()->where('date > DATE_SUB(NOW(), INTERVAL ' . $ttl . ' HOUR)');
		}
        //echo $new_collection->getSelect()->__toString();
        return $new_collection;

	}
	
	
    /**
     * Get price of single product from CombiPac
     *
     * @param Mage_Eav_Model_Entity_Collection_Abstract|array  $collection
     * @param int $qty
     * @param int $details
     * @return array $result
     */

    public function getProductDebterPrice($product) {
		
		$_prijsfactor = $product->getData('prijsfactor');
		
		$model = Mage::getModel('price/price');
		
		$new_collection = $model->getCollection()
								->addFieldToFilter('Artinr', array('in' => $product->getSku()))
								->addFieldToFilter('debiteurnr', (string)$this->_userinfo['mavis_debiteurnummer']);

        $new_collection->getSelect()->group('Artinr');
		//echo $new_collection->getSelect()->__toString();		

		foreach($new_collection as $c) {
			$debter_price = $c->getData('Nettpr');
		}

		if(isset($_prijsfactor) && strlen($_prijsfactor) > 0) {
			/* Delen productprijs door prijsfactor */
			$prijs = $debter_price / $_prijsfactor;
		} else {
			$prijs = $debter_price;
		}
		return $prijs;
		
	}

	/**
	 * Get price for multiple products from mage_combipac_cache Table
	 *
	 * @param Mage_Eav_Model_Entity_Collection_Abstract|array $collection
	 * @param int $qty
	 * @return array $result
	 */

	public function getDebiteurPriceMore($collection) {
	
		$arr = array();
		if((int)$this->_config->cache) {

            foreach($collection as $c) {
                $arr[] = $c->getData('sku');
            }

            $model = Mage::getModel('price/price');

            $new_collection = $model->getCollection()
                ->addFieldToFilter('Artinr', array('in' => $arr))
                ->addFieldToFilter('debiteurnr', (string)$this->_userinfo['mavis_debiteurnummer']);

            $new_collection->getSelect()->group('Artinr');
            //echo $new_collection->getSelect()->__toString();

		}
		foreach($new_collection->getData() as $r) {
			$returnPrice[$r['Artinr']] = $r;
		}

		return (object)$returnPrice;
	
	}

    /**
     * Get price of single product from CombiPac
     *
     * @param Mage_Eav_Model_Entity_Collection_Abstract|array  $collection
     * @param int $qty
     * @param int $details
     * @return array $result
     */

    public function getPrice($collection, $_qty = null, $details = null) {

        $_product = Mage::getModel('catalog/product')->load($collection->getData('entity_id'));
        $_prijsfactor = $_product->getData('prijsfactor');

        /* FETCH QTY FOR PRODUCT IN QUOTE */

        /*$quote = Mage::helper('checkout/cart')->getCart()->getQuote();
        $salesQuoteItem = Mage::getModel('sales/quote_item')->getCollection()
            ->setQuote($quote)
            ->addFieldToFilter('product_id', $_product->getData('entity_id'))
            ->getFirstItem();

        if($salesQuoteItem) {
            $_qty = $salesQuoteItem->getQty();
        }*/

        if($collection->getData('combipac_result')) {
            return $collection->getData('combipac_result')->Nettpr;
        }

        if((int)$this->_config->cache) {
            $checkCache = $this->checkCache($collection, false);
            $fromcache = false;
        }

        if($this->_config->cache && isset($checkCache) && count($checkCache->getData()) > 0) {

            foreach($checkCache->getData() as $c) {
                $result = array((object)$c);
                $fromcache = true;
            }

            if((int)$this->_config->debug) {
                $debug = array('nr_of_cachehits' => count($checkCache->getData()));

                if (count($checkCache->getData()) > 0)
                {
                    $this->debug($debug);
                }

            }

        } else {
            $params = $this->buildParams($collection, false, $_qty);

            $result = $this->soapConnect('GetPrijs', 'GetPrijsResult', $params);

            if((int)$this->_config->cache && isset($result)) {
                $saveToCache = $this->saveToCache($result, $checkCache);
            }
        }

        if(isset($fromcache) && $fromcache) {
            $result = $result[0];
        }

        if(empty($result)) {

            $model = Mage::getModel('catalog/product');
            $_product = $model->loadByAttribute('sku', $collection->sku);

            return $_product->getData('price');

        }

        if($details) {
            return $result;
        } else {

            if(isset($_prijsfactor) && strlen($_prijsfactor) > 0) {
                /* Delen productprijs door prijsfactor */
                $prijs = $result->Nettpr / $_prijsfactor;
            } else {
                $prijs = $result->Nettpr;
            }
            return $prijs;
        }

    }

    /**
     * Get price for multiple products from CombiPac
     *
     * @param Mage_Eav_Model_Entity_Collection_Abstract|array $collection
     * @param int $qty
     * @return array $result
     */

    public function getPriceMore($collection, $qty = null, $key = null) {

        if((int)$this->_config->cache) {
            $checkCache = $this->checkCache($collection, true);
        }

        if($this->_config->cache && isset($checkCache)) {
            foreach($checkCache->getData() as $c) {
                foreach($collection as $key => $value) {
                    if($c['Artinr'] == $value->getData('sku')) {
                        $collection->removeItemByKey($key);
                    }
                }
            }

            if((int)$this->_config->debug) {

                $debug = array('nr_of_cachehits' => count($checkCache->getData()));

                if (count($checkCache->getData()) > 0)
                {
                    $debug['result'] = $collection->getData();
                }

                $this->debug($debug);
            }
        }

        $params = $this->buildParams($collection, true, $qty);

        if(isset($params['artikelen']) && count($params['artikelen']) > 0) {

            $result = $this->soapConnect('GetPrijsMore', 'GetPrijsMoreResult', $params);

            if(isset($result) && $result) {

                if(property_exists($result, 'Prijs') && count($result->Prijs) == 1) {

                    /*
                     * Single result does not return multi-dimensional object.
                     * Here we cast single result set to multi object.
                     */

                    $singleResult = new stdClass();
                    $singleResult->Prijs = array($result->Prijs);
                    $singeResult = (object)$singleResult;

                    if((int)$this->_config->cache) {
                        $saveToCache = $this->saveToCache($singleResult, $checkCache);
                    }

                    return $singleResult->Prijs;

                } else {

                    if($this->_config->cache && isset($checkCache)) {
                        foreach($checkCache->getData() as $cache) {
                            $result->Prijs[] = (object)$cache;
                        }

                    }

                    if((int)$this->_config->cache) {
                        $saveToCache = $this->saveToCache($result, $checkCache);
                    }

                    if($key) {

                        foreach($result->Prijs as $r) {
                            $return[$r->Artinr] = $r;
                        }

                        return $return;

                    } else {

                        return $result->Prijs;

                    }

                }
            }
        } else {

            foreach($checkCache->getData() as $r) {
                $return[$r['Artinr']] = $r;
            }

            return (object)$return;

        }

    }

	/**
	 * Get stock message for checkout / cart
	 *
	 * @param object $product
	 * @return string
	 */

	public function getCheckoutVoorraad($_item) {

		$entity_id = $_item->getProductId();

		$product = Mage::getModel('catalog/product')->load($entity_id);

		$params = $this->buildParams($product, false, null, false, false);
		$params['bedrijfnr'] = '1';
		$params['filiaalnr'] = '1';
		$client = new Zend_Soap_Client((string)$this->_config->apipath);
		$result = $client->GetVoorraad($params);

		$qty = $_item->getData('qty');
		if(!isset($qty))
				$qty=(int) $_item->getQtyOrdered();
		if($product->getData(afwijkenidealeverpakking)!=0){
			$availableqty=$result->GetVoorraadResult;
		}
		else{
			$availableqty=(int)($result->GetVoorraadResult / $product->getData(idealeverpakking));
		}
		//$availableqty =(int)($result->GetVoorraadResult / $product->getData(idealeverpakking));
		//$availableqty =$result->GetVoorraadResult ;
		$backorder = $availableqty - $qty;
		if($backorder < 0) {
			$backorder = abs($backorder);
			/*
			* Author : Helios
			* Date :23-july-2014
			* Desc : fetch out of product Status
			*/

			$prod=Mage::getModel('catalog/product')->load($_item->getProductId());
			if($prod->getLeverancier()==3797)
				return false;
			if($prod->getArtikelstatus()==3){
				return '<span class="item-msg notice">Dit is een uitlopend artikel en zal uit ons assortiment verdwijnen. ' . $backorder . ' items worden niet <span id="besteld-term">besteld</span>.</span>';
			}
			else{
				if($backorder==1){
					$items="item";
				}else{
					$items="items";
				}
				return '<span class="item-msg notice">'. $backorder . ' '. $items .' worden in <span id="backorder-term">backorder</span> geplaatst. Uw order wordt niet direct verzonden.</span>';
			}
		}

		return false;

	}

	/**
	 * Check current stock for selected product and qty
	 *
	 * @param object $product
	 * @param int $qty
	 * @return object $result
	 */

    /**
     * SJD++ 08082018
     * Gets current stock and delivery time for product from mavis
     *
     * @param stdClass $product
     * @return stdClass $result
     */
    public function getLevertijdMore($product)
    {
        $params = $this->buildParams($product, true, null, false, true);

        $params['bedrijfnr'] = '1';
        $params['filiaalnr'] = '1';
        unset($params['debiteurnr']);

        return $this->soapConnect('GetLevertijdMore', 'GetLevertijdMoreResult', $params);
    }

	public function getVoorraad($product) {

		$params = $this->buildParams($product, false, null, false, false);

		$result = $this->soapConnect('GetVoorraad', 'GetVoorraadResult', $params);

		return $result;

	}

	/**
	 * Check current stock for product collection
	 *
	 * @param stdClass $collection
	 * @return stdClass $result
	 */
    public function getVoorraadMore($producten)
    {
        $params = $this->buildParams($producten, true, null, false, true);

		$params['bedrijfnr'] = '1';
		$params['filiaalnr'] = '1';
		unset($params['debiteurnr']);

        return $this->soapConnect('GetVoorraadMore', 'GetVoorraadMoreResult', $params);
	}

    public function saveToCache($soapresult, $cacheresult) {

        if(isset($soapresult->Prijs)) {
            $soapresult = $soapresult->Prijs;
        }

        if(count($soapresult) == 1) {
            $soapresult = array($soapresult);
        }

        $soapcount = count($soapresult);
        $cachecount = count($cacheresult);

        $cacheresult = (array)$cacheresult->getData();

        if($soapcount <> $cachecount) {

            // We have results in soapresult with no cachehits

            foreach($soapresult as $s) {

                // Check each soapresult against cacheresult

                $s = (array)$s;

                if(!$this->in_arrayr($s, $cacheresult)) {

                    $data = array();
                    $data['debiteurnr'] = (string)$this->_userinfo['mavis_debiteurnummer'];

                    foreach($s as $key => $value) {
                        $data[$key] = $value;
                    }

                    $model = Mage::getModel('price/price');
                    $model->setData($data);

                    try {
                        $model->save()->getId();
                    } catch (Exception $e){
                        Mage::Log($e->getMessage());
                    }

                }

            }

        }

        return true;

    }

	/**
	 * Build userinfo based on currently logged in user
	 *
	 * @return array $params;
	 */

	public function buildUserInfo() {
	    $userinfo = $this->_userinfo;
		if( is_array($userinfo)) {
		    $userinfo['combipac_debiteurnr'] = (isset($userinfo['combipac_debiteurnr'])) ? $userinfo['combipac_debiteurnr'] : '';
		    $userinfo['combipac_debiteurnr'] = ($userinfo['combipac_debiteurnr'] == '' && isset($userinfo['mavis_debiteurnummer'])) ? $userinfo['mavis_debiteurnummer'] : $userinfo['combipac_debiteurnr'];
		    $userinfo = (object)$userinfo;
		}

		$params = array(
				'bedrijfnr' => (string)($userinfo->bedrijfnr),
				'filiaalnr'	=> (string)($userinfo->filiaalnr),
				'debiteurnr' => (string)($userinfo->combipac_debiteurnr)
		);

		return $params;
	}

	/**
	 * Build params array
	 *
	 * @param Mage_Eav_Model_Entity_Collection_Abstract|array $collection
	 * @param boolean $multiple
	 * @param int $qty
	 * @param boolean $useqty
	 * @return array $params
	 */

	public function buildParams($collection, $multiple = false, $qty = null, $useqty = true, $builduserinfo = true) {

		if($qty == null) {
			$qty = (float)1.00;
		}

		if($builduserinfo) {
			$params = $this->buildUserInfo();
			//Zend_Debug::Dump($params);exit;
		}
		if($multiple) {
			foreach($collection as $p) {
				if(isset($p->sku)) {
					$params['artikelen'][] = $p->sku;
					if($useqty) {
						$params['aantallen'][] = (float)$qty;
					}
				} else if(isset($p['sku'])) {
					$params['artikelen'][] = $p['sku'];
					if($useqty) {
						$params['aantallen'][] = (float)$qty;
					}
				} else {
					if(!$p->isGrouped()) {
						$params['artikelen'][] = (string)$p->getSKU();
						if($useqty) {
							$params['aantallen'][] = (float)$qty;
						}
					}
				}
			}
		} else {
			$params['artikelnr'] = $collection->sku;
			if($useqty) {
				$params['aantal'] = $qty;
			}
		}
		if((int)$this->_config->debug) {
			$this->debug( array('soapparams' => $params) );
		}
		return $params;
	}

	/**
	 * Generic soap connect call
	 *
	 * @param string $call
	 * @param string $response
	 * @param array $params
	 * @result array $result
	 */
    public function soapConnect($call, $response, $params)
    {
        /*** Check if WSDL is up, else PHP will present FATAL ERROR which cannot by try-catched ***/
        $session = Mage::getSingleton('core/session');

        if ($session->getApiAvailable()) {

            $handle = curl_init((string)$this->_config->apipath);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($handle, CURLOPT_TIMEOUT, 2);
            $curlresponse = curl_exec($handle);
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

            if ($httpCode == 0) {

                /*** We don't have a WSDL. Service is down. exit the function, display notification to user ***/
                if ((int)$this->_config->debug) {

                    $this->debug(array('soaperror' => 'Cannot read from wsdl'));
                    if ($this->_config->log_calls) {
						
						/* commented below line due to large soapcalls log in var/log folder */
                        //$this->soapLog($call, $params, null, 'soapcalls.log');
                    }
                }

                $session->setApiAvailable(false);
                $session->setApiAvailableTimeOut(time());
                $messagecnt = $session->getMessages()->count('notice');

                if ($messagecnt == 0) {
                    /*$session->addNotice('Geachte bezoeker, op dit moment kunnen wij helaas geen klant specifieke prijzen tonen. De prijs die nu getoond wordt, is de standaard prijs voor consumenten. Deze kan afwijken van de voor u geldende prijs.');*/
					
					if(isset($params['artikelen']) && count($params['artikelen']) > 0) {

						$staticVoorraad = array();
						foreach ($params['artikelen'] as $val) {
							$staticVoorraad[$val] = 1000;
						}

						$singleResult = new stdClass();
						$singleResult = array('decimal' =>$staticVoorraad);
						$singleResult = (object)$singleResult;

						return $singleResult;

					}
                }

                //return false;
            }

            curl_close($handle);

        } else {

            /*** We don't have a WSDL (getApiAvailable() is false). Service is down. exit the function, display notification to user ***/
            $messagecnt = $session->getMessages()->count('notice');
            if ($messagecnt == 0) {
                /*$session->addNotice('Geachte bezoeker, op dit moment kunnen wij helaas geen klant specifieke prijzen tonen. De prijs die nu getoond wordt, is de standaard prijs voor consumenten. Deze kan afwijken van de voor u geldende prijs.');*/
				
				if(isset($params['artikelen']) && count($params['artikelen']) > 0) {

					$staticVoorraad = array();
					foreach ($params['artikelen'] as $val) {
						$staticVoorraad[$val] = 1000;
					}

					$singleResult = new stdClass();
					$singleResult = array('decimal' =>$staticVoorraad);
					$singleResult = (object)$singleResult;
					
					return $singleResult;

				}
            }

            //return false;
        }
        /*** End WSDL check ***/

        //Zend_Debug::Dump($this->_config);exit;
        $client = new Zend_Soap_Client((string)$this->_config->apipath);

        try {
            $startTime = microtime(true);
            $params['debiteurnr'] = isset($params['debiteurnr']) ? $params['debiteurnr'] : '400005';

            $result = $client->{$call}($params);

            if ((int)$this->_config->log_calls) {
				/* commented below line due to large soapcalls log in var/log folder */
                //$this->soapLog($call, $params, null, 'soapcalls.log', $result);
            }

            if ((int)$this->_config->debug) {

                $this->debug(array('time' => microtime(true) - $startTime, 'soapresult' => $result->{$response}, 'backtrace' => $this->get_caller()));
            }

            return $result->{$response};

        } catch (Exception $e) {
            Mage::Log($e->getMessage());
            Mage::logException($e);
            if ((int)$this->_config->debug) {
                $this->debug(array('soaperror' => $e));
                //	var_dump($e);exit;
            }
        }

        if ($this->_config->log_calls) {
			/* commented below line due to large soapcalls log in var/log folder */
            //$this->soapLog($call, $params, null, 'soapcalls.log');
        }
    }

	/*
	 * Function Backtrace
	 */

	function getCallingMethodName(){
		$e = new Exception();
		$trace = $e->getTrace();
		//position 0 would be the line that called this function so we ignore it
		$last_call = $trace[1];
		//print_r($last_call);
	}

	/*
	 * Function Stack
	 */

	function get_caller($function = NULL, $use_stack = NULL) {

		/*if ( is_array($use_stack) ) {
			// If a function stack has been provided, used that.
			$stack = $use_stack;
		} else {
			// Otherwise create a fresh one.
			$stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			return $stack;
		}

		if ($function == NULL) {
			// We need $function to be a function name to retrieve its caller. If it is omitted, then
			// we need to first find what function called get_caller(), and substitute that as the
			// default $function. Remember that invoking get_caller() recursively will add another
			// instance of it to the function stack, so tell get_caller() to use the current stack.
			$function = $this->get_caller(__FUNCTION__, $stack);
		}

		if ( is_string($function) && $function != "" ) {
			// If we are given a function name as a string, go through the function stack and find
			// it's caller.
			for ($i = 0; $i < count($stack); $i++) {
				$curr_function = $stack[$i];
				// Make sure that a caller exists, a function being called within the main script
				// won't have a caller.
				if ( $curr_function["function"] == $function && ($i + 1) < count($stack) ) {
					return $stack[$i + 1]["function"];
				}
			}
		}*/

		// At this stage, no caller has been found, bummer.
		return "";
	}

	/**
	 * Soap call logging
	 *
	 * @param string $call
	 * @param array $params
	 * @param sring $level
	 * @param string $filename
	 * @param array $result
	 */

	public function soapLog($call, $params, $level = null, $filename = null, $result = null, $backtrace = false) {

		$date = date("Y-m-d H:i:s");
		$log = array('Datum' => $date, 'Call' => $call, 'Params' => $params, 'Result' => $result);

		if($backtrace) {
			$log['backtrace'] = debug_backtrace();
		}

		Mage::Log($log, $level, $filename);

	}

	/**
	 * Set debug messages
	 *
	 * @param mixed $input
	 */

	public function debug($input) {

		if(Mage::getSingleton('core/session')->getApiAvailable()) {
			Mage::helper('debug')->setdebug( $input );
		} else {
			Mage::helper('debug')->setdebug( 'API is not available. Further logging disabled', true );
		}
	}

	function in_arrayr( $needle, $haystack ) {
		foreach( $haystack as $v ){
			if( $needle == $v )
				return true;
			elseif( is_array( $v ) )
			if( $this->in_arrayr( $needle, $v ) )
				return true;
		}
		return false;
	}

}
