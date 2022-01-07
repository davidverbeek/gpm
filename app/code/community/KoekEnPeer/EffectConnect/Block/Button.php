<?php

    class KoekEnPeer_EffectConnect_Block_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
    {
        protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
        {
            $this->setElement($element);
            $url  = $this->getUrl('adminhtml/effectconnectexport/index', array('_store'=>0));
            $html = $this->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setType('button')
                ->setClass('scalable')
                ->setLabel('Synchronise data')
                ->setOnClick('setLocation(\''.$url.'\')')
                ->toHtml()
            ;

            return $html;
        }
    }