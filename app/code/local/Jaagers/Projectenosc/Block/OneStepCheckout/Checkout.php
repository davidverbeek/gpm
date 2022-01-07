<?php
class Jaagers_Projectenosc_Block_OneStepCheckout_Checkout extends Idev_OneStepCheckout_Block_Checkout
{

	public function getProjects() {

		$userinfo = Mage::helper('customer')->getCustomer()->getData();

        $params = array();
        $params['debiteurnr'] = (string)$userinfo['mavis_debiteurnummer'];
        $params['lopend'] = true;

        $result = $this->soapConnect('GetProjecten', 'GetProjectenResult', $params);

        if(isset($result->WebshopPrjct)) {
            $result = $result->WebshopPrjct;
            if(isset($result->debinr))
                $result = array($result);

            foreach($result as $key => $value) {
                $valuesArray = (array)$value->adrssn;
                if(!count($valuesArray)) {
                    unset($result[$key]);
                }
            }

            return $result;
        }

        return false;

    }

    public function soapConnect($call, $response, $params) {

		$client = new Zend_Soap_Client((string)Mage::getConfig()->getNode('default/mavis/apipath'));

        try {
            $result = $client->{$call}($params);
            return $result->{$response};
		} catch (Exception $e) {
            Mage::Log($e);
        }

    }

	public function getAddressesHtmlSelect($type)
    {
        
        $projects = $this->getProjects();

        $userinfo = Mage::helper('customer')->getCustomer()->getData();

        $config = Mage::getStoreConfig('projectenosc');

        if(!isset($userinfo['mavis_debiteurnummer']) || !(int)$config['general']['enabled'] || !count($projects)) {
        	return parent::getAddressesHtmlSelect();
        }

        if ($this->isCustomerLoggedIn()) {

            $options = array();
            
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value'=>$address->getId(),
                    'label'=>$address->format('oneline')
                );
            }

            $addressId = '';
            
            if (empty($addressId)) {
                if ($type=='billing') {
                    $address = $this->getCustomer()->getDefaultBillingAddress();
                } else {
                    $address = $this->getCustomer()->getDefaultShippingAddress();
                }
                if ($address) {
                    $addressId = $address->getId();
                }
            }

            
            if ($address) {
                $addressIde = $address->getCustomerAddressId();
                if($addressIde){
                    $addressId = $addressIde;
                }
            }

	        //CUSTOM JAAGERS
            
	        if(isset($userinfo['mavis_debiteurnummer'])) {

		        if($type=='billing') {

                    $mavisProjectAddressId = array();
                    
                    $defaultAddress = $this->getCustomer()->getDefaultShippingAddress();
                    
			        $projectoptions = array(array('value' => $address->getId(), 'label'=>$address->format('oneline')));
                    
                    unset($defaultAddress);

                    $projectadresidoptions = array();

					$projectAddressId = null;

                    if(count($projects) > 0 && $projects != false)
                    {
    					foreach ($projects as $project) {
                            
                            if(isset($project->adrssn->int) && count($project->adrssn) > 0) {
    
    							if(count($project->adrssn->int) > 1) {
                                    foreach($project->adrssn->int as $mpai)
                                    {
                                        $mavisProjectAddressId['p'.(string)$project->projnr]['a'.(string)$mpai] = 
                                            $project->projnr . ' - ' . $project->prnam1 . ' ' . $project->prnam2 . ' - ' . $project->pradr . ' ' . $project->prpost . ' ' . $project->prwnpl;
                                    }
    							} else {
    								$mavisProjectAddressId['p'.(string)$project->projnr]['a'.(string)$project->adrssn->int] = 
                                            $project->projnr . ' - ' . $project->prnam1 . ' ' . $project->prnam2 . ' - ' . $project->pradr . ' ' . $project->prpost . ' ' . $project->prwnpl;
    							}
    
    						}
    		            }

                        foreach ($this->getCustomer()->getAddresses() as $address) {
                            if(!is_null($address->getData('mavis_projectadresid')) && !is_null($address->getData('mavis_adresid')))
                            {
                                // check project adress
                                if(isset($mavisProjectAddressId['p'.$address->getData('mavis_projectadresid')])) {
                                    // check verzendadres
                                    if(isset($mavisProjectAddressId['p'.$address->getData('mavis_projectadresid')]['a'.$address->getData('mavis_adresid')]))
                                    {
                                        $addr = $mavisProjectAddressId['p'.$address->getData('mavis_projectadresid')]['a'.$address->getData('mavis_adresid')];
                                    }else{
                                        reset($mavisProjectAddressId['p'.$address->getData('mavis_projectadresid')]); // reset to begin
                                        $addr = current($mavisProjectAddressId['p'.$address->getData('mavis_projectadresid')]);
                                    }

                                    $projectadresidoptions[$address->getData('entity_id')] = $address->getData('mavis_projectadresid');

                                    $projectoptions[] = array(
                	                    'value'=>$address->getData('entity_id'),
                	                    'label'=>$addr
                	                );

                                }
                            }
    		            }
                    }

                    if(count($projectoptions) > 0)
	                {
    			        $projectselect = $this->getLayout()->createBlock('core/html_select')
    		                ->setName('billing_address_id')
    		                ->setId('billing-address-select')
    		                ->setClass('address-select')
    		                ->setExtraParams('onchange="billing.newAddress(!this.value)"')
    		                ->setOptions($projectoptions);
			            return $projectselect->getHtml() .
                        "<input type='hidden' id='id_projectnr' name='id_projectnr' />
                        <script type='text/javascript'>
                            j = jQuery.noConflict();
                            j(document).ready(function(){

                                j('#billing-address-select').change(function(){
                                    var obj = j.parseJSON('" . json_encode($projectadresidoptions) . "');
                                    var value = j(this).val();
                                    console.log(value);
                                    console.log(obj[value]);
                                    j('#id_projectnr').val(obj[value]);
                                })

                            })
                        </script>";
                    }
                    
                    return '<br /><p style="font-weight: bold;">'.$this->__('Uw heeft momenteel geen geldige project verzend adressen.').'</p>';
		        }

	        }
            if ($type=='billing') {
                $address = $this->getQuote()->getBillingAddress();
            } else {
                $address = $this->getQuote()->getShippingAddress();
            }
           if ($address) {
                    $addressIde = $address->getCustomerAddressId();
                    if($addressIde){
                        $addressId = $addressIde;
                    }
            }
	       
	       	// END CUSTOM JAAGERS

            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
                ->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
                ->setValue($addressId)
                ->setOptions($options);

            //$select->addOption('', Mage::helper('checkout')->__('New Address'));

            $isPost = $this->getRequest()->getPost();
            $isPost = (!empty($isPost));
            $selectedValue = $this->getRequest()->getPost('billing_address_id', false);


            if($this->getNewAddressSelectValueOnError($type)){
                 $select->setValue('');
            }

            return $select->getHtml();
        }
        
        return '';
    
    }

}
			