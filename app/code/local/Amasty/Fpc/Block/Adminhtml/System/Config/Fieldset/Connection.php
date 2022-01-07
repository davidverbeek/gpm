<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


class Amasty_Fpc_Block_Adminhtml_System_Config_Fieldset_Connection extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        if (Mage::helper('core')->isModuleEnabled('Amasty_Optimization')) {
            $cssClass = 'amasty_is_instaled';
            $value = 'Installed';
            $element->setComment($this->__('Specify Google Page Speed Optimizer settings properly See more details '
                . '<a href="%1$s" target="_blank">here.</a>',
                Mage::helper("adminhtml")->getUrl('adminhtml/system_config/edit', array('section'=>'amoptimization'))
            ));
        } else {
            $cssClass = 'amasty_not_instaled';
            $value = 'Not installed';
            $element->setComment('Make your store much more Google and user-friendly by optimizing your code '
                . 'structure with Magento Speed Optimization extension. See more details <a href="'
                . 'https://amasty.com/magento-google-page-speed-optimizer.html'
                . '?utm_source=extension&utm_medium=backend&utm_campaign=from_fpcm1_to_google_index_optimisation_m1" '
                . 'target="_blank">here.</a>'
            );
        }
        $html = '<span class="' . $cssClass . '">' . $value . ' </span>' . "\n";

        return $html;
    }
}
