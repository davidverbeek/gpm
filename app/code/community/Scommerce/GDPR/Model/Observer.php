<?php
/**
 * @category   Scommerce
 * @package    Scommerce_GDPR
 * @author     Scommerce Mage <core@scommerce-mage.co.uk>
 */

class Scommerce_GDPR_Model_Observer
{
    /**
     * Add buttons (delete, send emails) to the customer edit page
     *
     * @param Varien_Event_Observer $observer
     */
    public function addButtons($observer)
    {
        /** @var Mage_Adminhtml_Block_Customer_Edit $block */
        $block = $observer->getBlock();
        if ($block->getType() != 'adminhtml/customer_edit') {
            return;
        }

        if (! $this->h()->isEnabled()) {
            return;
        }

        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::registry('current_customer');
        if (! $customer->getId()) {
            return;
        }

        // Add delete customer button
        $block->addButton('scommerce_gdpr_delete_button', array(
            'label' => Mage::helper('adminhtml')->__('Delete Personal Data'),
            'onclick' => 'setLocation(\'' . $this->getDeleteUrl($customer->getId()) . '\')',
        ));
		
        // Add send email button
        $block->addButton('scommerce_gdpr_email_button', $data = array(
            'label' => Mage::helper('adminhtml')->__('Send Deletion link To Customer'),
            'onclick' => 'setLocation(\'' . $this->getEmailUrl($customer->getId()) . '\')',
        ));
		
		// Add export button
        $block->addButton('scommerce_gdpr_export_button', $data = array(
            'label' => Mage::helper('adminhtml')->__('Export GDPR Data'),
            'onclick' => 'setLocation(\'' . $this->getExportUrl($customer->getId()) . '\')',
        ));
    }
	
	/**
     * Extends the mass action select box to append the option to anonymise transaction data
     * Event: core_block_abstract_prepare_layout_before
     */
    public function addMassaction($observer) 
    {
		if (! $this->h()->isEnabled()) {
            return;
        }
		$block = $observer->getEvent()->getBlock();
		if(get_class($block) ==$this->h()->getOverrideAdminMassActionBlock()
				&& $block->getRequest()->getControllerName() == 'sales_order') {
			try{
				$block->addItem('orderactions', array(
				'label' => Mage::helper('adminhtml')->__('Anonymise Orders'),
				'url' => Mage::helper('adminhtml')->getUrl('adminhtml/scommerce_gdpr/anonymisation')
				));
			}	
			catch(Exception $e){
			}
		}
	}
	
	/**
     * Processed value field needs to updated to null in quote and quote address table as soon something changes in the existing quote
     *
     * @param Varien_Event_Observer $observer
     */
	public function updateProcessedValue($observer)
	{
		$quote = $observer->getCart()->getQuote();
		
		//Making sure we clear the processed flag to null as soon customer comes back so it can be set to null as part of GDPR cron job
		$quote->setProcessedValue(null)
			->save();
		$quote->getBillingAddress()
				->setProcessedValue(null)
				->save();
		$quote->getShippingAddress()
				->setProcessedValue(null)
				->save();
	}
	
	/**
     * change_status_at field needs to updated every time the newsletter gets saved
     *
     * @param Varien_Event_Observer $observer
     */
	public function newsletterSaveBefore($observer)
	{
		$model = $observer->getObject();
		if (!$model instanceof Mage_Newsletter_Model_Subscriber) {
			return;
		}
		$model->setChangeStatusAt(date('Y-m-d H:i'));
		return $model;
	}
	
    /**
     * Get customer delete url
     *
     * @param int $customerId
     * @return string
     */
    private function getDeleteUrl($customerId)
    {
        return Mage::helper('adminhtml')->getUrl(
            'adminhtml/scommerce_gdpr/delete',
            array('id' => $customerId)
        );
    }
	
	/**
     * Get customer export url
     *
     * @param int $customerId
     * @return string
     */
    private function getExportUrl($customerId)
    {
        return Mage::helper('adminhtml')->getUrl(
            'adminhtml/scommerce_gdpr/export',
            array('id' => $customerId)
        );
    }

    /**
     * Get send email url
     *
     * @param int $customerId
     * @return string
     */
    private function getEmailUrl($customerId)
    {
        return Mage::helper('adminhtml')->getUrl(
            'adminhtml/scommerce_gdpr/email',
            array('id' => $customerId)
        );
    }
	
	/**
     * Helper method for getting helper instance
     *
     * @return Scommerce_GDPR_Helper_Data
     */
    private function h()
    {
        return Mage::helper("scommerce_gdpr");
    }
	
}
