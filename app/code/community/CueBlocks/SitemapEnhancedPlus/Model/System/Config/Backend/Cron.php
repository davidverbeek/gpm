<?php

/**
 * Backend Model for Cron
 *
 * @package   CueBlocks_SitemapEnhancedPlus
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Model_System_Config_Backend_Cron extends Mage_Core_Model_Config_Data
{

    const CRON_STRING_PATH = 'crontab/jobs/sitemapEnhancedPlus_generate/schedule/cron_expr';
    const CRON_MODEL_PATH = 'crontab/jobs/sitemapEnhancedPlus_generate/run/model';

    protected function _afterSave()
    {
        $enabled = $this->getData('groups/cron/fields/enabled/value');
        $time = $this->getData('groups/cron/fields/time/value');
        $frequency = $this->getData('groups/cron/fields/frequency/value');

        $frequencyDaily = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY;
        $frequencyWeekly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
        $frequencyMonthly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY;

        if ($enabled) {
            $cronExprArray = array(
                intval($time[1]),                              # Minute
                intval($time[0]),                              # Hour
                ($frequency == $frequencyMonthly) ? '1' : '*', # Day of the Month
                '*',                                           # Month of the Year
                ($frequency == $frequencyWeekly) ? '1' : '*',  # Day of the Week
            );

            $cronExprString = join(' ', $cronExprArray);
        } else {
            $cronExprString = '';
        }

        try {
            Mage::getModel('core/config_data')
                ->load(self::CRON_STRING_PATH, 'path')
                ->setValue($cronExprString)
                ->setPath(self::CRON_STRING_PATH)
                ->save();

            Mage::getModel('core/config_data')
                ->load(self::CRON_MODEL_PATH, 'path')
                ->setValue((string)Mage::getConfig()->getNode(self::CRON_MODEL_PATH))
                ->setPath(self::CRON_MODEL_PATH)
                ->save();

        } catch (Exception $e) {
            throw new Exception(Mage::helper('cron')->__('Unable to save the cron expression.'));
        }
    }

}
