<?php
class Helios_Gfeed_Block_Config_Pricecalculation extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {

	protected $_itemRenderer;
	protected $_itemRendererPriceTier;
	protected $_itemRendererPriceType;

	public function _prepareToRender() {

		$this->addColumn('price_type', array(
			'label' => Mage::helper('gfeed')->__('Price Type'),
			'renderer' => $this->_getRendererPriceType(),
		));

		$this->addColumn('product_type', array(
			'label' => Mage::helper('gfeed')->__('Product Type'),
			'renderer' => $this->_getRenderer(),
		));

		$this->addColumn('price_tier', array(
			'label' => Mage::helper('gfeed')->__('Price Tier'),
			'renderer' => $this->_getRendererPriceTier(),
		));

		$this->addColumn('margin', array(
			'label' => Mage::helper('gfeed')->__('Margin in percentage'),
			'style' => 'width:100px',
		));

		$this->addColumn('cost', array(
			'label' => Mage::helper('gfeed')->__('Shipping Cost excl vat'),
			'style' => 'width:80px',
		));
 
		$this->_addAfter = false;
		$this->_addButtonLabel = Mage::helper('gfeed')->__('Add');
	}

	protected function _getRenderer()  {
		if (!$this->_itemRenderer) {
			$this->_itemRenderer = $this->getLayout()->createBlock(
				'gfeed/adminhtml_form_field_producttype', '',
				array('is_render_to_js_template' => true)
			);
		}
		return $this->_itemRenderer;
	}

	protected function _getRendererPriceTier()  {
		if (!$this->_itemRendererPriceTier) {
			$this->_itemRendererPriceTier = $this->getLayout()->createBlock(
				'gfeed/adminhtml_form_field_pricetier', '',
				array('is_render_to_js_template' => true)
			);
		}
		return $this->_itemRendererPriceTier;
	}

	protected function _getRendererPriceType()  {
		if (!$this->_itemRendererPriceType) {
			$this->_itemRendererPriceType = $this->getLayout()->createBlock(
				'gfeed/adminhtml_form_field_pricetype', '',
				array('is_render_to_js_template' => true)
			);
		}
		return $this->_itemRendererPriceType;
	}

	protected function _prepareArrayRow(Varien_Object $row) {
		$row->setData(
			'option_extra_attr_' . $this->_getRenderer()
				->calcOptionHash($row->getData('product_type')),
			'selected="selected"'
		);

		$row->setData(
			'option_extra_attr_' . $this->_getRendererPriceTier()
				->calcOptionHash($row->getData('price_tier')),
			'selected="selected"'
		);

		$row->setData(
			'option_extra_attr_' . $this->_getRendererPriceType()
				->calcOptionHash($row->getData('price_type')),
			'selected="selected"'
		);
	}
}