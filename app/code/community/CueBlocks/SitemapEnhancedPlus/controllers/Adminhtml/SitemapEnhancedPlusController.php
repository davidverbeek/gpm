<?php

/**
 * Description of SitemapEnhancedPlusController
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Adminhtml_SitemapEnhancedPlusController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/sitemap_enhanced_plus');
    }

    /**
     * Init actions
     *
     * @return Mage_Adminhtml_SitemapController
     */
    protected function _initAction()
    {

        // load layout, set active menu and breadcrumbs
        $this->loadLayout();

        $this->_setActiveMenu('catalog/sitemapenhancedplus');

        $this->_addBreadcrumb(Mage::helper('catalog')->__('Catalog'), Mage::helper('catalog')->__('Catalog'));
        $this->_addBreadcrumb(Mage::helper('sitemapEnhancedPlus')->__('Sitemap Enhanced'), Mage::helper('sitemapEnhancedPlus')->__('Sitemap Enhanced'));

        return $this;
    }

    /**
     * Index action
     */
    public function indexAction()
    {

        $this->_title($this->__('Catalog'))->_title($this->__('Sitemap Enhanced'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_initAction();

        // moved in layout file
//                ->_addContent($this->getLayout()->createBlock('sitemapEnhancedPlus/adminhtml_sitemapEnhancedPlus'))
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('sitemapEnhancedPlus/adminhtml_sitemapEnhancedPlus_grid')->toHtml()
        );
    }

    /**
     * Create new sitemap
     */
    public function newAction()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }

    /**
     * Edit sitemap
     */
    public function editAction()
    {
        $this->_title($this->__('Catalog'))->_title($this->__('Sitemap Enhanced'));

        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('sitemap_id');
        $model = Mage::getModel('sitemapEnhancedPlus/sitemapEnhancedPlus');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('sitemapEnhancedPlus')->__('This sitemap no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getSitemapFilename() : $this->__('New Sitemap'));

        // 3. Set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        Mage::register('sitemapEnhancedPlus_sitemap', $model);

        // 5. Build edit form
        $this->_initAction()
            ->_addBreadcrumb(
                $id ? Mage::helper('sitemapEnhancedPlus')->__('Edit Sitemap') : Mage::helper('sitemapEnhancedPlus')->__('New Sitemap'), $id ? Mage::helper('sitemapEnhancedPlus')->__('Edit Sitemap') : Mage::helper('sitemapEnhancedPlus')->__('New Sitemap'))
            ->_addContent($this->getLayout()->createBlock('sitemapEnhancedPlus/adminhtml_sitemapEnhancedPlus_edit'))
            ->renderLayout();
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        // check if data sent
        if ($data = $this->getRequest()->getPost()) {

            //validate path to generate
            if (!empty($data['sitemap_filename']) && !empty($data['sitemap_path'])) {

                $path = rtrim($data['sitemap_path'], '\\/')
                    . DS . $data['sitemap_filename'];

                /** @var $validator Mage_Core_Model_File_Validator_AvailablePath */
                $validator = Mage::getModel('core/file_validator_availablePath');
                /* 1.4 doesn't have validator class */
                if ($validator) {
                    /** @var $helper Mage_Adminhtml_Helper_Catalog */
                    $helper = Mage::helper('adminhtml/catalog');
                    $validator->setPaths($helper->getSitemapValidPaths());
                    $validator->addAvailablePath('/*/*');
                    if (!$validator->isValid($path)) {
                        foreach ($validator->getMessages() as $message) {
                            Mage::getSingleton('adminhtml/session')->addError($message);
                        }
                        // save data in session
                        Mage::getSingleton('adminhtml/session')->setFormData($data);
                        // redirect to edit form
                        $this->_redirect('*/*/edit', array(
                            'sitemap_id' => $this->getRequest()->getParam('sitemap_id')));
                        return;
                    }
                }
            }
            // init model and set data
            $sitemap_id = $this->getRequest()->getParam('sitemap_id');
            /* @var $model CueBlocks_SitemapEnhancedPlus_Model_SitemapEnhancedPlus */
            $model = Mage::getModel('sitemapEnhancedPlus/sitemapEnhancedPlus')
                ->load($sitemap_id);

            $model->removeFiles();
            //set data and counters
            $model->setData($data);

            // try to save it
            try {
                // save the data
                $model->save();
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('sitemapEnhancedPlus')->__('The sitemap has been saved.'));
                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('sitemap_id' => $model->getId()));
                    return;
                }
                // go to grid or forward to generate action
                if ($this->getRequest()->getParam('generate')) {
                    $this->getRequest()->setParam('sitemap_id', $model->getId());
                    if ($this->getRequest()->getParam('generate') == '2')
                        $this->getRequest()->setParam('submit', '1');
                    $this->_forward('generate');
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // save data in session
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                // redirect to edit form
                $this->_redirect('*/*/edit', array(
                    'sitemap_id' => $this->getRequest()->getParam('sitemap_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Generate sitemap
     */
    public function generateAction()
    {
        // init and load sitemap model
        $id = $this->getRequest()->getParam('sitemap_id');
        $sitemap = Mage::getModel('sitemapEnhancedPlus/sitemapEnhancedPlus');
        // ajax var ( popoup action ... not implemented yet)
        $popup = $this->getRequest()->getPost('popup');

        /* @var $sitemap Mage_Sitemap_Model_Sitemap */
        $sitemap->load($id);
        $sitemap->removeFiles();

        // if sitemap record exists
        if ($sitemap->getId()) {
            try {
                // Generate Sitemap
                $sitemap->generateXml();

                if ($warning = $sitemap->genWarnReport()) {
                    $this->_getSession()->addNotice($warning);
                }

                $this->_getSession()->addSuccess(
                    Mage::helper('sitemapEnhancedPlus')->__($sitemap->genNoticeReport()));

            } catch (Mage_Core_Exception $e) {
                $this->getResponse()->setBody($e->getMessage());
                $this->_getSession()->addError($e->getMessage());

            } catch (Exception $e) {
                $this->_getSession()->addException($e, Mage::helper('sitemapEnhancedPlus')->__('Unable to generate the sitemap.' . $e->getMessage()));
            }
        } else {
            $this->_getSession()->addError(
                Mage::helper('sitemapEnhancedPlus')->__('Unable to find a sitemap to generate.'));
        }

        if (!$popup)
            // go to grid
        $this->_redirect('*/*/');
    }

    /**
     * Generate sitemap
     */
    public function generatepopupAction()
    {
        $this->loadLayout();

        // init and load sitemap model
        $id = $this->getRequest()->getParam('sitemap_id');
        $sitemap = Mage::getModel('sitemapEnhancedPlus/sitemapEnhancedPlus');

        $this->renderLayout();
    }

    public function pingAction()
    {
        // init and load sitemap model
        $id = $this->getRequest()->getParam('sitemap_id');
        $sitemap = Mage::getModel('sitemapEnhancedPlus/sitemapEnhancedPlus');
        /* @var $sitemap Mage_Sitemap_Model_Sitemap */
        $sitemap->load($id);
        // if sitemap record exists
        if ($sitemap->getId()) {
            try {
                $msg = $sitemap->ping();

                $this->_getSession()->addSuccess(
                    Mage::helper('sitemapEnhancedPlus')->__('Sitemap submitted:<br/>' . $msg));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e, Mage::helper('sitemapEnhancedPlus')->__('Unable to submit the sitemap.' . $e));
            }
        } else {
            $this->_getSession()->addError(
                Mage::helper('sitemapEnhancedPlus')->__('Unable to find a sitemap to submit.'));
        }

        // go to grid
        $this->_redirect('*/*/');
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        // check if we know what should be deleted
        if ($id = $this->getRequest()->getParam('sitemap_id')) {
            try {
                // init model and delete
                $model = Mage::getModel('sitemapEnhancedPlus/sitemapEnhancedPlus');
                $model->setId($id);
                // init and load sitemap model

                /* @var $sitemap Mage_Sitemap_Model_Sitemap */
                $model->load($id);

                // delete file
                $model->removeFiles();

                $model->delete();
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('sitemapEnhancedPlus')->__('The sitemap has been deleted.'));
                // go to grid
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // go back to edit form
                $this->_redirect('*/*/edit', array('sitemap_id' => $id));
                return;
            }
        }
        // display error message
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('sitemapEnhancedPlus')->__('Unable to find a sitemap to delete.'));
        // go to grid
        $this->_redirect('*/*/');
    }

}
