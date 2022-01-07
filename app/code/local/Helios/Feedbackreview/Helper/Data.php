<?php

class Helios_Feedbackreview_Helper_Data extends Mage_Core_Helper_Abstract
{
    /*
     * Get Xml Feed Data
     */
    public function getXmlFeedData()
    {
        $xml = @simplexml_load_file('getreviewxml.xml');
        return $xml;
    }
}
	 