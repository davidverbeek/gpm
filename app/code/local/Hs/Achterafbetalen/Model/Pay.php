<?php

/**
 *  ICEPAY Advanced - Start payment
 * @version 1.0.0
 * @author Wouter van Tilburg
 * @copyright ICEPAY <www.icepay.com>
 *
 *  Disclaimer:
 *  The merchant is entitled to change de ICEPAY plug-in code,
 *  any changes will be at merchant's own risk.
 *  Requesting ICEPAY support for a modified plug-in will be
 *  charged in accordance with the standard ICEPAY tariffs.
 *
 */
class Hs_Achterafbetalen_Model_Pay extends Icepay_IceAdvanced_Model_Pay
{
    const PAYMENT_METHOD = 'ACHTERAFBETALEN';
    const PAYMENT_ISSUER = 'DEFAULT';

    private $sqlModel;
    private $ic_order;

    public function __construct()
    {
        $this->sqlModel = Mage::getModel('icecore/mysql4_iceCore');
        $this->ic_order = Mage::getModel('iceadvanced/order');

        parent::__construct();
    }

    public function getCheckoutResult()
    {
        // Get Magento's checkout session
        $session = Mage::getSingleton('checkout/session');

        // Retrieve icepay order
        if ($session->getLastRealOrderId()) {
            $icedata = $this->sqlModel->loadPaymentByID($session->getLastRealOrderId());
        } else {
            $coresession = Mage::getSingleton('core/session');
            $orderIds = $coresession->getOrderIds();
            foreach ($orderIds as $order) {
                $icedata = $this->sqlModel->loadPaymentByID($order);
            }
        }

        // Retrieve payment data
        $paymentData = unserialize(urldecode($icedata["transaction_data"]));

        // Retrieve merchant id and secretcode
        $merchantID = Mage::app()->getStore($icedata["store_id"])->getConfig(Icepay_IceCore_Model_Config::MERCHANTID);
        $secretCode = Mage::app()->getStore($icedata["store_id"])->getConfig(Icepay_IceCore_Model_Config::SECRETCODE);

        // Initialize webservice
        $webservice = Mage::getModel('achterafbetalen/webservice_advanced');
        $webservice->init($merchantID, $secretCode);

        // Create the PaymentObject
        $paymentObject = Mage::getModel('Icepay_IceAdvanced_Model_Checkout_PaymentObject');
        $paymentObject->setAmount($paymentData['ic_amount'])
            ->setCountry($paymentData['ic_country'])
            ->setLanguage($paymentData['ic_language'])
            ->setCurrency($paymentData['ic_currency'])
            ->setPaymentMethod(self::PAYMENT_METHOD)
            ->setPaymentMethodIssuer(self::PAYMENT_ISSUER)
            ->setReference($paymentData['ic_reference'])
            ->setOrderID($paymentData['ic_orderid'])
            ->setDescription($paymentData['ic_description']);

        // Fetch the Icepay_Order class
        $ic_order = Mage::getModel('achterafbetalen/order');

        $order = Mage::getModel('sales/order')->loadByIncrementId($paymentData['ic_orderid']);
        $ic_order = $this->getAchterafbetalenICOrder($ic_order, $order, $paymentData);

        try {

            $soapResponse = $webservice->doCheckout($paymentObject, $ic_order);

            //print_r($soapResponse);die;
            Mage::helper("icecore")->log(print_r($soapResponse, true));
            return $soapResponse;
        } catch (Exception $e) {
            //echo $e->getMessage();die;
            Mage::helper("icecore")->log($e->getMessage());
            return $e->getMessage();
        }
    }

