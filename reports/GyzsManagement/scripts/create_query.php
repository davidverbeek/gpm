<?php

 include "../define/constants.php";
 include "../config/dbconfig.php";
 
$table = "mage_catalog_product_entity AS mcpe
INNER JOIN mage_catalog_category_product AS mccp ON mccp.product_id = mcpe.entity_id
INNER JOIN price_management_data AS pmd ON pmd.product_id = mcpe.entity_id
LEFT JOIN price_management_averages AS pma ON pma.product_id = pmd.product_id
LEFT JOIN mage_catalog_product_entity_varchar AS mcpev ON mcpev.entity_id = pmd.product_id AND mcpev.attribute_id = '".EAN."'
LEFT JOIN mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = pmd.product_id AND mcpei.attribute_id = '".MERK."'
LEFT JOIN mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value
LEFT JOIN mage_catalog_product_entity_varchar AS mcpev_productname ON mcpev_productname.entity_id = pmd.product_id AND mcpev_productname.attribute_id = '".PRODUCTNAME."'

LEFT JOIN mage_catalog_product_entity_text AS mcpev_grossprice ON mcpev_grossprice.entity_id = pmd.product_id AND mcpev_grossprice.attribute_id = '".GROSSUNITPRICE."'

LEFT JOIN mage_catalog_product_entity_text AS mcpet_af ON mcpet_af.entity_id = pmd.product_id AND mcpet_af.attribute_id = '".afwijkenidealeverpakking."'
LEFT JOIN mage_catalog_product_entity_text AS mcpet ON mcpet.entity_id = pmd.product_id AND mcpet.attribute_id = '".IDEALEVERPAKKING."'

LEFT JOIN mage_catalog_product_entity_varchar AS mcpev_afw ON mcpev_afw.entity_id = pmd.product_id AND mcpev_afw.attribute_id = '".afwijkenidealeverpakking."'
LEFT JOIN mage_catalog_product_entity_varchar AS mcpev_ideal ON mcpev_ideal.entity_id = pmd.product_id AND mcpev_ideal.attribute_id = '".IDEALEVERPAKKING."'

LEFT JOIN mage_catalog_product_entity_decimal AS mcped ON mcped.entity_id = pmd.product_id AND mcped.attribute_id = '".COST."'

LEFT JOIN mage_catalog_product_entity_decimal AS mcped_selling_price ON mcped_selling_price.entity_id = pmd.product_id AND mcped_selling_price.attribute_id = '".PRICE."'
LEFT JOIN price_management_afzet_data AS pmaf ON pmaf.product_id = pmd.product_id";

//mcpev_grossprice
//mcpev_netprice

//mcpip
// Table's primary key
$primaryKey = 'DISTINCT mcpe.entity_id';


// Extra Where
if($_POST['categories']) {
   //$selected_categories = implode(",",$_REQUEST['categories']);
  $extra_where = "mccp.category_id IN (".$_POST['categories'].")";
} else {
   //$extra_where = "mccp.category_id IN ('')";
  $extra_where = "";  
}


