<?php
/**
 * Autor: SG
 * Datum: 13.04.2015 19:24 Uhr
 * (c) 2015 ||GEISSWEB|
 */

class Geissweb_Euvatgrouper_Helper_Admin extends Geissweb_Euvatgrouper_Helper_Abstract
{
	public function getYearRange()
	{
		$first = date("Y", strtotime(Mage::getModel('sales/order')->getCollection()->setOrder('entity_id', 'ASC')->getFirstItem()->getCreatedAt()));
		$last = date("Y", strtotime(Mage::getModel('sales/order')->getCollection()->setOrder('entity_id', 'DESC')->getFirstItem()->getCreatedAt()));
		$years = array();
		for($i=$first; $i<=$last; $i++)
		{
			$years[$i] = $i;
		}
		return $years;
	}

	public function getMonthsArray()
	{
		return array(
			'01' => '01',
			'02' => '02',
			'03' => '03',
			'04' => '04',
			'05' => '05',
			'06' => '06',
			'07' => '07',
			'08' => '08',
			'09' => '09',
			'10' => '10',
			'11' => '11',
			'12' => '12',
			'1q' => Mage::helper('euvatgrouper')->__('1st Quarter'),
			'2q' => Mage::helper('euvatgrouper')->__('2nd Quarter'),
			'3q' => Mage::helper('euvatgrouper')->__('3rd Quarter'),
			'4q' => Mage::helper('euvatgrouper')->__('4th Quarter'),
			'yy' => Mage::helper('euvatgrouper')->__('Yearly'),
		);
	}
}