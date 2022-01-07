<?php
class Dutchworld_Directcontact_IndexController extends Mage_Core_Controller_Front_Action {
    function indexAction()
    {
        error_log('test4');
        echo "TEST41";
        //$this->_redirect('checkout/onepage', array('_secure'=>true));
    }
    function sendemailAction() {
        echo "TEST5";
    }
}