if(isset(($_POST['hdn_filters'])) && $_POST['hdn_filters'] != '') {
  switch($_POST['hdn_filters']) {
    case "1":
      $extra_where = "CAST(pmd.buying_price AS DECIMAL(10,".$scale.")) > CAST(pmd.selling_price AS DECIMAL(10,".$scale."))";  
    break;
    
    case "2":
      $extra_where = "CAST(pmd.profit_percentage_selling_price AS DECIMAL(10,".$scale.")) < 40";   
    break;
    
    case "3":
      for($deb=0;$deb<=10;$deb++) { 
        $debt_number = intval(4027100 + $deb);
        $deb_col_selling_price = "pmd.group_".$debt_number."_debter_selling_price";
        $extra_where .= "(".$deb_col_selling_price." > (CAST(pmd.selling_price AS DECIMAL(10,".$scale.")))) OR "; 
      }
      $extra_where = rtrim($extra_where," OR ");  
    break;
    
    case "4":
      for($deb=0;$deb<=10;$deb++) { 
        $debt_number = intval(4027100 + $deb);
        $deb_col_margin_on_selling_price = "pmd.group_".$debt_number."_margin_on_selling_price";
        $extra_where .= "(".$deb_col_margin_on_selling_price." < 40) OR "; 
      }
      $extra_where = rtrim($extra_where," OR ");  
    break;

    case "5":
      $extra_where = "pmd.is_updated = '1'";
      if($_POST['categories']) {
        $extra_where = "mccp.category_id IN (".$_POST['categories'].") AND pmd.is_updated = '1'";
      }  
    break;

    case "6":
      $extra_where = "pmd.buying_price > (CASE WHEN ( CASE WHEN mcpet_af.value IS NOT NULL THEN mcpet_af.value = 0 ELSE mcpev_afw.value = 0 END ) THEN CAST((mcped.value * CASE WHEN mcpet.value IS NOT NULL THEN mcpet.value ELSE mcpev_ideal.value END) AS DECIMAL (10 , ".$scale.")) ELSE CAST((mcped.value) AS DECIMAL (10 , ".$scale.")) END)";
    break;

    case "7":
      $extra_where = "pmd.buying_price < (CASE WHEN ( CASE WHEN mcpet_af.value IS NOT NULL THEN mcpet_af.value = 0 ELSE mcpev_afw.value = 0 END ) THEN CAST((mcped.value * CASE WHEN mcpet.value IS NOT NULL THEN mcpet.value ELSE mcpev_ideal.value END) AS DECIMAL (10 , ".$scale.")) ELSE CAST((mcped.value) AS DECIMAL (10 , ".$scale.")) END)";
    break;

    case "8":
      $extra_where = "pmd.selling_price > (CASE WHEN ( CASE WHEN mcpet_af.value IS NOT NULL THEN mcpet_af.value = 0 ELSE mcpev_afw.value = 0 END ) THEN CAST((mcped_selling_price.value * CASE WHEN mcpet.value IS NOT NULL THEN mcpet.value ELSE mcpev_ideal.value END) AS DECIMAL (10 , ".$scale." )) ELSE CAST((mcped_selling_price.value) AS DECIMAL (10 , ".$scale." )) END)";
    break;

    case "9":
      $extra_where = "pmd.selling_price < (CASE WHEN ( CASE WHEN mcpet_af.value IS NOT NULL THEN mcpet_af.value = 0 ELSE mcpev_afw.value = 0 END ) THEN CAST((mcped_selling_price.value * CASE WHEN mcpet.value IS NOT NULL THEN mcpet.value ELSE mcpev_ideal.value END) AS DECIMAL (10 , ".$scale." )) ELSE CAST((mcped_selling_price.value) AS DECIMAL (10 , ".$scale." )) END)";
    break;

    case "10":
      $hdn_stijging_text = $_POST['hdn_stijging_text'];
      $extra_where = "pmd.percentage_increase <= '".$hdn_stijging_text."'"; 
    break;

    case "11":
      $hdn_stijging_text =  explode("|||",$_POST['hdn_stijging_text']);
      $extra_where = "pmd.percentage_increase > '".$hdn_stijging_text[0]."' AND pmd.percentage_increase < '".$hdn_stijging_text[1]."'"; 
    break;

    case "12":
      $hdn_stijging_text = $_POST['hdn_stijging_text'];
      $extra_where = "pmd.percentage_increase >= '".$hdn_stijging_text."'"; 
    break;

    case (strpos($_POST['hdn_filters'],"task-all-numbers-filterable") !== FALSE):
      $column_to_search = trim(str_replace("task-all-numbers-filterable","",$_POST['hdn_filters']));
      $db_column_name = array_search($column_to_search, $column_index);
      if ($db_column_name == 'profit_percentage') {
        $db_column_name = 'pmd.profit_percentage_buying_price';
      } elseif($db_column_name == 'discount_on_gross_price') {
        $db_column_name = 'pmd.discount_on_gross';
      } elseif($db_column_name == 'afzet') {
        $db_column_name = '(pmaf.total_quantity_sold-pmaf.refund_qty)';
      } elseif($db_column_name == 'supplier_discount_gross_price') {
        $db_column_name = '(1 - (pmd.net_unit_price / CASE WHEN (pmd.gross_unit_price = 0) THEN 1 ELSE (pmd.gross_unit_price) END )) * 100';
      } elseif($db_column_name == 'supplier_buying_price') {
        $db_column_name = 'pmd.net_unit_price';
      } elseif($db_column_name == 'gyzs_discount_gross_price') {
        $db_column_name = '(1 - ((IF(CASE WHEN mcpet_af.value IS NOT NULL THEN mcpet_af.value = 0 ELSE mcpev_afw.value = 0 END,mcped_selling_price.value * CASE WHEN mcpet.value IS NOT NULL THEN mcpet.value ELSE mcpev_ideal.value END,mcped_selling_price.value)) / (IF(((REPLACE(mcpev_grossprice.value,",",".")) = 0 OR (REPLACE(mcpev_grossprice.value,",",".")) IS NULL), 1, (REPLACE(mcpev_grossprice.value,",",".")))))) * 100';
      } elseif($db_column_name == 'supplier_gross_price') {
        $db_column_name = 'pmd.gross_unit_price';
      } elseif($db_column_name == 'webshop_supplier_gross_price') {
        $db_column_name = 'mcpev_grossprice.value';
      } elseif($db_column_name == 'avg_category') {
        $db_column_name = 'pma.avg_category';
      } elseif($db_column_name == 'avg_brand') {
        $db_column_name = 'pma.avg_brand';
      } elseif($db_column_name == 'gyzs_buying_price') {
        $db_column_name = '(CASE WHEN (CASE WHEN mcpet_af.value IS NOT NULL THEN mcpet_af.value = 0 ELSE mcpev_afw.value = 0 END) THEN CAST((mcped.value * CASE WHEN mcpet.value IS NOT NULL THEN mcpet.value ELSE mcpev_ideal.value END) AS DECIMAL (10 , '.$scale.' )) ELSE CAST((mcped.value) AS DECIMAL (10 , '.$scale.' )) END)';
      }elseif($db_column_name == 'gyzs_selling_price') {
        $db_column_name = '(CASE WHEN (CASE WHEN mcpet_af.value IS NOT NULL THEN mcpet_af.value = 0 ELSE mcpev_afw.value = 0 END) THEN CAST((mcped_selling_price.value * CASE WHEN mcpet.value IS NOT NULL THEN mcpet.value ELSE mcpev_ideal.value END) AS DECIMAL (10 , '.$scale.' )) ELSE CAST((mcped_selling_price.value) AS DECIMAL (10 , '.$scale.' )) END)';
      } elseif($db_column_name == 'avg_per_category_per_brand') {
        $db_column_name = 'pma.avg_per_category_per_brand';
      } elseif($db_column_name == 'webshop_idealeverpakking') {
        $db_column_name = 'CAST(mcpet.value AS UNSIGNED)';
      } else {
        $db_column_name = 'pmd.'.$db_column_name;
      }
      $hdn_search_exp = $_POST['hdn_group_search_text'];
      $extra_where .= ' AND '.str_replace('pmd.db_column', $db_column_name, $hdn_search_exp);
    break;
  }
}

