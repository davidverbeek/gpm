<?php
class Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
	protected function _preparePage()
    {
    	$collection = $this->getCollection();		
    	$store = $this->_getStore();
    	if ( $store->getId() )
    	{			
       		$collection->joinAttribute('cost', 'catalog_product/cost', 'entity_id', null, 'left', $store->getId());
       	}
       	else
       	{
        	$collection->joinAttribute('cost', 'catalog_product/cost', 'entity_id', null, 'left');
        	$collection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left');
        }		
        $this->setCollection($collection);

        // create the gyzs sku
        $collection->getSelect()->columns( array('gyzs_sku' => new Zend_Db_Expr("CONCAT( '".Hs_Common_Helper_Data::GYZS_SKU_PREFIX."', e.sku )")) );
		
		$collection->addAttributetoSelect('url_key');
		$collection->addAttributetoSelect('prijsfactor');
		$collection->addAttributetoSelect('verkoopeenheid');
		$collection->addAttributetoSelect('idealeverpakking');
		$collection->addAttributetoSelect('afwijkenidealeverpakking');
		$collection->addAttributetoSelect('manualproduct');
		$collection->addAttributetoSelect('categorie1');
		$collection->addAttributetoSelect('categorie2');
		$collection->addAttributetoSelect('categorie3');
        
		parent::_preparePage();

        return $this;
    }

	protected function _prepareColumns()
	{

		
		$this->addColumnAfter('categorie1', array(
			'header'   => Mage::helper('gyzs')->__('Level 1'),
			'align'    => 'left',
			'index'    => 'categorie1',
			'renderer' => 'Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Categorie1'
		), 'set_name');
		$this->addColumnAfter('categorie2', array(
			'header'   => Mage::helper('gyzs')->__('Level 2'),
			'align'    => 'left',
			'index'    => 'categorie2',
			'renderer' => 'Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Categorie2'
		), 'categorie1');
		$this->addColumnAfter('categorie3', array(
			'header'   => Mage::helper('gyzs')->__('Level 3'),
			'align'    => 'left',
			'index'    => 'categorie3',
			'renderer' => 'Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Categorie3'
		), 'categorie2');		
		$this->addColumnAfter('gyzs_sku', array(
			'header'   => Mage::helper('gyzs')->__('Gyzs sku'),
			'align'    => 'left',
			'index'    => 'gyzs_sku',
			'width'    => '70',
			'renderer' => 'Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Sku'
		), 'sku');

		$this->addColumnAfter('cost', array(
			'header'   => Mage::helper('gyzs')->__('Cost'),
			'align'    => 'right',
			'type'  => 'price',
      'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
			'index'    => 'cost',
			'width'    => '70',
			'renderer' => 'Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Cost',
			'filter_condition_callback' => array($this, '_costFilter'),
		), 'gyzs_sku');

		$this->addColumnAfter('marge', array(
			'header'   => Mage::helper('gyzs')->__('Marge'),
			'align'    => 'right',
			'index'    => 'marge',
			'type'  => 'price',
      'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
			'width'    => '70',
			'renderer' => 'Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Marge',
			'filter_condition_callback' => array($this, '_margeCustomFilter'),
		), 'cost');

		$this->addColumnAfter('url_key', array(
			'header'   => Mage::helper('gyzs')->__('URL'),
			'align'    => 'left',
			'index'    => 'url_key',
			'width'    => '70',
//			'renderer' => 'Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Url'
		), 'marge');
		
		$this->addColumnAfter('pricecustom', array(
      'header'=> Mage::helper('catalog')->__('Price'),
			'align'    => 'right',
      'type'  => 'price',
      'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
      'index' => 'pricecustom',
      'renderer' => 'Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Price',
      'filter_condition_callback' => array($this, '_customPriceFilter'),
		), 'url_key');

		$this->addColumnAfter('price_btw', array(
      'header'=> Mage::helper('catalog')->__('Price').' '.Mage::helper('tax')->getIncExcText(true),
      'type'  => 'price',
      'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
      'index' => 'price_btw',
      'renderer' => 'Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_PriceTax',
      'filter_condition_callback' => array($this, '_customPriceBtwFilter'),
		), 'pricecustom');

		$this->addColumnAfter('prijsfactor', array(
			'header'   => Mage::helper('gyzs')->__('Prijs per'),
			'align'    => 'left',
			'index'    => 'prijsfactor',
      'sortable'  => false,
      'filter' => false,
			'renderer' => 'Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Prijsper'
		), 'price_btw');

		$this->addColumnAfter('idealeverpakking', array(
			'header'   => Mage::helper('gyzs')->__('Idealeverpakking'),
			'align'    => 'left',
			'index'    => 'idealeverpakking',
      'sortable'  => false,
      'filter' => false,
			'renderer' => 'Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Idealeverpakking'
		), 'prijsfactor');
		
		$this->addColumnAfter('afwijkenidealeverpakking', array(
			'header'   => Mage::helper('gyzs')->__('Afname per'),
			'align'    => 'left',
			'index'    => 'afwijkenidealeverpakking',
      'sortable'  => false,
      'filter' => false,
			'renderer' => 'Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Afnameper'
		), 'idealeverpakking');
		
		$this->addColumnAfter('manualproduct', array(
			'header'   => Mage::helper('gyzs')->__('Manualproduct'),
			'align'    => 'left',
			'index'    => 'manualproduct',
			'sortable'  => false,
			'renderer' => 'Techtwo_Gyzs_Block_Adminhtml_Catalog_Product_Grid_Renderer_Manualproduct',
			'type'      => 'options',
            'options'   => array(
                0 => 'No',
				1 => 'Yes',
				2 => 'Transferro',
				3 => 'Gyzs Warehouse'
            )
		), 'afwijkenidealeverpakking');

		// Remove columns
    $this->removeColumn('price');

		parent::_prepareColumns();
		unset($this->_columns['price']);
		return $this;
	}

	
	protected function _ignoreFilter()
	{

	}

	protected function _addColumnFilterToCollection($column)
	{
		// echo "\r\n<!-- _addColumnFilterToCollection() ".$column->getIndex()." -->\r\n";

		$columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
		$dir      = $this->getParam($this->getVarNameDir(), $this->_defaultDir);

		if ('gyzs_sku' === $column->getIndex() )
		{
			$column->setFilterConditionCallback(array($this, '_ignoreFilter'));

			$filter   = $this->getParam($this->getVarNameFilter(), null);

			if ( NULL === $filter )
			{
				$filter = $this->_defaultFilter;
			}
			else
			{
				$filter = base64_decode($filter);
				if ( $filter )
					parse_str( $filter, $filter );
				else
					$filter  = array();
			}

			if ( array_key_exists('gyzs_sku', $filter) && '' !== $filter['gyzs_sku'] )
			{
				//$this->getCollection()->getSelect()->having("gyzs_sku LIKE ?", "%$filter[gyzs_sku]%" );
				$filter_value = Mage::helper('common')->getSkuFromGyzsSku($filter['gyzs_sku']);
				$this->getCollection()->getSelect()->where("sku LIKE ?", "%$filter_value%" );
			}
		}

		// if ( 'marge' === $column->getIndex() )
		// {
		// 	$column->setFilterConditionCallback(array($this, '_ignoreFilter'));
		// }

		if ( 'gyzs_sku' === $columnId )
		{	// prefix is not set .. since.. a prefix is always the same and thus not relevant in ordering stuff
			$this->getCollection()->getSelect()->order('REVERSE( sku ) ' . $dir);
		}

		return parent::_addColumnFilterToCollection( $column );
	}

	// Added by Parth
	// Price Column
	protected function _customPriceFilter($collection, $column){

		$cond = $column->getFilter()->getCondition();

		if ( array_key_exists('from', $cond) && '' !== $cond['from']){

			$collection->addFieldToFilter('prijsfactor',array('NOT NULL' => true));
			$collection->addFieldToFilter('afwijkenidealeverpakking',array('NOT NULL' => true));		

			$collection->getSelect()->where('CASE WHEN 
	 				`at_afwijkenidealeverpakking`.value = "1" THEN `at_price`.value >= ?
    		ELSE
					(`at_price`.value*`at_prijsfactor`.value)  >= ?
		 		END', $cond['from']);
		}

		if ( array_key_exists('to', $cond) && '' !== $cond['to']){
			$collection->addFieldToFilter('prijsfactor',array('NOT NULL' => true));
			$collection->addFieldToFilter('afwijkenidealeverpakking',array('NOT NULL' => true));		

			$collection->getSelect()->where('CASE WHEN 
		 			`at_afwijkenidealeverpakking`.value = "1" THEN `at_price`.value <= ?
      	ELSE
					`at_price`.value*`at_prijsfactor`.value  <= ?
			 	END', $cond['to']);
		}

		return $this;
	}

	// Price with BTW Column
	protected function _customPriceBtwFilter($collection, $column){

	}

	// Cost Column
	protected function _costFilter($collection, $column){

		$cond = $column->getFilter()->getCondition();

		if ( array_key_exists('from', $cond) && '' !== $cond['from']){

			$collection->addFieldToFilter('prijsfactor',array('NOT NULL' => true));
			$collection->addFieldToFilter('afwijkenidealeverpakking',array('NOT NULL' => true));		

			$collection->getSelect()->where('CASE WHEN 
	 				`at_afwijkenidealeverpakking`.value = "1" THEN `at_cost`.value >= ?
    		ELSE
					(`at_cost`.value*`at_prijsfactor`.value)  >= ?
		 		END', $cond['from']);
		}

		if ( array_key_exists('to', $cond) && '' !== $cond['to']){
			$collection->addFieldToFilter('prijsfactor',array('NOT NULL' => true));
			$collection->addFieldToFilter('afwijkenidealeverpakking',array('NOT NULL' => true));		

			$collection->getSelect()->where('CASE WHEN 
		 			`at_afwijkenidealeverpakking`.value = "1" THEN `at_cost`.value <= ?
      	ELSE
					`at_cost`.value*`at_prijsfactor`.value  <= ?
			 	END', $cond['to']);
		}
		return $this;
	}

	// Marge Column
	protected function _margeCustomFilter($collection, $column){
		
		$cond = $column->getFilter()->getCondition();

		if ( array_key_exists('from', $cond) && '' !== $cond['from']){

			$collection->addFieldToFilter('prijsfactor',array('NOT NULL' => true));
			$collection->addFieldToFilter('afwijkenidealeverpakking',array('NOT NULL' => true));		

			$collection->getSelect()->where('CASE WHEN 
	 				`at_afwijkenidealeverpakking`.value = "1" THEN (`at_price`.value-`at_cost`.value) >= ?
    		ELSE
					((`at_price`.value-`at_cost`.value)*`at_prijsfactor`.value)  >= ?
		 		END', $cond['from']);
		}

		if ( array_key_exists('to', $cond) && '' !== $cond['to']){
			$collection->addFieldToFilter('prijsfactor',array('NOT NULL' => true));
			$collection->addFieldToFilter('afwijkenidealeverpakking',array('NOT NULL' => true));		

			$collection->getSelect()->where('CASE WHEN 
		 			`at_afwijkenidealeverpakking`.value = "1" THEN (`at_price`.value-`at_cost`.value) <= ?
      	ELSE
					(`at_price`.value-`at_cost`.value)*`at_prijsfactor`.value  <= ?
			 	END', $cond['to']);
		}
		return $this;

	}
	
	public function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('catalog')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('catalog/product_status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('catalog')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('catalog')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('catalog/update_attributes')){
            $this->getMassactionBlock()->addItem('attributes', array(
                'label' => Mage::helper('catalog')->__('Update Attributes'),
                'url'   => $this->getUrl('*/catalog_product_action_attribute/edit', array('_current'=>true))
            ));
        }
		
		$this->getMassactionBlock()->addItem('movetoacties', array(
			'label' => Mage::helper('catalog')->__('Move to acties'),
			'url'   => $this->getUrl('admin_gyzs/adminhtml_updatecategory', array('_current'=>true))
		));	
	
        Mage::dispatchEvent('adminhtml_catalog_product_grid_prepare_massaction', array('block' => $this));
        return $this;
    }

}
