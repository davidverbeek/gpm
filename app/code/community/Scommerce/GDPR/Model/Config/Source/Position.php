<?php
/**
 * @category   Scommerce
 * @package    Scommerce_GDPR
 * @author     Scommerce Mage <core@scommerce-mage.co.uk>
 */
class Scommerce_GDPR_Model_Config_Source_Position
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => '1',
                'label' => 'Top',
            ),
            array(
                'value' => '2',
                'label' => 'Bottom',
            ),
        );
    }
}