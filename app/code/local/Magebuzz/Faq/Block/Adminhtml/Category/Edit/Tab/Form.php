<?php

class Magebuzz_Faq_Block_Adminhtml_Category_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('faq_form', array('legend'=>Mage::helper('faq')->__('General Information')));
     
      $fieldset->addField('category_name', 'text', array(
          'name'      => 'category_name',
          'label'     => Mage::helper('faq')->__('Category Name'),
          'title'     => Mage::helper('faq')->__('Category Name'),
          'required'  => true,
      ));
			
			$fieldset->addField('store_id', 'multiselect', array(
			    'name'      => 'stores[]',
			    'label'     => Mage::helper('cms')->__('Store View'),
			    'title'     => Mage::helper('cms')->__('Store View'),
			    'required'  => true,
			    'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
			));
			
			$category_id = $this->getRequest()->getParam('id');
			$fieldset->addField('category_id', 'hidden', array(
				'name'  => 'category_id',
				'value' =>  $category_id,
			));	
		
      $fieldset->addField('is_active', 'select', array(
          'label'     => Mage::helper('faq')->__('Active'),
          'name'      => 'is_active',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('faq')->__('Enabled'),
              ),

              array(
                  'value'     => 0,
                  'label'     => Mage::helper('faq')->__('Disabled'),
              ),
          ),
      ));
	  
     
      if ( Mage::getSingleton('adminhtml/session')->getFaqData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getFaqData());
          Mage::getSingleton('adminhtml/session')->setFaqData(null);
      } elseif ( Mage::registry('faq_data') ) {
          $form->setValues(Mage::registry('faq_data')->getData());
      }
      return parent::_prepareForm();
  }
}