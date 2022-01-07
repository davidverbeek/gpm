<?php

/**
 * Class Helios_Omniafeed_Helper_Data
 *
 * @copyright   Copyright (c) 2017 Helios
 */
class Helios_Omniafeed_Helper_Data extends Mage_Core_Helper_Abstract
{

		/**
		 * Global constants
		 */
		const LOG_FILE_NAME = 'omnia_feed.log';
		const OMNIA_FEED_FILE_NAME = 'omnia_feed.xml';
		const GENERAL_FEED_FILE_NAME = 'general_feed.xml';
		const GOOGLE_FEED_FILE_NAME = 'google_feed.xml';
		const GOOGLE_FEED_HIGH_FILE_NAME = 'google_high_feed.xml';
		const ADCHIEVE_FEED_FILE_NAME = 'adchieve_feed.xml';
		const PRODUCT_NOT_SOLD = 'NOT SOLD';
		const PRODUCT_LOW = 'LOW';
		const PRODUCT_HIGH = 'HIGH';
		const PRODUCT_MID = 'MID';
		const DECIMAL = 2;
		const VAT_AMMOUNT = 21; // in percentage
		const FEED_LOCALE = 'en_US';

        /* Stock Monitoring Starts */
        const GOOGLE_FEED_ZERO_STOCK_FILE_NAME = 'google_feed_zero_stock.log';
        const SKU_STOCK_ALWAYS_AVAILABLE = array("1091121","1070306","1071053");
        /* Stock Monitoring Ends */

		//'stock_sold',
		//'yearly_sales',
		//'potential',
		private $googleFeedAttributes = array(
			'is_salable',
			'merk',
			'name',
			'short_description',
			'url_path',
			'leverancierartikelnr',
			'eancode',
			'sku',
			'price',
			'image',
			'prijsfactor',
			'verkoopeenheid',
			'cost',
			'afwijkenidealeverpakking',
			'idealeverpakking',
			'verkoopeenheid',
			'verpakkingsean_'
		);

