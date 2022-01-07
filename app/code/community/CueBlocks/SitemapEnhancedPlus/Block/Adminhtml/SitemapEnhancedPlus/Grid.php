<?php

/**
 * Description of 
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Block_Adminhtml_SitemapEnhancedPlus_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setUseAjax(true);
        $this->setId('sitemapEnhancedPlusGrid');
        $this->setDefaultSort('sitemap_id');

        $urlNew    = $this->getUrl('*/sitemapEnhancedPlus/new');
        $urlConfig = $this->getUrl('*/system_config/edit/section/sitemapEnhancedPlus');

        $this->_emptyText = Mage::helper('adminhtml')->__('No XML Sitemaps to show here. <br /> You can add a sitemap by clicking on <a href="' . $urlNew . '">\'Add Sitemap\'</a>, which will create XML Sitemaps based on the default configuration setting of this extenion. <br /> If you want to change the default settings, please go to <a href="' . $urlConfig . '">\'Configuration\'</a> and make the desired changes');
    }

    /**
     * Prepare collection for grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('sitemapEnhancedPlus/sitemapEnhancedPlus')->getCollection();
        /* @var $collection Mage_Sitemap_Model_Mysql4_Sitemap_Collection */

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('sitemap_id', array(
            'header' => Mage::helper('sitemapEnhancedPlus')->__('ID'),
            'index'  => 'sitemap_id',
            'width'  => '16px',
        ));

        $this->addColumn('sitemap_type', array(
            'header'   => Mage::helper('sitemapEnhancedPlus')->__('Type'),
            'index'    => 'sitemap_type',
            'renderer' => 'sitemapEnhancedPlus/adminhtml_sitemapEnhancedPlus_grid_renderer_type'
        ));

        $this->addColumn('sitemap_filename', array(
            'header'   => Mage::helper('sitemapEnhancedPlus')->__('XML Sitemap(s)'),
            'index'    => 'sitemap_filename',
            'renderer' => 'sitemapEnhancedPlus/adminhtml_sitemapEnhancedPlus_grid_renderer_files'
        ));

        $this->addColumn('sitemap_path', array(
            'header' => Mage::helper('sitemapEnhancedPlus')->__('Path'),
            'index'  => 'sitemap_path',
        ));

        $this->addColumn('link', array(
            'header'   => Mage::helper('sitemapEnhancedPlus')->__('Sitemap Location'),
            'index'    => 'concat(sitemap_path, sitemap_filename)',
            'renderer' => 'sitemapEnhancedPlus/adminhtml_sitemapEnhancedPlus_grid_renderer_link',
            'filter'   => false,
            'sortable' => false
        ));

        $this->addColumn('sitemap_tot_links', array(
            'header'   => Mage::helper('sitemapEnhancedPlus')->__('Number of Pages'),
            'renderer' => 'sitemapEnhancedPlus/adminhtml_sitemapEnhancedPlus_grid_renderer_pages',
            'filter'   => false,
            'sortable' => false,
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => Mage::helper('sitemapEnhancedPlus')->__('Store View'),
                'index'  => 'store_id',
                'type'   => 'store',
            ));
        }

        $this->addColumn('sitemap_time', array(
            'header' => Mage::helper('sitemapEnhancedPlus')->__('Last Time Generated'),
            'index'  => 'sitemap_time',
            'type'   => 'datetime',
        ));

        $this->addColumn('action', array(
            'header'   => Mage::helper('sitemapEnhancedPlus')->__('Action'),
            'filter'   => false,
            'sortable' => false,
            'renderer' => 'sitemapEnhancedPlus/adminhtml_sitemapEnhancedPlus_grid_renderer_action'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('sitemap_id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=> true));
    }

}
