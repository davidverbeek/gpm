<?php

class Helios_Sliconfiguration_Helper_Data extends Mage_Core_Helper_Abstract {

	const LOG_FILE_NAME = 'sliscript.log';
	const SLI_PROTOCOL = 'https:';
	const CSS_PATH = '/css/sli/';
	const JS_PATH = '/js/sli/';
	const RAC_CSS = 'sli-rac.css';
	const RAC_JS = 'sli-rac.config.js';
	const WRAPPER_JS = 'wrapper.js';

	/**
	 * To get SLI loading type.
	 */
	public function getLoadingType() {
		return Mage::getStoreConfig('sliconfiguration/sli_script_load/sli_load_from', Mage::app()->getStore());
	}

	/**
	 * To get SLI RAC JS from SLI.
	 */
	public function getSliRacJs(){
		return Mage::getStoreConfig('sliconfiguration/sli_header_fields/thirdparty_js_url', Mage::app()->getStore());
	}

	/**
	 * To get SLI RAC CSS from SLI.
	 */
	public function getSliRacCss(){
		return Mage::getStoreConfig('sliconfiguration/sli_header_fields/thirdparty_css_url', Mage::app()->getStore());
	}

	/**
	 * To get SLI RAC JS from GYZS.
	 */
	public function getGyzsRacJs(){
		return Mage::getStoreConfig('sliconfiguration/sli_header_fields/local_js_path', Mage::app()->getStore());
	}

	/**
	 * To get SLI RAC CSS from GYZS.
	 */
	public function getGyzsRacCss(){
		return Mage::getStoreConfig('sliconfiguration/sli_header_fields/local_css_path', Mage::app()->getStore());
	}

	/**
	 * To get SLI Wrapper JS from SLI.
	 */
	public function getSliWrapperJs(){
		return Mage::getStoreConfig('sliconfiguration/sli_footer_field/sli_thirdparty_js_footer', Mage::app()->getStore());
	}

	/**
	 * To get SLI Wrapper JS from GYZS.
	 */
	public function getGyzsWrapperJs(){
		return Mage::getStoreConfig('sliconfiguration/sli_footer_field/sli_local_js_footer', Mage::app()->getStore());
	}
}