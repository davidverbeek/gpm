<?php

include "../GyzsManagement/config/dbconfig.php";
include "../GyzsManagement/define/constants.php";

//$resource = Mage::getSingleton('core/resource');
//$readConnection = $resource->getConnection('core_read');

/* ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
//$created_at = "2018-01-01";
//$created_at_to_date = "2019-06-30";


ini_set('max_execution_time', 0);

$revenue_data = array();
$revenue_data_365 = getAfzet_365();
$revenue_data = getRevenue($created_at,$created_at_to_date,$revenue_data_365,$categories,$filter_name);
function getAfzet_365() {
  global $conn;
  $sql_all_orders = "SELECT sku, total_quantity_sold - refund_qty AS afzet_365 FROM price_management_afzet_data";
  $allactiveOrdersByDays = $conn->query($sql_all_orders);
  $allactiveOrdersByDays = $allactiveOrdersByDays->fetch_all(MYSQLI_ASSOC);
  $all_ordered_skus_365 = array();
  if(count($allactiveOrdersByDays)) {
    foreach($allactiveOrdersByDays as $orders) {
        $all_ordered_skus_365[$orders['sku']]['sku_total_quantity_sold_365'] = $orders['afzet_365'];
    }
  }
  return $all_ordered_skus_365;
}//end getafzet_365()

function getRevenue($created_at,$created_at_to_date,$all_ordered_skus_365,$categories,$filter_name) { //echo json_encode($categories);exit;
global $conn;
$sql_all_orders = "SELECT entity_id, created_at FROM mage_sales_flat_order WHERE state != 'canceled' AND (created_at >= '".$created_at." 00:00:00' AND  created_at <= '".$created_at_to_date." 23:59:00') limit 10";
$allactiveOrders = $conn->query($sql_all_orders);
$allactiveOrders = $allactiveOrders->fetch_all(MYSQLI_ASSOC);
$order_lines = 0;
$total_item_quantity = array();
$sku_quantities_in_order = $all_ordered_skus= array();

  foreach($allactiveOrders as $order) {

    $order_id = $order['entity_id'];

    if ($filter_name == 'get_revenue_data_by_category') { 

      $sql_order_items = "SELECT msoi.product_id as prod_id, msoi.* FROM mage_sales_flat_order_item AS msoi
      JOIN
      mage_catalog_category_product AS mccp ON mccp.product_id = msoi.product_id AND msoi.order_id = '".$order_id."'
      AND 
      mccp.category_id IN (".$categories[0].")";
    } elseif($filter_name == 'get_revenue_data_by_brand') { 
      $sql_order_items = "SELECT DISTINCT msoi.product_id as prod_id, msoi.* FROM mage_sales_flat_order_item AS msoi
      JOIN
      mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = msoi.product_id AND mcpei.attribute_id = '".MERK."' AND msoi.order_id = '".$order_id."' 
      JOIN
      mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value 
      WHERE
      meaov.value = '".$categories[0]."'"; 
    } elseif($filter_name == 'get_revenue_data_by_both') { 
      $sql_order_items = "SELECT DISTINCT msoi.product_id as prod_id, msoi.* FROM mage_sales_flat_order_item AS msoi
      JOIN
      mage_catalog_category_product AS mccp ON mccp.product_id = msoi.product_id AND msoi.order_id = '".$order_id."'
      JOIN
      mage_catalog_product_entity_int AS mcpei ON mcpei.entity_id = msoi.product_id AND mcpei.attribute_id = '".MERK."' 
      JOIN
      mage_eav_attribute_option_value AS meaov ON meaov.option_id = mcpei.value 
      WHERE
      meaov.value = '".$categories[0]."' AND mccp.category_id IN (".$categories[1].")"; 
    } else {
      $sql_order_items = "SELECT * FROM mage_sales_flat_order_item WHERE order_id = '".$order_id."'";
    }

    $allOrderItems = $conn->query($sql_order_items);
    $allOrderItems = $allOrderItems->fetch_all(MYSQLI_ASSOC);
    
   /*  if(count($allOrderItems) == 0) {
      break;
    }
 */
    foreach($allOrderItems as $order_item) {
      $product_id = $order_item['product_id'];
      $qty_ordered = $order_item['qty_ordered'];
      $qty_refunded = $order_item['qty_refunded'];
      $product_price_excl_tax = $order_item['base_price'];
      $base_cost = $order_item['base_cost'];
      $afwijkenidealeverpakking = $order_item['afwijkenidealeverpakking'];
      $idealeverpakking = $order_item['idealeverpakking'];
       
      
      $sku_quantities_in_order[$order_item['sku']][$order_item['order_id']]['product_id'] = $product_id;
      $sku_quantities_in_order[$order_item['sku']][$order_item['order_id']]['Quantity'] = $qty_ordered;
      $sku_quantities_in_order[$order_item['sku']][$order_item['order_id']]['Refunded'] = $qty_refunded;
      $sku_quantities_in_order[$order_item['sku']][$order_item['order_id']]['base_price'] = $product_price_excl_tax;
      $sku_quantities_in_order[$order_item['sku']][$order_item['order_id']]['base_cost'] = $base_cost;
      
      $sku_quantities_in_order[$order_item['sku']][$order_item['order_id']]['afwijkenidealeverpakking'] = $afwijkenidealeverpakking;
      $sku_quantities_in_order[$order_item['sku']][$order_item['order_id']]['idealeverpakking'] = $idealeverpakking;
    }
    
  }
 // echo json_encode($sku_quantities_in_order);exit;
  $vericale_som = array();
  $vericale_som_bp = array();
  $vericale_som_abs = array();

  if(count($sku_quantities_in_order)) {
    
    foreach($sku_quantities_in_order as $sku=>$orders) {

      $all_ordered_skus[$sku]['sku'] = $sku;

      $order_wise_quantities = array();
      $order_wise_sku_price_excl_tax = array();
      $bp_excl_tax = array();
      $sp_excl_tax = array();

      $order_wise_refunded_quantities = array();
      $refunded_sp_excl_tax = array();
      $refunded_bp_excl_tax = array();

      foreach($orders as $order_id=>$sku_data) {
        $order_wise_quantities[] = $sku_data['Quantity'];
        $order_wise_refunded_quantities[] = $sku_data['Refunded'];

        $order_wise_sku_price_excl_tax[] = $sku_data['base_price'] * $sku_data['Quantity'];
        $all_ordered_skus[$sku]['product_id'] = $sku_data['product_id'];

        $sp_excl_tax[] = $sku_data['base_price'] * $sku_data['Quantity'];
        $refunded_sp_excl_tax[] = $sku_data['base_price'] * $sku_data['Refunded'];

        if($sku_data['afwijkenidealeverpakking'] == "" || $sku_data['afwijkenidealeverpakking'] === NULL) {
          $sku_data['afwijkenidealeverpakking'] = 1;
        }

        if($sku_data['idealeverpakking'] == "" || $sku_data['idealeverpakking'] === NULL) {
          $sku_data['idealeverpakking'] = 1;
        }

        if($sku_data['afwijkenidealeverpakking'] == 0) {
          $bp_excl_tax[] = $sku_data['base_cost'] * $sku_data['Quantity'] * $sku_data['idealeverpakking'];
          $refunded_bp_excl_tax[] = $sku_data['base_cost'] * $sku_data['Refunded'] * $sku_data['idealeverpakking'];
        } else {
          $bp_excl_tax[] = $sku_data['base_cost'] * $sku_data['Quantity'];
          $refunded_bp_excl_tax[] = $sku_data['base_cost'] * $sku_data['Refunded'];
        }
      }

      $all_ordered_skus[$sku]['sku_total_quantity_sold'] = array_sum($order_wise_quantities);
      $all_ordered_skus[$sku]['sku_total_price_excl_tax'] = array_sum($order_wise_sku_price_excl_tax) - array_sum($refunded_sp_excl_tax);

      $all_ordered_skus[$sku]['sku_bp_excl_tax'] = array_sum($bp_excl_tax) - array_sum($refunded_bp_excl_tax);
      $all_ordered_skus[$sku]['sku_sp_excl_tax'] = array_sum($sp_excl_tax) - array_sum($refunded_sp_excl_tax);

      $all_ordered_skus[$sku]['sku_abs_margin'] = $all_ordered_skus[$sku]['sku_sp_excl_tax'] - $all_ordered_skus[$sku]['sku_bp_excl_tax'];

      $temp_bp_excl_tax = "";
      $temp_sp_excl_tax = "";

      $temp_bp_excl_tax = ($all_ordered_skus[$sku]['sku_bp_excl_tax'] == 0 ? 1 : $all_ordered_skus[$sku]['sku_bp_excl_tax']);
      $temp_sp_excl_tax = ($all_ordered_skus[$sku]['sku_sp_excl_tax'] == 0 ? 1 : $all_ordered_skus[$sku]['sku_sp_excl_tax']);
      $all_ordered_skus[$sku]['sku_margin_bp'] =  round(($all_ordered_skus[$sku]['sku_abs_margin'] / $temp_bp_excl_tax) * 100,2);
      $all_ordered_skus[$sku]['sku_margin_sp'] =  round(($all_ordered_skus[$sku]['sku_abs_margin'] / $temp_sp_excl_tax) * 100,2);

      $vericale_som[] = $all_ordered_skus[$sku]['sku_total_price_excl_tax'];
      $vericale_som_bp[] = $all_ordered_skus[$sku]['sku_bp_excl_tax'];
      $vericale_som_abs[] = round($all_ordered_skus[$sku]['sku_abs_margin'],2);
      
      $all_ordered_skus['total_vericale_som'] = array_sum($vericale_som);
      $all_ordered_skus['total_vericale_som_bp'] = array_sum($vericale_som_bp);
      $all_ordered_skus['total_vericale_som_abs'] = array_sum($vericale_som_abs); 

      $all_ordered_skus[$sku]['sku_refund_qty'] = array_sum($order_wise_refunded_quantities);
      $all_ordered_skus[$sku]['sku_refund_revenue_amount'] = array_sum($refunded_sp_excl_tax);
      $all_ordered_skus[$sku]['sku_refund_bp_amount'] = array_sum($refunded_bp_excl_tax);
      $all_ordered_skus[$sku]['sku_total_quantity_sold_365'] = $all_ordered_skus_365[$sku]['sku_total_quantity_sold_365'] ?? 0;
    }
    
  }
  //echo json_encode($all_ordered_skus);exit; 
   
  return $all_ordered_skus;
}
$total_vericale_sum_of_revenue = $revenue_data["total_vericale_som"];
$total_vericale_sum_of_bp = $revenue_data["total_vericale_som_bp"];
$total_vericale_sum_of_abs = $revenue_data["total_vericale_som_abs"];

