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

                    //$order->setData('mavis_ordernr', $args->mavisOrderNr);
                    //$order->save();

                    return $order->getData('mavis_ordernr');

                    return true;
                    
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

