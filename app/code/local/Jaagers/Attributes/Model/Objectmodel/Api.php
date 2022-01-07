<?php

class Jaagers_Attributes_Model_Objectmodel_Api extends Mage_Api_Model_Resource_Abstract
{
	
	public function testMethod($args)
    {
    	 
    	$return = 'args: ' . $args;
    	 
    	return $return;
    
    }
    
    public function createSet($name, $copyGroupsFromID = 4)
    {
    	
        $setName = trim($name);
        
        Mage::Log("Creating attribute-set with name " . $setName);
        
        if($setName == '')
        {
        	Mage::Log("Could not create attribute set with an empty name.");
        	return false;
        }
        
        //>>>> Create an incomplete version of the desired set.
        
        $model = Mage::getModel('eav/entity_attribute_set');
        
        // Set the entity type.
        
        $entityTypeID = Mage::getModel('catalog/product')->getResource()->getTypeId();
        Mage::Log("Using entity-type-ID" . $entityTypeID);
        
        $model->setEntityTypeId($entityTypeID);
        
        // We don't currently support groups, or more than one level. See
        // Mage_Adminhtml_Catalog_Product_SetController::saveAction().
        
        Mage::Log("Creating vanilla attribute-set with name [$setName].");
        
        $model->setAttributeSetName($setName);
        
        // We suspect that this isn't really necessary since we're just
        // initializing new sets with a name and nothing else, but we do
        // this for the purpose of completeness, and of prevention if we
        // should expand in the future.
        $model->validate();
        
        // Create the record.
        
        try
        {
        	$model->save();
        }
        catch(Exception $ex)
        {
        	Mage::Log("Initial attribute-set with name [$setName] could not be saved: " . $ex->getMessage());
        	return false;
        }
        
        if(($id = $model->getId()) == false)
        {
        	Mage::Log("Could not get ID from new vanilla attribute-set with name [$setName].");
        	return false;
        }
        
        Mage::Log("Set ($id) created.");
        
        //<<<<
        
        //>>>> Load the new set with groups (mandatory).
        
        // Attach the same groups from the given set-ID to the new set.
        if($copyGroupsFromID !== -1)
        {
        	Mage::Log("Cloning group configuration from existing set with ID ($copyGroupsFromID).");
        	 
        	$model->initFromSkeleton($copyGroupsFromID);
        }
        
        // Just add a default group.
        else
        {
        	Mage::Log("Creating default group [{$this->groupName}] for set.");
        
        	$modelGroup = Mage::getModel('eav/entity_attribute_group');
        	$modelGroup->setAttributeGroupName($this->groupName);
        	$modelGroup->setAttributeSetId($id);
        
        	// This is optional, and just a sorting index in the case of
        	// multiple groups.
        	// $modelGroup->setSortOrder(1);
        
        	$model->setGroups(array($modelGroup));
        }
        
        //<<<<
        
        // Save the final version of our set.
        
        try
        {
        	$model->save();
        }
        catch(Exception $ex)
        {
        	Mage::Log("Final attribute-set with name [$setName] could not be saved: " . $ex->getMessage());
        	return false;
        }
        
        /*
        if(($groupID = $modelGroup->getId()) == false)
        {
        	Mage::Log("Could not get ID from new group [$this->groupName].");
        	return false;
        }
        */
        
        Mage::Log("Created attribute-set with ID ($id) and default-group with ID ($copyGroupsFromID).");
        
        /*
        return array(
        		'SetID'     => $id,
        		'GroupID'   => $groupID,
        );
        */
        
        return $id;
    	
    }
        