		private $customFiltersAttributes = array('special_price', 'special_from_date', 'special_to_date', 'artikelstatus', 'cost', 'verpakkingseanhoeveelheid_', 'verpakkingsvorm', 'erplangeomschrijving', 'erpomschrijving', 'leverancier', 'leveranciernaam','aandrijving_','aandrijving_maat_','aandrijving_maat_mm','aansluitdiameter_mm','aansluiting_inch','aansluiting_mm','aantal_aders_st','aantal_contactdozen_st','aantal_deuren_st','aantal_haken_st','aantal_lagen_st','aantal_legborden_st','aantal_schoten_st','aantal_sluitstiften_st','aantal_tanden_st','aantal_tanden_tpi_st','accutype_','afgegeven_vermogen_w','afmeting_','aisi_','amperage_a','asgat_mm','bediening_','bekijk_hoek_','belastbaar_kg_','bestaande_uit_delen','bevestigingsdikte_mm','bewerking_','boordiameter_mm','boordiepte_mm','boormaat_','boring_mm','brandwerend_','breedte_','breedte_band_mm','breedte_beitel_mm','breedte_bek_mm','breedte_blad_mm','breedte_cm','breedte_houtmaat_mm','breedte_mes_mm','breedte_mm','breedte_schild_mm','breedte_tip_mm','breedte_voeg_mm','breedte_zaag_mm','buitendiameter_mm','capaciteit_hout_mm','capaciteit_lengte_houtdraad_mm','capaciteit_mm','cilindersparing_mm','cilinders_aantal_','co2_system_','color','comfort_system_','c_system_','deurbreedte_max_','deurbreedte_mm','deurdikte_','deurdikte_min_','deurdikte_mm','deurdikte_skg_mm','deurgewicht_kg','deurhoogte_','diameter_','diameter_ader_mm','diameter_asgat_mm','diameter_bout_m','diameter_bout_mm','diameter_draad_m','diameter_draad_mm','diameter_gaten_mm','diameter_inwendig_mm','diameter_kop_mm','diameter_mm','diameter_nagelschroef_mm','diameter_ring_mm','diameter_schacht_mm','diameter_schijf_mm','diameter_schroef_','diameter_schroef_mm','diameter_tip_mm','diameter_wiel_mm','diameter_zaagblad_mm','diepte_mm','dikte_band_mm','dikte_glas_mm_','dikte_isolatie_mm','dikte_mm','dikte_vijl_mm','din_','doornmaat_mm','draadsoort_','draad_m','draagkracht_bij_2_scharnieren_','draagkracht_bij_3_scharnieren_','draagvermogen_kg','draairichting_','excentrisch_','gatenpatroon_','geluidsdruk_db','geluidsniveau_dba_','gereedschapsopname_','geschikt_voor_','geslacht_','gevarenfunctie_','gewicht_g','gewicht_kg','glasgoot_','groef_','handschoenmaat_','hart_op_hart_mm','hoh_afstand_binnenzijde_','hoh_afstand_buitenzijde_','hoh_binnenzijde_','hoh_buitenzijde_','hoogte_','hoogte_cm','hoogte_mm','houtmaat_mm','inbouwlengte_mm','inclusiefexclusief_','inclusief_montage_','inhoud_cc','inhoud_g','inhoud_kg','inhoud_l','inhoud_ml','inschroefdiepte_mm','inschroeflengte_mm','kerntrekbeveiliging_','kettingsteek_inch','keurmerken_','keurmerk_','klassekwaliteitsnorm_','klasse_','kledingmaat_','klembereik_mm','klemwijdte_mm','kleur_','kleur_k_','kopmontage_','korrel_','kwaliteit_materiaal_','leeftijd_','lengte_','lengte_beitel_mm','lengte_binnen_mm','lengte_boor_mm','lengte_buiten_mm','lengte_cm','lengte_frees_mm','lengte_inch','lengte_kabel_m','lengte_kop_mm','lengte_lemmet_mm','lengte_m','lengte_mm','lengte_nagelschroef_mm','lengte_plug_mm','lengte_rail_mm','lengte_rol_m','lengte_schild_mm','lengte_slang_m','lengte_steel_cm','lengte_steel_mm','lengte_stift_mm','lengte_tang_mm','lengte_vijl_mm','lengte_zaag_mm','luchtdoorlaat_cm','lumen_lm_','maaswijdte_','maat_','manufacturer','materiaal_','materiaal_basis_','materiaal_handvat_','materiaal_steel_','materiaal_voorplaat_','max_belasting_ton','max_uitzetting_','meetbereik_mm','met_rem_','model_','model_anker_','model_kop_','model_sluitplaat_','model_tandvorm_','model_vijl_','model_voorplaat_','montage_','montage_deurblad_','normering_','nummer_','omtrek_','oogdiameter_inwendig_mm','oogdiameter_uitwendig_mm','opberglengte_mm','overschilderbaar_','pc_maat_mm','plaat_of_boutgat_','power_factor_pf_','raambreedte_mm','radius_mm','railsysteem_','schaafbreedte_mm','schoenmaat_','skg_','slagen_per_min_1_belast_1min','slagen_per_min_2_onbelast_1min','slagkracht_j','sleutelwijdte_mm','slottype_','sluitkracht_deurdranger_','sluitkracht_en_','sluitsysteem_','snijdiameter_mm','snijlengte_mm','soort_','soort_glas_','spanning_v','spoed_mm','spouwmaat_max_mm','spouwmaat_mm','steekmaat_','techniek_accu_','techniek_lamp_','terugligging_cm','thema_','toelaatbare_belasting_kn','toepassing_','toepassing_arbeid_','toepassing_machine_','toepassing_materiaal_','toerental_1_onbelast_trmin','totale_hoogte_mm_','tredensporten_','tronic_wired_','tronic_zwave_','tussenruimte_','type_','type_deuroplossing_','uitvoering_','uitvoering_linksrechts_','verankeringsdiepte_minimaal_mm','verankeringsdiepte_mm','vermogen_a','vermogen_ah','vermogen_w','verpakking_','verpakt_per_st','vloeibelasting_kn','volume_opvang_l','voorplaat_','voorzien_van_','vorm_','vouwbreedte_mm','wattage_','werkende_lengte_mm','werkhoogte_m','zaagdiepte_mm','custom_product_description','draagvermogen_per_2_stuks_kg', 'custom_product_name');

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
		 * get directory path for feed
		 *
		 * @return string
		 */
		public function _baseDirectory() {
				return Mage::getBaseDir() . DS;
		}

