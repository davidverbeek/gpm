<?php

class Hs_Banner_Block_Adminhtml_Banner_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form
				->addFieldset("banner_form", array("legend" => Mage::helper("banner")
				->__("Item information")));

		$fieldset
			->addField('image', 'image', array(
				'label' => Mage::helper('banner')->__('Banner Image'),
				'name' => 'image',
				'class' => 'required-entry required-file',
				'required' => $this->getRequest()->getParam('id') ? false : true,
				'note' => '(*.jpg, *.png, *.gif)',
			))
			->setAfterElementHtml(
				"<script type=\"text/javascript\">$('image').addClassName('required-entry');</script>"
			);

		$fieldset->addField("link", "text", array(
			"label" => Mage::helper("banner")->__("Banner Link"),
			"name" => "link",
		));

		$fieldset->addField('status', 'select', array(
			'label' => Mage::helper('banner')->__('Status'),
			'values' => Hs_Banner_Block_Adminhtml_Banner_Grid::getValueArray2(),
			'name' => 'status',
		));

		if (Mage::getSingleton("adminhtml/session")->getBannerData()) {
			$form->setValues(Mage::getSingleton("adminhtml/session")->getBannerData());
			Mage::getSingleton("adminhtml/session")->setBannerData(null);
		} elseif (Mage::registry("banner_data")) {
			$form->setValues(Mage::registry("banner_data")->getData());
		}

		return parent::_prepareForm();
	}
}