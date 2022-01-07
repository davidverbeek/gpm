<?php

class Techtwo_FeedbackReview_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        if ( !Mage::helper('techtwo_feedbackreview')->isActive() )
            return $this->_forward('404');

        $this->loadLayout();
        $this->renderLayout();
    }
}

?>