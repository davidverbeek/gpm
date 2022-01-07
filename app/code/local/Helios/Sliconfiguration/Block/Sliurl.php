<?php

/**
 * @author    Helios Team
 * @copyright Copyright (c) 2018 Helios
 * @package   Helios_Sli
 */
class Helios_Sliconfiguration_Block_Sliurl extends Mage_Core_Block_Template {
	
	protected function _construct() {
		parent::_construct();
	}

	public function getLoadingType() {
		return Mage::helper('sliconfiguration')->getLoadingType();
	}

	public function getSliRacJs(){
		return Mage::helper('sliconfiguration')->getSliRacJs();
	}

	public function getSliWrapperJs(){
		return Mage::helper('sliconfiguration')->getSliWrapperJs();
	}

	public function getGyzsRacJs(){
		return Mage::helper('sliconfiguration')->getGyzsRacJs();
	}

	public function getGyzsWrapperJs(){
		return Mage::helper('sliconfiguration')->getGyzsWrapperJs();
	}

	public function getGyzsRacCss(){
		return Mage::helper('sliconfiguration')->getGyzsRacCss();
	}

	public function getSliRacCss(){
		return Mage::helper('sliconfiguration')->getSliRacCss();
	}
	
}