<?php

class Magebuzz_Faq_Block_Adminhtml_Suggest_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('faq_suggest_form', array('legend'=>Mage::helper('faq')->__('Suggestion Information')));
     
			$model = Mage::registry('faq_suggest_data');
			$fieldset->addField('message', 'editor', array(
				'name' => 'message',
				'label' => Mage::helper('faq')->__('Message'),
				'title' => Mage::helper('faq')->__('Message'),
				'style' => 'height:12em;width:600px;',
				'wysiwyg' => true,
				'required' => true,
				'config' => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
			));
      if ( Mage::getSingleton('adminhtml/session')->getFaqSuggestData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getFaqSuggestData());
          Mage::getSingleton('adminhtml/session')->getFaqSuggestData(null);
      } elseif ( Mage::registry('faq_suggest_data') ) {
          $form->setValues(Mage::registry('faq_suggest_data')->getData());
      }
      return parent::_prepareForm();
  }
}