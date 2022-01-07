<?php
class Hs_Common_Helper_Unmatchedideal extends Mage_Core_Helper_Abstract {

	/**
	 * Global constants
	 */
	const LOG_FILE_NAME = 'unmatch_idealverpakking.log';

	const UNMATCHED_IDEALPACKAGEVERPAKKING_DIR = 'unmatchedIdealverpakking';
	
	const UNMATCHED_IDEALPACKAGEVERPAKKING_FILE = 'unmatch_idealverpakking.csv';

	// get connection based on requirment
	protected function _getConnection($type = 'core_read') {
		return Mage::getSingleton('core/resource')->getConnection($type);
	}

	/**
	 * get magento table name
	 *
	 * @param $tableName
	 *
	 * @return string
	 */
	public function _getTableName($tableName) {
		return Mage::getSingleton('core/resource')->getTableName($tableName);
	}

	/**
	 * Get product collection for all products sales data by product sku
	 *
	 * @return Object
	 */
	public function getProductCollectionForUnmatchIdealverpakking() {
		try {
			//Check if the directory already exists.
			if(!is_dir(Mage::getBaseDir('var') . DS . Hs_Common_Helper_Unmatchedideal::UNMATCHED_IDEALPACKAGEVERPAKKING_DIR)){
				mkdir(Mage::getBaseDir('var') . DS . Hs_Common_Helper_Unmatchedideal::UNMATCHED_IDEALPACKAGEVERPAKKING_DIR, 0755, true);
			}

			$file = fopen(Mage::getBaseDir('var') . DS . Hs_Common_Helper_Unmatchedideal::UNMATCHED_IDEALPACKAGEVERPAKKING_DIR . DS . Hs_Common_Helper_Unmatchedideal::UNMATCHED_IDEALPACKAGEVERPAKKING_FILE, 'w');

			fputcsv($file, array('sku', 'afwijkenidealeverpakking', 'idealeverpakking', 'verpakkingseanhoeveelheid'));

			// idealeverpakking Attribute Id
			$idealeverpakking = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'idealeverpakking');

			// afwijkenidealeverpakking Attribute Id
			$afwijkenidealeverpakking = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'afwijkenidealeverpakking');

