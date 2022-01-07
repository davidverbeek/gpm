<?php

class Gyzs_Blog_Block_Adminhtml_Blog_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'blog';
        $this->_controller = 'adminhtml_blog';
        
        $this->_updateButton('save', 'label', Mage::helper('faq')->__('Save Blog'));
        $this->_updateButton('delete', 'label', Mage::helper('faq')->__('Delete Blog'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('blog_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'blog_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'blog_content');
                }
            }				
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }
		
		protected function _prepareLayout() {
			parent::_prepareLayout();
			if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
					$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
			}
		}

    public function getHeaderText()
    {
        if( Mage::registry('blog_data') && Mage::registry('blog_data')->getId() ) {
            return Mage::helper('blog')->__("Edit Blog '%s'", $this->htmlEscape(Mage::registry('blog_data')->getPostTitle()));
        } else {
            return Mage::helper('blog')->__('New Blog');
        }
    }
}