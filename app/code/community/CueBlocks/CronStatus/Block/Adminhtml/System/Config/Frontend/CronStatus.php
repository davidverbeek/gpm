<?php
/**
 * Created by Ravinder.
 * Date: 19/2/16
 * Time: 11:33 AM
 */
class CueBlocks_CronStatus_Block_Adminhtml_System_Config_Frontend_CronStatus extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $cronRunning = Mage::getModel('cb_cronstatus/cronStatus')->getCronStatus();
        $gray = "rgba(200, 202, 198, 0.74)";
        $runningStyle = $cronRunning? "#28C528":$gray;
        $notRunningStyle = $cronRunning?$gray:"#F7031C";

        return "<div style='width: 280px;float: left'>
                    <span style='background-color: $runningStyle;color: #fafafa;padding: 4px 4px 4px 4px;'>
                        <strong>Running :)</strong>
                    </span>
                    <span style='background-color: $notRunningStyle; color: #fafafa;padding: 4px 4px 4px 4px;'>
                        <strong>Not Running :(</strong>
                    </span>
                 </div>";
    }
}