    private function getAchterafbetalenICOrder($ic_order, $order, $paymentData = array())
    {
        /*$xml = '<?xml version="1.0" encoding="utf-8"?>
<achterafBetalen xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <customer>
      <initials>T</initials>
      <prefix />
      <lastName>Testpersoon</lastName>
      <birthDate>1981-02-01</birthDate>
      <gender>M</gender>
      <phoneNumber>0201234567</phoneNumber>
      <email>emailadres@bedrijf.nl</email>
    </customer>
    <iban>NL41RABO0181593308</iban>
    <invoiceAddress>
      <street>Straatweg</street>
      <houseNumber>123</houseNumber>
      <extension>a</extension>
      <zipCode>1234AB</zipCode>
      <city>Amsterdam</city>
      <country>NL</country>
    </invoiceAddress>
    <shippingAddress>
      <street>Straatweg</street>
      <houseNumber>123</houseNumber>
      <extension>a</extension>
      <zipCode>1234AB</zipCode>
      <city>Amsterdam</city>
      <country>NL</country>
    </shippingAddress>
    <order>
      <amount>18500</amount>
      <amountVAT>3885</amountVAT>
      <shippingCost>1000</shippingCost>
      <shippingCostVAT>210</shippingCostVAT>
      <orderDetail>
        <productCode>Artikel01</productCode>
        <productName>Artikelomschrijving 1</productName>
        <productDescription>Zelfmaakmode stap voor stap met alle basiskennis. Deel 1 van 4 nummers om te verzamelen. *** dit artikel kan licht beschadigd zijn! ***</productDescription>
        <quantity>2</quantity>
        <vatCode>1000</vatCode>
        <unitPrice>2500</unitPrice>
        <unitPriceVAT>525</unitPriceVAT>
      </orderDetail>
      <orderDetail>
        <productCode>Artikel02</productCode>
        <productName>Artikelomschrijving 2</productName>
        <productDescription>Zelfmaakmode stap voor stap met alle basiskennis. Deel 2 van 4 nummers om te verzamelen.</productDescription>
        <quantity>1</quantity>
        <vatCode>1000</vatCode>
        <unitPrice>5000</unitPrice>
        <unitPriceVAT>1050</unitPriceVAT>
      </orderDetail>
      <orderDetail>
        <productCode>Artikel03</productCode>
        <productName>Artikelomschrijving 3</productName>
        <productDescription>Zelfmaakmode stap voor stap met alle basiskennis. Deel 3 van 4 nummers om te verzamelen.</productDescription>
        <quantity>1</quantity>
        <vatCode>1000</vatCode>
        <unitPrice>7500</unitPrice>
        <unitPriceVAT>1575</unitPriceVAT>
      </orderDetail>
    </order>
</achterafBetalen>'; return $xml;*/

        try {
            $coreSession = Mage::getSingleton('checkout/session');
            $selectedPayment = $coreSession->getSelectedPayment();

            if (is_array($selectedPayment) && isset($selectedPayment['account_no'])) {
                $ic_order->setAchterafbetalenData('iban', $selectedPayment['account_no']);
            }

            //Set initials based on firstname middlename & lastname of customer start
            $firstNameInitials = '';

            $firstName = $order->getBillingAddress()->getFirstname();
            $firstname = explode(' ', $firstName);

            foreach ($firstname as $fName) {
                if ($fName != '') {
                    $firstNameInitials .= substr(strtoupper($fName), 0, 1) . ' ';
                }
            }
            $firstNameInitials = substr($firstNameInitials, 0, -1);

            $middleNameInitials = '';
            $middleName = $order->getBillingAddress()->getMiddlename();
            $middleNameInitials = ($middleName != '') ? ' ' . substr(strtoupper($middleName), 0, 1) : '';

            $lastNameInitials = '';
            $lastName = $order->getBillingAddress()->getLastname();
            $lastName = explode(' ', $lastName);
            $lastNameInitials = (count($lastName) > 1 && $lastName[0] != '') ? ' ' . substr(strtoupper($lastName[0]), 0, 1) : '';

            $initials = $firstNameInitials . $middleNameInitials . $lastNameInitials;
            //Set initials based on firstname middlename & lastname of customer end

            // Add the consumer information for Achterafbetalen
            $customer = $ic_order->createCustomer()
                //->setInitials($order->getBillingAddress()->getFirstname())
                ->setInitials($initials)
                //->setPrefix($order->getBillingAddress()->getPrefix()) //$order->getBillingAddress()->getPrefix()
                //->setGender('F') //TODO: set gender based on $order->getBillingAddress()->getPrefix()
                ->setLastName($order->getBillingAddress()->getLastname())
                ->setEmailAddress($order->getCustomerEmail());

            if (is_array($selectedPayment) && isset($selectedPayment['mobile_no'])) {
                $customer->setPhoneNumber($selectedPayment['mobile_no']);
            } else {
                $customer->setPhoneNumber($order->getBillingAddress()->getTelephone());
            }

            if (is_array($selectedPayment) && isset($selectedPayment['dob'])) {
                $customer->setBirthDate($selectedPayment['dob']);
            }

            if (is_array($selectedPayment) && isset($selectedPayment['gender'])) {
                $customer->setGender($selectedPayment['gender']);

                if ($selectedPayment['gender'] == 'F') {
                    $customer->setPrefix('Mevr.');
                } else {
                    $customer->setPrefix('Dhr.');
                }
            }


            $ic_order->setAchterafbetalenData('customer', $customer);

            // Add the billing address information for Achterafbetalen
            $billingStreetaddress = implode(' ', $order->getBillingAddress()->getStreet());

            $billingAddress = $ic_order->createAchterafbetalenAddress()
                ->setStreetName(Icepay_Order_Helper::getStreetFromAddress($billingStreetaddress))
                ->setHouseNumber(Icepay_Order_Helper::getHouseNumberFromAddress())
                ->setExtension(Icepay_Order_Helper::getHouseNumberAdditionFromAddress())
                ->setZipCode($order->getBillingAddress()->getPostcode())
                ->setCity($order->getBillingAddress()->getCity())
                ->setCountry($order->getBillingAddress()->getCountry());

            $ic_order->setAchterafbetalenData('invoiceAddress', $billingAddress);

            // Add the shipping address information for Achterafbetalen
            $shippingStreetAddress = implode(' ', $order->getShippingAddress()->getStreet());

            $shippingAddress = $ic_order->createAchterafbetalenAddress()
                ->setStreetName(Icepay_Order_Helper::getStreetFromAddress($shippingStreetAddress))
                ->setHouseNumber(Icepay_Order_Helper::getHouseNumberFromAddress())
                ->setExtension(Icepay_Order_Helper::getHouseNumberAdditionFromAddress())
                ->setZipCode($order->getShippingAddress()->getPostcode())
                ->setCity($order->getShippingAddress()->getCity())
                ->setCountry($order->getShippingAddress()->getCountry());

            $ic_order->setAchterafbetalenData('shippingAddress', $shippingAddress);

            $orderDetails = array();

            // payment fee needs to be divided by total no. of order items & added to each product unit price to accomodate the payment fee in xml
            $orderMultifeesDetails = unserialize($order->getDetailsMultifees());

            //counter for the all products that has been added into the cart
            $counter = 0;
            $totalItemPrice = 0;

            foreach ($order->getAllItems() as $orderItem) {
                if (empty($orderItem) || $orderItem->hasParentItemId()) {
                    continue;
                }

                $itemData = $orderItem->getData();

                $counter++;
                $unitPrice = ($itemData['price_incl_tax']);// - ($itemData['discount_amount'] / $orderItem->getQtyOrdered());// + $paymentfeeToAddToProduct;

                //for compatibility reasons, $orderItem->getProduct() was not used
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $orderItem->getSku());


                $orderDetails[] = array(
                    'productCode' => $orderItem->getSku(),
                    'productName' => preg_replace('/[^A-Za-z0-9\. -]/', '', $product->getName()),
                    'productDescription' => preg_replace('/[^A-Za-z0-9\. -]/', '', $product->getName()),
                    'quantity' => (int)$orderItem->getQtyOrdered(),
                    'vatCode' => 1000,
                    'unitPrice' => $this->convertToCents($unitPrice),
                    'unitPriceVAT' => $this->convertToCents($itemData['tax_amount'] / (int)$orderItem->getQtyOrdered())
                );
                //productCode
                $totalItemPrice += $this->convertToCents($unitPrice) * (int)$orderItem->getQtyOrdered();

            }

            $paymentFee = 0;

            foreach ($orderMultifeesDetails as $key => $feeDetail) {

                $productName = $feeDetail['options'][$key];

                if ($feeDetail['options'][$key] == 'payment fee') {

                    $productName = 'Extra betaalkosten';
                }

                $orderDetails[] = array(
                    'productCode' => 'multifees_' . $key,
                    'productName' => Mage::helper('multistepcheckout')->__($productName),
                    'productDescription' => $feeDetail['options'][$key],
                    'quantity' => 1,
                    'vatCode' => 1000,
                    'unitPrice' => $this->convertToCents($feeDetail['price_incl_tax'][$key]),
                    'unitPriceVAT' => $this->convertToCents($itemData['price_incl_tax'][$key] - $itemData['price'][$key])
                );

                $paymentFee += $this->convertToCents($feeDetail['price_incl_tax'][$key]);

            }

            $calculatedTotal = $totalItemPrice + $paymentFee;

            $discountAmount = $this->convertToCents($order->getDiscountAmount());

            if ($discountAmount < 0) {

                $orderDetails[] = array(
                    'productCode' => 'Korting',
                    'productName' => 'Korting',
                    'productDescription' => 'Korting',
                    'quantity' => 1,
                    'vatCode' => 1000,
                    'unitPrice' => $discountAmount,
                    'unitPriceVAT' => $this->convertToCents(0)
                );

                $calculatedTotal += $discountAmount;
            }

            $orderData = $order->getData();

            /* sometimes order_amount in payment and order amount in the order grand_total is mimmatch due to rounding deltas
             * so instead of considering grand total of the order,consider payment object ordered amount.
             *
             *
             */
            if (count($paymentData) > 0 && isset($paymentData['ic_amount']) && $paymentData['ic_amount'] > 0) {

                $ic_order->setAchterafbetalenData('orderAmount', $paymentData['ic_amount']);

                $grandTotal = $paymentData['ic_amount'];
            } elseif (isset($orderData['grand_total'])) {

                $ic_order->setAchterafbetalenData('orderAmount', $this->convertToCents($orderData['grand_total']));
                $grandTotal = $this->convertToCents($orderData['grand_total']);
            }

            if (isset($orderData['tax_amount'])) {
                $ic_order->setAchterafbetalenData('orderVat', $this->convertToCents($orderData['tax_amount']));
            }

            if (isset($orderData['shipping_amount']) && isset($orderData['shipping_tax_amount'])) {
                $shippingAmount = $orderData['shipping_amount'] + $orderData['shipping_tax_amount'];

                $shippingAmount += isset($orderData['sdd_amount']) ? $orderData['sdd_amount'] : 0;
                $ic_order->setAchterafbetalenData('shippingCost', $this->convertToCents($shippingAmount));
            }

            if (isset($orderData['shipping_tax_amount'])) {
                $ic_order->setAchterafbetalenData('shippingCostVat', $this->convertToCents($orderData['shipping_tax_amount']));
            }


            //total price based on the total calculati
            $calculatedTotal += $this->convertToCents($shippingAmount);


            //find difffrence in amount and then distribute to all cart Items
            $diffrenceAmount = round(($grandTotal - $calculatedTotal));

//            SJD++ 11072018
//            Rounding condition only if it's below 100 cent.
//            It's removed after confirmation.
//            Include rounding any ammount if exists.
//            if (($diffrenceAmount > 0 && $diffrenceAmount <= 100) || ($diffrenceAmount < 0 && $diffrenceAmount >= -100)) {
                if (($diffrenceAmount > 0 && $diffrenceAmount <= 1000) || ($diffrenceAmount < 0 && $diffrenceAmount >= -1000)) {
                $orderDetails[] = array(
                    'productCode' => 'Rounding',
                    'productName' => 'Rounding Deltas',
                    'productDescription' => 'Rounding Detlas',
                    'quantity' => 1,
                    'vatCode' => 1000,
                    'unitPrice' => $diffrenceAmount,
                    'unitPriceVAT' => $this->convertToCents(0)
                );
            }


            $ic_order->setAchterafbetalenData('orderDetails', $orderDetails);


            // Log the XML Send
            Mage::helper("icecore")->log(serialize($ic_order->getAchterafbetalenXML()));
            //file_put_contents('icepayxml.xml', $ic_order->getAchterafbetalenXML());
        } catch (Exception $e) {
            Mage::helper("icecore")->log($e->getMessage());
            return $e->getMessage();
        }

        return $ic_order;
    }

    private function convertToCents($amount)
    {

        $amount = preg_replace("/([^0-9\.\-])/i", "", $amount);
        // make sure we are dealing with a proper number now, no +.4393 or 3...304 or 76.5895,94
        if (!is_numeric($amount)) {
            return 0;
        }

        $amount = Mage::app()->getStore()->roundPrice($amount, 2);
        return $amount * 100;
    }
}
