<?php
class Helios_Productgrid_Block_Adminhtml_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{

    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('cost')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id');

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }
        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
            $collection->joinAttribute('cost', 'catalog_product/cost', 'entity_id', null, 'left', $store->getId());
        }
        else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
            $collection->joinAttribute('cost', 'catalog_product/cost', 'entity_id', null, 'inner');
        }
        //echo $collection->getSelect();
        $this->setCollection($collection);

        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }
    protected function _prepareColumns()
    {
        $store = $this->_getStore();
         $this->addColumnAfter('gyzs_sku',
            array(
                'header'=> Mage::helper('catalog')->__('GYZS SKU'),
                'width' => '80px',
                'index' => 'gyzs_sku',
                'renderer' => 'Helios_Productgrid_Block_Adminhtml_Catalog_Product_Grid_Renderer_Gyzssku',
                'filter_condition_callback' => array($this, '_gyzsSkuFilter'),
                'sortable'  => false
            ),'sku');
        $this->addColumnAfter('cost',
            array(
                'header'=> Mage::helper('catalog')->__('Cost'),
                'width' => '80px',
                'index' => 'cost',
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
            ),'gyzs_sku');
        $this->addColumnAfter('marge',
            array(
                'header'=> Mage::helper('catalog')->__('Marge'),
                'width' => '80px',
                'type'  => 'marge',
                'align' => 'right',
                'filter'=> false,
                'renderer' => 'Helios_Productgrid_Block_Adminhtml_Catalog_Product_Grid_Renderer_Marge'
            ),'cost');
        $this->addColumnAfter('price_btw',
            array(
                'header'=> Mage::helper('catalog')->__('Price').' '.Mage::helper('tax')->getIncExcText(true),
                'width' => '80px',
                'type'  => 'price',
                'index' => 'price_btw',
                'align' => 'right',
                'filter'=> false,
                'renderer' => 'Helios_Productgrid_Block_Adminhtml_Catalog_Product_Grid_Renderer_Pricebtw'
            ),'price');
        return parent::_prepareColumns();
    }
    protected function _gyzsSkuFilter($collection, $column)
    {

        $filter_value=strrev(str_replace('GY1','',$column->getFilter()->getValue()));
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $this->getCollection()->getSelect()->where("sku LIKE ?", "%$filter_value%" );
        return $this;
    }
}
			