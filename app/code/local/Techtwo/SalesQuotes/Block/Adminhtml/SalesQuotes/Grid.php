<?php

class Techtwo_SalesQuotes_Block_Adminhtml_SalesQuotes_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('salesquotes_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('updated_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {        
        $collection = Mage::getModel('sales/quote')->getCollection()
                        ->addFieldToFilter('is_cart_auto',0);

        $collection = $this->joinCustomers($collection);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('techtwo_salesquotes')->__('Quote #'),
            'align'     =>'left',
            'width'     => '75px',
            'index'     => 'entity_id',
            'filter_index' => 'main_table.entity_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('techtwo_salesquotes')->__('Requested In (Store)'),
                'index'     => 'store_id',
                'filter_index' => 'main_table.store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
                'width'     => '180px'
            ));
        }

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('techtwo_salesquotes')->__('Created'),
            'align'     => 'left',
            'index'     => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type'      => 'datetime',
            'width'     => '150px'
        ));

        $this->addColumn('updated_at', array(
            'header'    => Mage::helper('techtwo_salesquotes')->__('Updated'),
            'align'     => 'left',
            'index'     => 'updated_at',
            'filter_index' => 'main_table.updated_at',
            'type'      => 'datetime',
            'width'     => '150px'
        ));

        $this->addColumn('customer_name', array(
            'header'    => Mage::helper('techtwo_salesquotes')->__('Customer Name'),
            'align'     =>'left',
            'index'     => 'customer_name',
        ));
        
        
        $this->addColumn('grand_total', array(
            'header'    => Mage::helper('techtwo_salesquotes')->__('Grand Total'),
            'align'     =>'left',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
            'index'     => 'grand_total',
        ));
        
        $this->addColumn('status', array(
            'header'    => Mage::helper('techtwo_salesquotes')->__('Status'),
            'align'     =>'left',
            'index'     => 'status',
            'type'      => 'options',
            'width'     => '100px',
            'options'   => array(
                'open'=>Mage::helper('techtwo_salesquotes')->__('Open'),
                'pending'=>Mage::helper('techtwo_salesquotes')->__('Pending'),
                'confirmed'=>Mage::helper('techtwo_salesquotes')->__('Confirmed'),
                'converted'=>Mage::helper('techtwo_salesquotes')->__('Converted')
            )
        ));
        
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/create')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('techtwo_salesquotes')->__('Action'),
                    'width'     => '150px',
                    'type'      => 'action',
                    'getter'     => 'getEntityId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('techtwo_salesquotes')->__('Edit/Order'),
                            'url'     => array(
                                'base'=>'adminhtml/salesquotes/edit/',
                            ),
                            'field'   => 'quote_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
            ));
        }

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/create')) {
            return $this->getUrl('*/*/edit', array('quote_id' => $row->getId()));
        }
        return false;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('quote_id');

        $this->getMassactionBlock()->addItem('update_open', array(
            'label' => Mage::helper('techtwo_salesquotes')->__('Change status to open'),
            'url'   => $this->getUrl('adminhtml/salesquotes/updateStatus/', array('status'=>'open','_current'=>true))
        ));

        $this->getMassactionBlock()->addItem('update_pending', array(
            'label' => Mage::helper('techtwo_salesquotes')->__('Change status to pending'),
            'url'   => $this->getUrl('adminhtml/salesquotes/updateStatus/', array('status'=>'pending','_current'=>true))
        ));

        $this->getMassactionBlock()->addItem('update_status', array(
            'label' => Mage::helper('techtwo_salesquotes')->__('Change status to confirmed'),
            'url'   => $this->getUrl('adminhtml/salesquotes/updateStatus/', array('status'=>'confirmed','_current'=>true))
        ));

        return $this;
    }

    
    // yeah, isn't EAV lovely?
    private function joinCustomers($collection)
    {
        $customer = Mage::getResourceSingleton('customer/customer');
        
        // get first name
        $firstnameAttr = $customer->getAttribute('firstname');
        $firstnameAttrId = $firstnameAttr->getAttributeId();
        $firstnameTable = $firstnameAttr->getBackend()->getTable();

        if ($firstnameAttr->getBackend()->isStatic()) {
            $firstnameField = 'firstname';
            $attrCondition = '';
        } else {
            $firstnameField = 'value';
            $attrCondition = ' AND _table_customer_firstname.attribute_id = '.$firstnameAttrId;
        }

        $collection->getSelect()->joinInner(array('_table_customer_firstname' => $firstnameTable),
            '_table_customer_firstname.entity_id=main_table.customer_id'.$attrCondition, array());



        // get email
        $emailAttr = $customer->getAttribute('email');
        $emailAttrId = $emailAttr->getAttributeId();
        $emailTable = $emailAttr->getBackend()->getTable();
        
        if ($emailAttr->getBackend()->isStatic()) {
            $emailField = 'email';
            $attrCondition = '';
        } else {
            $emailField = 'value';
            $attrCondition = ' AND _table_customer_email.attribute_id = '.$emailAttrId;
        }

        $collection->getSelect()->joinInner(array('_table_customer_email' => $emailTable),
            '_table_customer_email.entity_id=main_table.customer_id'.$attrCondition, array());


        // get lastname
        $lastnameAttr = $customer->getAttribute('lastname');
        $lastnameAttrId = $lastnameAttr->getAttributeId();
        $lastnameTable = $lastnameAttr->getBackend()->getTable();

        if ($lastnameAttr->getBackend()->isStatic()) {
            $lastnameField = 'lastname';
            $attrCondition = '';
        } else {
            $lastnameField = 'value';
            $attrCondition = ' AND _table_customer_lastname.attribute_id = '.$lastnameAttrId;
        }


        $collection->getSelect()->joinInner(
            array('_table_customer_lastname' => $lastnameTable),
            '_table_customer_lastname.entity_id=main_table.customer_id'.$attrCondition, array())
            ->from("", array(
                        'customer_name'  => "CONCAT(_table_customer_firstname.{$firstnameField}, ' ', _table_customer_lastname.{$lastnameField})",
                        'customer_email' => $emailField
                        )
                  );

        //echo $this->getSelect()->__toString();

        return $collection;
    }
   
}
