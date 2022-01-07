<?php

class Techtwo_ImportExport_Model_Entity_Attribute_Source_Table extends Mage_Eav_Model_Entity_Attribute_Source_Table {

    /**
     * Create new attribute option and return the new option_id.
     *
     * @param string $value
     * @return integer
     */
    public function addOption ($value) {

        // add the option
        $option['attribute_id'] = $this->getAttribute()->getId();
        $option['value'][0][0] = $value;
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $setup->addAttributeOption($option);
        unset($setup);

        // force reload options
        $this->_options = array();
        
        // return new option id
        return parent::getOptionId($value);
    }

    /**
     * Retrieve option_id for given attribute option value. Creates new option when not found.
     * Usage: $optionId = $attribute->getSource()->getOptionId($optionName);
     *
     * @param string $value
     * @return integer
     */
    public function getOptionId ($value) {

        $optionId = parent::getOptionId($value);

        if (is_null($optionId) && $value !== "" && !is_null($value)) {
            // add new option
            $optionId = $this->addOption($value, false);
        }

        return $optionId;
    }
    
}
