<?php
class Helios_Creports_Block_Adminhtml_Vatopercentage_Grid extends Mage_Adminhtml_Block_Report_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('vatopercentagegrid');
		$this->setDefaultSort('created_at');
		$this->setDefaultDir('ASC');
		$this->setTemplate('helios/grid.phtml');
		$this->setSaveParametersInSession(true);
		$this->setSubReportSize(false);
	}

	protected function _getCollectionClass() {
		return 'creports/vatopercentage';
	}

	protected function _prepareCollection() {
		parent::_prepareCollection();
		$this->getCollection()->initReport('creports/vatopercentage');
		return $this;
	}

	protected function _prepareColumns() {
		$this->addColumn('increment_id', array(
			'header'    =>Mage::helper('creports')->__('Order #'),
			'index'     =>'increment_id',
			'width'    => '100px'
		));
		$this->addColumn('invoice_number', array(
			'header'    =>Mage::helper('creports')->__('Invoice #'),
			'index'     =>'invoice_number',
			'renderer' => 'Helios_Creports_Block_Adminhtml_Wiretransfer_Grid_Renderer_Invoice',
			'width'  => '100px'
		));
		$this->addColumn('customer_name', array(
			'header'    =>Mage::helper('creports')->__('Customer Name'),
			'index'     =>'customer_name',
			'renderer' => 'Helios_Creports_Block_Adminhtml_Wiretransfer_Grid_Renderer_Name',
			// 'width'  => '180px'
		));
		$this->addColumn('company', array(
			'header'    =>Mage::helper('creports')->__('Company Name'),
			'index'     =>'company',
			'renderer' => 'Helios_Creports_Block_Adminhtml_Wiretransfer_Grid_Renderer_Company',
		));
		$this->addColumn('vat_number', array(
			'header'    =>Mage::helper('creports')->__('VAT Number'),
			'index'     =>'vat_number',
			'renderer' => 'Helios_Creports_Block_Adminhtml_Wiretransfer_Grid_Renderer_Vatnumber',
		));
		$this->addColumn('base_grand_total', array(
			'header'    =>Mage::helper('creports')->__('Grand Total'),
			'index'     =>'base_grand_total',
			'type'  => 'currency',
			'currency' => 'order_currency_code',
			'width'  => '100px'
		));
		$this->addColumn('base_tax_amount', array(
			'header'    =>Mage::helper('creports')->__('Tax'),
			'index'     =>'base_tax_amount',
			'type'  => 'currency',
			'currency' => 'order_currency_code',
			'width'  => '70px'
		));
		$this->addColumn('status', array(
			'header'    =>Mage::helper('creports')->__('Status'),
			'index'     =>'status',
			'type'  => 'options',
			'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
		));
		$this->addColumn('created_at', array(
			'header'    =>Mage::helper('creports')->__('Created at'),
			'index'     =>'created_at',
		));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('creports')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('creports')->__('XML'));
		return parent::_prepareColumns();

	}

	public function getRowUrl($row) {
		return false;
	}
}