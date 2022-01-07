<?php
/**
 * Created by Ravinder.
 * Date: 19/2/16
 * Time: 11:49 AM
 */
class CueBlocks_CronStatus_Model_CronStatus  extends Mage_Cron_Model_Schedule
{
    public function getCronStatus()
    {
        try{
            $lastRun  = $this->getCollection()
                ->addFieldToFilter('job_code','cueBlocks_cronStatusCheck')
                ->setOrder('schedule_id','desc')
                ->getFirstItem()
                ->getCreatedAt();
            $timeDifference = (strtotime(strftime('%Y-%m-%d %H:%M:%S', time())) - strtotime($lastRun))/60;
            if($timeDifference > 60) {
                return false;
            }
            return true;
        } catch(Exception $e) {
            return false;
        }
    }
}