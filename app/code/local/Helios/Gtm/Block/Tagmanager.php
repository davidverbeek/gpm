<?php

/**
 * @author    Helios Team
 * @copyright Copyright (c) 2017 Helios
 * @package   Helios_Gtm
 */
class Helios_Gtm_Block_Tagmanager extends Mage_Core_Block_Template
{
	protected function _construct()
	{
		parent::_construct();
	}

	public function getEnabled()
	{
		return Mage::helper('gtm')->getEnabled();
	}

	public function getGtmCode()
	{
		return Mage::helper('gtm')->getGtmCode();
	}
}