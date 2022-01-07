<?php

/**
 * Class Helios_Omniafeed_Model_Feed
 *
 * @copyright   Copyright (c) 2018 Helios
 */
class Helios_Sliconfiguration_Model_Hssli extends Mage_Core_Model_Abstract {

	public function updateSLIScripts() {
		
		// get all files need to update
		$sli_rac_css = Mage::helper('sliconfiguration')->getSliRacCss();
		$sli_rac_js = Mage::helper('sliconfiguration')->getSliRacJS();
		$sli_wrapper_js = Mage::helper('sliconfiguration')->getSliWrapperJs();

		// Theme PATH
		$basePath = 'frontend/base/default';
		$themePath = 'frontend/'. Mage::getSingleton('core/design_package')->getPackageName() . DS . Mage::getSingleton('core/design_package')->getTheme('frontend');

		// CSS Folder Create if not exist
		if (!file_exists(Mage::getBaseDir('skin') . DS . $themePath . Helios_Sliconfiguration_Helper_Data::CSS_PATH)) {
		  mkdir(Mage::getBaseDir('skin') . DS . $themePath . Helios_Sliconfiguration_Helper_Data::CSS_PATH, 0777, true);
		}

		if (!file_exists(Mage::getBaseDir('skin') . DS . $basePath . Helios_Sliconfiguration_Helper_Data::CSS_PATH)) {
		  mkdir(Mage::getBaseDir('skin') . DS . $basePath . Helios_Sliconfiguration_Helper_Data::CSS_PATH, 0777, true);
		}

		// JS Folder Create if not exist
		if (!file_exists(Mage::getBaseDir('skin') . DS . $themePath . Helios_Sliconfiguration_Helper_Data::JS_PATH)) {
		  mkdir(Mage::getBaseDir('skin') . DS . $themePath . Helios_Sliconfiguration_Helper_Data::JS_PATH, 0777, true);
		}
		if (!file_exists(Mage::getBaseDir('skin') . DS . $basePath . Helios_Sliconfiguration_Helper_Data::JS_PATH)) {
		  mkdir(Mage::getBaseDir('skin') . DS . $basePath . Helios_Sliconfiguration_Helper_Data::JS_PATH, 0777, true);
		}

		// SLI RAC CSS
		$download_rac_css = file_get_contents(Helios_Sliconfiguration_Helper_Data::SLI_PROTOCOL . $sli_rac_css);
		// Write the contents back to the file
		file_put_contents(Mage::getBaseDir('skin') . DS . $themePath . Helios_Sliconfiguration_Helper_Data::CSS_PATH . Helios_Sliconfiguration_Helper_Data::RAC_CSS, $download_rac_css);
		file_put_contents(Mage::getBaseDir('skin') . DS . $basePath . Helios_Sliconfiguration_Helper_Data::CSS_PATH . Helios_Sliconfiguration_Helper_Data::RAC_CSS, $download_rac_css);

		// SLI RAC JS
		$download_rac_js = file_get_contents(Helios_Sliconfiguration_Helper_Data::SLI_PROTOCOL . $sli_rac_js);
		// Write the contents back to the file
		file_put_contents(Mage::getBaseDir('skin') . DS . $themePath . Helios_Sliconfiguration_Helper_Data::JS_PATH . Helios_Sliconfiguration_Helper_Data::RAC_JS, $download_rac_js);
		file_put_contents(Mage::getBaseDir('skin') . DS . $basePath . Helios_Sliconfiguration_Helper_Data::JS_PATH . Helios_Sliconfiguration_Helper_Data::RAC_JS, $download_rac_js);

		// SLI RAC CSS
		$download_wrapper_js = file_get_contents(Helios_Sliconfiguration_Helper_Data::SLI_PROTOCOL . $sli_wrapper_js);
		// Write the contents back to the file
		file_put_contents(Mage::getBaseDir('skin') . DS . $themePath . Helios_Sliconfiguration_Helper_Data::JS_PATH . Helios_Sliconfiguration_Helper_Data::WRAPPER_JS, $download_wrapper_js);
		file_put_contents(Mage::getBaseDir('skin') . DS . $basePath . Helios_Sliconfiguration_Helper_Data::JS_PATH . Helios_Sliconfiguration_Helper_Data::WRAPPER_JS, $download_wrapper_js);


		return true;

	}

}