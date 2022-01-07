<?php 

class Helios_Gfeed_Block_Adminhtml_Form_Field_Pricetype extends Mage_Core_Block_Html_Select {
	
	/**
	 * Prepare HTML output
	 *
	 * @return Mage_Core_Block_Html_Select
	 */
	public function _toHtml()	{
		$options = Mage::getSingleton('gfeed/pricetype')
			->toOptionArray();
		foreach ($options as $option) {
			$this->addOption($option['value'], $option['label']);
		}

		$this->setClass();
		
		return parent::_toHtml();
	}

	/**
	 * Set field name
	 *
	 * @param string $value
	 */
	public function setInputName($value) 	{
		return $this->setName($value);
	}

	/**
	 * Set element's CSS class
	 *
	 * @param string $class Class
	 * @return Mage_Core_Block_Html_Select
	 */
	public function setClass($class = 'pricetypeselect') {
		$this->setData('class', $class);
		return $this;
	}
}