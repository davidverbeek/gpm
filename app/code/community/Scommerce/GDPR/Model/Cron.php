<?php
/**
 * @category   Scommerce
 * @package    Scommerce_GDPR
 * @author     Scommerce Mage <core@scommerce-mage.co.uk>
 */
class Scommerce_GDPR_Model_Cron
{
    /**
     * Clear personal data in quote
     */
    public function clear()
    {
        if (!$this->getCookieQuoteExpire() || !$this->h()->isEnabled()) {
            return;
        }
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $resource = Mage::getResourceModel('sales/quote');
        $connection->update(
            $resource->getMainTable(),
            array(
                'processed_value' => 1,
                'customer_email' => null,
                'customer_prefix' => null,
                'customer_firstname' => null,
                'customer_middlename' => null,
                'customer_lastname' => null,
                'customer_suffix' => null,
                'customer_dob' => null,
                'customer_note' => null,
				'remote_ip' => null
            ),
            'is_active = 1 and processed_value IS NULL and (TO_DAYS(NOW()) - TO_DAYS(updated_at)) > ' . $this->getCookieQuoteExpire()
        );
		
		$resource = Mage::getResourceModel('sales/quote_address');
		$connection->update(
            $resource->getMainTable(),
            array(
                'processed_value' => 1,
                'email' => null,
                'prefix' => null,
                'firstname' => null,
                'middlename' => null,
                'lastname' => null,
                'suffix' => null,
                'company' => null,
				'street' => null,
				'city' => null,
				'region' => null,
				'region_id' => null,
				'postcode' => null,
				'telephone' => null,
				'fax' => null,
				'vat_id' => null
            ),
            'processed_value IS NULL and (TO_DAYS(NOW()) - TO_DAYS(updated_at)) > ' . $this->getCookieQuoteExpire()
        );
    }

    /**
     * @return string
     */
    private function getCookieQuoteExpire()
    {
        return $this->h()->getCookieQuoteExpire();
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
}