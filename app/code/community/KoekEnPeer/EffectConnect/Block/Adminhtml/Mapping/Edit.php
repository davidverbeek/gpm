<?php

    class KoekEnPeer_EffectConnect_Block_Adminhtml_Mapping_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
    {
        public function __construct()
        {
            parent::__construct();
            $this->_objectId   = 'id';
            $this->_blockGroup = 'effectconnect';
            $this->_controller = 'adminhtml_mapping';
            $this->_updateButton('save', 'label', Mage::helper('effectconnect')
                    ->__('Save mapping')
            )
            ;
            $this->_updateButton('delete', 'label', Mage::helper('effectconnect')
                    ->__('Delete mapping')
            )
            ;
        }

        public function getHeaderText()
        {
            return (Mage::registry('effectconnect_data') && Mage::registry('effectconnect_data')
                    ->getId()) ? Mage::helper('effectconnect')
                ->__('Edit mapping') : Mage::helper('effectconnect')
                ->__('Add mapping')
                ;
        }
    }