<?php
include "server_constants.php";

/* Column Mappings */

$column_index = array(
  "product_id" => 0,
  "supplier_type" => 1,
  "name" => 2,
  "sku" => 3,
  "ean" => 4,
  "brand" => 5,
  "afzet" => 6,
  "supplier_gross_price" => 7,
  "webshop_supplier_gross_price" => 8,

  "supplier_discount_gross_price" => 9,

  "supplier_buying_price" => 10,
  "webshop_supplier_buying_price" => 11,

  "idealeverpakking" => 12,
  "webshop_idealeverpakking" => 13,

  "afwijkenidealeverpakking" => 14,
  "webshop_afwijkenidealeverpakking" => 15,
  "buying_price" => 16,
  "minimum_bol_percentage" => 17,

  "avg_category" => 18,
  "avg_brand" => 19,
  "avg_per_category_per_brand" => 20,
  "gyzs_buying_price" => 21,
  "gyzs_selling_price" => 22,
  "gyzs_discount_gross_price" => 23,
  "selling_price" => 24,
  "profit_percentage" => 25,
  "profit_percentage_selling_price" => 26,
  "discount_on_gross_price" => 27,
  "percentage_increase" => 28,
  //"updated_product_cnt" => 24,
  //"older_updated_product_cnt" => 25,
  "group_4027100_debter_selling_price" => 29,
  "group_4027100_margin_on_buying_price" => 30,
  "group_4027100_margin_on_selling_price" => 31,
  "group_4027100_discount_on_grossprice_b_on_deb_selling_price" => 32,

  "group_4027101_debter_selling_price" => 33,
  "group_4027101_margin_on_buying_price" => 34,
  "group_4027101_margin_on_selling_price" => 35,
  "group_4027101_discount_on_grossprice_b_on_deb_selling_price" => 36,

  "group_4027102_debter_selling_price" => 37,
  "group_4027102_margin_on_buying_price" => 38,
  "group_4027102_margin_on_selling_price" => 39,
  "group_4027102_discount_on_grossprice_b_on_deb_selling_price" => 40,

  "group_4027103_debter_selling_price" => 41,
  "group_4027103_margin_on_buying_price" => 42,
  "group_4027103_margin_on_selling_price" => 43,
  "group_4027103_discount_on_grossprice_b_on_deb_selling_price" => 44,

  "group_4027104_debter_selling_price" => 45,
  "group_4027104_margin_on_buying_price" => 46,
  "group_4027104_margin_on_selling_price" => 47,
  "group_4027104_discount_on_grossprice_b_on_deb_selling_price" => 48,

  "group_4027105_debter_selling_price" => 49,
  "group_4027105_margin_on_buying_price" => 50,
  "group_4027105_margin_on_selling_price" => 51,
  "group_4027105_discount_on_grossprice_b_on_deb_selling_price" => 52,

  "group_4027106_debter_selling_price" => 53,
  "group_4027106_margin_on_buying_price" => 54,
  "group_4027106_margin_on_selling_price" => 55,
  "group_4027106_discount_on_grossprice_b_on_deb_selling_price" => 56,

  "group_4027107_debter_selling_price" => 57,
  "group_4027107_margin_on_buying_price" => 58,
  "group_4027107_margin_on_selling_price" => 59,
  "group_4027107_discount_on_grossprice_b_on_deb_selling_price" => 60,

  "group_4027108_debter_selling_price" => 61,
  "group_4027108_margin_on_buying_price" => 62,
  "group_4027108_margin_on_selling_price" => 63,
  "group_4027108_discount_on_grossprice_b_on_deb_selling_price" => 64,

  "group_4027109_debter_selling_price" => 65,
  "group_4027109_margin_on_buying_price" => 66,
  "group_4027109_margin_on_selling_price" => 67,
  "group_4027109_discount_on_grossprice_b_on_deb_selling_price" => 68,

  "group_4027110_debter_selling_price" => 69,
  "group_4027110_margin_on_buying_price" => 70,
  "group_4027110_margin_on_selling_price" => 71,
  "group_4027110_discount_on_grossprice_b_on_deb_selling_price" => 72,


  "is_updated" => 73,
  "is_activated" => 74,
  //"updated_product_cnt" => 70,
  "mag_updated_product_cnt" => 75
 );
/* Column Mappings */

/* Define Attribute Ids*/
define("EAN", "191");
define("MERK", "2120");

//define("NETUNITPRICE", "2801");

define("IDEALEVERPAKKING", "1495");
define("COST", "79");
define("PRICE", "75");
define("BOLMINPRICE","2452");
define("ECDELIVERYTIME","2453");
define("ECDELIVERYTIMEBE","2454");
define("BOLSELLINGPRICE","2455");


//define("GROSSUNITPRICE", "2802");
define("GROSSUNITPRICE", "1513");

define("TRADEITEMDESC", "2803");
define("CATEGORYNAME_ATTRIBUTE_ID", "41");
define("PRODUCTNAME","71");
define("afwijkenidealeverpakking","1511");
define("tansmission","848");
define("brievenbuspakket","2204");
define("ROASINSERTCHUNK","10000");
define("PMCHUNK","5000");
define("LOCATIE","2435");
define("DEFAULTBOLPERCENTAGE", "15.0000");


/* Define Attribute Ids*/

/* ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit','3G');
ini_set('max_execution_time', 0); */

$scale = 4;

