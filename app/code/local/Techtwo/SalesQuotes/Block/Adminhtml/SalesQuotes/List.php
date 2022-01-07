<?php

class Techtwo_SalesQuotes_Block_Adminhtml_SalesQuotes_List extends Mage_Adminhtml_Block_Widget_Container
{
    protected $_blockGroup = 'adminhtml';
    
    public function __construct()
    {
        parent::__construct();

        $this->_addButtonLabel = Mage::helper('techtwo_salesquotes')->__('Create New Quote');

        $this->_controller = 'salesquotes';
        $this->_headerText = Mage::helper('techtwo_salesquotes')->__('Quotes');

        $this->setTemplate('widget/grid/container.phtml');

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/create')) {
            $this->_addButton('add', array(
                'label'     => $this->getAddButtonLabel(),
                'onclick'   => 'setLocation(\'' . $this->getCreateUrl() .'\')',
                'class'     => 'add',
            ));
        }
    }

    protected function _prepareLayout()
    {        
        $this->setChild('grid',
            $this->getLayout()->createBlock('techtwo_salesquotes/adminhtml_salesQuotes_grid',
            $this->_controller . '_grid')->setSaveParametersInSession(true)
        );
        return parent::_prepareLayout();
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    protected function getAddButtonLabel()
    {
        return $this->_addButtonLabel;
    }

    protected function getBackButtonLabel()
    {
        return $this->_backButtonLabel;
    }

    protected function _addBackButton()
    {
        $this->_addButton('back', array(
            'label'     => $this->getBackButtonLabel(),
            'onclick'   => 'setLocation(\'' . $this->getBackUrl() .'\')',
            'class'     => 'back',
        ));
    }

    public function getHeaderCssClass()
    {
        return 'icon-head ' . parent::getHeaderCssClass();
    }

    public function getHeaderWidth()
    {
        return 'width:50%;';
    }
}
