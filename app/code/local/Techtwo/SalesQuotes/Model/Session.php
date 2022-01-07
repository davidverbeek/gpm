<?php

class Techtwo_SalesQuotes_Model_Session extends Mage_Core_Model_Session_Abstract
{
    public function __construct()
    {
        $this->init('techtwo_salesquotes/session');
    }
}
