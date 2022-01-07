<?php

class Helios_Garantiesservice_Block_Adminhtml_Garantiesservice_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('garantiesservice_form', array('legend' => Mage::helper('garantiesservice')->__('Item information')));

        $object = Mage::getModel('garantiesservice/garantiesservice')->load($this->getRequest()->getParam('id'));
        $imgPath = Mage::getBaseUrl('media') . "Garantiesservice/images/thumb/" . $object['imageicon'];
		
		

        $fieldset->addField('title', 'text', array(
            'label' => Mage::helper('garantiesservice')->__('Title'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'title',
        ));

        $fieldset->addField('imageicon', 'image', array(
            'label' => Mage::helper('garantiesservice')->__('Garantiesservice Image'),
            'required' => false,
            'name' => 'imageicon',
			'src' => $imgPath,
        ));

		
		
        
        if ($object->getId()) {
            $tempArray = array(
                'name' => 'filethumbnail',
                'style' => 'display:none;',
            );
     //       $fieldset->addField($imgPath, 'thumbnail', $tempArray);
        }

       
        $fieldset->addField('shortcontent', 'editor', array(
            'name' => 'shortcontent',
            'label' => Mage::helper('garantiesservice')->__('Short Content'),
            'title' => Mage::helper('garantiesservice')->__('Short Content'),
            'style' => 'width:700px; height:500px;',
            'wysiwyg' => true,
            'required' => true,
        ));
        
        $fieldset->addField('longcontent', 'editor', array(
            'name' => 'longcontent',
            'label' => Mage::helper('garantiesservice')->__('Long Content'),
            'title' => Mage::helper('garantiesservice')->__('Long Content'),
            'style' => 'width:700px; height:500px;',
            'wysiwyg' => true,
            'required' => true,
        ));
        

        $fieldset->addField('store_id', 'multiselect', array(
            'name' => 'stores[]',
            'label' => Mage::helper('garantiesservice')->__('Store View'),
            'title' => Mage::helper('garantiesservice')->__('Store View'),
            'required' => true,
            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
        ));


        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('garantiesservice')->__('Status'),
            'name' => 'status',
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('garantiesservice')->__('Enabled'),
                ),
                array(
                    'value' => 2,
                    'label' => Mage::helper('garantiesservice')->__('Disabled'),
                ),
            ),
        ));

      
        if (Mage::getSingleton('adminhtml/session')->getGarantiesserviceData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getGarantiesserviceData());
            Mage::getSingleton('adminhtml/session')->setGarantiesserviceData(null);
        } elseif (Mage::registry('garantiesservice_data')) {
            $form->setValues(Mage::registry('garantiesservice_data')->getData());
        }
        return parent::_prepareForm();
    }

}
