<?php

/**
 * Class Hs_Common_Model_Cron
 *
 * @copyright   Copyright (c) 2019 Helios
 */
class Hs_Common_Model_Cron
{
    public function sendEmailforUnmatchIdealverpakking()
    {
        Mage::log('Unmatch Idealverpakking cron started.', null, Hs_Common_Helper_Unmatchedideal::LOG_FILE_NAME);
        $filepath = Mage::helper('common/unmatchedideal')->getProductCollectionForUnmatchIdealverpakking();
        if ($filepath) {
            Mage::log('Unmatch Idealverpakking cron completed successfully.', null, Hs_Common_Helper_Unmatchedideal::LOG_FILE_NAME);
            return "Cron Completed Successfully";
        }
    }
}