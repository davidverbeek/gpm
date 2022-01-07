<?php

class Jaagers_Remoteauth_Model_Api_V2 extends Mage_Api_Model_Resource_Abstract
{
    
    public function authenticate( $website, $credentials ) { 
        
    	Mage::app()->setCurrentStore('english');

		$email = $credentials->email;
		$password = $credentials->password;
		
		$session = Mage::getSingleton('customer/session', array('name' => 'frontend'));
		
		try {
			
			$session->login( $email, $password );
			return true;

		} catch (Exception $e)	{

			Mage::Log($e);

		}

		return false;

	}
    
}