//$extra_where = "mccp.category_id IN (2004,2114,2122,2407)";

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
  array( 'db' => 'DISTINCT mcpe.entity_id AS product_id', 'dt' => $column_index["product_id"]),
  array( 'db' => 'pmd.supplier_type AS supplier_type', 'dt' => $column_index["supplier_type"]),
  array( 'db' => 'mcpev_productname.value AS product_name', 'dt' => $column_index["name"]),
  array( 'db' => 'mcpe.sku AS sku', 'dt' => $column_index["sku"] ),
  array( 'db' => 'mcpev.value AS ean', 'dt' => $column_index["ean"]),
  array( 'db' => 'meaov.value AS brand',  'dt' => $column_index["brand"]),
  array( 'db' => 'pmaf.total_quantity_sold-pmaf.refund_qty AS afzet',  'dt' => $column_index["afzet"]),
  
  array( 'db' => 'CAST(pmd.gross_unit_price AS DECIMAL(10,'.$scale.')) AS supplier_gross_price',  'dt' => $column_index["supplier_gross_price"]),
  array( 'db' => 'CAST(REPLACE(mcpev_grossprice.value,",",".") AS DECIMAL(10,'.$scale.')) AS webshop_supplier_gross_price',  'dt' => $column_index["webshop_supplier_gross_price"]),

  array( 'db' => 'CAST((1 - (pmd.net_unit_price / CASE WHEN (pmd.gross_unit_price = 0) THEN 1 ELSE (pmd.gross_unit_price) END )) * 100 AS DECIMAL (10 , '.$scale.' )) AS supplier_discount_gross_price',  'dt' => $column_index["supplier_discount_gross_price"]),
  
  array( 'db' => 'CAST(pmd.net_unit_price AS DECIMAL(10,'.$scale.')) AS supplier_buying_price',  'dt' => $column_index["supplier_buying_price"]),
  array( 'db' => '(CASE WHEN (mcpet_af.value = 0) THEN CAST((mcped.value * mcpet.value) AS DECIMAL (10 , '.$scale.' )) ELSE CAST((mcped.value) AS DECIMAL (10 , '.$scale.' )) END) AS webshop_supplier_buying_price',  'dt' => $column_index["webshop_supplier_buying_price"]),

  array( 'db' => 'pmd.idealeverpakking AS idealeverpakking',  'dt' => $column_index["idealeverpakking"]),
  array( 'db' => 'CAST(mcpet.value AS UNSIGNED) AS webshop_idealeverpakking',  'dt' => $column_index["webshop_idealeverpakking"]),


  array( 'db' => 'pmd.afwijkenidealeverpakking AS afwijkenidealeverpakking',  'dt' => $column_index["afwijkenidealeverpakking"]),
  array( 'db' => 'mcpet_af.value AS webshop_afwijkenidealeverpakking',  'dt' => $column_index["webshop_afwijkenidealeverpakking"]),

  array( 'db' => 'CAST(pmd.buying_price AS DECIMAL(10,'.$scale.')) AS buying_price',  'dt' => $column_index["buying_price"]),


  array( 'db' => 'pma.avg_category AS avg_category',  'dt' => $column_index["avg_category"]),
  array( 'db' => 'pma.avg_brand AS avg_brand',  'dt' => $column_index["avg_brand"]),
  array( 'db' => 'pma.avg_per_category_per_brand AS avg_per_category_per_brand',  'dt' => $column_index["avg_per_category_per_brand"]),



  array( 'db' => '(CASE WHEN (CASE WHEN mcpet_af.value IS NOT NULL THEN mcpet_af.value = 0 ELSE mcpev_afw.value = 0 END) THEN CAST((mcped.value * CASE WHEN mcpet.value IS NOT NULL THEN mcpet.value ELSE mcpev_ideal.value END) AS DECIMAL (10 , '.$scale.' )) ELSE CAST((mcped.value) AS DECIMAL (10 , '.$scale.' )) END) AS gyzs_buying_price',  'dt' => $column_index["gyzs_buying_price"]),
  array( 'db' => '(CASE WHEN (CASE WHEN mcpet_af.value IS NOT NULL THEN mcpet_af.value = 0 ELSE mcpev_afw.value = 0 END) THEN CAST((mcped_selling_price.value * CASE WHEN mcpet.value IS NOT NULL THEN mcpet.value ELSE mcpev_ideal.value END) AS DECIMAL (10 , '.$scale.' )) ELSE CAST((mcped_selling_price.value) AS DECIMAL (10 , '.$scale.' )) END) AS gyzs_selling_price',  'dt' => $column_index["gyzs_selling_price"]),

   /* array( 'db' => 'CAST((1 - (  (CASE WHEN (mcpet_af.value = 0) THEN mcped_selling_price.value * mcpet.value ELSE mcped_selling_price.value END) /  REPLACE((CASE WHEN (mcpev_grossprice.value = 0 OR mcpev_grossprice.value IS NULL) THEN 1 ELSE mcpev_grossprice.value END),",",".") )) * 100 AS DECIMAL (10 , '.$scale.')) AS gyzs_discount_gross_price',  'dt' => $column_index["gyzs_discount_gross_price"]), */

  // array( 'db' => '(REPLACE(mcpev_grossprice.value,",","."))  AS gyzs_discount_gross_price',  'dt' => $column_index["gyzs_discount_gross_price"]),

   array( 'db' => 'CAST((1 - ((IF(CASE WHEN mcpet_af.value IS NOT NULL THEN mcpet_af.value = 0 ELSE mcpev_afw.value = 0 END,mcped_selling_price.value * CASE WHEN mcpet.value IS NOT NULL THEN mcpet.value ELSE mcpev_ideal.value END,mcped_selling_price.value)) / (IF(((REPLACE(mcpev_grossprice.value,",",".")) = 0 OR (REPLACE(mcpev_grossprice.value,",",".")) IS NULL), 1, (REPLACE(mcpev_grossprice.value,",",".")))))) * 100 AS DECIMAL (10 , '.$scale.')) AS gyzs_discount_gross_price',  'dt' => $column_index["gyzs_discount_gross_price"]),


  array( 'db' => 'pmd.selling_price AS selling_price',  'dt' => $column_index["selling_price"]),
  array( 'db' => 'CAST(pmd.profit_percentage_buying_price AS DECIMAL(10,'.$scale.')) AS profit_percentage',  'dt' => $column_index["profit_percentage"]),
  array( 'db' => 'CAST(pmd.profit_percentage_selling_price AS DECIMAL(10,'.$scale.')) AS profit_percentage_selling_price',  'dt' => $column_index["profit_percentage_selling_price"]),


  array( 'db' => 'CAST(pmd.discount_on_gross AS DECIMAL(10,'.$scale.')) AS discount_on_gross_price',  'dt' => $column_index["discount_on_gross_price"]),
  array( 'db' => 'CAST(pmd.percentage_increase AS DECIMAL(10,'.$scale.')) AS percentage_increase',  'dt' => $column_index["percentage_increase"]),
  /* array( 'db' => 'CAST((SELECT COUNT(*) AS updated_product_cnt FROM price_management_history WHERE product_id = mcpe.entity_id and is_viewed = "No") AS UNSIGNED) AS updated_product_cnt',  'dt' => $column_index["updated_product_cnt"]),
  array( 'db' => 'CAST((SELECT COUNT(*) AS older_updated_product_cnt FROM price_management_history WHERE product_id = mcpe.entity_id and is_viewed = "Yes") AS UNSIGNED) AS older_updated_product_cnt',  'dt' => $column_index["older_updated_product_cnt"]), */

  array( 'db' => 'pmd.group_4027100_debter_selling_price AS group_4027100_debter_selling_price', 'dt' => $column_index["group_4027100_debter_selling_price"]),
  array( 'db' => 'pmd.group_4027100_margin_on_buying_price AS group_4027100_margin_on_buying_price', 'dt' => $column_index["group_4027100_margin_on_buying_price"]),
  array( 'db' => 'pmd.group_4027100_margin_on_selling_price AS group_4027100_margin_on_selling_price', 'dt' => $column_index["group_4027100_margin_on_selling_price"]),
  array( 'db' => 'pmd.group_4027100_discount_on_grossprice_b_on_deb_selling_price AS group_4027100_discount_on_grossprice_b_on_deb_selling_price', 'dt' => $column_index["group_4027100_discount_on_grossprice_b_on_deb_selling_price"]),

  array( 'db' => 'pmd.group_4027101_debter_selling_price AS group_4027101_debter_selling_price', 'dt' => $column_index["group_4027101_debter_selling_price"]),
  array( 'db' => 'pmd.group_4027101_margin_on_buying_price AS group_4027101_margin_on_buying_price', 'dt' => $column_index["group_4027101_margin_on_buying_price"]),
  array( 'db' => 'pmd.group_4027101_margin_on_selling_price AS group_4027101_margin_on_selling_price', 'dt' => $column_index["group_4027101_margin_on_selling_price"]),
  array( 'db' => 'pmd.group_4027101_discount_on_grossprice_b_on_deb_selling_price AS group_4027101_discount_on_grossprice_b_on_deb_selling_price', 'dt' => $column_index["group_4027101_discount_on_grossprice_b_on_deb_selling_price"]),
  
  array( 'db' => 'pmd.group_4027102_debter_selling_price AS group_4027102_debter_selling_price', 'dt' => $column_index["group_4027102_debter_selling_price"]),
  array( 'db' => 'pmd.group_4027102_margin_on_buying_price AS group_4027102_margin_on_buying_price', 'dt' => $column_index["group_4027102_margin_on_buying_price"]),
  array( 'db' => 'pmd.group_4027102_margin_on_selling_price AS group_4027102_margin_on_selling_price', 'dt' => $column_index["group_4027102_margin_on_selling_price"]),
  array( 'db' => 'pmd.group_4027102_discount_on_grossprice_b_on_deb_selling_price AS group_4027102_discount_on_grossprice_b_on_deb_selling_price', 'dt' => $column_index["group_4027102_discount_on_grossprice_b_on_deb_selling_price"]),
  
  array( 'db' => 'pmd.group_4027103_debter_selling_price AS group_4027103_debter_selling_price', 'dt' => $column_index["group_4027103_debter_selling_price"]),
  array( 'db' => 'pmd.group_4027103_margin_on_buying_price AS group_4027103_margin_on_buying_price', 'dt' => $column_index["group_4027103_margin_on_buying_price"]),
  array( 'db' => 'pmd.group_4027103_margin_on_selling_price AS group_4027103_margin_on_selling_price', 'dt' => $column_index["group_4027103_margin_on_selling_price"]),
  array( 'db' => 'pmd.group_4027103_discount_on_grossprice_b_on_deb_selling_price AS group_4027103_discount_on_grossprice_b_on_deb_selling_price', 'dt' => $column_index["group_4027103_discount_on_grossprice_b_on_deb_selling_price"]),
  
  array( 'db' => 'pmd.group_4027104_debter_selling_price AS group_4027104_debter_selling_price', 'dt' => $column_index["group_4027104_debter_selling_price"]),
  array( 'db' => 'pmd.group_4027104_margin_on_buying_price AS group_4027104_margin_on_buying_price', 'dt' => $column_index["group_4027104_margin_on_buying_price"]),
  array( 'db' => 'pmd.group_4027104_margin_on_selling_price AS group_4027104_margin_on_selling_price', 'dt' => $column_index["group_4027104_margin_on_selling_price"]),
  array( 'db' => 'pmd.group_4027104_discount_on_grossprice_b_on_deb_selling_price AS group_4027104_discount_on_grossprice_b_on_deb_selling_price', 'dt' => $column_index["group_4027104_discount_on_grossprice_b_on_deb_selling_price"]),
  
  array( 'db' => 'pmd.group_4027105_debter_selling_price AS group_4027105_debter_selling_price', 'dt' => $column_index["group_4027105_debter_selling_price"]),
  array( 'db' => 'pmd.group_4027105_margin_on_buying_price AS group_4027105_margin_on_buying_price', 'dt' => $column_index["group_4027105_margin_on_buying_price"]),
  array( 'db' => 'pmd.group_4027105_margin_on_selling_price AS group_4027105_margin_on_selling_price', 'dt' => $column_index["group_4027105_margin_on_selling_price"]),
  array( 'db' => 'pmd.group_4027105_discount_on_grossprice_b_on_deb_selling_price AS group_4027105_discount_on_grossprice_b_on_deb_selling_price', 'dt' => $column_index["group_4027105_discount_on_grossprice_b_on_deb_selling_price"]),
  
  array( 'db' => 'pmd.group_4027106_debter_selling_price AS group_4027106_debter_selling_price', 'dt' => $column_index["group_4027106_debter_selling_price"]),
  array( 'db' => 'pmd.group_4027106_margin_on_buying_price AS group_4027106_margin_on_buying_price', 'dt' => $column_index["group_4027106_margin_on_buying_price"]),
  array( 'db' => 'pmd.group_4027106_margin_on_selling_price AS group_4027106_margin_on_selling_price', 'dt' => $column_index["group_4027106_margin_on_selling_price"]),
  array( 'db' => 'pmd.group_4027106_discount_on_grossprice_b_on_deb_selling_price AS group_4027106_discount_on_grossprice_b_on_deb_selling_price', 'dt' => $column_index["group_4027106_discount_on_grossprice_b_on_deb_selling_price"]),
  
  array( 'db' => 'pmd.group_4027107_debter_selling_price AS group_4027107_debter_selling_price', 'dt' => $column_index["group_4027107_debter_selling_price"]),
  array( 'db' => 'pmd.group_4027107_margin_on_buying_price AS group_4027107_margin_on_buying_price', 'dt' => $column_index["group_4027107_margin_on_buying_price"]),
  array( 'db' => 'pmd.group_4027107_margin_on_selling_price AS group_4027107_margin_on_selling_price', 'dt' => $column_index["group_4027107_margin_on_selling_price"]),
  array( 'db' => 'pmd.group_4027107_discount_on_grossprice_b_on_deb_selling_price AS group_4027107_discount_on_grossprice_b_on_deb_selling_price', 'dt' => $column_index["group_4027107_discount_on_grossprice_b_on_deb_selling_price"]),
  
  array( 'db' => 'pmd.group_4027108_debter_selling_price AS group_4027108_debter_selling_price', 'dt' => $column_index["group_4027108_debter_selling_price"]),
  array( 'db' => 'pmd.group_4027108_margin_on_buying_price AS group_4027108_margin_on_buying_price', 'dt' => $column_index["group_4027108_margin_on_buying_price"]),
  array( 'db' => 'pmd.group_4027108_margin_on_selling_price AS group_4027108_margin_on_selling_price', 'dt' => $column_index["group_4027108_margin_on_selling_price"]),
  array( 'db' => 'pmd.group_4027108_discount_on_grossprice_b_on_deb_selling_price AS group_4027108_discount_on_grossprice_b_on_deb_selling_price', 'dt' => $column_index["group_4027108_discount_on_grossprice_b_on_deb_selling_price"]),
  
  array( 'db' => 'pmd.group_4027109_debter_selling_price AS group_4027109_debter_selling_price', 'dt' => $column_index["group_4027109_debter_selling_price"]),
  array( 'db' => 'pmd.group_4027109_margin_on_buying_price AS group_4027109_margin_on_buying_price', 'dt' => $column_index["group_4027109_margin_on_buying_price"]),
  array( 'db' => 'pmd.group_4027109_margin_on_selling_price AS group_4027109_margin_on_selling_price', 'dt' => $column_index["group_4027109_margin_on_selling_price"]),
  array( 'db' => 'pmd.group_4027109_discount_on_grossprice_b_on_deb_selling_price AS group_4027109_discount_on_grossprice_b_on_deb_selling_price', 'dt' => $column_index["group_4027109_discount_on_grossprice_b_on_deb_selling_price"]),
  
  array( 'db' => 'pmd.group_4027110_debter_selling_price AS group_4027110_debter_selling_price', 'dt' => $column_index["group_4027110_debter_selling_price"]),
  array( 'db' => 'pmd.group_4027110_margin_on_buying_price AS group_4027110_margin_on_buying_price', 'dt' => $column_index["group_4027110_margin_on_buying_price"]),
  array( 'db' => 'pmd.group_4027110_margin_on_selling_price AS group_4027110_margin_on_selling_price', 'dt' => $column_index["group_4027110_margin_on_selling_price"]),
  array( 'db' => 'pmd.group_4027110_discount_on_grossprice_b_on_deb_selling_price AS group_4027110_discount_on_grossprice_b_on_deb_selling_price', 'dt' => $column_index["group_4027110_discount_on_grossprice_b_on_deb_selling_price"]),
  array( 'db' => 'pmd.is_updated AS is_updated',  'dt' => $column_index["is_updated"]),
  array( 'db' => 'pmd.is_activated AS is_activated',  'dt' => $column_index["is_activated"]),
  
  array( 'db' => 'CAST((SELECT COUNT(*) AS mag_updated_product_cnt FROM price_management_history WHERE product_id = mcpe.entity_id and is_viewed = "No" and updated_by = "Magento" and buying_price_changed = "1") AS UNSIGNED) AS mag_updated_product_cnt',  'dt' => $column_index["mag_updated_product_cnt"]),

  /*array( 'db' => 'CAST((SELECT COUNT(*) AS updated_product_cnt FROM price_management_history WHERE product_id = mcpe.entity_id and buying_price_changed = "1" and is_synced = "No") AS UNSIGNED) AS updated_product_cnt',  'dt' => $column_index["updated_product_cnt"]), */
  array( 'db' => 'pmd.minimum_bol_price AS minimum_bol_price',  'dt' => $column_index["minimum_bol_price"]),
);


// SQL server connection information
$sql_details = array(
  'user' => $username,
  'pass' => $password,
  'db'   => $dbname,
  'host' => $servername
);

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

 //print_r($table);exit;
require( 'ssp.class.php' );

echo json_encode(
  SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, $extra_where)
);


?>