		/**
		 * Get product collection for all products combine sales data
		 *
		 * @return Object
		 */
		public function getTotalSalesData() {
			try {

				// idealeverpakking Attribute Id
				$idealeverpakking = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'idealeverpakking');

				// afwijkenidealeverpakking Attribute Id
				$afwijkenidealeverpakking = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'afwijkenidealeverpakking');

				// EAN Code Attribute Id
				// $eancode = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'eancode');

				// Product Collection
				$_totalSalesData = Mage::getResourceModel('catalog/product_collection');
				$_totalSalesData->getSelect()

					// Join idealeverpakking from EAV
					->joinLeft(array('eav_varchar' => $this->_getTableName('catalog_product_entity_text')), 'e.entity_id = eav_varchar.entity_id and eav_varchar.attribute_id=' . $idealeverpakking, array('idealeverpakking' => 'value'))

					// Join afwijkenidealeverpakking from EAV
					->joinLeft(array('eav_varchar_' => $this->_getTableName('catalog_product_entity_text')), 'e.entity_id = eav_varchar_.entity_id and eav_varchar_.attribute_id=' . $afwijkenidealeverpakking, array('afwijkenidealeverpakking' => 'value'))

					// sales_flat_order_item join on product ID
					->join(
						array('sfoi' => $this->_getTableName('sales_flat_order_item')),
						'sfoi.product_id = e.entity_id',
						array('sales_since_start' => 'sum(sfoi.qty_invoiced)', 'sales_since_start' => 'CASE WHEN eav_varchar_.value = 1 THEN SUM(sfoi.qty_invoiced / eav_varchar.value) ELSE SUM(sfoi.qty_invoiced) END'
						)
					)
					->where('e.type_id="simple"')
					->group('e.sku');

