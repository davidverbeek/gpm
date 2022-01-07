<?php
/**
 * @category   Scommerce
 * @package    Scommerce_GDPR
 * @author     Scommerce Mage <core@scommerce-mage.co.uk>
 */
class Scommerce_GDPR_Model_Account
{
	protected $_tab;
    
	protected $_cr;
	

	public function __construct() {
		$this->_tab = ",";
		$this->_cr = "\r\n"; 
	}	
    /**
     * Remove customer details from Magento
     *
     * @param Mage_Customer_Model_Customer $customer
     * @throws \Exception
     */
    public function anonymise($customer)
    {
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customer->getId());
        foreach ($orders as $order) {
            $this->anonymiseSale($order);
        }

        $quotes = Mage::getModel('sales/quote')->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customer->getId());
        foreach($quotes as $quote) {
            $this->anonymiseSale($quote);
        }

        /** @var Mage_Newsletter_Model_Subscriber $subscriber */
        $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
        if ($subscriber->getId()) {
            $subscriber->unsubscribe();
            $subscriber->delete();
        }

        /** @var Mage_Core_Model_Email_Template $email */
		$this->h()->sendEmail($this->h()->getSuccessDeletionEmailTemplate(), $customer->getName(), $customer->getEmail());
		
        $customer->delete();
    }
	
	/**
     * export customer details from Magento
     *
     * @param Mage_Customer_Model_Customer $customer
     * @throws \Exception
     */
    public function exportData($customer)
    {
		$customerData = "";
		
		$salesDataHeader = '"First Name"'.$this->_tab.'"Middle Name"'.$this->_tab.'"Last Name"'.$this->_tab.'"Email Address"'.$this->_tab.'"Remote IP"'.$this->_tab;
		$salesDataHeader .= '"Billing First Name"'.$this->_tab.'"Billing Middle Name"'.$this->_tab.'"Billing Last Name"'.$this->_tab;
		$salesDataHeader .= '"Billing Company"'.$this->_tab.'"Billing Email Address"'.$this->_tab.'"Billing Region"'.$this->_tab.'"Billing Street"'.$this->_tab;
		$salesDataHeader .= '"Billing City"'.$this->_tab.'"Billing Post Code"'.$this->_tab.'"Billing Telephone"'.$this->_tab.'"Billing Fax"'.$this->_tab;
		$salesDataHeader .= '"Billing VAT"'.$this->_tab.'"Shipping First Name"'.$this->_tab.'"Shipping Middle Name"'.$this->_tab.'"Shipping Last Name"'.$this->_tab;
		$salesDataHeader .= '"Shipping Company"'.$this->_tab.'"Shipping Email Address"'.$this->_tab.'"Shipping Region"'.$this->_tab.'"Shipping Street"'.$this->_tab;
		$salesDataHeader .= '"Shipping City"'.$this->_tab.'"Shipping Post Code"'.$this->_tab.'"Shipping Telephone"'.$this->_tab.'"Shipping Fax"'.$this->_tab.'"Shipping VAT"';
		
		//Customer Details
		if ($customer->getEmail()){
			$header="Customer Details".$this->_cr;
			$header.='"Prefix"'.$this->_tab.'"First Name"'.$this->_tab.'"Middle Name"'.$this->_tab.'"Last Name"'.$this->_tab.'"Email Address"'.$this->_tab.'"DOB"'.$this->_tab.'"Gender"';
			$details=$header.$this->_cr;
			$details.='"'.$customer->getPrefix().'"'.$this->_tab.'"'.$customer->getFirstname().'"'.$this->_tab.'"'.$customer->getMiddlename().'"'.$this->_tab;
			$details.='"'.$customer->getLastname().'"'.$this->_tab.'"'.$customer->getEmail().'"'.$this->_tab.'"'.$customer->getDob().'"'.$this->_tab.'"'.$this->getCustomerAttrText($customer,'gender').'"'.$this->_cr;
			$customerData .=$details.$this->_cr;
		}
		
		//Customer Addresses
		$addresses = $customer->getAddresses();
		if (!empty($addresses)){
			$header="Customer Address Details".$this->_cr;
			$header.='"Prefix"'.$this->_tab.'"First Name"'.$this->_tab.'"Middle Name"'.$this->_tab.'"Last Name"'.$this->_tab.'"Company"'.$this->_tab.'"Region"'.$this->_tab;
			$header.='"Street"'.$this->_tab.'"City"'.$this->_tab.'"Post Code"'.$this->_tab.'"Telephone"'.$this->_tab.'"Fax"'.$this->_tab.'"VAT"';
			$details=$header.$this->_cr;
			foreach ($addresses as $address) {
				$details.='"'.$address->getPrefix().'"'.$this->_tab.'"'.$address->getFirstname().'"'.$this->_tab.'"'.$address->getMiddlename().'"'.$this->_tab;
				$details.='"'.$address->getLastname().'"'.$this->_tab.'"'.$address->getCompany().'"'.$this->_tab.'"'.$address->getRegion().'"'.$this->_tab;
				$details.='"'.$this->getStreet($address->getStreet()).'"'.$this->_tab.'"'.$address->getCity().'"'.$this->_tab.'"'.$address->getPostcode().'"'.$this->_tab;
				$details.='"'.$address->getTelephone().'"'.$this->_tab.'"'.$address->getFax().'"'.$this->_tab.'"'.$address->getVatId().'"'.$this->_cr;
			}
			$customerData .=$details.$this->_cr;
		}
		
		//Order Details
		$orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customer->getId());
		
		if ($orders->count()>0){
			$header="Order Details".$this->_cr;
			$header.='"Order number"'.$this->_tab.$salesDataHeader;
			$details=$header.$this->_cr;
			foreach ($orders as $order) {
				$details.='"'.$order->getIncrementId().'"'.$this->_tab.$this->exportSale($order).$this->_cr;
			}
			$customerData .=$details.$this->_cr;
		}		
		
		//Quotes Details
		$quotes = Mage::getModel('sales/quote')->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customer->getId())
			->addFieldToFilter('processed_value', array('null'=>true));

		if ($quotes->count()>0){
			$header="Quote Details".$this->_cr;
			$header.=$salesDataHeader;
			$details=$header.$this->_cr;
			foreach($quotes as $quote) {
				$details.=$this->exportSale($quote).$this->_cr;
			}
			$customerData .=$details.$this->_cr;
		}
		
		//Newsletter Subscription Details
        /** @var Mage_Newsletter_Model_Subscriber $subscriber */
        $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
		
        if ($subscriber->getId()) {
			$header="Newsletter Subscription Details".$this->_cr;
			$header.='"Subscriber Email"'.$this->_tab.'"Status"';
			$detail=$header.$this->_cr;
			$detail.='"'.$subscriber->getSubscriberEmail().'"'.$this->_tab.'"'.$this->subscriberStatus($subscriber->getSubscriberStatus()).'"'.$this->_cr;
			
			$customerData .=$detail;
        }
		
		return $customerData;
	}
	
	/**
     * get customer details from a quote or order
     *
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Quote $obj
     */
    public function exportSale(&$obj) {
        $data = '"'.$obj->getCustomerFirstname().'"'.$this->_tab.'"'.$obj->getCustomerMiddlename().'"'.$this->_tab.'"'.$obj->getCustomerLastname().'"'.$this->_tab;
		$data .= '"'.$obj->getCustomerEmail().'"'.$this->_tab.'"'.$obj->getRemoteIp().'"'.$this->_tab;
        $address = $obj->getBillingAddress();
        $data.= $this->exportAddress($address);
        $address = $obj->getShippingAddress();
		if ($address) $data.= $this->_tab.$this->exportAddress($address);
        return $data;
    }
	
	/**
     * get customer details from the address
     *
     * @param Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address $address
     */
    private function exportAddress(&$address) {
        if (! (
            $address instanceof Mage_Sales_Model_Order_Address ||
            $address instanceof Mage_Sales_Model_Quote_Address
        )) {
            return;
        }
		$streetAddress = $this->getStreet($address->getStreet());
		$data = '"'.$address->getFirstname().'"'.$this->_tab.'"'.$address->getMiddlename().'"'.$this->_tab.'"'.$address->getLastname().'"'.$this->_tab;
		$data .= '"'.$address->getCompany().'"'.$this->_tab.'"'.$address->getEmail().'"'.$this->_tab.'"'.$address->getRegion().'"'.$this->_tab.'"'.$streetAddress.'"'.$this->_tab;
		$data .= '"'.$address->getCity().'"'.$this->_tab.'"'.$address->getPostcode().'"'.$this->_tab.'"'.$address->getTelephone().'"'.$this->_tab;
		$data .= '"'.$address->getFax().'"'.$this->_tab.'"'.$address->getVatId().'"';
		return $data;
    }
	
	/**
     * return option text value of the dropdown attribute
     *
     * @param $customer Mage_Customer_Model_Customer 
     * @return $attribute string
     */
	private function getCustomerAttrText($customer, $attribute)
	{
		return $customer->getResource()->getAttribute('gender')
										->getSource()
										->getOptionText($customer->getData($attribute));
	}
	
	/**
     * return newsletter status description
     *
     * @param $status int 
     * @return $status string
     */
	private function subscriberStatus($status)
	{
		switch ($status){
			case 1:
				return $this->h()->__("Subscribed");
				break;
			case 2:
				return $this->h()->__("Not Active");
				break;
			case 3:
				return $this->h()->__("Unsubscribed");
				break;
			case 4:
				return $this->h()->__("Unconfirmed");
				break;
		}
	}
	
	/**
     * get street details as string
     *
     * @param array
     */
	private function getStreet($street)
	{
		if (is_array($street)){
			return implode(' ',$street);
		}
		else{
			return $street;
		}
	}

    /**
     * Remove customer details from a quote or order
     *
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Quote $obj
     */
    public function anonymiseSale(&$obj) {
        $obj->setCustomerFirstname($this->anonymise_data());
        $obj->setCustomerMiddlename($this->anonymise_data());
        $obj->setCustomerLastname($this->anonymise_data());
        $obj->setCustomerEmail($this->obfuscate_email($obj->getCustomerEmail()));
		$obj->setRemoteIp($this->obfuscate_ipaddress($obj->getRemoteIp()));
		$obj->setCustomerDob($this->anonymise_data());
		$address = $obj->getBillingAddress();
        $this->anonymiseAddress($address);
        $address = $obj->getShippingAddress();
		$this->anonymiseAddress($address);
        $obj->save();
    }

    /**
     * Remove customer details from the address
     *
     * @param Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address $address
     */
    private function anonymiseAddress(&$address) {
        if (! (
            $address instanceof Mage_Sales_Model_Order_Address ||
            $address instanceof Mage_Sales_Model_Quote_Address
        )) {
            return;
        }
        $address->setFirstname($this->anonymise_data());
        $address->setMiddlename($this->anonymise_data());
        $address->setLastname($this->anonymise_data());
        $address->setCompany($this->anonymise_data());
        $address->setEmail($this->obfuscate_email($address->getEmail()));
        $address->setRegion($this->anonymise_data());
        $address->setStreet($this->anonymise_data());
        $address->setCity($this->anonymise_data());
        $address->setPostcode($this->anonymise_data());
        $address->setTelephone($this->anonymise_data());
        $address->setFax($this->anonymise_data());
    }

	 /**
     * Generate anonymised string
     *
     * @return string
     */
	private function anonymise_data($length=10){
		$data = '';
		$keys = array_merge(range(0, 9), range('a', 'z'));
		for ($i = 0; $i < $length; $i++) {
			$data .= $keys[array_rand($keys)];
		}
		return $data;
	}

    /**
     * Generate anonymised email
     *
     * @return string
     */
    private function obfuscate_email($email)
    {
        $em   = explode("@",$email);
		
		//before @
		$name = implode(array_slice($em, 0, count($em)-1), '@');
		$len  = floor(strlen($name)/2);
		$name = substr($name,0, $len) . str_repeat('*', $len);
		
		//after @
		$domain = end($em);
		$len  = floor(strlen($domain)/2);
		$domain = str_repeat('*', $len). substr(end($em),$len,strlen($domain));

		return  $name. "@" .$domain ; 
    }
	
	/**
     * Generate anonymised ip address
     *
     * @return string
     */
    private function obfuscate_ipaddress($remote_ip)
    {
        return preg_replace('~(\d+)\.(\d+)\.(\d+)\.(\d+)~', "$1.$2.x.x", $remote_ip);
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