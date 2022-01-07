<?php

    class KoekEnPeer_EffectConnect_Block_Adminhtml_Mapping extends Mage_Adminhtml_Block_Widget_Grid_Container
    {
        public function __construct()
        {
            $this->_controller     = 'adminhtml_mapping';
            $this->_blockGroup     = 'effectconnect';
            $this->_headerText     = Mage::helper('effectconnect')
                ->__('Channel mapping')
            ;
            $this->_addButtonLabel = Mage::helper('effectconnect')
                ->__('Add mapping')
            ;
            parent::__construct();
        }
    }