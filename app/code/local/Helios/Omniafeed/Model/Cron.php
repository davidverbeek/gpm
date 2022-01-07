<?php

/**
 * Class Helios_Omniafeed_Model_Cron
 *
 * @copyright   Copyright (c) 2017 Helios
 */
class Helios_Omniafeed_Model_Cron
{
    public function generateOminaFeed()
    {
        Mage::log('Omnia feed cron started.', null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
        $filepath = Mage::getModel('omniafeed/feed')->collectFeedData();
        if ($filepath) {
            Mage::log('Omnia feed cron completed successfully.', null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
            return "Cron Completed Successfully";
        }
    }

    public function generateGoogleFeed()
    {
        $startTime = time();
        Mage::log('Google feed cron started.', null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
        $filepath = Mage::getModel('omniafeed/feed')->prepareFeed();
        if ($filepath) {
            Mage::log('Google feed cron completed successfully.', null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
            $endTime = time();
            Mage::log('Google feed total generation time::'.($endTime - $startTime), null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
        }
    }

    public function generatePotentialCalculation()
    {
        $startTime = time();
        Mage::log('Potential Calcualtion cron started.', null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
        $result = Mage::getModel('omniafeed/potentialcount')->potentialCalculation();
        if ($result) {
            Mage::log('Potential Calcualtion completed successfully.', null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
            $endTime = time();
            Mage::log('Potential Calcualtion total execution time::'.($endTime - $startTime), null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
        }
    }
}