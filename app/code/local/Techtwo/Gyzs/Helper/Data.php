<?php

class Techtwo_Gyzs_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getLabelImage( $_product, $format='' )
    {
	    if ( 'large' === $format )
		    $format = '360-';
	    else
		    $format = '';

        // on 1 value it's a string, on multi an array abd empty it's false >.<
        $original_value = $label = $_product->getAttributeText('label');
        if ( $label && is_array($label) )
            $label = reset($label);
		
		if (!$label)
			return NULL;
		
		$filename = $format.trim(preg_replace('`[^a-z0-9-_]`','-',strtolower($label)),'-').'.png';
		
		return (object) array(
			'url'   => Mage::getDesign()->getSkinUrl( 'images/labels/banners/'.$filename ),
			'label' => $label,
			'value' => $original_value
		);
        
    }
}
