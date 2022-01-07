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
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Page
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Top menu block
 *
 * @category    Mage
 * @package     Mage_Page
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Hs_Catalog_Block_Page_Html_Topmenu extends Mage_Page_Block_Html_Topmenu
{
    protected $_templateFile;
    /**
     * Get top menu html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @return string
     */
    public function getHtml($outermostClass = '', $childrenWrapClass = '')
    {
        Varien_Profiler::start(__METHOD__);

        $cacheId = 'catalog_category_custom_cache';
        if ($cacheContent = Mage::app()->loadCache($cacheId)) {
            return unserialize($cacheContent);
        }
        else{
            Mage::dispatchEvent('page_block_html_topmenu_gethtml_before', array(
                'menu' => $this->_menu,
                'block' => $this
            ));

            $this->_menu->setOutermostClass($outermostClass);
            $this->_menu->setChildrenWrapClass($childrenWrapClass);
            $html = $this->_getHtml($this->_menu, $childrenWrapClass);
            $cacheContent = serialize($html);
            $tags = array(
                "catalog_category_custom_cache",
                $cacheId
            );
            $lifetime = Mage::getStoreConfig('core/cache/lifetime');
            Mage::app()->saveCache($cacheContent, $cacheId, $tags, $lifetime);
            Mage::dispatchEvent('page_block_html_topmenu_gethtml_after', array(
                'menu' => $this->_menu,
                'html' => $html
            ));
        }
        Varien_Profiler::stop(__METHOD__);
        return $html;
    }

    /**
     * Recursively generates top menu html from data that is specified in $menuTree
     *
     * @param Varien_Data_Tree_Node $menuTree
     * @param string $childrenWrapClass
     * @param string $cacheCacheId
     * @return string
     * @deprecated since 1.8.2.0 use child block catalog.topnav.renderer instead
     */
    protected function _getHtml(Varien_Data_Tree_Node $menuTree, $childrenWrapClass, $cacheCacheId = '')
    {
        Varien_Profiler::start(__METHOD__);

        $cacheId = 'catalog_category_custom_cache_child'.$cacheCacheId;
        if ($cacheContent = Mage::app()->loadCache($cacheId)) {
            return unserialize($cacheContent);
        }
        else{
            $html = '';

            $children = $menuTree->getChildren();
            $parentLevel = $menuTree->getLevel();
            $childLevel = is_null($parentLevel) ? 0 : $parentLevel + 1;

            $counter = 1;
            $childrenCount = $children->count();

            $parentPositionClass = $menuTree->getPositionClass();
            $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

            foreach ($children as $child) {
                $child->setLevel($childLevel);
                $child->setIsFirst($counter == 1);
                $child->setIsLast($counter == $childrenCount);
                $child->setPositionClass($itemPositionClassPrefix . $counter);

                $outermostClassCode = 'level'. $childLevel;
                $_hasChildren = ($child->hasChildren()) ? 'has-children' : '';

                $html .= '<li '. $this->_getRenderedMenuItemAttributes($child) .'>';

                $html .= '<a href="'. $child->getUrl() .'" class="'. $outermostClassCode .' '. $_hasChildren .'">'. $this->escapeHtml($this->__($child->getName())) .'</a>';

                if (!empty($childrenWrapClass)) {
                    $html .= '<div class="'. $childrenWrapClass .'">';
                }

                $nextChildLevel = $childLevel + 1;

                $containerLevel = $nextChildLevel + 1;

                if (!empty($_hasChildren)) {
                    $html .= '<div class="level'.$containerLevel.'-container submenu-nav ' . strtolower(str_replace(' ', '-', $this->escapeHtml($this->__($child->getName())))) . '">';
                    $html .= '<ul class="level'. $childLevel .' sub-cat-list-'.$containerLevel.'">';
                    $html .=     '<li class="level'. $nextChildLevel .' view-all">';
                    $html .=         '<a class="level'. $nextChildLevel .' href="'. $child->getUrl() .'">';
                    $html .=             $this->escapeHtml($this->__($child->getName())).':';
                    $html .=         '</a>';
                    $html .=     '</li>';
                    $html .=     $this->_getHtml($child, $childrenWrapClass,$child->getName());
                    $html .= '</ul>';
                    $html .= '</div>';
                }

                if (!empty($childrenWrapClass)) {
                    $html .= '</div>';
                }

                $html .= '</li>';

                $counter++;
            }
            $cacheContent = serialize($html);
            $tags = array(
                "catalog_category_custom_cache",
                $cacheId
            );
            $lifetime = Mage::getStoreConfig('core/cache/lifetime');
            Mage::app()->saveCache($cacheContent, $cacheId, $tags, $lifetime);
        }
        Varien_Profiler::stop(__METHOD__);
        return $html;
    }
}