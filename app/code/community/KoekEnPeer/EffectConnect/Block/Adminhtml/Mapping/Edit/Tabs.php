<?php

    class KoekEnPeer_EffectConnect_Block_Adminhtml_Mapping_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
    {
        public function __construct()
        {
            parent::__construct();
            $this->setId('effectconnect_tab');
            $this->setDestElementId('edit_form');
            $this->setTitle(
                Mage::helper('effectconnect')
                    ->__('Mapping information')
            )
            ;
        }

        protected function _beforeToHtml()
        {
            $this->addTab(
                'form_section', array(
                    'label'   => Mage::helper('effectconnect')
                        ->__('Mapping information'),
                    'title'   => Mage::helper('effectconnect')
                        ->__('Mapping information'),
                    'content' => $this->getLayout()
                        ->createBlock('effectconnect/adminhtml_mapping_edit_tab_form')
                        ->toHtml(),
                )
            )
            ;

            return parent::_beforeToHtml();
        }
    }