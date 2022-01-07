<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml newsletter queue grid block action item renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Hs_Featured_Block_Adminhtml_Featured_Grid_Renderer_Specialfromdate extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {

        $html='<input class="input-text-fromdate special-fromdate-'.$row->getId().'" type="text" id="from_'.$row->getId().'" value="'.$row->getSpecialFromDate().'" name="date_from['.$row->getId().']" />';
        $html .='<img title="'.$this->__('Select date').'" id="date_from_trig_'.$row->getId().'" class="v-middle" src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'/adminhtml/default/default/images/grid-cal.gif" >';
        //return $html;

        //$html = parent::render($row);

      //  $html .= '<button onclick="updatefromdate(this, '. $row->getId() .'); return false">' . Mage::helper('featured')->__('Update') . '</button>';

        $html .= "<script type='text/javascript'>// <![CDATA[
                    Calendar.setup({
                        inputField : 'from_".$row->getId()."',
                        ifFormat : '%Y-%m-%e',
                        button : 'date_from_trig_".$row->getId()."',
                        align : 'Bl',
                        singleClick : true,
                        showsTime: true
                    });

                    // ]]></script>";

        return $html;
    }
}
