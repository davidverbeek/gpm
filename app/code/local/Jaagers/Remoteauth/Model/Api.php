<?php
class Jaagers_Remoteauth_Model_Api extends Mage_Api_Model_Resource_Abstract
{        
	
	public function authenticate( $website, $email, $password ) { 
        
        $store = $this->_getStore($website); 
        
        $session = $this->_getCustomerSession(); 
        $authenticated = $session->login($email, $password); 
        
        return $authenticated; 

    }
}