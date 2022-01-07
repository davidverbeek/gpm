<?php
/**
 * @category   Scommerce
 * @package    Scommerce_GDPR
 * @author     Scommerce Mage <core@scommerce-mage.co.uk>
 */
class Scommerce_GDPR_CustomerController extends Mage_Core_Controller_Front_Action
{
    /**
     * Render page with delete confirmation link
     */
    public function deletionAction()
    {
        if (! $this->getCustomerSession()->isLoggedIn()) {
            $this->_redirect('customer/account/login');
            return;
        }
		if (! $this->h()->isDeletionEnabledOnFrontend()) {
			$this->_redirect('customer/account/login');
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }
    /**
     * Render page with export gdpr data link
     */
    public function exportgdprdataAction()
    {
        if (! $this->getCustomerSession()->isLoggedIn()) {
            $this->_redirect('customer/account/login');
            return;
        }
        if (! $this->h()->isDeletionEnabledOnFrontend()) {
            $this->_redirect('customer/account/login');
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Export customer data admin action
     */
    public function exportAction()
    {
        if (! $this->h()->isEnabled()) {
            return;
        }

        if (!Mage::getSingleton('customer/session')->isLoggedIn()){
            return;
        }
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        // $customer = $this->getCustomer();
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
     * Delete cookie notification cookie to change customer cookie preference
     */
    public function cookieresetAction()
    {
		Mage::getModel('core/cookie')->delete($this->h()->getCookieKey());
		Mage::getModel('core/cookie')->delete($this->h()->getCookieClosedKey());
        $this->_redirectReferer();
		return;
    }

    /**
     * Self-delete customer account action from front
     *
     * @throws Mage_Core_Exception
     * @throws Varien_Exception
     */
    public function deleteAction()
    {
        if (! $this->h()->isDeletionEnabledOnFrontend()) {
            return;
        }

        $customer = $this->getCustomerSession()->getCustomer();
        if (! $customer->getId()) {
            $this->getCustomerSession()->setBeforeAuthUrl($this->h()->getDeletUrl());
            $this->_redirect('customer/account/login');
            return;
        }

        Mage::register('isSecureArea', true);
        Mage::getModel('scommerce_gdpr/account')->anonymise($customer);
        Mage::unregister('isSecureArea');

        $this->getSession()->clear();
        $this->_redirect('/index.php');

        $this->getSession()->addSuccess($this->h()->getSuccessMessage());
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
     * Helper method for get customer session
     *
     * @return Mage_Core_Model_Abstract|Mage_Customer_Model_Session
     */
    private function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }
}