<?php

class Gyzs_Blog_Block_Adminhtml_Blog_Edit_Tab_Product extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set grid params
     *
     */
    public function __construct()
    { 
        parent::__construct();
        $this->setId('blog_product_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        if (Mage::registry('blog_data')->getId()) {
            $this->setDefaultFilter(array('in_products' => 1));
        }
        if ($this->isReadonly()) {
            $this->setFilterVisibility(false);
        }
    }
    
    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        /*$collection = Mage::getModel('catalog/product_link')->useRelatedLinks()
            ->getProductCollection()
            ->setProduct($this->_getProduct())
            ->addAttributeToSelect('*');*/
        $collection=Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect('*');

        if ($this->isReadonly()) {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = array(0);
            }
            $collection->addFieldToFilter('entity_id', array('in' => $productIds));
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    public function isReadonly()
    {
        return Mage::registry('blog_data')->getRelatedReadonly();
    }
    /**
     * Add filter
     *
     * @param object $column
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Related
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } else {
                if($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
    
    protected function _prepareColumns()
    {
        if (!$this->isReadonly()) {
            $this->addColumn('in_products', array(
                'header_css_class'  => 'a-center',
                'type'              => 'checkbox',
                'name'              => 'in_products',
                'values'            => $this->_getSelectedProducts(),
                'align'             => 'center',
                'index'             => 'entity_id'
            ));
        }

        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'entity_id'
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name'
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('catalog')->__('Type'),
            'width'     => 100,
            'index'     => 'type_id',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name', array(
            'header'    => Mage::helper('catalog')->__('Attrib. Set Name'),
            'width'     => 130,
            'index'     => 'attribute_set_id',
            'type'      => 'options',
            'options'   => $sets,
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('catalog')->__('Status'),
            'width'     => 90,
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('visibility', array(
            'header'    => Mage::helper('catalog')->__('Visibility'),
            'width'     => 90,
            'index'     => 'visibility',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => 80,
            'index'     => 'sku'
        ));

        $this->addColumn('price', array(
            'header'        => Mage::helper('catalog')->__('Price'),
            'type'          => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'         => 'price'
        ));

        $this->addColumn('position', array(
            'header'            => Mage::helper('catalog')->__('Position'),
            'name'              => 'position',
            'type'              => 'number',
            'validate_class'    => 'validate-number',
            'index'             => 'position',
            'width'             => 60,
            'editable'          => !Mage::registry('blog_data')->getRelatedReadonly(),
            //'edit_only'         => !Mage::registry('blog_data')->getId()
        ));

        return parent::_prepareColumns();
    }
    
    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData('grid_url')
            ? $this->getData('grid_url')
            : $this->getUrl('*/*/productGrid', array('_current' => true));
    }
    
    
 
    /**
     * Retrieve selected related products
     *
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getBlogProducts();
       // $products = Mage::getModel('catalog/product')->getCollection();
        if (!is_array($products)) {
            $products = array_keys($this->getSelectedBlogProducts());
        }
        return $products;
    }
    
    /**
     * Retrieve related products
     *
     * @return array
     */
    public function getSelectedBlogProducts()
    {
        $products = array();
        
        $blog=Mage::registry('blog_data');
        $blogId=$blog->getId();
        
        $tableName=Mage::getSingleton('core/resource')->getTableName('wordpress_association');
        $resource=Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');

        $writeConnection = $resource->getConnection('core_write');
        
        $query = 'SELECT * FROM ' . $tableName.' where wordpress_object_id='.$blogId;

        $results = $readConnection->fetchAll($query);
        
        
        if(count($results)>0){
            foreach ($results as $product) {
                $products[$product['object_id']] = array('position' => $product['position']);
            }
        }
        return $products;
    }
}