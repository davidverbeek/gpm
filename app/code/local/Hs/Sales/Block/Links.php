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
 * @package     Mage_Wishlist
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 

/**
 * Links block
 *
 * @category    Mage
 * @package     Mage_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */    
class Hs_Sales_Block_Links extends Mage_Core_Block_Template
{
     public function addOrderLink()
    {
        $parentBlock = $this->getParentBlock();
        
            $count = $this->getItemCount();
            if ($count == 1) {
                $text = $this->__('My order history (%s)', $count);
            } elseif ($count > 0) {
                $text = $this->__('My order history (%s)', $count);
            } else {
                $text = $this->__('My order history');
            }

        
            $parentBlock->addLink($text, 'sales/order/history', $text, true, array(), 50, null, 'class="top-link-cart"');
        
        return $this;
    }
    
    public function getItemCount()
    {
        if(!Mage::getSingleton('customer/session')->isLoggedIn()){
            return 0;
        }else{
            $customer=Mage::getSingleton('customer/session')->getCustomer();
            $customerId=$customer->getId();
            //$table = Mage::getSingleton('core/resource')->getTableName('sales/order');
            
            $collection=Mage::getModel('sales/order')->getCollection()
                        ->addFieldToFilter('customer_id',$customerId);
                      //  ->addFieldToFilter('status',array('neq'=>'pending'));
            //$collection->getSelect()->join(array('am'=>$table), 'am.list_id=main_table.list_id');
           
            return $collection->getSize();
        }
    }
}
