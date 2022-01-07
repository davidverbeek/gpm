<?php

    class KoekEnPeer_EffectConnect_Block_Adminhtml_Mapping_Grid extends Mage_Adminhtml_Block_Widget_Grid
    {
        public function __construct()
        {
            parent::__construct();
            $this->setId('effectconnectGrid');
            $this->setDefaultSort('mapping_id');
            $this->setDefaultDir('ASC');
            $this->setSaveParametersInSession(true);
        }

        protected function _prepareCollection()
        {
            $collection = Mage::getModel('effectconnect/mapping')
                ->getCollection()
            ;
            $this->setCollection($collection);

            return parent::_prepareCollection();
        }

        protected function _prepareColumns()
        {
            $this->addColumn(
                'mapping_id', array(
                    'header' => Mage::helper('effectconnect')
                        ->__('ID'),
                    'align'  => 'right',
                    'width'  => '50px',
                    'index'  => 'mapping_id',
                )
            )
            ;
            $this->addColumn(
                'channel_id', array(
                    'header'  => Mage::helper('effectconnect')
                        ->__('Channel'),
                    'align'   => 'left',
                    'index'   => 'channel_id',
                    'type'    => 'options',
                    'options' => Mage::helper('effectconnect/data')->getChannels()
                )
            )
            ;
            $this->addColumn(
                'store_id', array(
                    'header'  => Mage::helper('effectconnect')
                        ->__('Store View'),
                    'align'   => 'left',
                    'index'   => 'store_id',
                    'type'    => 'options',
                    'options' => Mage::getSingleton('adminhtml/system_store')
                        ->getStoreOptionHash()
                )
            )
            ;
            $customerGroups      = Mage::helper('customer')
                ->getGroups()
                ->toOptionArray()
            ;
            $customerGroupsArray = array();
            foreach ($customerGroups as $customerGroup)
            {
                $customerGroupsArray[$customerGroup['value']] = $customerGroup['label'];
            }
            $this->addColumn(
                'customer_group_id', array(
                    'header'  => Mage::helper('effectconnect')
                        ->__('Customer Group'),
                    'align'   => 'left',
                    'index'   => 'customer_group_id',
                    'type'    => 'options',
                    'options' => $customerGroupsArray
                )
            )
            ;
            $collection  = Mage::getModel('effectconnect/mapping')
                ->getCollection()
            ;
            $customerIds = array(
                0 => 'Create a customer'
            );
            foreach ($collection as $record)
            {
                $customerId = $record->getCustomerId();
                if ($customerId)
                {
                    if (!isset($customerIds[$customerId]))
                    {
                        $customer = Mage::getModel('customer/customer')
                            ->load($customerId);
                        if ($customer)
                        {
                            $customerLabel =
                                $customer->getFirstname().' '.$customer->getLastname().' ('.$customer->getEmail().')';
                        } else
                        {
                            $customerLabel = 'Invalid customer';
                        }
                        $customerIds[$customerId] = $customerId.'. '.$customerLabel;
                    }
                }
            }
            $this->addColumn(
                'customer_id', array(
                    'header'  => Mage::helper('effectconnect')
                        ->__('Customer'),
                    'align'   => 'left',
                    'index'   => 'customer_id',
                    'type'    => 'options',
                    'options' => $customerIds
                )
            )
            ;
            $this->addColumn(
                'discount_code', array(
                    'header' => Mage::helper('effectconnect')
                        ->__('Discount code'),
                    'align'  => 'left',
                    'index'  => 'discount_code',
                    'type'   => 'text'
                )
            )
            ;
            $this->addColumn(
                'price_attribute', array(
                    'header' => Mage::helper('effectconnect')
                        ->__('Price attribute'),
                    'align'  => 'left',
                    'index'  => 'price_attribute',
                    'type'   => 'text'
                )
            )
            ;

            return parent::_prepareColumns();
        }

        public function getRowUrl($row)
        {
            return $this->getUrl('*/*/edit', array('id' => $row->getId()));
        }

        public function getGridUrl()
        {
            return $this->getUrl('*/*/grid', array('_current' => true));
        }
    }