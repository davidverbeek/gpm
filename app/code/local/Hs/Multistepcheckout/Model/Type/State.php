<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Multishipping checkout state model
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Hs_Multistepcheckout_Model_Type_State extends Varien_Object
{
    const STEP_CART_ITEM = 'cartitem';
    const STEP_SELECT_ADDRESSES = 'addresses';
    const STEP_PRODUCT = 'product';
    const STEP_PAYMENT = 'payment';
    const STEP_OVERVIEW = 'overview';
    const STEP_SUCCESS = 'success';

    /**
     * Allow steps array
     *
     * @var array
     */
    protected $_steps;

    /**
     * Checkout model
     *
     * @var Mage_Checkout_Model_Type_Multishipping
     */
    protected $_checkout;

    /**
     * Init model, steps
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->_steps = array(
            self::STEP_CART_ITEM => new Varien_Object(array(
                'label' => Mage::helper('checkout')->__('shopping cart'),
                'url' => Mage::getUrl('checkout/cart')
            )),
            self::STEP_SELECT_ADDRESSES => new Varien_Object(array(
                'label' => Mage::helper('checkout')->__('Data'),
                'url' => Mage::getUrl('multistepcheckout/index/login', array('_secure' => true))
            )),
            self::STEP_PRODUCT => new Varien_Object(array(
                'label' => Mage::helper('checkout')->__('Deliver'),
                'url' => Mage::getUrl('multistepcheckout/index/product', array('_secure' => true))
            )),
            self::STEP_PAYMENT => new Varien_Object(array(
                'label' => Mage::helper('checkout')->__('Pay'),
                'url' => Mage::getUrl('multistepcheckout/index/payment', array('_secure' => true))
            )),
            self::STEP_OVERVIEW => new Varien_Object(array(
                'label' => Mage::helper('checkout')->__('Confirmation', array('_secure' => true))

            )),
            self::STEP_SUCCESS => new Varien_Object(array(
                'label' => Mage::helper('checkout')->__('Success'),

            )),
        );

        foreach ($this->_steps as $key => $step) {
            if ($key != self::STEP_CART_ITEM) {
                //$this->_checkout = Mage::getSingleton('checkout/type_multishipping');
                break;
            }
            $step->setIsComplete(false);
        }
        if ($this->getActiveStep() != self::STEP_CART_ITEM) {
            $this->_checkout = Mage::getSingleton('checkout/type_onepage');
        }

        $this->_steps[$this->getActiveStep()]->setIsActive(true);
    }

    /**
     * Retrieve checkout model
     *
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    public function getCheckout()
    {
        return $this->_checkout;
    }

    /**
     * Retrieve available checkout steps
     *
     * @return array
     */
    public function getSteps()
    {
        if ($this->getCheckoutSession()->getQuote()->getCheckoutMethod() == 'guest') {

            $this->_steps[self::STEP_SELECT_ADDRESSES]->setUrl(Mage::getUrl('multistepcheckout/index/register', array('_secure' => true)));

        }

        return $this->_steps;
    }

    /**
     * Retrieve active step code
     *
     * @return string
     */
    public function getActiveStep()
    {
        $step = $this->getCheckoutSession()->getCheckoutState();
        if (isset($this->_steps[$step])) {
            return $step;
        }
        return self::STEP_CART_ITEM;
    }

    public function setActiveStep($step)
    {

        if (isset($this->_steps[$step])) {
            $this->getCheckoutSession()->setCheckoutState($step);
        } else {
            $this->getCheckoutSession()->setCheckoutState(self::STEP_SELECT_ADDRESSES);
        }

        // Fix active step changing
        if (!$this->_steps[$step]->getIsActive()) {
            foreach ($this->getSteps() as $stepObject) {
                $stepObject->unsIsActive();
            }
            $this->_steps[$step]->setIsActive(true);
        }
        return $this;
    }

    /**
     * Mark step as completed
     *
     * @param string $step
     * @return Mage_Checkout_Model_Type_Multishipping_State
     */
    public function setCompleteStep($step)
    {
        if (isset($this->_steps[$step])) {
            $this->getCheckoutSession()->setStepData($step, 'is_complete', true);
        }
        return $this;
    }

    /**
     * Retrieve step complete status
     *
     * @param string $step
     * @return bool
     */
    public function getCompleteStep($step)
    {
        if (isset($this->_steps[$step])) {
            return $this->getCheckoutSession()->getStepData($step, 'is_complete');
        }
        return false;
    }

    /**
     * Unset complete status from step
     *
     * @param string $step
     * @return Mage_Checkout_Model_Type_Multishipping_State
     */
    public function unsCompleteStep($step)
    {
        if (isset($this->_steps[$step])) {
            $this->getCheckoutSession()->setStepData($step, 'is_complete', false);
        }
        return $this;
    }

    public function canSelectAddresses()
    {

    }

    public function canInputShipping()
    {

    }

    public function canSeeOverview()
    {

    }

    public function canSuccess()
    {

    }

    /**
     * Retrieve checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }
}
