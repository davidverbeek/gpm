<?php

/**
 *  ICEPAY Advanced - Webservice Order
 * 
 *  @version 1.0.0
 *  @author Wouter van Tilburg <wouter@icepay.eu>
 *  @copyright ICEPAY <www.icepay.com>
 *  
 *  Disclaimer:
 *  The merchant is entitled to change de ICEPAY plug-in code,
 *  any changes will be at merchant's own risk.
 *  Requesting ICEPAY support for a modified plug-in will be
 *  charged in accordance with the standard ICEPAY tariffs.
 * 
 */
class Hs_Achterafbetalen_Model_Order extends Icepay_IceAdvanced_Model_Order {

    private $data = array();
    private $defaultVATCategories = array();
    private $orderData;
    private $consumerNode;
    private $addressesNode;
    private $productsNode;
	/* added for Achterafbetalen payment method - start */
    private $customerNode;
    private $invoiceAddressNode;
    private $shippingAddressNode;
    private $ibanNode;
    private $orderNode;
    private $orderDetailNode;
    private $amount;
    private $amountVAT;
    private $shippingCost;
    private $shippingCostVAT;
	/* added for Achterafbetalen payment method - end */

    private $debug = false;

    public function __construct()
    {

        parent::__construct();

    }

    public function getProducts()
    {
        return $this->data['products'];
    }

    private function setData($tag, $object)
    {
        $this->data[$tag] = $object;
    }

    public function setShippingAddress(Icepay_Order_Address $shippingAddress)
    {
        $this->setData('shippingAddress', $shippingAddress);
        return $this;
    }

    public function setBillingAddress(Icepay_Order_Address $billingAddress)
    {
        $this->setData('billingAddress', $billingAddress);
        return $this;
    }

    public function setConsumer(Icepay_Order_Consumer $consumer)
    {
        $this->setData('consumer', $consumer);
        return $this;
    }

    public function addProduct(Icepay_Order_Product $product)
    {
        array_push($this->data["products"], $product);
        return $this;
    }

    public function createAddress()
    {
        return new Icepay_Order_Address();
    }

    public function createConsumer()
    {
        return new Icepay_Order_Consumer();
    }

    public function createProduct()
    {
        return new Icepay_Order_Product();
    }

    public function getCategoryForPercentage($number = null, $default = "exempt")
    {
        foreach ($this->defaultVATCategories as $category => $value) {
            if (!is_array($value)) {
                if ($value == $number)
                    return $category;
            }

            if ($number >= $value[0] && $number <= $value[1])
                return $category;
        }

        return $default;
    }

    public function setOrderDiscountAmount($amount, $name = 'Discount', $description = 'Order Discount')
    {
        $obj = $this->createProduct();
        $obj->setProductID('02')
                ->setProductName($name)
                ->setDescription($description)
                ->setQuantity('1')
                ->setUnitPrice(-$amount)
                ->setVATCategory($this->getCategoryForPercentage(-1));

        $this->addProduct($obj);

        return $this;
    }

    public function setShippingCosts($amount, $vat = -1, $name = 'Shipping Costs')
    {
        $obj = $this->createProduct();
        $obj->setProductID('01')
                ->setProductName($name)
                ->setDescription('')
                ->setQuantity('1')
                ->setUnitPrice($amount)
                ->setVATCategory($this->getCategoryForPercentage($vat));

        $this->addProduct($obj);

        return $this;
    }

    private function array_to_xml($childs, $node = 'Order')
    {
        $childs = (array) $childs;

        foreach ($childs as $key => $value) {
            $node->addChild(ucfirst($key), $value);
        }

        return $node;
    }

	private function achterafbetalenArrayToXml($childs, $node = 'Order')
    {
        $childs = (array) $childs;

        foreach ($childs as $key => $value) {
            $node->addChild($key, $value);
        }

        return $node;
    }


    public function getXML()
    {

        $this->orderData = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><Order></Order>");
        $this->consumerNode = $this->orderData->addChild('Consumer');
        $this->addressesNode = $this->orderData->addChild('Addresses');
        $this->productsNode = $this->orderData->addChild('Products');

        // Set Consumer
        $this->array_to_xml($this->data['consumer'], $this->consumerNode);

        // Set Addresses
        $shippingNode = $this->addressesNode->addChild('Address');
        $shippingNode->addAttribute('id', 'shipping');

        $this->array_to_xml($this->data['shippingAddress'], $shippingNode);

        $billingNode = $this->addressesNode->addChild('Address');
        $billingNode->addAttribute('id', 'billing');

        $this->array_to_xml($this->data['billingAddress'], $billingNode);

        // Set Products
        foreach ($this->data['products'] as $product) {
            $productNode = $this->productsNode->addChild('Product');
            $this->array_to_xml($product, $productNode);
        }

        if ($this->debug == true) {
            header("Content-type: text/xml");
            echo $this->orderData->asXML();
            exit;
        }

        return $this->orderData->asXML();
    }

