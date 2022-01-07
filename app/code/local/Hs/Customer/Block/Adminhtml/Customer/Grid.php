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
 * @category    Customer Grid
 * @package     Hs_Customer
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml customer grid block
 *
 * @category   Customer Grid
 * @package    Hs_Customer
 * @author     Parth <parth.pabari.hs@gmail.com>
 */

class Hs_Customer_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Customer_Grid{
	
	/**
	 * override the setCollection to add an other attribute to the grid
	 * @return $this
	 */

	public function setCollection($collection) {
		$collection->addAttributeToSelect('mavis_debiteurnummer')
			->joinAttribute('billing_company', 'customer_address/company', 'default_billing', null, 'left');
		parent::setCollection($collection);
	}

	/**
	 * override the _prepareColumns method to add a new column
	 */
	protected function _prepareColumns(){
		$this->addColumn('billing_company', array(
			'header'    => Mage::helper('customer')->__('Company'),
			'width'     => '90',
			'index'     => 'billing_company',
		));

		// show new column named 'billing_company' after 'billing_region'
		$this->addColumnsOrder('billing_company', 'billing_postcode');

		$this->addColumn('mavis_debiteurnummer', array(
			'header'    => Mage::helper('customer')->__('Debiteurnummer'),
			'width'     => '100',
			'index'     => 'mavis_debiteurnummer',
		));

		// show new column named 'mavis_debiteurnummer' after 'billing_region'
		$this->addColumnsOrder('mavis_debiteurnummer', 'billing_region');

		return parent::_prepareColumns();
	}
}