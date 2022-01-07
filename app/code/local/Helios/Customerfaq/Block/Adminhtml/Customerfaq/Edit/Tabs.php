<?php
class Helios_Customerfaq_Block_Adminhtml_Customerfaq_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
				parent::__construct();
				$this->setId("customerfaq_tabs");
				$this->setDestElementId("edit_form");
				$this->setTitle(Mage::helper("customerfaq")->__("Item Information"));
		}
		protected function _beforeToHtml()
		{
				$this->addTab("form_section", array(
				"label" => Mage::helper("customerfaq")->__("Item Information"),
				"title" => Mage::helper("customerfaq")->__("Item Information"),
				"content" => $this->getLayout()->createBlock("customerfaq/adminhtml_customerfaq_edit_tab_form")->toHtml(),
				));
				return parent::_beforeToHtml();
		}

}
