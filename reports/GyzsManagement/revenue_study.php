<?php
include "config/config.php";
include "define/constants.php";
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 0);

global $conn;
$truncate_sql = "TRUNCATE TABLE revenue_study_price_management";
$conn->query($truncate_sql);

$revenue_data_60 = $all_col_data = array();
// $sql_settings = "SELECT * FROM pm_settings WHERE id = 1";
// $setting_resource = $conn->query($sql_settings);
// $setting_row = $setting_resource->fetch_assoc();
// $setting_row['roas'] = unserialize($setting_row['roas']);
// $days = $setting_row['roas']['sku_afzet_in_days'] ?? '1 year';

$col_data = array();

$current_date = date('Y-m-d H:i:s');
$days = 60;
$sixty_days_back = strtotime("-".$days." day", time());
$sixty_days_back_date = date("Y-m-d",  $sixty_days_back);

 $revenue_data_60 = getRevenue_study($sixty_days_back_date, $current_date, 'current_revenue');
$revenue_data_60 = array_slice($revenue_data_60, 0,6,true);

$sixty_one_days_back_date = date('Y-m-d H:i:s', strtotime('-1 day', strtotime($sixty_days_back_date)));
$date = new DateTime($sixty_one_days_back_date);
$date->modify('-60 days');
$previous_date = $date->format('Y-m-d');//exit;

$revenue_data_120 = getRevenue_study($previous_date, $sixty_one_days_back_date, 'previous_revenue');
//$revenue_data_120 = array_slice($revenue_data_120,0,6,true);

$result = array();
foreach($revenue_data_60 as $key => $value) {
    if(isset($revenue_data_120[$key])) {
      $result[$key] = array_merge($revenue_data_120[$key], $revenue_data_60[$key]);      

      // calculate
      $pecentage_revenue = (($revenue_data_60[$key]['current_revenue'] - $revenue_data_120[$key]['previous_revenue'])/$revenue_data_60[$key]['current_revenue'])*100;
      $result[$key]['percentage_revenue'] = $pecentage_revenue;

      unset($revenue_data_120[$key]);
      unset($revenue_data_60[$key]);

    }  
}

/*echo "<pre>";
print_r($result);
echo "</pre>";
exit;*/

 $lastyear = strtotime("-5 year", time());
 $last_year_date = date("Y-m-d H:i:s", $lastyear);//echo $last_year_date;
 $sixty_days_back_last_year_date = date('Y-m-d', strtotime('-60 days', $lastyear));

 $revenue_data_365 = getRevenue_study($sixty_days_back_last_year_date, $last_year_date, 'last_year_current_revenue');

/*  $revenue_data_365[1417105] = Array
        (
           
            'product_id' => 33534,
            'last_year_current_revenue' => 100
        );*/


 foreach($result as $key => $value) {
    if(isset($revenue_data_365[$key])) {
      $result[$key] = array_merge($revenue_data_365[$key], $result[$key]);

      // calculate
      $last_year_percentage_revenue = (($result[$key]['current_revenue'] - $revenue_data_365[$key]['last_year_current_revenue'])/$result[$key]['current_revenue'])*100;
      $result[$key]['last_year_percentage_revenue'] = $last_year_percentage_revenue;
      unset($revenue_data_365[$key]);
    }  
}


$revenue_60_365 = array();
foreach($revenue_data_60 as $key => $value) {
    if(isset($revenue_data_365[$key])) {
      $revenue_60_365[$key] = array_merge($revenue_data_365[$key], $revenue_data_60[$key]);      

      // calculate
      $last_year_percentage_revenue = (($revenue_data_60[$key]['current_revenue'] - $revenue_data_365[$key]['last_year_current_revenue'])/$revenue_data_60[$key]['current_revenue'])*100;
      $revenue_60_365[$key]['last_year_percentage_revenue'] = $last_year_percentage_revenue;

      unset($revenue_data_365[$key]);
      unset($revenue_data_60[$key]);

    }  
}

