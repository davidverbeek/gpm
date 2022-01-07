<?php

class Gyzs_Blog_Block_Adminhtml_Blog_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('blogGrid');
      $this->setDefaultSort('faq_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
		$collection = Mage::getResourceModel('wordpress/post_collection')
					->addPostTypeFilter(array_keys(Mage::helper('wordpress/app')->getPostTypes()))
#					->removePermalinkFromSelect()
					->addIsViewableFilter();
                
		$this->setCollection($collection);
                //echo "<pre>"; print_r($collection->getData()); exit;
		return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('ID', array(
          'header'    => Mage::helper('blog')->__('Blog#'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'ID',
      ));

      $this->addColumn('post_title', array(
			'header'    => Mage::helper('blog')->__('Post Title'),
			'align'     => 'left',
			'index'     => 'post_title',
      ));
	
      $this->addColumn('post_status', array(
          'header'    => Mage::helper('blog')->__('Active'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'post_status',
          'type'      => 'options',
		  'filter'    => false,
          'sortable'  => false,
          'options'   => array(
              'publish' => 'Publish',
              0 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('blog')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('blog')->__('Edit'),
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

   /* protected function _prepareMassaction()
    {
        $this->setMassactionIdField('faq_id');
        $this->getMassactionBlock()->setFormFieldName('faq');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('faq')->__('Delete'),
             'url'      => $this->getUrl('/massDelete'),
             'confirm'  => Mage::helper('faq')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('faq/status')->getOptionArray();


 //       array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('faq')->__('Change status'),
             'url'  => $this->getUrl('/massStatus', array('_current'=>true)),
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
    } */

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
	
	protected function _afterLoadCollection() {
		$this->getCollection()->walk('afterLoad');
		parent::_afterLoadCollection();
	}

}