    function createAttribute($name, $attributeData, $store = null)
    {

        $labelText = $name;
        $attributeCode = $attributeData->attribute_code;
 
        if($labelText == '' || $attributeCode == '')
        {
            Mage::Log("Can't import the attribute with an empty label or code.  LABEL= " . $labelText . " CODE= " . $attributeCode);
            return false;
        }
 		 
        $data = array(
                        'is_global'                     => $attributeData->is_global,
                        'frontend_input'                => $attributeData->frontend_input,
                        'default_value_text'            => $attributeData->default_value_text,
                        'default_value_yesno'           => $attributeData->default_value_yesno,
                        'default_value_date'            => $attributeData->default_value_date,
                        'default_value_textarea'        => $attributeData->default_value_textarea,
                        'is_unique'                     => $attributeData->is_unique,
                        'is_required'                   => $attributeData->is_required,
                        'frontend_class'                => $attributeData->frontend_class,
                        'is_searchable'                 => $attributeData->is_searchable,
                        'is_visible_in_advanced_search' => $attributeData->is_visible_in_advanced_search,
                        'is_comparable'                 => $attributeData->is_comparable,
                        'is_used_for_price_rules'       => $attributeData->is_used_for_price_rules,
                        'is_html_allowed_on_front'      => $attributeData->is_html_allowed_on_front,
                        'is_visible_on_front'           => $attributeData->is_visible_on_front,
                        'used_in_product_listing'       => $attributeData->used_in_product_listing,
                        'used_for_sort_by'              => $attributeData->used_for_sort_by,
                        'is_configurable'               => $attributeData->is_configurable,
                        'is_filterable'                 => $attributeData->is_filterable,
                        'is_filterable_in_search'       => $attributeData->is_filterable_in_search,
                        'backend_type'                  => $attributeData->backend_type,
                        'default_value'                 => $attributeData->default_value
                    );
        
        $data['apply_to']	= 	$attributeData->apply_to;
        
        $data['attribute_code'] = $attributeCode;
        $data['frontend_label'] = array(
                                            0 => $labelText,
                                            1 => '',
                                            3 => '',
                                            2 => '',
                                            4 => '',
                                        );
 
        $model = Mage::getModel('catalog/resource_eav_attribute');
 
        $model->addData($data);
 
        $entityTypeID = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
        $model->setEntityTypeId($entityTypeID);
 
        $model->setIsUserDefined(1);
 
        try
        {
            $model->save();
        }
        catch(Exception $ex)
        {
            Mage::Log("Attribute " . $labelText . " could not be saved: " . $ex->getMessage());
            return false;
        }
 
        $id = $model->getId();
 
        return $id;
    }
    
    public function editAttribute($name, $attributeData, $store = null) {
    
    	$labelText = $name;
    	$attributeCode = $attributeData->attribute_code;
    
    	if($labelText == '' || $attributeCode == '')
    	{
    		Mage::Log("Can't edit the attribute with an empty label or code.  LABEL= " . $labelText . " CODE= " . $attributeCode);
    		return false;
    	}
    
    	$data = array(
    			'is_global'                     => $attributeData->is_global,
    			'frontend_input'                => $attributeData->frontend_input,
    			'default_value_text'            => $attributeData->default_value_text,
    			'default_value_yesno'           => $attributeData->default_value_yesno,
    			'default_value_date'            => $attributeData->default_value_date,
    			'default_value_textarea'        => $attributeData->default_value_textarea,
    			'is_unique'                     => $attributeData->is_unique,
    			'is_required'                   => $attributeData->is_required,
    			'frontend_class'                => $attributeData->frontend_class,
    			'is_searchable'                 => $attributeData->is_searchable,
    			'is_visible_in_advanced_search' => $attributeData->is_visible_in_advanced_search,
    			'is_comparable'                 => $attributeData->is_comparable,
    			'is_used_for_price_rules'       => $attributeData->is_used_for_price_rules,
    			'is_html_allowed_on_front'      => $attributeData->is_html_allowed_on_front,
    			'is_visible_on_front'           => $attributeData->is_visible_on_front,
    			'used_in_product_listing'       => $attributeData->used_in_product_listing,
    			'used_for_sort_by'              => $attributeData->used_for_sort_by,
    			'is_configurable'               => $attributeData->is_configurable,
    			'is_filterable'                 => $attributeData->is_filterable,
    			'is_filterable_in_search'       => $attributeData->is_filterable_in_search,
    			'backend_type'                  => $attributeData->backend_type,
    			'default_value'                 => $attributeData->default_value,
    	);
    
    	$data['apply_to']	= 	$attributeData->apply_to;
    
    	$data['attribute_code'] = $attributeCode;
    	$data['frontend_label'] = array(
    			0 => $labelText,
    			1 => '',
    			3 => '',
    			2 => '',
    			4 => '',
    	);
    	
    	Mage::Log($data);
    
    	$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', $attributeData->attribute_code);
    
    	if ($attributeId) {
    		$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);

    		Mage::Log($attribute->getData());
    		
    		$attribute->addData($data);
    		
    		Mage::Log($attribute->getData());
    		
    		try {
    			$attribute->save();
    			return true;
    		} catch(Exception $e) {
    			return $e;
    		}
    	}
    
    }
    
    public function addAttributeToSet($setId, $groupName, $attributeId)
    {
        
        $model = Mage::getModel('catalog/resource_eav_attribute');
        
        $model->load($attributeId);
        
        $model->setAttributeSetId($setId);
        $model->setAttributeGroupName($groupName);
        
    	try
        {
            $model->save();
        }
        catch(Exception $ex)
        {
            $error = "Could not be saved: " . $ex->getMessage();
            return $error;
        }
    
    	return true;
    	
    }
    
}

