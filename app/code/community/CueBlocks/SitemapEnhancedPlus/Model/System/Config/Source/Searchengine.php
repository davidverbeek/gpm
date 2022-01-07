<?php

/**
 * Description of Category
 * @package   CueBlocks_SitemapEnhancedPlus
 *** @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_System_Config_Source_Searchengine
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'Bing', 'label' => 'Bing & Yahoo'),
            array('value' => 'Google', 'label' => 'Google'),
//            array('value' => 'Yahoo', 'label' => 'Yahoo',),
//            array('value' => 'Ask', 'label' => 'Ask',)
//            array('value' => 'MoreOver', 'label' => 'MoreOver.com',)
        );
    }

}
