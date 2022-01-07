<?php

class Jaagers_Myapi_Model_Api_V2 extends Mage_Api_Model_Resource_Abstract
{

    public function attributecodeid($args)
    {
		try {
			$attributecode = $args->attributecode; 

			if(isset($attributecode)){
				$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', $attributecode);
				return $attributeId;
				
			}
		}catch(Exception $e) {
			Mage::log($e,null,'soapattributeinfo.log');
			return $e;
		}
        
    }
	
	public function attributesetid($args)
    {
		try {
			$attributeset = $args->attributeset; 

			if(isset($attributeset)){
									
				$attrSetName = $attributeset;
				$attributeSetId = Mage::getModel('eav/entity_attribute_set')->load($attrSetName, 'attribute_set_name')->getAttributeSetId();
				
				return $attributeSetId;
				
			}
		}catch(Exception $e) {
			Mage::log($e,null,'soapattributeinfo.log');
			return $e;
		}
        
    }
	
	public function isassigned($args)
    {
		try {
			
			$attributecode = $args->attributecodeid; 
			$attrSetName = $args->attributesetid; 
			
			if(isset($attributecode) && isset($attrSetName)){
				
				$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', $attributecode);
				$attributeSetId = Mage::getModel('eav/entity_attribute_set')->load($attrSetName, 'attribute_set_name')->getAttributeSetId();
			}
			
			$attrinfo['attributeid'] = $attributeId;
			$attrinfo['attributesetid'] = $attributeSetId;
			
			return json_encode($attrinfo);
			//return $attributeId.''.$attributeSetId;
			
		}catch(Exception $e) {
			Mage::log($e,null,'soapattributeinfo.log');
			return $e;
		}
        
    }
    
}

