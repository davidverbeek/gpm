<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company    CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_ProductOutByAttribute extends CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_ProductByAttribute
{
    protected $_currentAttributeValue = null;
    protected $_attributeCode = 'manufacturer';

    protected $_configKey = 'prod_out';
    protected $_fileName = '_prod_out';
    protected $_counterLabel= 'Prod. Out';

    protected $_imgCounterLabel = 'Prod. Out Image';
    protected $_prodCatcounterLabel = 'Prod. Out Cat. Path';

    protected $_isOutOfStock = TRUE;
    protected $_filterInStock = TRUE;
    protected $_filterOutStock = FALSE;

}