			// verpakkingseanhoeveelheid_ Attribute Id
			$verpakkingseanhoeveelheid_ = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'verpakkingseanhoeveelheid_');


			$_productCollection = Mage::getResourceModel('catalog/product_collection')
			->addAttributeToSelect(array('sku'));

			$_productCollection->getSelect()

				// Join idealeverpakking from EAV
				->joinLeft(array('eav_varchar' => $this->_getTableName('catalog_product_entity_text')), 'e.entity_id = eav_varchar.entity_id and eav_varchar.attribute_id=' . $idealeverpakking, array('idealeverpakking' => 'value'))
				
				// Join afwijkenidealeverpakking from EAV
				->joinLeft(array('eav_text' => $this->_getTableName('catalog_product_entity_text')), 'e.entity_id = eav_text.entity_id and eav_text.attribute_id=' . $afwijkenidealeverpakking, array('afwijkenidealeverpakking' => 'value'))

				// Join verpakkingseanhoeveelheid_ from EAV
				->joinLeft(array('eav_int' => $this->_getTableName('catalog_product_entity_int')), 'e.entity_id = eav_int.entity_id and eav_int.attribute_id=' . $verpakkingseanhoeveelheid_, array('verpakkingseanhoeveelheid_' => 'value'))

				// Join verpakkingseanhoeveelheid_ with option values
				->joinLeft(array('eav_int_option_value' => $this->_getTableName('eav_attribute_option_value')), 'eav_int.value = eav_int_option_value.option_id and eav_int.attribute_id=' . $verpakkingseanhoeveelheid_, array('verpakkingseanhoeveelheid_value' => 'value'))

				->where("e.sku <> '' and e.type_id='simple'")
				->where("eav_text.value = 0")
				->where("eav_int_option_value.value != eav_varchar.value");

			foreach ($_productCollection as $product) {
				$tempSKUArray = array();
				$tempSKUArray['sku'] = $product->getSku();
				$tempSKUArray['afwijkenidealeverpakking'] = $product->getAfwijkenidealeverpakking();
				$tempSKUArray['idealeverpakking'] = $product->getIdealeverpakking();
				$tempSKUArray['verpakkingseanhoeveelheid'] = $product->getVerpakkingseanhoeveelheidValue();
					

				fputcsv($file, $tempSKUArray);
				
				$sku = $product->getSku();
				$idealeverpakking = $product->getIdealeverpakking();
				$verpakkingseanhoeveelheid = $product->getVerpakkingseanhoeveelheidValue();
				
				/* ST: 25-11-2019 :: Below function is executed and change the $verpakkingseanhoeveelheid value to $idealeverpakking */
				
				$this->updateVerpakkingseanhoeveelheid($sku,$idealeverpakking);
			}

			fclose($file);
			
			
			$status = $this->sendEmailForUnmatched();

			return $status;

		} catch (Exception $e) {
			Mage::log($e->getMessage(), null, Hs_Common_Helper_Unmatchedideal::LOG_FILE_NAME);
		}
	}

	protected function attributeValueExists($arg_attribute, $arg_value) {
		$attribute_model        = Mage::getModel('eav/entity_attribute');
		$attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;

		$attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
		$attribute              = $attribute_model->load($attribute_code);

		$attribute_table        = $attribute_options_model->setAttribute($attribute);
		$options                = $attribute_options_model->getAllOptions(false);
		
		foreach($options as $option) {
			if ($option['label'] == $arg_value) {
				return $option['value'];
			}
		}

		return false;
	}
	protected function updateVerpakkingseanhoeveelheid($sku,$idealeverpakking){
		
		if(isset($sku) && $sku != '') {
				
			$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku); // LOAD PRODUCT BASED ON SKU TO CHECKED PRODUCT EXISTANCE

			if($product) {
				$attr_id = $this->attributeValueExists('verpakkingseanhoeveelheid_', $idealeverpakking);
				
				Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
				$product->setData('verpakkingseanhoeveelheid_',$attr_id);
				
				if($product->save()){
					Mage::log("Attribute Verpakkingseanhoeveelheid Updated successfully " . $sku, NULL, 'update-product-verpakkingseanhoeveelheid.log');
				} else {
					Mage::log("Problem in saving data : " . $sku, NULL, 'update-product-verpakkingseanhoeveelheid.log');
				}
			} else {
				Mage::log("Product Not Found : " . $sku, NULL, 'update-product-verpakkingseanhoeveelheid.log');
			}
		}		
	}
	
	protected function sendEmailForUnmatched($value=''){
		// Get Config variables
		$subject = 'Unmatched idealeverpakking';
		$name = Mage::getStoreConfig('sales_email/manualproductorder/name', Mage::app()->getStore());
		$emailTo = Mage::getStoreConfig('sales_email/manualproductorder/email', Mage::app()->getStore());
		$emailFrom = Mage::getStoreConfig('trans_email/ident_sales/email', Mage::app()->getStore());
		$emailFromName = Mage::getStoreConfig('trans_email/ident_sales/name', Mage::app()->getStore());

		$emailBody = 'Unmatched idealeverpakking, please check attachement.';
		//Send E-Mail.
		$mail = new Zend_Mail('utf-8');
		$mail->addTo(explode(',', $emailTo))	
			->setBodyHtml($emailBody)
			->setSubject($subject)
			->setFrom($emailFrom, $emailFromName);

		$file = Mage::getBaseDir('var') . DS . Hs_Common_Helper_Unmatchedideal::UNMATCHED_IDEALPACKAGEVERPAKKING_DIR . DS . Hs_Common_Helper_Unmatchedideal::UNMATCHED_IDEALPACKAGEVERPAKKING_FILE;
		$attachment = file_get_contents($file);

		$mail->createAttachment(
			$attachment,
			Zend_Mime::TYPE_OCTETSTREAM,
			Zend_Mime::DISPOSITION_ATTACHMENT,
			Zend_Mime::ENCODING_BASE64,
			Hs_Common_Helper_Unmatchedideal::UNMATCHED_IDEALPACKAGEVERPAKKING_FILE
		);

		try{
			//Confimation E-Mail Send
			$mail->send();
			Mage::log("email successfully sent to " . $emailTo, null, Hs_Common_Helper_Unmatchedideal::LOG_FILE_NAME);
			return true;
		} catch(Exception $e) {
		 Mage::log($e->getMessage(), null, Hs_Common_Helper_Unmatchedideal::LOG_FILE_NAME);
		 return false;
		}
	}

}
	 