    public function getAchterafbetalenXML()
    {

        $this->orderData = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><achterafBetalen xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\"></achterafBetalen>");/*   */
        $this->customerNode = $this->orderData->addChild('customer');

		// Set iban
        $this->ibanNode = $this->orderData->addChild('iban',$this->data['iban']);

        $this->invoiceAddressNode = $this->orderData->addChild('invoiceAddress');
        $this->shippingAddressNode = $this->orderData->addChild('shippingAddress');
        $this->orderNode = $this->orderData->addChild('order');
        //$this->productsNode = $this->orderData->addChild('Products');

        // Set Customer
        $this->achterafbetalenArrayToXml($this->data['customer'], $this->customerNode);

        // Set Shipping Address
        $this->achterafbetalenArrayToXml($this->data['shippingAddress'], $this->shippingAddressNode);

        // Set billing Address
        $this->achterafbetalenArrayToXml($this->data['invoiceAddress'], $this->invoiceAddressNode);

        // Set Order Amount
		$orderAmountNode = $this->orderNode->addChild('amount', $this->data['orderAmount']);
        //$this->achterafbetalenArrayToXml($this->data['orderAmount'], $orderAmountNode);

        // Set Order Amount VAT
		$orderAmountVatNode = $this->orderNode->addChild('amountVAT', $this->data['orderVat']);
        //$this->achterafbetalenArrayToXml($this->data['orderVat'], $orderAmountVatNode);

        // Set Order Shipping cost
		$orderAmountNode = $this->orderNode->addChild('shippingCost', $this->data['shippingCost']);
        //$this->achterafbetalenArrayToXml($this->data['shippingCost'], $orderAmountNode);

        // Set Order Shipping cost VAT
		$orderAmountVatNode = $this->orderNode->addChild('shippingCostVAT', $this->data['shippingCostVat']);
        //$this->achterafbetalenArrayToXml($this->data['shippingCostVat'], $orderAmountVatNode);

		// Set Order details
        foreach ($this->data['orderDetails'] as $oDetails) {
            $orderDetailsNode = $this->orderNode->addChild('orderDetail');
            $this->achterafbetalenArrayToXml($oDetails, $orderDetailsNode);
        }
		
        if ($this->debug == true) {
            header("Content-type: text/xml");
            echo $this->orderData->asXML();
            exit;
        }

        return $this->orderData->asXML();
    }

	public function createAchterafbetalenAddress()
    {
        return new Hs_Achterafbetalen_Model_Address();
    }

    public function createCustomer()
    {
        return new Hs_Achterafbetalen_Order_Customer();
    }

	public function setAchterafbetalenData($tag, $object)
    {
        $this->data[$tag] = $object;
    }

    /*public function createProduct()
    {
        return new Icepay_Order_Product();
    }*/

    public function validateOrder($paymentObj)
    {
        switch (strtoupper($paymentObj->getPaymentMethod())) {
            case 'AFTERPAY':
                if ($this->data['shippingAddress']->country !== $this->data['billingAddress']->country)
                    throw new Exception('Billing and Shipping country must be equal in order to use Afterpay.');

                if (!Icepay_Order_Helper::validateZipCode($this->data['shippingAddress']->zipCode, $this->data['shippingAddress']->country))
                    throw new Exception('Zipcode format for shipping address is incorrect.');

                if (!Icepay_Order_Helper::validateZipCode($this->data['billingAddress']->zipCode, $this->data['billingAddress']->country))
                    throw new Exception('Zipcode format for billing address is incorrect.');

                if (!Icepay_Order_Helper::validatePhonenumber($this->data['consumer']->phone))
                    throw new Exception('Phonenumber is incorrect.');

                break;
        }
    }

}

class Hs_Achterafbetalen_Order_Customer {

    public $initials = '';
    public $prefix = '';
    public $lastName = '';
    public $birthDate = '';
    public $gender = '';
    public $phoneNumber = '';
    public $email = '';

    /**
     * Sets the customer initials
     *
     * @param string $initials
     * @return \Icepay_Order_Customer
     */
    public function setInitials($initials)
    {
        $initials = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities(utf8_encode($initials), ENT_QUOTES, 'UTF-8'));

