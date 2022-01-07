<?php

class Helios_Garantiesservice_Block_Adminhtml_Garantiesservice_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('garantiesserviceGrid');
        $this->setDefaultSort('garantiesservice_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('garantiesservice/garantiesservice')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('garantiesservice_id', array(
            'header' => Mage::helper('garantiesservice')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'garantiesservice_id',
        ));
        $this->addColumn('filethumbgrid', array(
          'header'    => Mage::helper('garantiesservice')->__('Icon'),
          'align'     =>'center',
          'index'     => 'filethumbgrid',
		  'type'      => 'text',
		  'width'     => '50px',
        ));
        $this->addColumn('title', array(
            'header' => Mage::helper('garantiesservice')->__('Title'),
            'align' => 'left',
            'index' => 'title',
        ));
        
        $this->addColumn('created_time', array(
            'header' => Mage::helper('garantiesservice')->__('Created time'),
            'align' => 'left',
            'index' => 'created_time',
        ));
        
        $this->addColumn('update_time', array(
            'header' => Mage::helper('garantiesservice')->__('Updated time'),
            'align' => 'left',
            'index' => 'update_time',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('garantiesservice')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => 'Enabled',
                2 => 'Disabled',
            ),
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('garantiesservice')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('garantiesservice')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        //$this->addExportType('*/*/exportCsv', Mage::helper('garantiesservice')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('garantiesservice')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('garantiesservice_id');
        $this->getMassactionBlock()->setFormFieldName('garantiesservice');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('garantiesservice')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('garantiesservice')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('garantiesservice/status')->getOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('garantiesservice')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('garantiesservice')->__('Status'),
                    'values' => $statuses
                )
            )
        ));
        return $this;
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
