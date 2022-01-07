<?php
	
class Helios_Customerfaq_Block_Adminhtml_Customerfaq_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "customerfaq_id";
				$this->_blockGroup = "customerfaq";
				$this->_controller = "adminhtml_customerfaq";
				$this->_updateButton("save", "label", Mage::helper("customerfaq")->__("Give Answer"));
				$this->_updateButton("delete", "label", Mage::helper("customerfaq")->__("Delete Item"));
                                $this->_updateButton("move", "label", Mage::helper("customerfaq")->__("Move in Faq"));

				



				$this->_formScripts[] = "

							function saveAndContinueEdit(){
								editForm.submit($('edit_form').action+'back/edit/');
							}
						";
		}

		public function getHeaderText()
		{
				if( Mage::registry("customerfaq_data") && Mage::registry("customerfaq_data")->getId() ){

				    return Mage::helper("customerfaq")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("customerfaq_data")->getId()));

				} 
				else{

				     return Mage::helper("customerfaq")->__("Add Item");

				}
		}
}