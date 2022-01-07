<?php
class CueBlocks_SitemapEnhancedPlus_Model_System_Config_Backend_Category extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
        $value = $this->getValue();

        // It works in combination with the DISABLE option
        // we have removed this option since we have added a yes/no
        if (in_array(-1, $value)) {
            $this->setValue(array());
        }
        return parent::_beforeSave();
    }
}
