<?php
include "config/config.php";
include "define/constants.php";
ini_set('memory_limit', '1024M');
global $conn;

//create new table
//get data and insert

$revenue_data = $all_col_data = array();
$sql_settings = "SELECT * FROM pm_settings WHERE id = 1";
$setting_resource = $conn->query($sql_settings);
$setting_row = $setting_resource->fetch_assoc();
$setting_row['roas'] = unserialize($setting_row['roas']);
$days = $setting_row['roas']['sku_afzet_in_days'] ?? '1 year';
$revenue_data = getAfzet_365($days);
function getAfzet_365($days) {
  global $conn;
  $current_date = date('Y-m-d');

  if($days == '1 year') {
    $lastyear = strtotime("-1 year", time());
    $last_year_date = date("Y-m-d", $lastyear);
    $sql_all_orders = "SELECT entity_id, created_at FROM mage_sales_flat_order WHERE state != 'canceled' AND (created_at >= '".$last_year_date." 00:00:00' AND  created_at < '".$current_date." 00:00:00')";
  } else {
    $lastyear = strtotime("-".$days." day", time());
    $last_year_date = date("Y-m-d", $lastyear);
    $sql_all_orders = "SELECT entity_id, created_at FROM mage_sales_flat_order WHERE state != 'canceled' AND (created_at >= '".$last_year_date." 00:00:00' AND  created_at <= '".$current_date." 23:59:00')";
  }
  $allactiveOrdersByDays = $conn->query($sql_all_orders);
  $allactiveOrdersByDays = $allactiveOrdersByDays->fetch_all(MYSQLI_ASSOC);
  $all_ordered_skus_365 = $sku_quantities_in_order = array();
  if(count($allactiveOrdersByDays)) {
    foreach($allactiveOrdersByDays as $order) {
        $order_id = $order['entity_id'];
        
        $sql_order_items = "SELECT * FROM mage_sales_flat_order_item WHERE order_id = '".$order_id."'";
        $allOrderItems = $conn->query($sql_order_items);
        $allOrderItems = $allOrderItems->fetch_all(MYSQLI_ASSOC);

        foreach($allOrderItems as $order_item) {
            $qty_ordered = $order_item['qty_ordered'];
            $qty_refunded = $order_item['qty_refunded'];
            $product_id = $order_item['product_id'];
            $sku = $order_item['sku'];

            $sku_quantities_in_order[$order_item['sku']][0]['product_id'] = $product_id;
            $sku_quantities_in_order[$order_item['sku']][$order_item['order_id']]['Quantity'] = $qty_ordered;
            $sku_quantities_in_order[$order_item['sku']][$order_item['order_id']]['Refunded'] = $qty_refunded;
        }
    }
/* echo '<pre>';
    print_r($sku_quantities_in_order);exit; */
    if(count($sku_quantities_in_order)) {
        foreach($sku_quantities_in_order as $sku=>$orders) {
            if($sku) {
            $order_wise_quantities_365 = array();
            $order_wise_refunded_quantities_365 = array();
            //$order_product_id = 0;
            foreach($orders as $order_id=>$sku_data) {
              if($order_id != 0) {
                $order_wise_quantities_365[] = $sku_data['Quantity'];
                $order_wise_refunded_quantities_365[] = $sku_data['Refunded'];
              }
            }
            $all_ordered_skus_365[$sku]['total_quantity_sold'] = array_sum($order_wise_quantities_365);
            $all_ordered_skus_365[$sku]['refund_qty'] = array_sum($order_wise_refunded_quantities_365);
            $all_ordered_skus_365[$sku]['product_id'] = $orders[0]['product_id'];
        }
      }
    }
  }
  return $all_ordered_skus_365;
}
//print_r($revenue_data);exit;

if ($revenue_data) {
  $truncate_sql = "TRUNCATE TABLE price_management_afzet_data";
    $conn->query($truncate_sql);
$sql = "INSERT INTO price_management_afzet_data VALUES ";
    foreach($revenue_data as $sku=>$r_data) {
      $all_col_data[] = "(NULL, 
                          " . $r_data['product_id'] . ",
                          '" . $sku . "',
                          " . $r_data['total_quantity_sold'] . ",
                          " . $r_data['refund_qty'] . ",
                          NOW()
                        )";
    }

  $sql .= implode(",", $all_col_data);
  //echo $sql;exit;
  if($conn->query($sql)) {
    echo "Inserted:-".count($revenue_data)."\n";
  } else {
    echo "Error in chunk insertion:- ".mysqli_error($conn)."\n";
  }
}