<?php

class Magebuzz_Faq_Block_Adminhtml_Faq_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('faqGrid');
      $this->setDefaultSort('faq_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
		$collection = Mage::getModel('faq/faq')->getCollection();
		$collection->getSelect()
			->joinLeft(array('faqcat' => Mage::getSingleton('core/resource')->getTableName('faq_category_item')), 'main_table.faq_id=faqcat.faq_id', '')
			->joinLeft(array('cat' => Mage::getSingleton('core/resource')->getTableName('faq_category')), 'faqcat.category_id=cat.category_id', 'cat.category_name')
			->order('main_table.faq_id', 'ASC');
		$this->setCollection($collection);
		return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('faq_id', array(
          'header'    => Mage::helper('faq')->__('FAQ#'),
          'align'     =>'right',
          'width'     => '50px',
          'filter_index' => 'main_table.is_active',
          'index'     => 'faq_id',
      ));

      $this->addColumn('question', array(
			'header'    => Mage::helper('faq')->__('Question'),
			'align'     => 'left',
			'index'     => 'question',
      ));
			
			$this->addColumn('category_name', array(
				'header'    => Mage::helper('faq')->__('Category'),
				'align'     => 'left',
				'index'  => 'category_name'
      ));
			
			// if (!Mage::app()->isSingleStoreMode()) {
			// 	$this->addColumn('store_id', array(
			// 			'header'        => Mage::helper('cms')->__('Store View'),
			// 			'index'         => 'store_id',
			// 			'type'          => 'store',
			// 			'store_all'     => true,
			// 			'store_view'    => true,
			// 			'sortable'      => false,
			// 			'filter_condition_callback'		=> array($this, '_filterStoreCondition'),
			// 	));
			// }

      $this->addColumn('is_active', array(
          'header'    => Mage::helper('faq')->__('Active'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'is_active',
          'type'      => 'options',
          'filter_index' => 'main_table.is_active',
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
        $this->setMassactionIdField('faq_id');
        $this->getMassactionBlock()->setFormFieldName('faq');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('faq')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('faq')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('faq/status')->getOptionArray();


 //       array_unshift($statuses, array('label'=>'', 'value'=>''));
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