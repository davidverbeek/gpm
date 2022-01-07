<?php

/**
 * Description of SitemapEnhancedPlus
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company    CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_ProductByAttribute extends CueBlocks_SitemapEnhancedPlus_Model_Processor_Catalog_Product
{
    protected $_currentAttributeValue = null;
    protected $_attributeCode = 'manufacturer';

     /**
     * Add filter for attribute code
     * @param bool $usePagination
     * @return Varien_Object
     */
    public function _getQueryModelConfig($usePagination = false)
    {
        $config = parent::_getQueryModelConfig($usePagination);

        $extraConfigData = array(
            'group_by_attribute_code' => $this->_attributeCode,
        );
        $config->addData($extraConfigData);
        return $config;
    }

    protected function _processRow($row, $bypass = true)
    {
        $xml = parent::_processRow($row, $bypass);
        if ($xml) {
            // add a new file
            if ($this->_firstFile || $row[$this->_attributeCode] != $this->_currentAttributeValue) {
                $this->_firstFile = false;
                $this->_currentAttributeValue = $row[$this->_attributeCode];
                $this->getSitemap()->addFirstFile($this->_getFileName($row));
            }
        }
        return $xml;
    }

    protected function _getFileName($row = null)
    {
        $this->_currentAttributeValue;
        $fileName = $this->_fileName . '_' . $this->_attributeCode;
        $attributeValue = $this->_currentAttributeValue;

        if (!$this->_currentAttributeValue)
            $attributeValue = 'none';
        if($attributeLabel = $this->_getAttributeValueText($attributeValue,$this->_attributeCode)) {
            return $fileName . '_' . $attributeLabel;
        }

        return $fileName . '_' . $attributeValue;
    }

    /*
     * Get manufacturer attribute's value label by id
     * */
    protected function _getAttributeValueText($valueId, $attributeCode)
    {
        $attribute = Mage::registry('manufacturer_attribute');
        if ($valueId && is_null($attribute)){
            $attribute = Mage::getModel('catalog/resource_eav_attribute')->loadByCode('catalog_product', $attributeCode);
            Mage::register('manufacturer_attribute', $attribute);
        }
        if ($attribute->usesSource()) {
            return $attribute->getSource()->getOptionText($valueId);
        }
        return false;
    }


    /**
     *  Don't add first file
     */
    protected function _getProcessCollection($addFirst = false)
    {
        parent::_getProcessCollection($addFirst);
        // It this required ?
        return ;//$queryCollection =  $this->getQueryModel()->getCollection();
    }

    /**
     *  Don't add first file
     */
    protected function _getProcessCollectionWithPagination($addFirst = false)
    {
        parent::_getProcessCollectionWithPagination(FALSE);
        // It this required ?
        return ;//$queryCollection =  $this->getQueryModel()->getCollection();
    }
}
