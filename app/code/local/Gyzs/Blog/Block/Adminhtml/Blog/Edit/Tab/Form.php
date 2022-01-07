<?php

class Gyzs_Blog_Block_Adminhtml_Blog_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('blog_form', array('legend'=>Mage::helper('faq')->__('General Information')));
     
      $model = Mage::registry('blog_data');
      
      $fieldset->addField('post_title', 'text', array(
          'name'      => 'post_title',
          'label'     => Mage::helper('blog')->__('Post Title'),
          'title'     => Mage::helper('blog')->__('Post Title'),
          'required'  => true,
          'disabled'  =>'disabled'
      ));
		
      
			
	if (!Mage::app()->isSingleStoreMode()) {
        $fieldset->addField('store_id', 'multiselect', array(
            'name'      => 'stores[]',
            'label'     => Mage::helper('cms')->__('Store View'),
            'title'     => Mage::helper('cms')->__('Store View'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
			'value'     => Mage::app()->getStore(true)->getId()
        ));
			}
			else {
					$fieldset->addField('store_id', 'hidden', array(
							'name'      => 'stores[]',
							'value'     => Mage::app()->getStore(true)->getId()
					));
					$model->setStoreId(Mage::app()->getStore(true)->getId());
			}
			
			
     
      if ( Mage::getSingleton('adminhtml/session')->getBlogData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getBlogData());
          Mage::getSingleton('adminhtml/session')->setBlogData(null);
      } elseif ( Mage::registry('blog_data') ) {
          $form->setValues(Mage::registry('blog_data')->getData());
      }
      return parent::_prepareForm();
  }
}