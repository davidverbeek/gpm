<?php   
class Helios_Feedbackreview_Block_Index extends Mage_Core_Block_Template{   


    public function getReviewDate($rDate)
    {
        $array = str_split($rDate,2);
        $dateFormat=$array[0].$array[1]."-".$array[2]."-".$array[3];
        
        return $dateFormat;
    }


}