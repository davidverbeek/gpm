<?php
class Vendor_Module_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getRobots()
    {
    	$requestUri = Mage::app()->getRequest()->getRequestUri();
        if (strpos($requestUri, "?") !== false) {
            return 'NOINDEX,FOLLOW';
        } else {
            return 'INDEX,FOLLOW';
        }
    }
}
?>