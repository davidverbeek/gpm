<?php

class Magebuzz_Faq_Block_Adminhtml_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('faqGrid');
      $this->setDefaultSort('category_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('faq/category')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('category_id', array(
          'header'    => Mage::helper('faq')->__('Category#'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'category_id',
      ));


      $this->addColumn('category_name', array(
			'header'    => Mage::helper('faq')->__('Category Name'),
			'align'     => 'left',
			'index'     => 'category_name',
      ));
			
			if (!Mage::app()->isSingleStoreMode()) {
				$this->addColumn('store_id', array(
        'header'        => Mage::helper('cms')->__('Store View'),
        'index'         => 'store_id',
        'type'          => 'store',
        'store_all'     => true,
        'store_view'    => true,
        'sortable'      => false,
        'filter_condition_callback'
                        => array($this, '_filterStoreCondition'),
				));
			}


      $this->addColumn('is_active', array(
          'header'    => Mage::helper('faq')->__('Active'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'is_active',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              0 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('faq')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('faq')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('category_id');
        $this->getMassactionBlock()->setFormFieldName('faq');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('faq')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('faq')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('faq/status')->getOptionArray();

  //      array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('faq')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('faq')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
	
	protected function _afterLoadCollection() { 
		$this->getCollection()->walk('afterLoad');
		parent::_afterLoadCollection(); 
	}

}