<?php

    class KoekEnPeer_EffectConnect_Adminhtml_EffectconnectmappingController extends Mage_Adminhtml_Controller_Action
    {
        protected function _initAction()
        {
            $this->loadLayout()
                ->_setActiveMenu('effectconnect')
                ->_addBreadcrumb(
                    Mage::helper('adminhtml')
                        ->__('Channel mapping'), Mage::helper('adminhtml')
                        ->__('Channel mapping')
                )
            ;

            return $this;
        }

        public function indexAction()
        {
            $this->_initAction();
            $this->_addContent(
                $this->getLayout()
                    ->createBlock('effectconnect/adminhtml_mapping')
            )
            ;
            $this->renderLayout();
        }

        public function editAction()
        {
            $data = Mage::getModel('effectconnect/mapping')
                ->load(
                    $this->getRequest()
                        ->getParam('id')
                )
            ;
            if ($data)
            {
                Mage::register('mapping_data', $data);
                $this->loadLayout();
                $this->_setActiveMenu('effectconnect');
                $this->_addBreadcrumb(
                    Mage::helper('adminhtml')
                        ->__('Channel mapping'), Mage::helper('adminhtml')
                    ->__('Channel mapping')
                );
                $this->getLayout()
                    ->getBlock('head')
                    ->setCanLoadExtJs(true)
                ;
                $this->_addContent(
                    $this->getLayout()
                        ->createBlock('effectconnect/adminhtml_mapping_edit')
                )
                    ->_addLeft(
                        $this->getLayout()
                            ->createBlock('effectconnect/adminhtml_mapping_edit_tabs')
                    )
                ;
                $this->renderLayout();
            } else
            {
                Mage::getSingleton('adminhtml/session')
                    ->addError(
                        Mage::helper('effectconnect')
                            ->__('Item does not exist')
                    )
                ;
                $this->_redirect('*/*/');
            }
        }

        public function newAction()
        {
            $this->_forward('edit');
        }

        public function saveAction()
        {
            if ($this->getRequest()
                ->getPost()
            ):
                try
                {
                    Mage::getModel('effectconnect/mapping')
                        ->setId(
                            $this->getRequest()
                                ->getParam('id')
                        )
                        ->setChannelId(
                            $this->getRequest()
                                ->getPost('channel_id')
                        )
                        ->setStoreId(
                            $this->getRequest()
                                ->getPost('store_id')
                        )
                        ->setCustomerGroupId(
                            $this->getRequest()
                                ->getPost('customer_group_id')
                        )
                        ->setCustomerId(
                            $this->getRequest()
                                ->getPost('customer_id')
                        )
                        ->setDiscountCode(
                            $this->getRequest()
                                ->getPost('discount_code')
                        )
                        ->setPriceAttribute(
                            $this->getRequest()
                                ->getPost('price_attribute')
                        )
                        ->save()
                    ;
                    Mage::getSingleton('adminhtml/session')
                        ->addSuccess(
                            Mage::helper('adminhtml')
                                ->__('Item was successfully saved')
                        )
                    ;
                    Mage::getSingleton('adminhtml/session')
                        ->setMappingData(false)
                    ;
                    $this->_redirect('*/*/');

                    return;
                } catch (Exception $e)
                {
                    Mage::getSingleton('adminhtml/session')
                        ->addError($e->getMessage())
                    ;
                    Mage::getSingleton('adminhtml/session')
                        ->setMappingData(
                            $this->getRequest()
                                ->getPost()
                        )
                    ;
                    $this->_redirect(
                        '*/*/edit', array(
                            'id' => $this->getRequest()
                                ->getParam('id')
                        )
                    )
                    ;

                    return;
                }
            endif;
            $this->_redirect('*/*/');
        }

        public function deleteAction()
        {
            if ($this->getRequest()
                    ->getParam('id') > 0
            ):
                try
                {
                    Mage::getModel('effectconnect/mapping')
                        ->setId(
                            $this->getRequest()
                                ->getParam('id')
                        )
                        ->delete()
                    ;
                    Mage::getSingleton('adminhtml/session')
                        ->addSuccess(
                            Mage::helper('adminhtml')
                                ->__('Item was successfully deleted')
                        )
                    ;
                    $this->_redirect('*/*/');
                } catch (Exception $e)
                {
                    Mage::getSingleton('adminhtml/session')
                        ->addError($e->getMessage())
                    ;
                    $this->_redirect(
                        '*/*/edit', array(
                            'id' => $this->getRequest()
                                ->getParam('id')
                        )
                    )
                    ;
                }
            endif;
            $this->_redirect('*/*/');
        }

        /**
         * Product grid for AJAX request.
         * Sort and filter result for example.
         */
        public function gridAction()
        {
            $this->loadLayout();
            $this->getResponse()
                ->setBody(
                    $this->getLayout()
                        ->createBlock('effectconnect/adminhtml_mapping_grid')
                        ->toHtml()
                )
            ;
        }
    }