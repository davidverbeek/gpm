<?php

class Magebuzz_Faq_Block_Adminhtml_Suggest_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('suggestGrid');
      $this->setDefaultSort('id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('faq/suggest')->getCollection();
			$this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('id', array(
          'header'    => Mage::helper('faq')->__('#'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'id',
      ));

      $this->addColumn('name', array(
			'header'    => Mage::helper('faq')->__('Name'),
			'align'     => 'left',
			'index'     => 'name',
      ));
			
			$this->addColumn('email', array(
				'header'    => Mage::helper('faq')->__('Email'),
				'align'     => 'left',
				'index'  => 'email'
      ));
			
			$this->addColumn('phone', array(
				'header'    => Mage::helper('faq')->__('Telephone'),
				'align'     => 'left',
				'index'  => 'phone'
      ));
			$this->addColumn('message', array(
				'header'    => Mage::helper('faq')->__('Message'),
				'align'     => 'left',
				'index'  => 'message'
      ));
			
			$this->addColumn('request_free_sample', array(
          'header'    => Mage::helper('faq')->__('Request Free Sample'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'request_free_sample',
          'type'      => 'options',
          'options'   => array(
              1 => 'Yes',
              0 => 'No',
          ),
      ));
			
			$this->addColumn('status', array(
          'header'    => Mage::helper('faq')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Converted',
              0 => 'Pending',
          ),
      ));
      return parent::_prepareColumns();
  }

	protected function _prepareMassaction()
	{
			$this->setMassactionIdField('id');
			$this->getMassactionBlock()->setFormFieldName('faq');

			$this->getMassactionBlock()->addItem('delete', array(
					 'label'    => Mage::helper('faq')->__('Delete'),
					 'url'      => $this->getUrl('*/*/massDelete'),
					 'confirm'  => Mage::helper('faq')->__('Are you sure?')
			));
			$this->getMassactionBlock()->addItem('convert', array(
					 'label'    => Mage::helper('faq')->__('Convert to FAQ'),
					 'url'      => $this->getUrl('*/*/massConvert'),
					 'confirm'  => Mage::helper('faq')->__('Are you sure?')
			));
			return $this;
	}

  public function getRowUrl($row)
  {
      return false;
  }
	
	protected function _afterLoadCollection() {
		$this->getCollection()->walk('afterLoad');
		parent::_afterLoadCollection();
	}

}