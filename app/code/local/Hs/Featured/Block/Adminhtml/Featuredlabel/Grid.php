<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Hs_Featured_Block_Adminhtml_Featuredlabel_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('featuredlabelGrid');
        $this->setDefaultSort('label_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('featured/featuredlabel')->getCollection();
      			//->addFieldToFilter('is_active','1');
                        
         
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('label_id', array(
            'header' => Mage::helper('featured')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'label_id',
        ));



        $this->addColumn('option_id', array(
            'header' => Mage::helper('featured')->__('Option name'),
            'align' => 'left',
            'index' => 'option_id',
            'width' => '80px',
            'renderer'	=>	'featured/adminhtml_featuredlabel_grid_renderer_label'
        ));

 
        $this->addColumn('created_at', array(
            'header'    => Mage::helper('featured')->__('Creation Time'),
            'align'     => 'left',
            'width'     => '120px',
            'type'      => 'date',
            'default'   => '--',
            'index'     => 'created_at',
        ));
 
        $this->addColumn('update_at', array(
            'header'    => Mage::helper('featured')->__('Update Time'),
            'align'     => 'left',
            'width'     => '120px',
            'type'      => 'date',
            'default'   => '--',
            'index'     => 'update_at',
        ));   
        
        $this->addColumn('is_active', array(
 
            'header'    => Mage::helper('featured')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => array(
                1 => 'Active',
                0 => 'Inactive',
            ),
        ));
        
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('featured')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('featured')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

       // $this->addExportType('*/*/exportCsv', Mage::helper('featured')->__('CSV'));
      //  $this->addExportType('*/*/exportXml', Mage::helper('featured')->__('Excel XML'));
        
        return parent::_prepareColumns();
    }




    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('featured_id');
        $this->getMassactionBlock()->setFormFieldName('featured');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('featured')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('featured')->__('Are you sure?')
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
 
    public function getGridUrl()
    {
      return $this->getUrl('*/*/grid', array('_current'=>true));
    }

}