unset($revenue_data["total_vericale_som"]);
unset($revenue_data["total_vericale_som_bp"]);
unset($revenue_data["total_vericale_som_abs"]);
array_multisort(array_column($revenue_data, 'sku_total_price_excl_tax'), SORT_DESC, $revenue_data);
if(count($revenue_data)) {
  $vericale_som = $vericale_som_bp = $vericale_som_abs = 0;
   foreach($revenue_data as $sku=>$sku_ordered_data) {
    
    $individual_sku_total_price_excl_tax = $sku_ordered_data["sku_total_price_excl_tax"];
    $individual_bp_excl_tax = $sku_ordered_data["sku_bp_excl_tax"];
    $individual_abs_margin_excl_tax = round($sku_ordered_data["sku_abs_margin"],2);
    
    $vericale_som += $individual_sku_total_price_excl_tax;
    $revenue_data[$sku]['sku_vericale_som'] = $vericale_som;
    $vericale_som_percentage = ($total_vericale_sum_of_revenue != 0) ? ($vericale_som / $total_vericale_sum_of_revenue) * 100 : 0.00;
    $revenue_data[$sku]["vericale_som_percentage"] = $vericale_som_percentage;

    $vericale_som_bp += $individual_bp_excl_tax;
    $revenue_data[$sku]['sku_vericale_som_bp'] = $vericale_som_bp;
    $vericale_som_percentage_bp = ($total_vericale_sum_of_bp != 0) ? ($vericale_som_bp / $total_vericale_sum_of_bp) * 100 : 0.00;
    $revenue_data[$sku]["sku_vericale_som_bp_percentage"] = $vericale_som_percentage_bp;

    $vericale_som_abs += $individual_abs_margin_excl_tax;
    $revenue_data[$sku]['sku_vericale_som_abs'] = $vericale_som_abs;
    $sku_vericale_som_abs_percentage = ($total_vericale_sum_of_abs != 0) ? ($vericale_som_abs / $total_vericale_sum_of_abs) * 100 : 0.00;
    $revenue_data[$sku]["sku_vericale_som_abs_percentage"] = $sku_vericale_som_abs_percentage; 
  
   

    /* $revenue_data[$sku]['sku_vericale_som_bp'] = "15.00";
    $revenue_data[$sku]['sku_vericale_som'] = "15.00";
    $revenue_data[$sku]["vericale_som_percentage"] = "15.00";
    $revenue_data[$sku]["sku_vericale_som_abs_percentage"] = "15.00";  
    $revenue_data[$sku]["sku_vericale_som_bp_percentage"] = "15.00";
    $revenue_data[$sku]['sku_vericale_som_abs'] = "15.00"; */
  }

}
$final_data = array();
$final_data['revenue_data'] = $revenue_data;
$final_data['total_revenue'] = $total_vericale_sum_of_revenue;
$final_data['total_bp'] = $total_vericale_sum_of_bp;