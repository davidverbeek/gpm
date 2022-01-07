<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Hs_Featured_Block_Adminhtml_Featured_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('featuredGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect('*')
        			->addAttributeToFilter('featured','1');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header' => Mage::helper('featured')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'entity_id',
        ));


        $this->addColumn('sku', array(
            'header' => Mage::helper('featured')->__('Sku'),
            'align' => 'left',
            'index' => 'sku',
            'width' => '80px',
        ));
        
        $this->addColumn('name', array(
            'header' => Mage::helper('featured')->__('Product Name'),
            'align' => 'left',
            'index' => 'name',
            'width' => '80px',
        ));

        $this->addColumn('price', array(
            'header' => Mage::helper('featured')->__('Product Price'),
            'align' => 'left',
            'index' => 'price',
            'renderer'	=>	'featured/adminhtml_featured_grid_renderer_price'
        ));
        $this->addColumn('dispprice', array(
            'header' => Mage::helper('featured')->__('Price'),
            'align' => 'left',
            'index' => 'dispprice',
            'renderer'	=>'featured/adminhtml_featured_grid_renderer_dispprice'
        ));
        $this->addColumn('special_price', array(
            'header' => Mage::helper('featured')->__('Special Price'),
            'align' => 'left',
            'index' => 'special_price',
            'renderer'	=>	'featured/adminhtml_featured_grid_renderer_specialprice'
        ));
        $this->addColumn('special_from_date', array(
            'header' => Mage::helper('featured')->__('Special Price From'),
            'align' => 'left',
            'index' => 'special_from_date',
            'renderer'	=>	'featured/adminhtml_featured_grid_renderer_specialfromdate'
        ));
        $this->addColumn('special_to_date', array(
            'header' => Mage::helper('featured')->__('Special Price From'),
            'align' => 'left',
            'index' => 'special_to_date',
            'renderer'	=>	'featured/adminhtml_featured_grid_renderer_specialtodate'
        ));
        $this->addColumn('label', array(
            'header' => Mage::helper('featured')->__('Label'),
            'align' => 'left',
            'index' => 'label',
            'renderer'	=>	'featured/adminhtml_featured_grid_renderer_label'
        ));
        /*$this->addColumn('action',
                array(
                    'header'    => Mage::helper('featured')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,

                    'renderer'	=>	'featured/adminhtml_featured_grid_renderer_action',
        ));*/
        $this->addColumn('action', array(
            'header' => Mage::helper('featured')->__('Action'),
            'align' => 'left',
            'index' => 'action',
            'renderer'	=>	'featured/adminhtml_featured_grid_renderer_action'
        ));


        return parent::_prepareColumns();
    }




    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('featured_id');
        $this->getMassactionBlock()->setFormFieldName('featured');

        $this->getMassactionBlock()->addItem('Update Changes', array(
            'label' => Mage::helper('featured')->__('Update Changes'),
            'url' => $this->getUrl('*/*/massUpdate')
        ));
        $this->getMassactionBlock()->addItem('Move to Cate', array(
            'label' => Mage::helper('featured')->__('Move to Acties'),
            'url' => $this->getUrl('*/*/moveToActies')
        ));

        return $this;
    }

    /*public function getRowUrl($row)
    {
        return $this->getUrl('//edit', array('id' => $row->getId()));
    }*/

}
