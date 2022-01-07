<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Hs_Multistepcheckout_Helper_Url extends Mage_Checkout_Helper_Url
{

    /**
     * Retrieve checkout url
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->_getUrl('multistepcheckout/index/login');
    }


	

}
