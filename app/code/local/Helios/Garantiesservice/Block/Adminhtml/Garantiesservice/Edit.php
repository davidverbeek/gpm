<?php

class Helios_Garantiesservice_Block_Adminhtml_Garantiesservice_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'garantiesservice';
        $this->_controller = 'adminhtml_garantiesservice';
        
        $this->_updateButton('save', 'label', Mage::helper('garantiesservice')->__('Save Garantiesservice'));
        $this->_updateButton('delete', 'label', Mage::helper('garantiesservice')->__('Delete Garantiesservice'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('garantiesservice_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'garantiesservice_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'garantiesservice_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('garantiesservice_data') && Mage::registry('garantiesservice_data')->getId() ) {
            return Mage::helper('garantiesservice')->__("Edit Garantiesservice '%s'", $this->htmlEscape(Mage::registry('garantiesservice_data')->getTitle()));
        } else {
            return Mage::helper('garantiesservice')->__('Add Garantiesservice');
        }
    }
}