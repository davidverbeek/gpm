<?php
/**
* ADd various columns
*
* ALSO COMBINES "EM_DeleteOrder" code.
* 
*/
class Techtwo_Gyzs_Block_Adminhtml_Sales_Order_Grid extends Magemaven_OrderComment_Block_Adminhtml_Sales_Order_Grid
{
	protected $_moduleEmDeleteOrderIsActive;

	public function __construct()
    {
        parent::__construct();

        $this->_moduleEmDeleteOrderIsActive = Mage::getConfig()->getModuleConfig('EM_DeleteOrder')->is('active', 'true');
        
        if ( $this->_moduleEmDeleteOrderIsActive )
		{
	        $this->setId('sales_order_grid');
	        $this->setUseAjax(TRUE);
	        $this->setDefaultSort('created_at');
	        $this->setDefaultDir('DESC');
	        $this->setSaveParametersInSession(TRUE);
        }
    }


	protected function _preparePage()
    {
    	// $collection = $this->getCollection();
    	// $collection = Mage::getModel('sales/order')->getCollection();
      // $this->setCollection($collection);

      // QUERY
      $collection = Mage::getModel('sales/order')->getCollection();
			$collection->getSelect()->join( array('mage_sales_flat_order_grid'), 'mage_sales_flat_order_grid.entity_id = main_table.entity_id', array('mage_sales_flat_order_grid.shipping_name','mage_sales_flat_order_grid.billing_name'));

			$collection->getSelect()->join(array('sfop'=>'mage_sales_flat_order_payment'),'main_table.entity_id=sfop.parent_id',array('pay_method'=>'sfop.method'));

			// Payment method name
			// $collection->getSelect()->columns( array('method' => new Zend_Db_Expr('(
			// 	SELECT pm_code as method
			//  	FROM `mage_icepay_issuerdata` iid
			// 	JOIN mage_icepay_transactions its ON its.model = iid.magento_code
			//  	WHERE main_table.increment_id=its.order_id ORDER BY its.local_id DESC LIMIT 1
			//  )')) );

			// Payment method name 2
			$collection->getSelect()->columns( array('method' => new Zend_Db_Expr('sfop.method')) );

			/* $collection->getSelect()->columns( array('method' => new Zend_Db_Expr('(
			 		CASE WHEN 
			 			`method` = "effectconnect_payment" THEN method
        	ELSE (SELECT magento_code as `method`
					 	FROM `mage_icepay_issuerdata` iid
						JOIN mage_icepay_transactions its ON its.model = iid.magento_code
					 	WHERE main_table.increment_id=its.order_id ORDER BY its.local_id LIMIT 1)
				END
			  )')) ); */

			// Order Comment
			// $collection->getSelect()->columns( array('ordercomment' => new Zend_Db_Expr('(
			// 	SELECT comment
			// 	FROM `mage_sales_flat_order_status_history` sfosh
			// 	WHERE main_table.entity_id=sfosh.parent_id AND sfosh.comment IS NOT NULL ORDER BY sfosh.entity_id DESC LIMIT 1
			// )')) );
			// $collection->getSelect()->columns( array('ordercomment'=>new Zend_Db_Expr('(
			//  	CASE WHEN 
		 // 			sfop.`method` = "effectconnect_payment" THEN "Order Effect Connect Bol/Amazon"
   //    	ELSE
			// 		main_table.customer_note
			//  	END
		 //  )')) );
			$collection->getSelect()->columns( array('ordercomment'=>new Zend_Db_Expr('(main_table.customer_note)')) );
			
			$this->setCollection($collection);

	    if ($collection)
	    {
	        /* @var $collection Mage_Sales_Model_Mysql4_Order_Grid_Collection */
	        // $collection->getSelect()->columns( array('marge' => new Zend_Db_Expr('(

	        //     SELECT sum( base_row_total - ( base_cost * qty_ordered ) ) as marge
	        //     FROM `mage_sales_flat_order_item`
	        //     WHERE order_id = main_table.entity_id

	        // )')) );

	        $collection->getSelect()->columns( array('mavis_ordernr' => new Zend_Db_Expr('(

	            SELECT mavis_ordernr
	            FROM `mage_sales_flat_order`
	            WHERE entity_id = main_table.entity_id

	        )')) );
	    }

      $collection->join('order', '`order`.entity_id = main_table.entity_id', array('mavis_ordernr','piew'=>'increment_id'));
        

        // Mage::log($collection, null, 'Order_grid_log_coll.log', true);
        // Mage::log((string)$collection->getSelect(),null,'Order_grid_log_coll.log',true);
        // Mage::log((string)$collection->getSelect(),null,'Order_grid_log_coll.log',true);

        parent::_preparePage();

        // $collection->setOrder('main_table.entity_id', 'ASC');
        // $collectionData = $collection->getData();
        

        return $this;
    }

	/**
	 * Condition must be in HAVING instead of WHERE due the fact the mavis_order_id is build from mage_sales_flat_order
	 *
	 * @note this is a workaround, the mavis_order_id should be in grid, so WHERE should be possible and should not be listed in _preparePage
	 * @param $collection
	 * @param $column
	 * @return Techtwo_Gyzs_Block_Adminhtml_Sales_Order_Grid
	 */
	protected function _filterMavisOrderId( $collection, $column)
	{
		$cond = $column->getFilter()->getCondition();

		// Old code
		// if ( array_key_exists('from', $cond) && '' !== $cond['from'])
		// 	$collection->getSelect()->having($column->getIndex().' >= ?', $cond['from']);
		// if ( array_key_exists('to', $cond) && '' !== $cond['to'])
		// 	$collection->getSelect()->having($column->getIndex().' <= ?', $cond['to']);

		// column ambiguous so added table before column name and instead of having condition used where 
		if ( array_key_exists('from', $cond) && '' !== $cond['from'])
			$collection->getSelect()->where('order.'.$column->getIndex().' >= ?', $cond['from']);
		if ( array_key_exists('to', $cond) && '' !== $cond['to'])
			$collection->getSelect()->where('order.'.$column->getIndex().' <= ?', $cond['to']);

		return $this;
	}


	protected function _prepareColumns()
	{
		$this->removeColumn('base_grand_total');
    $this->removeColumn('payment_method');
		$this->addColumnAfter('mavis_ordernr', array(
			'header'   => Mage::helper('gyzs')->__('Mavis Order Id'),
			'align'    => 'left',
			'index'    => 'mavis_ordernr',
			'width'    => '70',
			'type'     => 'text',
			'filter_index' => 'order.mavis_ordernr'
			// 'filter_condition_callback' => array( $this, '_filterMavisOrderId')
		), 'real_order_id' );
		
		$payments = Mage::getSingleton('payment/config')->getActiveMethods();
		$methods = array();
		foreach($payments as $paymentCode => $paymentModel) {
			$paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
			$methods[$paymentCode] = $paymentTitle;
		}
		$this->addColumnAfter('payment_method', array(
			'header'   => Mage::helper('gyzs')->__('Payment Method'),
			'align'    => 'left',
			'index'    => 'method',
			'width'    => '70px',
			'type'     => 'options',
			'options' => $methods,
			'filter_index' => 'method',
			// 'filter_condition_callback' => array( $this, '_filterMavisOrderId')
		), 'shipping_name' );
		
		/*$this->addColumnAfter('payment_method', array(
			'header'   => Mage::helper('gyzs')->__('Payment Method'),
			'align'    => 'left',
			'index'    => 'method',
			'width'    => '70',
			'type'     => 'text',
			'filter_index' => 'method',
			// 'filter_condition_callback' => array( $this, '_filterMavisOrderId')
		), 'shipping_name' ); */

		// Fixed By Parth
		$this->addColumnAfter('marge', array(
			'header'   => Mage::helper('gyzs')->__('Marge'),
			'align'    => 'right',
			'index'    => 'marge',
			'width'    => '70',
			'renderer' => 'Techtwo_Gyzs_Block_Adminhtml_Sales_Order_Grid_Renderer_Marge',
			'type'     => 'price',
			'filter'    => false,
      'sortable'  => false,
		), 'grand_total' );

                // Remove columns
    
		return parent::_prepareColumns();
	}

	protected function _ignoreFilter()
	{

	}

	protected function _addColumnFilterToCollection($column)
	{
		// echo "\r\n<!-- _addColumnFilterToCollection() ".$column->getIndex()." -->\r\n";

		$columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
		$dir      = $this->getParam($this->getVarNameDir(), $this->_defaultDir);

		if ('marge' === $column->getIndex() )
		{
			// echo $this->getCollection()->getSelect(); die();

			$column->setFilterConditionCallback(array($this, '_ignoreFilter'));

			$filter   = $this->getParam($this->getVarNameFilter(), NULL);

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
			// print_r($filter); die();

			if ( array_key_exists('marge', $filter) && '' !== $filter['marge'] )
			{

				$from = array_key_exists('from', $filter['marge'])? intval($filter['marge']['from']):FALSE;
				$to = array_key_exists('to', $filter['marge'])? intval($filter['marge']['to']):FALSE;
				if ($from)
					$this->getCollection()->getSelect()->having("marge > ?", $from );
				if ($to)
					$this->getCollection()->getSelect()->having("marge < ?", $to );
			}
		}

		return parent::_addColumnFilterToCollection( $column );
	}


	protected function _prepareMassaction()
    {
		if ( FALSE  === $this->_moduleEmDeleteOrderIsActive )
			return parent::_prepareMassaction();

        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(FALSE);

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
            $this->getMassactionBlock()->addItem('cancel_order', array(
                 'label'=> Mage::helper('sales')->__('Cancel'),
                 'url'  => $this->getUrl('*/sales_order/massCancel'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/hold')) {
            $this->getMassactionBlock()->addItem('hold_order', array(
                 'label'=> Mage::helper('sales')->__('Hold'),
                 'url'  => $this->getUrl('*/sales_order/massHold'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/unhold')) {
            $this->getMassactionBlock()->addItem('unhold_order', array(
                 'label'=> Mage::helper('sales')->__('Unhold'),
                 'url'  => $this->getUrl('*/sales_order/massUnhold'),
            ));
        }

        $this->getMassactionBlock()->addItem('pdfinvoices_order', array(
             'label'=> Mage::helper('sales')->__('Print Invoices'),
             'url'  => $this->getUrl('*/sales_order/pdfinvoices'),
        ));

        $this->getMassactionBlock()->addItem('pdfshipments_order', array(
             'label'=> Mage::helper('sales')->__('Print Packingslips'),
             'url'  => $this->getUrl('*/sales_order/pdfshipments'),
        ));

        $this->getMassactionBlock()->addItem('pdfcreditmemos_order', array(
             'label'=> Mage::helper('sales')->__('Print Credit Memos'),
             'url'  => $this->getUrl('*/sales_order/pdfcreditmemos'),
        ));

        $this->getMassactionBlock()->addItem('pdfdocs_order', array(
             'label'=> Mage::helper('sales')->__('Print All'),
             'url'  => $this->getUrl('*/sales_order/pdfdocs'),
        ));		
		$this->getMassactionBlock()->addItem('delete_order', array(
             'label'=> Mage::helper('sales')->__('Delete order'),
             'url'  => $this->getUrl('*/sales_order/deleteorder'),
        ));	
        return $this;
    }

    public function getRowUrl($row)
    {
    	if ( FALSE  === $this->_moduleEmDeleteOrderIsActive )
    		return parent::getRowUrl($row);

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
        }

        return FALSE;
    }

    public function getGridUrl()
    {
    	if ( FALSE  === $this->_moduleEmDeleteOrderIsActive )
    		return parent::getGridUrl();
    	
        return $this->getUrl('*/*/grid', array('_current'=>TRUE));
    }
}
