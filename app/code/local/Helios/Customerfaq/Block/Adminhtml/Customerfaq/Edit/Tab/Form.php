<?php
class Helios_Customerfaq_Block_Adminhtml_Customerfaq_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("customerfaq_form", array("legend"=>Mage::helper("customerfaq")->__("Item information")));

				
						$fieldset->addField("customer_id", "text", array(
						"label" => Mage::helper("customerfaq")->__("Customer Id"),
						"name" => "customer_id",
						));
						
						$fieldset->addField("product_id", "text", array(
						"label" => Mage::helper("customerfaq")->__("Product Id"),
						"name" => "product_id",
						));
					
						$fieldset->addField("name", "text", array(
						"label" => Mage::helper("customerfaq")->__("Name"),
						"name" => "name",
						));
						
						$fieldset->addField("info1", "textarea", array(
						"label" => Mage::helper("customerfaq")->__("What could be better in your opinion?"),
						"name" => "info1",
						));
						
						$fieldset->addField("info2", "textarea", array(
						"label" => Mage::helper("customerfaq")->__("What did you like most?"),
						"name" => "info2",
						));
						
					
						$fieldset->addField("question", "textarea", array(
						"label" => Mage::helper("customerfaq")->__("Question"),
						"name" => "question",
						));
					
						$fieldset->addField("answer", "textarea", array(
						"label" => Mage::helper("customerfaq")->__("Answer"),
						"name" => "answer",
						));
					

				if (Mage::getSingleton("adminhtml/session")->getCustomerfaqData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getCustomerfaqData());
					Mage::getSingleton("adminhtml/session")->setCustomerfaqData(null);
				} 
				elseif(Mage::registry("customerfaq_data")) {
				    $form->setValues(Mage::registry("customerfaq_data")->getData());
				}
				return parent::_prepareForm();
		}
}
