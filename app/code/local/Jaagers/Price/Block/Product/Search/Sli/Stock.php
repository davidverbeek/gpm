<?php
/**
 * Created by Helios.
 * User: Helio
 * Date: 9/10/17
 * Time: 7:19 PM
 */

class Jaagers_Price_Block_Product_Search_Sli_Stock extends Mage_Core_Block_Template
{
    protected $_product;
    protected $_mode = 'grid';

    public function __construct()
    {

        parent::__construct();
        $this->setTemplate('api/sli/product/stock.phtml');
    }

    public function getProduct()
    {

        return $this->_product;
    }

    public function setProduct($product)
    {

        $this->_product = $product;
    }

    public function setMode($mode)
    {

        $this->_mode = $mode;
    }

    public function getMode()
    {

        return $this->_mode;
    }

}