/* Column Mappings Price Logs*/
$column_index_price_log = array(
  "updated_date_time" => 0,
  "old_net_unit_price" => 1,
  "old_gross_unit_price" => 2,
  "old_idealeverpakking" => 3,
  "old_afwijkenidealeverpakking" => 4,
  "old_buying_price" => 5,
  "old_selling_price" => 6,
  "new_net_unit_price" => 7,
  "new_gross_unit_price" => 8,
  "new_idealeverpakking" => 9,
  "new_afwijkenidealeverpakking" => 10,
  "new_buying_price" => 11,
  "new_selling_price" => 12,
  "updated_by" => 13,
  "is_viewed" => 14,
  "history_id" => 15,
  "product_id" => 16,
  "fields_changed" => 17
 );
/* Column Mappings Price Logs*/


/* Column Mappings Roas Feed Logs*/
$column_index_roas_feed_log = array(
  "id" => 0,
  "sku" => 1,
  "name" => 2,
  "brand" => 3,
  "carrier_level" => 4,
  "total_quantity" => 5,
  "total_orders" => 6,

  "total_orders_bol" => 7,
  "total_quantity_bol" => 8,


  "return_general" => 9,
  "return_bol" => 10,
  "return_nobol" => 11,

  "return_order_general" => 12,
  "return_order_bol" => 13,
  "return_order_nobol" => 14,

  "parent_product_factor" => 15,
  "parent_absolute_margin" => 16,
  "parent_return_margin" => 17,
  "total_parent_margin" => 18,
  "average_order_per_month" => 19,
  "other_absolute_margin" => 20,
  "total_absolute_margin" => 21,
  "shipment_revenue" => 22,
  "shipment_cost" => 23,
  "shipment_diff" => 24,
  "employee_cost" => 25,
  "margin_after_deductions" => 26,
  "total_selling_price" => 27,
  "payment_other_company_cost" => 28,
  "burning_margin" => 29,
  "roas_target" => 30,
  
  "google_kosten" => 31,
  "google_roas" => 32,
  "performance" => 33,
  
  "avg_per_cat" => 34,
  "avg_per_cat_per_brand" => 35,
  "roas_per_cat_per_brand" => 36,
  "end_roas" => 37,
  
  "roas_cal" => 38,
  "return_help" => 39,
  "return_order_help" => 40,
  "roas_per_cat_per_brand_help" => 41
 );
/* Column Mappings Roas Feed Logs*/


/* Column Mappings Roas Feed Logs*/
$column_index_roas_current_log = array(
  "id" => 0,
  "sku" => 1,
  "name" => 2,
  "brand" => 3,
  "carrier_level" => 4,
  "total_quantity" => 5,
  "total_orders" => 6,

  "total_orders_bol" => 7,
  "total_quantity_bol" => 8,


  "return_general" => 9,
  "return_bol" => 10,
  "return_nobol" => 11,

  "return_order_general" => 12,
  "return_order_bol" => 13,
  "return_order_nobol" => 14,

  "parent_product_factor" => 15,
  "parent_absolute_margin" => 16,
  "parent_return_margin" => 17,
  "total_parent_margin" => 18,
  "average_order_per_month" => 19,
  "other_absolute_margin" => 20,
  "total_absolute_margin" => 21,
  "shipment_revenue" => 22,
  "shipment_cost" => 23,
  "shipment_diff" => 24,
  "employee_cost" => 25,
  "margin_after_deductions" => 26,
  "total_selling_price" => 27,
  "payment_other_company_cost" => 28,
  "burning_margin" => 29,
  "roas_target" => 30,
  
  "google_kosten" => 31,
  "google_roas" => 32,
  "performance" => 33,
  
  "avg_per_cat" => 34,
  "avg_per_cat_per_brand" => 35,
  "roas_per_cat_per_brand" => 36,
  "end_roas" => 37,
  
  "roas_cal" => 38,
  "return_help" => 39,
  "return_order_help" => 40,
  "roas_per_cat_per_brand_help" => 41
 );
/* Column Mappings Roas Feed Logs*/



/* Revenue Report Indexes */
$column_index_revenue_report = array(

  "id" => 0,
  "supplier_type" => 1,
  "sku" => 2,
  "carrier_level" => 3,
  "name" => 4,
  "brand" => 5,
  "sku_total_quantity_sold_365" => 6,
  "sku_total_quantity_sold" => 7,
  "sku_total_price_excl_tax" => 8,
  "sku_vericale_som" => 9,
  "vericale_som_percentage" => 10,
  "sku_bp_excl_tax" => 11,
  "sku_sp_excl_tax" => 12,
  "sku_abs_margin" => 13,
  "sku_margin_bp" => 14,
  "sku_margin_sp" => 15,
  "sku_vericale_som_bp" => 16,
  "sku_vericale_som_bp_percentage" => 17,
  "sku_refund_qty" => 18,
  "sku_refund_revenue_amount" => 19,
  "sku_refund_bp_amount" => 20,
  "sku_vericale_som_abs" => 21,
  "sku_vericale_som_abs_percentage" => 22,
  "product_id" => 23,
  "reportdate" => 24,
 
 );
/* Revenue Report Indexes */


$column_index_compare_prices = array(
  "m_ean" => 0,
  "m_sku" => 1,
  "m_buying_price" => 2,
  "m_qty" => 3,
  "m_afw" => 4,
  "m_piece" => 5,
  "p_sku" => 6,
  "p_buying_price" => 7,
  "p_qty" => 8,

  "p_afw" => 9,

  "p_piece" => 10,
  "t_sku" => 11,

  "t_buying_price" => 12,
  "t_qty" => 13,

  "t_afw" => 14,
  "t_piece" => 15,
  "d_sku" => 16,
  "d_buying_price" => 17,
  "d_qty" => 18,
  "d_afw" => 19,


  "d_piece" => 20,
  "m_supplier_id" => 21,
  "p_supplier_id" => 22,
  "t_supplier_id" => 23,
  "d_supplier_id" => 24,
 );


?>