storeRevenueInDB($revenue_data_60);
storeRevenueInDB($revenue_data_120);
storeRevenueInDB($revenue_data_365);
storeRevenueInDB($result);
storeRevenueInDB($revenue_60_365);

/*echo "<pre>";
print_r($revenue_data_365);
echo "</pre>";
exit;*/


function getRevenue_study($start_date, $end_date, $new_key) {
  global $conn;
  $current_date = date('Y-m-d H:i:s');

       $sql_all_orders = "SELECT entity_id, created_at FROM mage_sales_flat_order WHERE state != 'canceled' AND (created_at >= '".$start_date." 00:00:00' AND  created_at <= '".$end_date."')";
      //echo $sql_all_orders;

  $allactiveOrdersByDays = $conn->query($sql_all_orders);
  $allactiveOrdersByDays = $allactiveOrdersByDays->fetch_all(MYSQLI_ASSOC);
  $all_ordered_skus_revenue = $sku_quantities_in_order = array();
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
           // $sku_quantities_in_order[$order_item['sku']][$order_item['order_id']]['price'] = $order_item['price'];
            $sku_quantities_in_order[$order_item['sku']][$order_item['order_id']]['base_price'] = $order_item['base_price'];
//$sku_quantities_in_order[$order_item['sku']][$order_item['order_id']]['afwijkenidealeverpakking'] =  $order_item['afwijkenidealeverpakking'];
//$sku_quantities_in_order[$order_item['sku']][$order_item['order_id']]['idealeverpakking'] =  $order_item['idealeverpakking'];

        }
    }
/* print_r($sku_quantities_in_order);exit; */
    if(count($sku_quantities_in_order)) {
        foreach($sku_quantities_in_order as $sku=>$orders) {
            if($sku) {
              $order_wise_quantities = array();
              $order_wise_refunded_quantities_365 = array();
              foreach($orders as $order_id=>$sku_data) {
                if($order_id != 0) {
                  $order_wise_quantities[] = $sku_data['Quantity'];
                  $order_wise_refunded_quantities[] = $sku_data['Refunded'];

                  $order_wise_sku_price_excl_tax[] = $sku_data['base_price'] * $sku_data['Quantity'];
                  $refunded_sp_excl_tax[] = $sku_data['base_price'] * $sku_data['Refunded'];
                }
              }

              $all_ordered_skus_revenue[$sku]['product_id'] = $orders[0]['product_id'];
              $all_ordered_skus_revenue[$sku][$new_key] = array_sum($order_wise_sku_price_excl_tax) - array_sum($refunded_sp_excl_tax);  
        }
      }
    }
  }

  
  return $all_ordered_skus_revenue;
}


function storeRevenueInDB($data_with_column_name) {
  global $conn;

  if (count(($data_with_column_name))) {   

    $sql_cols = array_keys($data_with_column_name);//print_r($sql_cols);exit;
    $table_cols = array_keys($data_with_column_name[$sql_cols[0]]);

    $sql = "INSERT INTO revenue_study_price_management(".implode(',', array_keys($data_with_column_name[$sql_cols[0]])).") VALUES";

    foreach($data_with_column_name as $sku=>$r_data) {
      $make_one_data = array();
      foreach($table_cols as $col_name_key) {
        $make_one_data[] = $r_data[$col_name_key];
      }

      $all_col_data[] = "(".implode(',', $make_one_data).")";
    }      

      unset($sql_cols['product_id']);
      $last_part_sql = " ON DUPLICATE KEY UPDATE ";
      $back_part_cols = array();
      foreach($table_cols as $col_name) {
        $back_part_cols[] = "{$col_name} = VALUES({$col_name})";
      }
      $last_part_sql .= implode(',', $back_part_cols);
      $sql .= implode(",", $all_col_data);
      $sql .= $last_part_sql;
      
      if($conn->query($sql)) {
        echo "Inserted:-".count($data_with_column_name)."\n";
      } else {
        echo "Error in chunk insertion:- ".mysqli_error($conn)."\n";
      }
  } else {
    echo 'no data';
  }
}