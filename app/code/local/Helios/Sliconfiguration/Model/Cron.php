<?php

/**
 * Class Helios_Omniafeed_Model_Cron
 *
 * @copyright   Copyright (c) 2017 Helios
 */
class Helios_Sliconfiguration_Model_Cron {

	public function updateSLIScripts() {

		$filepath = Mage::getModel('sliconfiguration/hssli')->updateSLIScripts();

		if ($filepath) {
			Mage::log('SLI CSS JS Updated', null, Helios_Sliconfiguration_Helper_Data::LOG_FILE_NAME);
			return "Cron Completed Successfully";
		}
	}
}