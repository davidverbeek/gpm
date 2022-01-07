<?php

class CueBlocks_SitemapEnhancedPlus_Model_System_Config_Source_ImageLicense
{
    public function toOptionArray()
    {
        return array(
            array('value' => '0', 'label' => 'No License Info'),
            array('value' => '1', 'label' => 'Custom License Url'),
            array('value' => 'https://creativecommons.org/licenses/by/4.0/legalcode', 'label' => 'Creative Commons: Attribution CC BY'),
            array('value' => 'https://creativecommons.org/licenses/by-nd/4.0/legalcode', 'label' => 'Creative Commons: Attribution-NoDerivsCC BY-ND'),
            array('value' => 'https://creativecommons.org/licenses/by-nc-sa/4.0/legalcode', 'label' => 'Creative Commons: Attribution-NonCommercial-ShareAlike CC BY-NC-SA'),
            array('value' => 'https://creativecommons.org/licenses/by-sa/4.0/legalcode', 'label' => 'Creative Commons: Attribution-ShareAlike CC BY-SA'),
            array('value' => 'https://creativecommons.org/licenses/by-nc/4.0/legalcode', 'label' => 'Creative Commons: Attribution-NonCommercial CC BY-NC'),
            array('value' => 'https://creativecommons.org/licenses/by-nc-nd/4.0/legalcode', 'label' => 'Creative Commons: Attribution-NonCommercial-NoDerivs CC BY-NC-ND'),
        );
    }
}