				return $_totalSalesData;

			} catch (Exception $e) {
				Mage::log($e->getMessage(), null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME, true);
			}
		}

		/**
		 * Get collection of product for yearly sales data by product sku
		 *
		 * @return Object
		 */
		public function getTotalSalesYearlyData() {
			try {

				// idealeverpakking Attribute Id
				$idealeverpakking = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'idealeverpakking');

				// afwijkenidealeverpakking Attribute Id
				$afwijkenidealeverpakking = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'afwijkenidealeverpakking');

				$_totalSalesYearlyData = Mage::getResourceModel('catalog/product_collection');

				$_totalSalesYearlyData->getSelect()
					
					// Join idealeverpakking from EAV
					->joinLeft(array('eav_varchar' => $this->_getTableName('catalog_product_entity_text')), 'e.entity_id = eav_varchar.entity_id and eav_varchar.attribute_id=' . $idealeverpakking, array('idealeverpakking' => 'value'))
					// Join afwijkenidealeverpakking from EAV
					->joinLeft(array('eav_varchar_' => $this->_getTableName('catalog_product_entity_text')), 'e.entity_id = eav_varchar_.entity_id and eav_varchar_.attribute_id=' . $afwijkenidealeverpakking, array('afwijkenidealeverpakking' => 'value'))
					// sales_flat_order_item join on product ID
					->join(
						array('sfoi' => $this->_getTableName('sales_flat_order_item')),
						'sfoi.product_id = e.entity_id',
						array('sales_since_start' => 'sum(sfoi.qty_invoiced)',
							'monthdiffcount' => '@monthdiffcount1 := TIMESTAMPDIFF(MONTH, e.created_at, CURDATE())', 'actual_sales_year' => 'IF(@monthdiffcount1 < 12, IF(eav_varchar_.value = 1, SUM(sfoi.qty_invoiced / eav_varchar.value), SUM(sfoi.qty_invoiced)) * (12 / @monthdiffcount1), IF(eav_varchar_.value = 1, SUM(sfoi.qty_invoiced / eav_varchar.value), SUM(sfoi.qty_invoiced)))'
							// 'actual_sales_year' => 'CASE WHEN @monthdiffcount1 < 12 THEN (SUM(sfoi.qty_invoiced)*(12/@monthdiffcount1)) ELSE SUM(sfoi.qty_invoiced) END'
						)
					)
					->where('e.type_id="simple" and sfoi.created_at >= DATE_SUB(NOW(),INTERVAL 1 YEAR)')
					// ->group('e.created_at')
					->group('e.sku');

				return $_totalSalesYearlyData;

			} catch (Exception $e) {
				Mage::log($e->getMessage(), null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
			}
		}

		/**
		 * Get product collection for all products sales data by product sku
		 *
		 * @return Object
		 */
		public function getProductCollection() {
			try {

				// idealeverpakking Attribute Id
				$idealeverpakking = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'idealeverpakking');

				// afwijkenidealeverpakking Attribute Id
				$afwijkenidealeverpakking = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'afwijkenidealeverpakking');

				// EAN Code Attribute Id
				$eancode = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'eancode');

				$_productCollection = Mage::getResourceModel('catalog/product_collection');

				$_productCollection->getSelect()
					
					// Join EAN Code from EAV
					->joinLeft(
							array('eav_ean' => $this->_getTableName('catalog_product_entity_text')),
							'e.entity_id = eav_ean.entity_id and eav_ean.attribute_id=' . $eancode,
							array('eancode' => 'value')
					)
					
					// Join idealeverpakking from EAV
					->joinLeft(array('eav_varchar' => $this->_getTableName('catalog_product_entity_text')), 'e.entity_id = eav_varchar.entity_id and eav_varchar.attribute_id=' . $idealeverpakking, array('idealeverpakking' => 'value'))
					
					// Join afwijkenidealeverpakking from EAV
					->joinLeft(array('eav_varchar_' => $this->_getTableName('catalog_product_entity_text')), 'e.entity_id = eav_varchar_.entity_id and eav_varchar_.attribute_id=' . $afwijkenidealeverpakking, array('afwijkenidealeverpakking' => 'value'))

					// potential columns join on product sku to get all data related potetinal
					->joinLeft(
						array('spp' => $this->_getTableName('sales_product_potential')),
						'spp.sku = e.sku',
						array('monthdiffcount', 'sold_qty_last_year_partial', 'sold_qty_last_year', 'total_sold_qty', 'stock_sold', 'yearly_sales','potential')
					)

					->where("e.sku <> '' and e.type_id='simple'")
					->group('e.sku');

				return $_productCollection;

			} catch (Exception $e) {
				Mage::log($e->getMessage(), null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
			}
		}

		/**
		 * Get product collection for all google feed related products data by sku
		 *
		 * @return Object productCollection
		 */
		public function getProductCollectionGoogleFeed() {
			try {
				$_productCollection = Mage::getResourceModel('catalog/product_collection')
					->addAttributeToSelect(array_merge($this->googleFeedAttributes, $this->customFiltersAttributes))
					// ->addAttributeToFilter(
					// 	'sku',
					// 	array('in' => array( '1010673', '1012165', '1193265', '1199960', '1199980', '1011025', '1011027', '1020361'))
					// )
				;

				$_productCollection->getSelect()
					
					// potential columns join on product sku to get all data related potetinal
					->joinLeft(
						array('spp' => $this->_getTableName('sales_product_potential')),
						'spp.sku = e.sku',
						array('monthdiffcount', 'sold_qty_last_year_partial', 'sold_qty_last_year', 'total_sold_qty', 'stock_sold', 'yearly_sales','potential')
					)

					// stock_information join on product sku to get stock info
					->joinLeft(
						array('si' => $this->_getTableName('cataloginventory_stock_mavis')),
						'si.product_id = e.entity_id',
						array('stock_level' => 'si.qty')
					)

					->where("e.sku <> '' and e.type_id='simple'")
					->group('e.sku');

				return $_productCollection;

			} catch (Exception $e) {
					Mage::log($e->getMessage(), null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
			}
		}

		/**
		 * Get product collection for all google feed related products data by sku
		 *
		 * @return Object productCollection
		 */
		public function getProductCollectionForPotentialCount() {
			try {

				$_productCollection = Mage::getResourceModel('catalog/product_collection')
					->addAttributeToSelect(array('sku', 'price', 'cost'))
					// ->addAttributeToFilter(
					// 	'sku',
					// 	array('in' => array( '1010673', '1012165', '1193265', '1199960', '1199980', '1011025', '1011027', '1020361'))
					// )
				;

				$_productCollection->getSelect()
					
					// Month Count of product created
					->columns(
						array(
							'monthdiffcount' => '@monthdiffcount:=TIMESTAMPDIFF(MONTH,e.created_at,CURDATE())'
						)
					)
					
					// calculation of product last year sold partial (because less than 12 month of created product need to calculate average base on that)
					->columns(
						array(
							'sold_qty_last_year_partial' => new Zend_Db_Expr(
									'@sold_qty_last_year_partial := (SELECT SUM(innersfoi.qty_invoiced)
									FROM ' . $this->_getTableName('sales_flat_order_item') . ' AS innersfoi
									WHERE innersfoi.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
									AND innersfoi.product_id = e.entity_id GROUP BY innersfoi.sku)'
							)
						)
					)

					// calculation of product last year sold
					->columns(
						array(
							'sold_qty_last_year' => 'CASE WHEN @monthdiffcount >= 12 THEN @sold_qty_last_year_partial ELSE (@sold_qty_last_year_partial * (12 / @monthdiffcount)) END'
						)
					)
					
					// sales_flat_order_item join on product ID
					->joinLeft(
						array('sfoi' => $this->_getTableName('sales_flat_order_item')),
						'sfoi.product_id = e.entity_id',
						array('total_sold_qty' => 'sum(sfoi.qty_invoiced)')
					)

					->where("e.sku <> '' and e.type_id='simple'")
					->group('e.sku');

				return $_productCollection;

			} catch (Exception $e) {
				Mage::log($e->getMessage(), null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
			}
		}

		public function savePotentialData($productCollection, $_currentProductAverageSalesQty){
			// Prepare SQL and Store data into DB
			Mage::log("Preparing SQL to store potential into DB.", null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);

			try{

				$sql = "INSERT INTO " . $this->_getTableName('sales_product_potential') . " (sku, monthdiffcount, sold_qty_last_year_partial, sold_qty_last_year, total_sold_qty, stock_sold, yearly_sales, potential) VALUES "; 

				foreach ($productCollection as $product) {

					$Monthdiffcount = $product->getMonthdiffcount() ? $product->getMonthdiffcount() : 0;
					$SoldQtyLastYearPartial = $product->getSoldQtyLastYearPartial() ? $product->getSoldQtyLastYearPartial() : 0;
					$SoldQtyLastYear = $product->getSoldQtyLastYear() ? $product->getSoldQtyLastYear() : 0;
					$TotalSoldQty = $product->getTotalSoldQty() ? $product->getTotalSoldQty() : 0;
					
					$potential = 0;
					// check Ideal package value and calculate qty
					$idealeverpakking = !empty($product->getIdealeverpakking()) ? $product->getIdealeverpakking() : 1;
					$lifetimeSales = $product->getTotalSoldQty() ? $product->getTotalSoldQty() : 0;
					$yearlySales = $product->getSoldQtyLastYear() ? $product->getSoldQtyLastYear() : 0;
					
					if ($product->getAfwijkenidealeverpakking()) {
						$lifetimeSales = $product->getTotalSoldQty() / $idealeverpakking;
						$yearlySales = $product->getSoldQtyLastYear() / $idealeverpakking;
					}

					if ($yearlySales > 0) {
						$potential = floor(($yearlySales * 100) / $_currentProductAverageSalesQty);
					}
					$potentialCode = $this->getPotentialCode($potential);


					$lifetimeSales = round($lifetimeSales, Helios_Omniafeed_Helper_Data::DECIMAL);
					$yearlySales = round($yearlySales, Helios_Omniafeed_Helper_Data::DECIMAL);

					$updateArray [] = "('" .$product->getSku() . "'," . $Monthdiffcount . "," . $SoldQtyLastYearPartial . "," . $SoldQtyLastYear . "," . $TotalSoldQty . "," . $lifetimeSales . "," . $yearlySales . ",'" . $potentialCode . "')";
				}

				$sql .= implode(",", $updateArray) . " ON DUPLICATE KEY UPDATE monthdiffcount = VALUES(monthdiffcount), sold_qty_last_year_partial = VALUES(sold_qty_last_year_partial), sold_qty_last_year = VALUES(sold_qty_last_year), total_sold_qty = VALUES(total_sold_qty), stock_sold = VALUES(stock_sold), yearly_sales = VALUES(yearly_sales), potential = VALUES(potential)";

				$connection = $this->_getConnection('core_write');

				$result = $connection->query($sql);

				Mage::log("Affected Raws : " . $result->rowCount(), NULL, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);

				Mage::log("Potential stored into DB.", null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
				return true;

			} catch (Exception $e) {
				Mage::log($e->getMessage(), null, Helios_Omniafeed_Helper_Data::LOG_FILE_NAME);
			}

		}

		/**
		* gets potential code from potential count
		*
		* @param $potential int
		*
		* @return string
		*/
		private function getPotentialCode($potential) {
			if ($potential > 0 && $potential <= 50) {
				return Helios_Omniafeed_Helper_Data::PRODUCT_LOW;
			}
			if ($potential > 50 && $potential <= 100) {
				return Helios_Omniafeed_Helper_Data::PRODUCT_MID;
			}
			if ($potential > 100) {
				return Helios_Omniafeed_Helper_Data::PRODUCT_HIGH;
			}
			return Helios_Omniafeed_Helper_Data::PRODUCT_NOT_SOLD;
		}

        /* Stock Monitoring Starts */
        public function MonitorGoogleFeedStocks($sku_stock_always_available, $sku_actual_zero_quantity) {
            if(count($sku_actual_zero_quantity) > 0) {
                $zero_html = "<div>Google Feed are having zero stock for below Sku's</div>";
                foreach($sku_actual_zero_quantity as $zero_sku=>$val_zero) {
                    $zero_html .= "<div style='margin-top:10px;'><div>SKU :- ".$zero_sku.". STOCK :- ".$val_zero."</div></div>";
                }
                Mage::log(date("Y/m/d")." === Zero Stocks For ".json_encode($sku_actual_zero_quantity), NULL, Helios_Omniafeed_Helper_Data::GOOGLE_FEED_ZERO_STOCK_FILE_NAME);
                $this->sendGoogleFeedMailAction($zero_html);
            } else {
                Mage::log(date("Y/m/d")." === Stocks are available For ".json_encode($sku_stock_always_available)."", NULL, Helios_Omniafeed_Helper_Data::GOOGLE_FEED_ZERO_STOCK_FILE_NAME);
            }
        }
        /* Stock Monitoring Ends */

        /*
        * -----------------------------------------------------------------------------
        * Send email to concerned persons in case of zero stocks
        * -----------------------------------------------------------------------------
        */
        public function sendGoogleFeedMailAction($html){
            $to      = 'georgestamgyzs@gmail.com,dverbeek.2019@gmail.com,peter@gyzs.nl';
            $subject = 'ALERT: Google Feed Zero Stock '.date("Y/m/d");
            $message = $html;
            $headers = 'From: webmaster@gyzs.nl' . "\r\n" .
                'Reply-To: webmaster@gyzs.nl' . "\r\n" .
                'MIME-Version: 1.0' . "\r\n" .
                'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
            try {
                mail($to, $subject, $message, $headers);
            }
            catch (Exception $e) {
                Mage::log(date("Y/m/d")."===".print_r($e->getMessage()), NULL, Helios_Omniafeed_Helper_Data::GOOGLE_FEED_ZERO_STOCK_FILE_NAME);
            }
        }

}
