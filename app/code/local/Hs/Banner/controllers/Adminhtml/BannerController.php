<?php

class Hs_Banner_Adminhtml_BannerController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed()
	{
		//return Mage::getSingleton('admin/session')->isAllowed('banner/banner');
		return true;
	}

	protected function _initAction()
	{
		$this->loadLayout()->_setActiveMenu("banner/banner")->_addBreadcrumb(Mage::helper("adminhtml")->__("Banner  Manager"), Mage::helper("adminhtml")->__("Banner Manager"));
		return $this;
	}

	public function indexAction()
	{
		$this->_title($this->__("Banner"));
		$this->_title($this->__("Manager Banner"));

		$this->_initAction();
		$this->renderLayout();
	}

	public function editAction()
	{
		$this->_title($this->__("Banner"));
		$this->_title($this->__("Banner"));
		$this->_title($this->__("Edit Item"));

		$id = $this->getRequest()->getParam("id");
		$model = Mage::getModel("banner/banner")->load($id);

		if ($model->getId()) {
			Mage::register("banner_data", $model);
			$this->loadLayout();
			$this->_setActiveMenu("banner/banner");
			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Banner Manager"), Mage::helper("adminhtml")->__("Banner Manager"));
			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Banner Description"), Mage::helper("adminhtml")->__("Banner Description"));
			$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock("banner/adminhtml_banner_edit"))->_addLeft($this->getLayout()->createBlock("banner/adminhtml_banner_edit_tabs"));
			$this->renderLayout();
		} else {
			Mage::getSingleton("adminhtml/session")->addError(Mage::helper("banner")->__("Item does not exist."));
			$this->_redirect("*/*/");
		}
	}

	public function newAction()
	{

		$this->_title($this->__("Banner"));
		$this->_title($this->__("Banner"));
		$this->_title($this->__("New Item"));

		$id = $this->getRequest()->getParam("id");
		$model = Mage::getModel("banner/banner")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("banner_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("banner/banner");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Banner Manager"), Mage::helper("adminhtml")->__("Banner Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Banner Description"), Mage::helper("adminhtml")->__("Banner Description"));

		$this->_addContent($this->getLayout()->createBlock("banner/adminhtml_banner_edit"))->_addLeft($this->getLayout()->createBlock("banner/adminhtml_banner_edit_tabs"));

		$this->renderLayout();

	}

	public function saveAction()
	{
		$post_data = $this->getRequest()->getPost();
		if ($post_data) {
			try {
				//save image
				try {
					if ((bool)$post_data['image']['delete'] == 1) {
						$post_data['image'] = '';
					} else {
						unset($post_data['image']);
						if (isset($_FILES)) {
							if ($_FILES['image']['name']) {
								if ($this->getRequest()->getParam("id")) {
									$model = Mage::getModel("banner/banner")->load($this->getRequest()->getParam("id"));
									if ($model->getData('image')) {
										$io = new Varien_Io_File();
										$io->rm(Mage::getBaseDir('media') . DS . implode(DS, explode('/', $model->getData('image'))));
									}
								}
								$path = Mage::getBaseDir('media') . DS . 'banner' . DS . 'banner' . DS;
								$uploader = new Varien_File_Uploader('image');
								$uploader->setAllowedExtensions(array('jpg', 'png', 'gif'));
								$uploader->setAllowRenameFiles(false);
								$uploader->setFilesDispersion(false);
								$destFile = $path . $_FILES['image']['name'];
								$filename = $uploader->getNewFileName($destFile);
								$uploader->save($path, $filename);

								$post_data['image'] = 'banner/banner/' . $filename;
							}
						}
					}

				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
					$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
					return;
				}
				//save image
				$category_data = "";
				if (isset($post_data['category_ids']) && !empty($post_data['category_ids'])) {
					$category_data = array_filter(array_unique(explode(",", $post_data['category_ids'])));
				}

				$model = Mage::getModel("banner/banner")
					->addData($post_data)
					->setId($this->getRequest()->getParam("id"))
					->save();

				if ($model->getId()) {
					$collection = Mage::getModel('banner/bannercategory')->getCollection()
						->addFieldToFilter('banner_id', $model->getId());
					$collection->walk('delete');
				}

				//echo "<pre>"; print_r($category_data); die;
				if (!empty($category_data)) {
					foreach ($category_data as $key => $cat_id) {
						$cat_model = Mage::getModel("banner/bannercategory");
						$temp = array();
						$temp['banner_id'] = $model->getId();
						$temp['category_id'] = $cat_id;
						$cat_model->addData($temp);
						$cat_model->save();
					}
				}

				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Banner was successfully saved"));
				Mage::getSingleton("adminhtml/session")->setBannerData(false);

				if ($this->getRequest()->getParam("back")) {
					$this->_redirect("*/*/edit", array("id" => $model->getId()));
					return;
				}
				$this->_redirect("*/*/");
				return;
			} catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
				Mage::getSingleton("adminhtml/session")->setBannerData($this->getRequest()->getPost());
				$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
				return;
			}
		}
		$this->_redirect("*/*/");
	}

	public function deleteAction()
	{
		if ($this->getRequest()->getParam("id") > 0) {
			try {
				$model = Mage::getModel("banner/banner");
				$model->setId($this->getRequest()->getParam("id"))->delete();
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
				$this->_redirect("*/*/");
			} catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
				$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
			}
		}

		$this->_redirect("*/*/");
	}

	public function massRemoveAction()
	{
		try {
			$ids = $this->getRequest()->getPost('banner_ids', array());
			foreach ($ids as $id) {
				$model = Mage::getModel("banner/banner");
				$model->setId($id)->delete();
			}
			Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
		} catch (Exception $e) {
			Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
		}

		$this->_redirect('*/*/');
	}

	public function categoriesAction()
	{
		$bannerId = $this->getRequest()->getParam('id');
		if (isset($bannerId) && !empty($bannerId)) {
			$categorytab = Mage::getModel('banner/bannercategory')->getCollection()
				->addFieldToSelect('category_id')
				->addFieldToFilter('banner_id', $bannerId);
			$array = array_column($categorytab->getData(), 'category_id');
			Mage::register('categorytab_data', $array);
		}

		$this->getResponse()->setBody(
			$this->getLayout()
				->createBlock('banner/adminhtml_banner_edit_tab_category')
				->toHtml()
		);
	}

	public function categoriesJsonAction()
	{
		$bannerId = $this->getRequest()->getParam('id');
		if (isset($bannerId) && !empty($bannerId)) {
			$categorytab = Mage::getModel('banner/bannercategory')->getCollection()
				->addFieldToSelect('category_id')
				->addFieldToFilter('banner_id', $bannerId);
			$array = array_column($categorytab->getData(), 'category_id');
			Mage::register('categorytab_data', $array);
		}

		$this->getResponse()->setBody(
			$this->getLayout()
				->createBlock('banner/adminhtml_banner_edit_tab_category')
				->getCategoryChildrenJson($this->getRequest()->getParam('category'))
		);
	}
}