        $this->initials = (string) $initials;
        return $this;
    }

    /**
     * Sets the customer prefix
     *
     * @param string $prefix
     * @return \Icepay_Order_Customer
     */
    public function setPrefix($prefix)
    {
        $this->prefix = (string) $prefix;
        return $this;
    }

    /**
     * Sets the customer lastname
     *
     * @param string $lastName
     * @return \Icepay_Order_Customer
     */
    public function setLastname($lastName)
    {
        $lastName = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities(utf8_encode($lastName), ENT_QUOTES, 'UTF-8'));

        $this->lastName = (string) $lastName;
        return $this;
    }

    /**
     * Sets the customer email address
     *
     * @param string $emailAddress
     * @return \Icepay_Order_Customer
     */
    public function setEmailAddress($emailAddress)
    {
        $this->email = (string) $emailAddress;
        return $this;
    }

    /**
     * sets the customer phonenumber
     *
     * @param string $phoneNumber
     * @return \Icepay_Order_Customer
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = (string) $phoneNumber;
        return $this;
    }

    /**
     * sets the customer birthdate
     *
     * @param string $birthDate
     * @return \Icepay_Order_Customer
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = (string) $birthDate;
        return $this;
    }

    /**
     * sets the customer gender
     *
     * @param string $gender
     * @return \Icepay_Order_Customer
     */
    public function setGender($gender)
    {
        $this->gender = (string) $gender;
        return $this;
    }
}
class Hs_Achterafbetalen_Model_Address {

	public $street = '';
	public $houseNumber = '';
	public $extension = '';
	public $zipCode = '';
	public $city = '';
	public $country = '';

	/**
	 * Sets the address initials
	 * 
	 * @since 1.0.0
	 * @param string $initials
	 * @return \Icepay_Order_Achterafbetalen_Address
	 */
	public function setInitials($initials)
	{
	    $initials = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities(utf8_encode($initials), ENT_QUOTES, 'UTF-8'));

	    $this->initials = (string) $initials;
	    return $this;
	}

	/**
	 * Sets the address prefix
	 * 
	 * @since 1.0.0
	 * @param string $prefix
	 * @return \Icepay_Order_Achterafbetalen_Address
	 */
	public function setPrefix($prefix)
	{
	    $this->prefix = (string) $prefix;
	    return $this;
	}

	/**
	 * Sets the address lastname
	 * 
	 * @since 1.0.0
	 * @param string $lastName
	 * @return \Icepay_Order_Achterafbetalen_Address
	 */
	public function setLastname($lastName)
	{
	    $lastName = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities(utf8_encode($lastName), ENT_QUOTES, 'UTF-8'));

	    $this->lastName = (string) $lastName;
	    return $this;
	}

	/**
	 * Sets the address streetname
	 * 
	 * @since 1.0.0
	 * @param string $streetName
	 * @return \Icepay_Order_Achterafbetalen_Address
	 */
	public function setStreetName($streetName)
	{
	    $streetName = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities(utf8_encode($streetName), ENT_QUOTES, 'UTF-8'));
	    $this->street = (string) $streetName;
	    return $this;
	}

	/**
	 * Sets the address housenumber
	 * 
	 * @since 1.0.0
	 * @param string $houseNumber
	 * @return \Icepay_Order_Achterafbetalen_Address
	 */
	public function setHouseNumber($houseNumber)
	{
	    $this->houseNumber = (string) $houseNumber;
	    return $this;
	}

	/**
	 * Sets the address extension
	 * 
	 * @since 1.0.0
	 * @param string $extension
	 * @return \Icepay_Order_Achterafbetalen_Address
	 */
	public function setExtension($extension)
	{
	    $this->extension = (string) $extension;
	    return $this;
	}

	/**
	 * Sets the address zipcode
	 * 
	 * @since 1.0.0
	 * @param string $zipCode
	 * @return \Icepay_Order_Achterafbetalen_Address
	 */
	public function setZipCode($zipCode)
	{
	    $this->zipCode = (string) $zipCode;
	    return $this;
	}

	/**
	 * Sets the address city
	 * 
	 * @since 1.0.0
	 * @param string $city
	 * @return \Icepay_Order_Achterafbetalen_Address
	 */
	public function setCity($city)
	{
	    $this->city = (string) $city;
	    return $this;
	}

	/**
	 * Sets the address country
	 * 
	 * @since 1.0.0
	 * @param string $country
	 * @return \Icepay_Order_Achterafbetalen_Address
	 */
	public function setCountry($country)
	{
	    $this->country = (string) $country;
	    return $this;
	}

}
