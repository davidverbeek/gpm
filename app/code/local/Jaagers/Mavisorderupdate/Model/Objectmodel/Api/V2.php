<?php

class Jaagers_Mavisorderupdate_Model_Objectmodel_Api_V2 extends Mage_Api_Model_Resource_Abstract
{

    public function update($args)
    {

        try {

            /* Load order by increment ID */

            $order = Mage::getModel('sales/order')->loadByIncrementId($args->magentoOrderNr);

            if($order) {
                
                try {

                    /* Set custom Mavis ordernr */

                    $order->setData('mavis_ordernr', $args->mavisOrderNr);
                    $order->save();

                    $checkOrder = Mage::getModel('sales/order')->loadByIncrementId($args->magentoOrderNr);

                    //Mage::Log($checkOrder->getData());

                    return $checkOrder->getData('mavis_ordernr');
                    
                } catch(Exception $e) {
                    return $e;
                }

            } else {
                return 'Unknown Order Increment ID';
            }

        } catch(Exception $e) {

            return $e;

        }
    
    }
    
}

