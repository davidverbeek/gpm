<?php

class Helios_Garantiesservice_Adminhtml_GarantiesserviceController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('garantiesservice/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Garantiesservice Manager'), Mage::helper('adminhtml')->__('Garantiesservice Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    /**
     * Get categories fieldset block
     *
     */
    public function categoriesAction() {
        $this->_initProduct();

        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_categories')->toHtml()
        );
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('garantiesservice/garantiesservice')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('garantiesservice_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('garantiesservice/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Garanties service Manager'), Mage::helper('adminhtml')->__('Garanties service Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Garanties service News'), Mage::helper('adminhtml')->__('Garanties service News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('garantiesservice/adminhtml_garantiesservice_edit'))
                    ->_addLeft($this->getLayout()->createBlock('garantiesservice/adminhtml_garantiesservice_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('garantiesservice')->__('Record does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {


            if (isset($_FILES['imageicon']['name']) && $_FILES['imageicon']['name'] != '') {
                //this way the name is saved in DB
                $data['imageicon'] = $_FILES['imageicon']['name'];

                //Save Image Tag in DB for GRID View
                $imgName = $_FILES['imageicon']['name'];
                $imgPath = Mage::getBaseUrl('media') . "Garantiesservice/images/" . $imgName;
                $data['filethumbgrid'] = '<img src="' . $imgPath . '" border="0" width="16" height="16" />';
            }

            $model = Mage::getModel('garantiesservice/garantiesservice');
            $model->setData($data)->setId($this->getRequest()->getParam('id'));

            try {
                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                            ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

                $model->save();


                if (isset($_FILES['imageicon']['name']) && $_FILES['imageicon']['name'] != '') {
                    try {

                        $path = Mage::getBaseDir('media') . "/Garantiesservice" . DS . "images" . DS;
                        /* Starting upload */
                        $uploader = new Varien_File_Uploader('imageicon');
                        // Any extention would work
                        $uploader->setAllowedExtensions(array('jpg', 'JPG', 'jpeg', 'gif', 'GIF', 'png', 'PNG'));
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        // We set media as the upload dir
                        $uploader->save($path, $_FILES['imageicon']['name']);


                        //Create Thumbnail and upload
                        $imgName = $_FILES['imageicon']['name'];
                        $imgPathFull = $path . $imgName;
                        $resizeFolder = "thumb";
                        $imageResizedPath = $path . $resizeFolder . DS . $imgName;
                        $imageObj = new Varien_Image($imgPathFull);
                        $imageObj->constrainOnly(TRUE);
                        $imageObj->keepAspectRatio(TRUE);
                        $imageObj->resize(150, 150);
                        $imageObj->save($imageResizedPath);

                        //Create View Size and upload
                        $imgName = $_FILES['imageicon']['name'];
                        $imgPathFull = $path . $imgName;
                        $resizeFolder = "medium";
                        $imageResizedPath = $path . $resizeFolder . DS . $imgName;
                        $imageObj = new Varien_Image($imgPathFull);
                        $imageObj->constrainOnly(TRUE);
                        $imageObj->keepAspectRatio(TRUE);
                        //$imageObj->resize(400, 400);
                        $imageObj->save($imageResizedPath);
                    } catch (Exception $e) {
                        
                    }
                }

                

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('garantiesservice')->__('Banner was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('garantiesservice')->__('Unable to find Banner to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('garantiesservice/garantiesservice');

                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Banner was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $garantiesserviceIds = $this->getRequest()->getParam('garantiesservice');
        if (!is_array($garantiesserviceIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Banner(s)'));
        } else {
            try {
                foreach ($garantiesserviceIds as $garantiesserviceId) {
                    $garantiesservice = Mage::getModel('garantiesservice/garantiesservice')->load($garantiesserviceId);
                    $garantiesservice->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($garantiesserviceIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $garantiesserviceIds = $this->getRequest()->getParam('garantiesservice');
        if (!is_array($garantiesserviceIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Banner(s)'));
        } else {
            try {
                foreach ($garantiesserviceIds as $garantiesserviceId) {
                    $garantiesservice = Mage::getSingleton('garantiesservice/garantiesservice')
                            ->load($garantiesserviceId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($garantiesserviceIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction() {
        $fileName = 'garantiesservice.csv';
        $content = $this->getLayout()->createBlock('garantiesservice/adminhtml_garantiesservice_grid')
                ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'garantiesservice.xml';
        $content = $this->getLayout()->createBlock('garantiesservice/adminhtml_garantiesservice_grid')
                ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

}
