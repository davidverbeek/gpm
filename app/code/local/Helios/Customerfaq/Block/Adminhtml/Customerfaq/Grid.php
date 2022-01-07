<?php

class Helios_Customerfaq_Block_Adminhtml_Customerfaq_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("customerfaqGrid");
				$this->setDefaultSort("customerfaq_id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("customerfaq/customerfaq")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("customerfaq_id", array(
				"header" => Mage::helper("customerfaq")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "customerfaq_id",
				));
                
				$this->addColumn("customer_id", array(
				"header" => Mage::helper("customerfaq")->__("Customer Id"),
				"index" => "customer_id",
				));
				$this->addColumn("name", array(
				"header" => Mage::helper("customerfaq")->__("Name"),
				"index" => "name",
				));
				$this->addColumn("product_sku", array(
				"header" => Mage::helper("customerfaq")->__("Gyzs Sku"),
				"index" => "product_sku",
				));
				$this->addColumn("created_at", array(
				"header" => Mage::helper("customerfaq")->__("Created At"),
				"index" => "created_at",
				  'type' => 'datetime',
				));
				
				$this->addColumn("is_move", array(
				"header" => Mage::helper("customerfaq")->__("Move in Faq"),
				"index" => "is_move",
				'type'      => 'options',
				'options'   => array(
              1 => 'Yes',
              0 => 'No',
          ),
		  ));
		  
		  $this->addColumn("mail_sent", array(
				"header" => Mage::helper("customerfaq")->__("Mail Sent"),
				"index" => "mail_sent",
				'type'      => 'options',
				'options'   => array(
              1 => 'Yes',
              0 => 'No',
          ),
		  
				));
				$this->addColumn("is_from", array(
				"header" => Mage::helper("customerfaq")->__("From"),
				"index" => "is_from",
				'type'      => 'options',
				'options'   => array(
              1 => 'Product Page',
              0 => 'Thanks Page',
          ),
				));
				
			$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
			$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}


		
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('customerfaq_id');
			$this->getMassactionBlock()->setFormFieldName('customerfaq_ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_customerfaq', array(
					 'label'=> Mage::helper('customerfaq')->__('Remove Customerfaq'),
					 'url'  => $this->getUrl('*/adminhtml_customerfaq/massRemove'),
					 'confirm' => Mage::helper('customerfaq')->__('Are you sure?')
				));
                        $this->getMassactionBlock()->addItem('move_customerfaq', array(
					 'label'=> Mage::helper('customerfaq')->__('Move Customerfaq'),
					 'url'  => $this->getUrl('*/adminhtml_customerfaq/massMove'),
					
				));
                        
			return $this;
		}
			

}