<?php

    class KoekEnPeer_EffectConnect_Block_Adminhtml_Mapping_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
    {
        protected function _prepareForm()
        {
            $form = new Varien_Data_Form();
            $this->setForm($form);
            $fieldset = $form->addFieldset(
                'effectconnect_form', array(
                    'legend' => Mage::helper('effectconnect')
                        ->__('Mapping information')
                )
            );

            $channelData   = array();
            $channelData[] = array(
                'value' => '',
                'label' => ''
            );
            $channels      = Mage::helper('effectconnect/data')->getChannels();
            if (!empty($channels))
            {
                foreach ($channels as $channelId => $channelName)
                {
                    $channelData[] = array(
                        'value' => $channelId,
                        'label' => $channelName,
                    );
                }
            }

            $fieldset->addField(
                'channel_id', 'select', array(
                    'label'    => Mage::helper('effectconnect')
                        ->__('Channel'),
                    'name'     => 'channel_id',
                    'values'   => $channelData,
                    'required' => true
                )
            );

            $fieldset->addField(
                'store_id', 'select', array(
                    'label'    => Mage::helper('effectconnect')
                        ->__('Store'),
                    'name'     => 'store_id',
                    'values'   => Mage::getSingleton('adminhtml/system_store')
                        ->getStoreValuesForForm(false, true),
                    'required' => true
                )
            );

            $customerGroupData = Mage::helper('customer')
                ->getGroups()
                ->toOptionArray();
            array_unshift(
                $customerGroupData, array(
                    'value' => '',
                    'label' => ''
                )
            );

            $fieldset->addField(
                'customer_group_id', 'select', array(
                    'label'    => Mage::helper('effectconnect')
                        ->__('Customer Group'),
                    'name'     => 'customer_group_id',
                    'values'   => $customerGroupData,
                    'required' => false
                )
            );

            $fieldset->addField(
                'customer_id', 'text', array(
                    'label'    => Mage::helper('effectconnect')
                        ->__('Customer ID'),
                    'name'     => 'customer_id',
                    'required' => false
                )
            );

            $fieldset->addField(
                'discount_code', 'text', array(
                    'label'    => Mage::helper('effectconnect')
                        ->__('Discount code'),
                    'name'     => 'discount_code',
                    'required' => false
                )
            );

            if (Mage::getSingleton('adminhtml/session')
                ->getEffectconnectData()
            )
            {
                $form->setValues(
                    Mage::getSingleton('adminhtml/session')->getEffectconnectData()
                );
                Mage::getSingleton('adminhtml/session')->setEffectconnectData(null);
            } elseif (Mage::registry('mapping_data'))
            {
                $form->setValues(
                    Mage::registry('mapping_data')->getData()
                );
            }

            $model = Mage::getModel('effectconnect/source_attribute_decimal');
            $fieldset->addField(
                'price_attribute', 'select', array(
                    'label'    => Mage::helper('effectconnect')
                        ->__('Price attribute'),
                    'name'     => 'price_attribute',
                    'values'   => $model->toOptionArray(),
                    'required' => false
                )
            );

            return parent::_prepareForm();
        }
    }