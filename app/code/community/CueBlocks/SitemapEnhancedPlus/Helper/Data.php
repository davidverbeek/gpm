<?php

/**
 * Description of Data
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/

 */
class CueBlocks_SitemapEnhancedPlus_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CONFIG_BASE_PATH = 'sitemap_enhanced_plus/';

    public function getConfig($key, $storeId)
    {
        return new Varien_Object(
            Mage::getStoreConfig(self::CONFIG_BASE_PATH . $key, $storeId));
    }

    public function isUnique($sitemap)
    {
        $current_id = $sitemap->getId();
        $current_path = str_replace("\\","/",$sitemap->getSitemapPath());
        if(substr($current_path,-1) !== '/') {
            $current_path.='/';
        }
        // with pathmap
        $current_real_path =str_replace("\\","/", $sitemap->getPath());
        if(substr($current_real_path,-1) !== '/') {
            $current_real_path.='/';
        }
        $current_filename = $this->clearExtension($sitemap->getSitemapFilename());

        $collection = $sitemap->getCollection()
            ->addFieldToFilter('sitemap_path', array('eq' => $current_path));
        if ($current_id != null)
            $collection->addFieldToFilter('sitemap_id', array('neq' => $current_id));

        foreach ($collection as $site) {
            if ($current_filename == $this->clearExtension($site->getSitemapFilename())
                && $current_real_path == $site->getPath()
            ) {
                // compare with real path to include pathmap
                return false;
            }
        }

        return true;
    }

    /**
     * Base Filename without extension
     *
     * @return string
     */
    public function clearExtension($filename)
    {
        $filename = str_replace(array('.xml.gz', '.xml'), '', $filename);

        return $filename;
    }

    /**
     * Return all product assigned to more than a category
     *
     * @param unknown_type $storeId
     * @return array
     */
    public static function getDoubleProduct($filterInvisible = false)
    {

        $show = true;
        $prodColl = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', 1);

        foreach ($prodColl as $prod) {
            $v = $prod->getVisibility();
            $name = $prod->getName();
            $catColl = $prod->getCategoryIds();

            if ($filterInvisible)
                $show = ($v == 4) ? true : false;

            if (count($catColl) > 1 & $show)
                echo $name . ' ' . implode(',', $catColl) . ' visibility: ' . $v . '<br>';
        }
    }

    /**
     * Return the absolute path
     *
     * @param $relPath the relative path
     * @return string
     */
    public function fixRelative($relPath)
    {

//        $fixPath = preg_replace('/\w+\/\.\.\//', 'OO', $relPath);

        $path = str_replace(array('/', '\\'), DS, $relPath);
        $parts = array_filter(explode(DS, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part)
                continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return DS . implode(DS, $absolutes) . DS;


//        return $fixPath;
    }

    /** Check if Magento is > CE 1.7 or EE 1.12
     *
     * @param $relPath the relative path
     * @return string
     */
    public function isMageAbove18()
    {
        $mage = new Mage;

        if (method_exists($mage, 'getEdition')) {
            $edition = Mage::getEdition();
        } else {
            // if 'getEdition' is not defined we are above CE 1.7
            return false;
        }
        $versionInfo = Mage::getVersionInfo();

        if (isset($versionInfo['minor']))

            if (($edition == 'Community' && $versionInfo['minor'] > 7)
                || ($edition == 'Enterprise' && $versionInfo['minor'] > 12)
            ) {
                return true;
            }

        return false;
    }

    public function isMageAboveEE12()
    {
        $mage = new Mage;

        if (method_exists($mage, 'getEdition')) {
            $edition = Mage::getEdition();
        } else {
            // if 'getEdition' is not defined we are above CE 1.7
            return false;
        }
        $versionInfo = Mage::getVersionInfo();

        if (isset($versionInfo['minor']))

            if ($edition == 'Enterprise' && $versionInfo['minor'] > 12) {
                return true;
            }

        return false;
    }

    public function isMageEE13()
    {
        $mage = new Mage;

        if (method_exists($mage, 'getEdition')) {
            $edition = Mage::getEdition();
        } else {
            // if 'getEdition' is not defined we are above CE 1.7
            return false;
        }
        $versionInfo = Mage::getVersionInfo();

        if (isset($versionInfo['minor']))

            if ($edition == 'Enterprise' && $versionInfo['minor'] == 13) {
                return true;
            }

        return false;
    }

    public function getFactory()
    {
        return Mage::getSingleton('catalog/factory');
    }

    public  function addProductAttributeToSelect($attributeCode, $select, $alias, $storeId)
    {
        if (!isset($this->_attributesCache[$attributeCode])) {
            $this->_loadAttribute($attributeCode);
        }

        $attribute = $this->_attributesCache[$attributeCode];

        if (!$select instanceof Zend_Db_Select) {
            return false;
        }

        if ($attribute['backend_type'] == 'static') {
            $select->columns($alias . '.' . $attributeCode);
        } else {
            $select->joinLeft(
                array('t1_' . $attributeCode => $attribute['table']),
                $alias . '.entity_id=t1_' . $attributeCode . '.entity_id AND t1_' . $attributeCode . '.store_id=0' .
                ' AND t1_' . $attributeCode . '.attribute_id=' . $attribute['attribute_id'],
                array()
            );

            if ($attribute['is_global']) {
                $select->columns(array($attributeCode => 't1_' . $attributeCode . '.value'));
            } else {
                $ifCase = $this->getCheckSql('t2_' . $attributeCode . '.value_id > 0',
                    't2_' . $attributeCode . '.value', 't1_' . $attributeCode . '.value'
                );
                $select->joinLeft(
                    array('t2_' . $attributeCode => $attribute['table']),
                    $select->getAdapter()->quoteInto(
                        't1_' . $attributeCode . '.entity_id = t2_' . $attributeCode . '.entity_id AND t1_'
                        . $attributeCode . '.attribute_id = t2_' . $attributeCode . '.attribute_id AND t2_'
                        . $attributeCode . '.store_id = ?', $storeId
                    ),
                    array($attributeCode => $ifCase)
                );
            }
        }
//        $select->order($attributeCode);
        return $select;
    }

    protected function _loadAttribute($attributeCode)
    {
        if (!isset($this->_attributesCache[$attributeCode])) {
            $attribute = Mage::getSingleton('catalog/product')->getResource()->getAttribute($attributeCode);

            $this->_attributesCache[$attributeCode] = array(
                'entity_type_id' => $attribute->getEntityTypeId(),
                'attribute_id' => $attribute->getId(),
                'table' => $attribute->getBackend()->getTable(),
                'is_global' => $attribute->getIsGlobal() == Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'backend_type' => $attribute->getBackendType()
            );
        }
        return $this;
    }

    /**
     * FOR COMPATIBILITY WITH 1.5 and 1.4
     * Generate fragment of SQL, that check condition and return true or false value
     *
     * @param Zend_Db_Expr|Zend_Db_Select|string $expression
     * @param string $true  true value
     * @param string $false false value
     */
    public function getCheckSql($expression, $true, $false)
    {
        if ($expression instanceof Zend_Db_Expr || $expression instanceof Zend_Db_Select) {
            $expression = sprintf("IF((%s), %s, %s)", $expression, $true, $false);
        } else {
            $expression = sprintf("IF(%s, %s, %s)", $expression, $true, $false);
        }

        return new Zend_Db_Expr($expression);
    }
}
