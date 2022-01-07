<?php
/**
 * @category   Scommerce
 * @package    Scommerce_GDPR
 * @author     Scommerce Mage <core@scommerce-mage.co.uk>
 */
class Scommerce_GDPR_Adminhtml_Scommerce_GdprController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Delete customer admin action
     */
    public function deleteAction()
    {
        if (! $this->h()->isEnabled()) {
            return;
        }

        $customer = $this->getCustomer();
        if (! $customer->getId()) {
            $this->getSession()->addError($this->__('No customer ID provided.'));
            $this->_redirect('adminhtml/dashboard/index');
            return;
        }

        Mage::getModel('scommerce_gdpr/account')->anonymise($customer);

        $this->_redirect('adminhtml/customer/index');
        $this->getSession()->addSuccess($this->__('The account has been successfully deleted, and all orders have been anonymised.'));
        return;
    }
	
	/**
     * Export customer data admin action
     */
    public function exportAction()
    {
        if (! $this->h()->isEnabled()) {
            return;
        }

        $customer = $this->getCustomer();
        if (! $customer->getId()) {
            $this->getSession()->addError($this->__('No customer ID provided.'));
            $this->_redirect('adminhtml/dashboard/index');
            return;
        }

        $file = Mage::getModel('scommerce_gdpr/account')->exportData($customer);

        $this->_prepareDownloadResponse('customer'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.csv', $file);
        return;
    }

    /**
     * Send anonymise email admin action
     */
    public function emailAction()
    {
        if (! $this->h()->isEnabled()) {
            return;
        }

        $customer = $this->getCustomer();
        if (! $customer->getId()) {
            $this->getSession()->addError($this->__('No customer ID provided.'));
            $this->_redirect('*');
            return;
        }

        /** @var Mage_Core_Model_Email_Template $email */
		$this->h()->sendEmail($this->h()->getConfirmationEmailTemplate(), $customer->getName(), $customer->getEmail(), array('link' => $this->h()->getDeletUrl()));
        $this->_redirect('adminhtml/customer/edit', array('id' => $customer->getId()));
        $this->getSession()->addSuccess($this->__('Deletion and Anonymisation link has been successfully sent to the customer.'));
    }
	
	public function anonymisationAction()
    {
		if (! $this->h()->isEnabled()) {
            return;
        }
        $orders = $this->getRequest()->getPost('order_ids', array());
		foreach ($orders as $order) {
            $objOrder = Mage::getModel('sales/order')->load($order);
			Mage::getModel('scommerce_gdpr/account')->anonymiseSale($objOrder);
        }
		$this->_redirect('adminhtml/sales_order/index');
		$this->getSession()->addSuccess($this->__('All selected orders have been successfully anonymised.'));
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/scommerce_gdpr');
    }

    /**
     * Helper method for get helper
     *
     * @return Scommerce_GDPR_Helper_Data
     */
    private function h()
    {
        return Mage::helper('scommerce_gdpr');
    }

    /**
     * Helper method for get core session
     *
     * @return Mage_Core_Model_Abstract|Mage_Core_Model_Session
     */
    private function getSession()
    {
        return Mage::getSingleton('core/session');
    }

    /**
     * Get customer via request
     *
     * @return Mage_Core_Model_Abstract|Mage_Customer_Model_Customer
     */
    private function getCustomer()
    {
        return Mage::getModel('customer/customer')->load($this->getRequest()->getParam('id'));
    }
}