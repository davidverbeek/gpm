<?php
class Hs_Sales_IndexController extends
    Mage_Core_Controller_Front_Action
{
    
    
   /* public function indexAction()
    {
       
        $this->loadLayout();
        $this->renderLayout();
    }*/
    
    public function pdfinvoicesAction(){ 
        $orderId=$this->getRequest()->getParam('orderid');
       // $invoicesIds = $this->getRequest()->getPost('invoice_ids');
        if (!empty($orderId)) {
            $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                ->addAttributeToSelect('*')
                //->addAttributeToFilter('entity_id', array('in' => $invoicesIds))
                ->addAttributeToFilter('order_id', $orderId);
//                ->addFieldToFilter('store_id',Mage::app()->getStore()->getId())
              //  ->getFirstItem();
                //->load();
//            echo "<pre>"; print_r($invoices->getData()); exit;
            if (!isset($pdf)){
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
            } else {
                $pages = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                $pdf->pages = array_merge ($pdf->pages, $pages->pages);
            }

            return $this->_prepareDownloadResponse('invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').
                '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }
	
	public function pdfcreditmemoAction(){
        $orderId=$this->getRequest()->getParam('orderid');
       // $invoicesIds = $this->getRequest()->getPost('invoice_ids');
        if (!empty($orderId)) {
            $creditmemo = Mage::getResourceModel('sales/order_creditmemo_collection');
            $creditmemo->addFieldToFilter('order_id', $orderId);
//                ->addFieldToFilter('store_id',Mage::app()->getStore()->getId())
              //  ->getFirstItem();
                //->load();
//            echo "<pre>"; print_r($invoices->getData()); exit;
            if (!isset($pdf)){
                $pdf = Mage::getModel('sales/order_pdf_creditmemo')->getPdf($creditmemo);
            } else {
                $pages = Mage::getModel('sales/order_pdf_creditmemo')->getPdf($creditmemo);
                $pdf->pages = array_merge ($pdf->pages, $pages->pages);
            }

            return $this->_prepareDownloadResponse('creditmemo'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').
                '.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }
    
    
}
