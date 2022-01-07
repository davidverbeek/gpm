<?php

/**
 * SeoPagination Observer
 *
 * @category    Hs
 * @package     Hs_SeoPagination
 * @author      Parth Pabari <parhtpabari@gmail.com>
 */
class Hs_SeoPagination_Model_Observer
{
    /**
     * doPagination() - kick off the process of adding the next and prev 
     * rel links to category pages where necessary
     *
     * @return Hs_SeoPagination_Model_Observer
     */
    public function doPagination()
    {
        try {
            if (Mage::helper('seopagination')->isEnabled()) {
                $paginator = Mage::getModel('seopagination/paginator');        
                $paginator->createLinks();
            }
        }
        catch(Exception $e) {
            Mage::logException($e);
        }
        
        return $this;
    }
}