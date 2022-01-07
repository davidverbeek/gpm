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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order API V2
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Api_V2 extends Mage_Sales_Model_Order_Api
{
    /**
     * Retrieve list of orders by filters
     *
     * @param array $filters
     * @return array
     */
    public function items($filters = null)
    {       
        //TODO: add full name logic
        $billingAliasName = 'billing_o_a';
        $shippingAliasName = 'shipping_o_a';

        $collection = Mage::getModel("sales/order")->getCollection()
            ->addAttributeToSelect('*')
            ->addAddressFields()
            ->addExpressionFieldToSelect(
                'billing_firstname', "{{billing_firstname}}", array('billing_firstname'=>"$billingAliasName.firstname")
            )
            ->addExpressionFieldToSelect(
                'billing_lastname', "{{billing_lastname}}", array('billing_lastname'=>"$billingAliasName.lastname")
            )
            ->addExpressionFieldToSelect(
                'shipping_firstname', "{{shipping_firstname}}", array('shipping_firstname'=>"$shippingAliasName.firstname")
            )
            ->addExpressionFieldToSelect(
                'shipping_lastname', "{{shipping_lastname}}", array('shipping_lastname'=>"$shippingAliasName.lastname")
            )
            ->addExpressionFieldToSelect(
                    'billing_name',
                    "CONCAT({{billing_firstname}}, ' ', {{billing_lastname}})",
                    array('billing_firstname'=>"$billingAliasName.firstname", 'billing_lastname'=>"$billingAliasName.lastname")
            )
            ->addExpressionFieldToSelect(
                    'shipping_name',
                    'CONCAT({{shipping_firstname}}, " ", {{shipping_lastname}})',
                    array('shipping_firstname'=>"$shippingAliasName.firstname", 'shipping_lastname'=>"$shippingAliasName.lastname")
            );

        $preparedFilters = array();
        if (isset($filters->filter)) {
            foreach ($filters->filter as $_filter) {
                $preparedFilters[][$_filter->key] = $_filter->value;
            }
        }
        if (isset($filters->complex_filter)) {
            foreach ($filters->complex_filter as $_filter) {
                $_value = $_filter->value;
                if(is_object($_value)) {
                    $preparedFilters[][$_filter->key] = array(
                        $_value->key => $_value->value
                    );
                } elseif(is_array($_value)) {
                    $preparedFilters[][$_filter->key] = array(
                        $_value['key'] => $_value['value']
                    );
                } else {
                    $preparedFilters[][$_filter->key] = $_value;
                }
            }
        }

        if (!empty($preparedFilters)) {
            try {
                foreach ($preparedFilters as $preparedFilter) {
                    foreach ($preparedFilter as $field => $value) {
                        if (isset($this->_attributesMap['order'][$field])) {
                            $field = $this->_attributesMap['order'][$field];
                        }

                        $collection->addFieldToFilter($field, $value);
                    }
                }
                //Mage::log("U R IN TRY",null,'orderpickup.log');
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
                //Mage::log("U R IN CATCH",null,'orderpickup.log');
            }
        }

        $result = array();

        foreach ($collection as $order) {

            $result[] = $this->_getAttributes($order, 'order');
        }
//		Mage::log(print_r($result,true),null,'orderpickup.log');
        return $result;
    }
	
	/**
     * Retrieve count of manual products from order items
     *
     * @param string $orderIncrementId
     * @return array
     */
	protected function _getManualProductCnt($order)
	{
		 $cnt = 0;
		 $manualOptionsValues = array(1,2);
		 foreach ($order->getAllItems() as $item) {
			if(in_array($item->getManualproduct(),$manualOptionsValues))
				$cnt++;	              
		 }
		 return $cnt;
	}
	
	/**
     * Retrieve count of Transferro products from order items
     *
     * @param string $orderIncrementId
     * @return array
     */
	protected function _getTransferroProductCnt($order)
	{
		$isTransferroProduct = 0;
		foreach ($order->getAllItems() as $item) {
			if($item->getManualproduct() == 2) {
				$isTransferroProduct = 1;
			}
			return $isTransferroProduct;
		}
	}

    /**
     * Retrieve full order information
     *
     * @param string $orderIncrementId
     * @return array
     */
    public function info($orderIncrementId)
    {
        $order = $this->_initOrder($orderIncrementId);

        if ($order->getGiftMessageId() > 0) {
            $order->setGiftMessage(
                Mage::getSingleton('giftmessage/message')->load($order->getGiftMessageId())->getMessage()
            );
        }
		
		$orderAllItemsCount = count($order->getAllItems());
		$checkOrderHasManualProductCnt = $this->_getManualProductCnt($order);
		$isTransferroProduct = $this->_getTransferroProductCnt($order);
		
		Mage::log('AllItemsCount::'.$orderAllItemsCount.' - ManualCount::'.$checkOrderHasManualProductCnt,null,'orderpinfo.log');
		
		if($orderAllItemsCount == $checkOrderHasManualProductCnt) {
			$result = array();
			
			Mage::log(print_r($result,true),null,'orderpinfo.log');
			return $result;
			
		} else {
			
			$result = $this->_getAttributes($order, 'order');

			$result['shipping_address'] = $this->_getAttributes($order->getShippingAddress(), 'order_address');
			
			/* ST::13-11-2019 The Below code added to send company name replace with firstname when customer fills company name */
			if(isset($result['shipping_address']['company']) && $result['shipping_address']['company'] != '') {
				$result['shipping_address']['firstname'] = $result['shipping_address']['company'];
				unset($result['shipping_address']['lastname']);
			}
			
			$result['billing_address']  = $this->_getAttributes($order->getBillingAddress(), 'order_address');
			
			/* ST::13-11-2019 The Below code added to send company name replace with firstname when customer fills company name */
			if(isset($result['billing_address']['company']) && $result['billing_address']['company'] != '') {
				$result['billing_address']['firstname'] = $result['billing_address']['company'];
				unset($result['billing_address']['lastname']);
			}
			
			$result['items'] = array();

			$rowTotalIncl = 0;
			$rowTotalExcl = 0;
			$rowTotalTax = 0;
			$countManualProduct = 0;

			$manualOptionsValues = array(1,2);
			foreach ($order->getAllItems() as $item) {

				if(in_array($item->getManualproduct(),$manualOptionsValues)){
				   
					$rowTotalIncl += $item->getRowTotalInclTax();
					$rowTotalExcl += $item->getRowTotal();
					$rowTotalTax += $item->getTaxAmount();
					$countManualProduct += $item->getQtyOrdered();
					continue;
				}

				if ($item->getGiftMessageId() > 0) {
					$item->setGiftMessage(
						Mage::getSingleton('giftmessage/message')->load($item->getGiftMessageId())->getMessage()
					);
				}

				$result['items'][] = $this->_getAttributes($item, 'order_item');
			}

			$result['payment'] = $this->_getAttributes($order->getPayment(), 'order_payment');
			// remove manual product revenue from order
			if($rowTotalIncl > 0){

				$result = $this->_removeManualProductPriceFromTotal($result, $rowTotalIncl, $rowTotalExcl, $rowTotalTax, $countManualProduct);
			}

			$result['status_history'] = array();

			foreach ($order->getAllStatusHistory() as $history) {
				
				// effect connect null comment remove
				if($order->getExtOrder()){
					if($history->getComment()){
						$result['status_history'][] = $this->_getAttributes($history, 'order_status_history');
					}       
				} else {
					$result['status_history'][] = $this->_getAttributes($history, 'order_status_history');
				}
				// $result['status_history'][] = $this->_getAttributes($history, 'order_status_history');
			}

			//same day delivery note adding logic
			if (!empty($result['extra_verzendkosten'])) {

				$sddMsg = 'VANDAAG OPGEHAALD OM 12.00 (REDJEPAKKETJE)';

				$sameDayDeliveryHistory = $result['status_history'][0];
				$sameDayDeliveryHistory['comment'] = $sddMsg;
				$sameDayDeliveryHistory['is_customer_notified'] = 1;
				$result['status_history'][] = $sameDayDeliveryHistory;

			}
			
			if($isTransferroProduct){

				$transferroMsg = 'Order verzenden op';

				$transferroOrderHistory = $result['status_history'][0];
				$transferroOrderHistory['comment'] = $transferroMsg;
				$transferroOrderHistory['is_customer_notified'] = 0;
				$result['status_history'][] = $transferroOrderHistory;
			}

			foreach ($result['items'] as $key=>$item)
			{		
				  $prod = Mage::getModel('catalog/product')->load($item['product_id']);
				  if(!$prod->getData('afwijkenidealeverpakking')){                
					  $idealeverpakking=str_replace(",",".",$prod->getData('idealeverpakking'));
					  $prijsfactor = ($idealeverpakking > 1) ? $idealeverpakking : 1;
					  $result['items'][$key]['qty_ordered']=($item['qty_ordered'] * $idealeverpakking);				  				  
					  $result['items'][$key]['price']=($prod->getFinalPrice() * $prijsfactor);
					  $result['items'][$key]['base_original_price']=$prod->getFinalPrice();
					  $result['items'][$key]['base_price']=$prod->getFinalPrice();
					  $result['items'][$key]['original_price']=$prod->getFinalPrice();				 
				  }
			}
			//Mage::log("HELLO",null,'orderpinfo.log');
			//Mage::log(print_r($result,true),null,'orderpinfo.log');
			return $result;
			
		}

        
    }

    /**
     * Calculation for final payment amount
     *
     * @param array $result
     * @param string $rowTotalIncl
     * @param string $rowTotalExcl
     * @param string $rowTotalTax
     * @param string $countManualProduct
     * @return array
     */
    protected function _removeManualProductPriceFromTotal($result, $rowTotalIncl, $rowTotalExcl, $rowTotalTax, $countManualProduct){
        // Order Object Value update
        $result['total_qty_ordered'] -= $countManualProduct;
        $result['tax_amount'] -= $rowTotalTax;
        $result['base_tax_amount'] -= $rowTotalTax;
        $result['subtotal'] -= $rowTotalExcl;
        $result['base_subtotal'] -= $rowTotalExcl;
        $result['grand_total'] -= $rowTotalIncl;
        $result['base_grand_total'] -= $rowTotalIncl;
        $result['total_paid'] -= $rowTotalIncl;
        $result['base_total_paid'] -= $rowTotalIncl;
        $result['total_invoiced'] -= $rowTotalIncl;
        $result['base_total_invoiced'] -= $rowTotalIncl;

        // Payment Object Value update
        $result['payment']['base_amount_ordered'] -= $rowTotalIncl;
        $result['payment']['amount_ordered'] -= $rowTotalIncl;

        return $result;
    }
}
