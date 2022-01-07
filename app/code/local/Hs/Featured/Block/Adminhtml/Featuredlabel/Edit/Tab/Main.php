<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Cms page edit form main tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Hs_Featured_Block_Adminhtml_Featuredlabel_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        /* @var $model Mage_Cms_Model_Page */
        //$model = Mage::registry('feauturedlabel_data');
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('featuredlabel_form', array('legend' => Mage::helper('featured')->__('General information')));


        /*if ($model->getLabelId()) {
            $fieldset->addField('label_id', 'hidden', array(
                'name' => 'label_id',
            ));
        }*/
        $featuredId=$this->getRequest()->getParam('id');

        if(!$featuredId)
        {
            $fieldset->addField('option_id', 'select', array(
                'name' => 'option_id',
                'label' => Mage::helper('featured')->__('Option Label'),
                'title' => Mage::helper('featured')->__('Option Label'),
                'required' => true,
                'options' => Mage::getModel('featured/featuredlabel')->getAvailableoptions(),

            ));
        }
        else{
            $fieldset->addField('option_id', 'select', array(
                'name' => 'option_id',
                'label' => Mage::helper('featured')->__('Option Label'),
                'title' => Mage::helper('featured')->__('Option Label'),
                'required' => true,
                'options' => Mage::getModel('featured/featuredlabel')->getAvailableoptions(),
                'disabled'=> 'disabled'

            ));
        }

        $fieldset->addField('bgcolor', 'text', array(
            'name' => 'bgcolor',
            'label' => Mage::helper('featured')->__('Background Color'),
            'title' => Mage::helper('featured')->__('Background Color'),
            'required' => true,
            'class' => 'color',
            'value' => $value1

        ));
       

        $fieldset->addField('bghovercolor', 'text', array(
            'name' => 'bghovercolor',
            'label' => Mage::helper('featured')->__('Background Hover Color'),
            'title' => Mage::helper('featured')->__('Background Hover Color'),
            'required' => true,
            'class' => 'color',
            'value' => $value2

        ));

        $fieldset->addField('textcolor', 'text', array(
            'name' => 'textcolor',
            'label' => Mage::helper('featured')->__('Text Color'),
            'title' => Mage::helper('featured')->__('Text Color'),
            'required' => true,
            'class' => 'color',
            'value' => $value3

        ));

        $fieldset->addField('texthovercolor', 'text', array(
            'name' => 'texthovercolor',
            'label' => Mage::helper('featured')->__('Text Hover Color'),
            'title' => Mage::helper('featured')->__('Text Hover Color'),
            'required' => true,
            'class' => 'color',
            'value' => $value4

        ));


        $fieldset->addField('is_active', 'select', array(
            'label' => Mage::helper('featured')->__('Status'),
            'title' => Mage::helper('featured')->__('Page Status'),
            'name' => 'is_active',
            'required' => true,
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('featured')->__('Active'),
                ),

                array(
                    'value' => 0,
                    'label' => Mage::helper('featured')->__('Inactive'),
                ),
            ),
        ));


        if (Mage::getSingleton('adminhtml/session')->getFeaturedlabelData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getFeaturedlabelData());
            Mage::getSingleton('adminhtml/session')->setFeaturedlabelData(null);
        } elseif (Mage::registry('featuredlabel_data')) {
            $form->setValues(Mage::registry('featuredlabel_data')->getData());
        }


        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('featured')->__('Featured Label Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('featured')->__('Featured label Information');
    }
}
    
