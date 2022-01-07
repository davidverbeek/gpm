<?php

class Hs_Banner_Block_Adminhtml_Banner_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();
		$this->setId("banner_tabs");
		$this->setDestElementId("edit_form");
		$this->setTitle(Mage::helper("banner")->__("Item Information"));
	}

	protected function _beforeToHtml()
	{
		$this->addTab("form_section", array(
			"label" => Mage::helper("banner")->__("Item Information"),
			"title" => Mage::helper("banner")->__("Item Information"),
			"content" => $this->getLayout()->createBlock("banner/adminhtml_banner_edit_tab_form")->toHtml(),
		));
		$this->addTab('categories', array(
			'label' => Mage::helper('catalog')->__('Categories'),
			'url' => $this->getUrl('*/*/categories', array('_current' => true)),
			'class' => 'ajax',
		));
		return parent::_beforeToHtml();
	}
}
