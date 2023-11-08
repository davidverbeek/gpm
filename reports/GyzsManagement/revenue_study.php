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


 

$current_date = date('Y-m-d');
$days = 60;
$sixty_days_back = strtotime("-".$days." day", time());
$sixty_days_back_date = date("Y-m-d",  $sixty_days_back);

$revenue_data_60 = getRevenue_study($sixty_days_back_date, $current_date, 'current_revenue');

$sixty_one_days_back_date = date('Y-m-d', strtotime('-1 day', strtotime($sixty_days_back_date)));

$date = new DateTime($sixty_one_days_back_date);
$date->modify('-60 days');
$previous_date = $date->format('Y-m-d');
//echo $sixty_one_days_back_date; echo $previous_date;
//exit;

$revenue_data_120 = getRevenue_study($previous_date, $sixty_one_days_back_date, 'previous_revenue');

$result = array();
foreach($revenue_data_60 as $key => $value) {
    if(isset($revenue_data_120[$key])) {
      $result[$key] = array_merge($revenue_data_120[$key], $revenue_data_60[$key]);      

      // calculate
      if($revenue_data_120[$key]['previous_revenue']) {
        $pecentage_revenue = (($revenue_data_60[$key]['current_revenue']  * 100)/$revenue_data_120[$key]['previous_revenue']);
        $result[$key]['percentage_revenue'] = ($pecentage_revenue-100);
      } elseif($revenue_data_60[$key]['current_revenue']) {
        $result[$key]['percentage_revenue'] = 100;
      } else {
        $result[$key]['percentage_revenue'] = 0;
      }
      unset($revenue_data_120[$key]);
      unset($revenue_data_60[$key]);
    }  
}

/*echo "<pre>";
print_r($result);
echo "</pre>";
exit;*/

 $lastyear = strtotime("-1 year", time());
 $last_year_date = date("Y-m-d", $lastyear);//echo $last_year_date;
 $sixty_days_back_last_year_date = date('Y-m-d', strtotime('-60 days', $lastyear));

 $revenue_data_365 = getRevenue_study($sixty_days_back_last_year_date, $last_year_date, 'last_year_current_revenue');

 foreach($result as $key => $value) {
    if(isset($revenue_data_365[$key])) {
      $result[$key] = array_merge($revenue_data_365[$key], $result[$key]);

      // calculate
      if($revenue_data_365[$key]['last_year_current_revenue']) {
        $last_year_percentage_revenue = (($result[$key]['current_revenue'] * 100)/$revenue_data_365[$key]['last_year_current_revenue']);
        $result[$key]['last_year_percentage_revenue'] = ($last_year_percentage_revenue-100);
      } elseif($result[$key]['current_revenue']) {
        $result[$key]['last_year_percentage_revenue'] = 100;
      } else {
        $result[$key]['last_year_percentage_revenue'] = 0;
      }
      unset($revenue_data_365[$key]);
    }  
}


$revenue_60_365 = array();
foreach($revenue_data_60 as $key => $value) {
    if(isset($revenue_data_365[$key])) {
      $revenue_60_365[$key] = array_merge($revenue_data_365[$key], $revenue_data_60[$key]);      

      // calculate
      if($revenue_data_365[$key]['last_year_current_revenue']) {
        $last_year_percentage_revenue = ($revenue_data_60[$key]['current_revenue'] * 100)/$revenue_data_365[$key]['last_year_current_revenue'];
        $revenue_60_365[$key]['last_year_percentage_revenue'] = ($last_year_percentage_revenue-100);

      } elseif($revenue_data_60[$key]['current_revenue']) {
        $revenue_60_365[$key]['last_year_percentage_revenue'] = 100;
      } else {
        $revenue_60_365[$key]['last_year_percentage_revenue'] = 0;
      }
      unset($revenue_data_365[$key]);
      unset($revenue_data_60[$key]);
    }
}

storeRevenueInDB($revenue_data_60, 'percentage_revenue',100, 'last_year_percentage_revenue', 100);
storeRevenueInDB($revenue_data_120, 'percentage_revenue',-100,'last_year_percentage_revenue', 0);
storeRevenueInDB($revenue_data_365, 'percentage_revenue', 0,'last_year_percentage_revenue',-100);
storeRevenueInDB($result,'',0);
storeRevenueInDB($revenue_60_365,'',0);

/*echo "<pre>";
print_r($result);
echo "</pre>";
exit;*/


function getRevenue_study($start_date, $end_date, $new_key) {
  global $conn;

  $sql_all_orders = "SELECT entity_id, created_at FROM mage_sales_flat_order WHERE (state != 'canceled' ) AND (created_at >= '".$start_date." 00:00:00' AND  created_at <= '".$end_date." 23:59:00')";
  // echo $sql_all_orders;exit;

  $allactiveOrders = $conn->query($sql_all_orders);
  $allactiveOrders = $allactiveOrders->fetch_all(MYSQLI_ASSOC);

  $all_ordered_skus = $sku_quantities_in_order = array();

  if(count($allactiveOrders)) {
    foreach($allactiveOrders as $order) {
      $order_id = $order['entity_id'];

      $sql_order_items = "SELECT * FROM mage_sales_flat_order_item WHERE order_id = '".$order_id."'";
      $allOrderItems = $conn->query($sql_order_items);
      $allOrderItems = $allOrderItems->fetch_all(MYSQLI_ASSOC);

      foreach($allOrderItems as $order_item) {

        $product_id = $order_item['product_id'];
        $qty_ordered = $order_item['qty_ordered'];
        $qty_refunded = $order_item['qty_refunded'];
        $product_price_excl_tax = $order_item['base_price'];

        $sku_quantities_in_order[$order_item['sku']][0]['product_id'] = $product_id;
        $sku_quantities_in_order[$order_item['sku']][$order_item['order_id']]['Quantity'] = $qty_ordered;
        $sku_quantities_in_order[$order_item['sku']][$order_item['order_id']]['Refunded'] = $qty_refunded;
        $sku_quantities_in_order[$order_item['sku']][$order_item['order_id']]['base_price'] = $product_price_excl_tax;

      }
    }
    /* print_r($sku_quantities_in_order);exit; */
    if(count($sku_quantities_in_order)) {
      foreach($sku_quantities_in_order as $sku=>$orders) {

        if($sku) {

          $order_wise_quantities = array();
          $order_wise_sku_price_excl_tax = array();

          $order_wise_refunded_quantities = array();
          $refunded_sp_excl_tax = array();

          foreach($orders as $order_id=>$sku_data) {
            if($order_id != 0) {
              $order_wise_quantities[] = $sku_data['Quantity'];
              $order_wise_refunded_quantities[] = $sku_data['Refunded'];

              $order_wise_sku_price_excl_tax[] = $sku_data['base_price'] * $sku_data['Quantity'];
              $refunded_sp_excl_tax[] = $sku_data['base_price'] * $sku_data['Refunded'];
            }
          }

          $sku_total_revenue = array_sum($order_wise_sku_price_excl_tax) - array_sum($refunded_sp_excl_tax);

          if ($sku_total_revenue > 0) {
            $all_ordered_skus[$sku]['product_id'] = $orders[0]['product_id'];
            $all_ordered_skus[$sku][$new_key] =  $sku_total_revenue;
          }
        }
      }
    }
  }

  //print_r($all_ordered_skus);exit;
  return $all_ordered_skus;
}


function storeRevenueInDB($data_with_column_name,$extra_col_name, $extra_col_value, $extra_col_name_2='', $extra_col_value_2=0) {
  global $conn;

  if (count(($data_with_column_name))) {

    $sql_cols = array_keys($data_with_column_name);//print_r($sql_cols);exit;
    $table_cols = array_keys($data_with_column_name[$sql_cols[0]]);

    if($extra_col_name == '') {
      $sql = "INSERT INTO revenue_study_price_management(".implode(',', array_keys($data_with_column_name[$sql_cols[0]])).") VALUES";
    } else {
      $sql = "INSERT INTO revenue_study_price_management(".implode(',', array_keys($data_with_column_name[$sql_cols[0]])).", {$extra_col_name}, {$extra_col_name_2}) VALUES";
    }

    foreach($data_with_column_name as $sku=>$r_data) {
      $make_one_data = array();
      foreach($table_cols as $col_name_key) {
        $make_one_data[] = isset($r_data[$col_name_key])?$r_data[$col_name_key]: 0;
      }

      if($extra_col_name != '') {
        $all_col_data[] = "(".implode(',', $make_one_data).", {$extra_col_value}, {$extra_col_value_2})";
      } else {
        $all_col_data[] = "(".implode(',', $make_one_data).")";
      }
    }      

      unset($sql_cols['product_id']);
      $last_part_sql = " ON DUPLICATE KEY UPDATE ";
      $back_part_cols = array();
      foreach($table_cols as $col_name) {
        $back_part_cols[] = "{$col_name} = VALUES({$col_name})";
      }

      if($extra_col_name != '') {
        $back_part_cols[] = "{$extra_col_name} = VALUES({$extra_col_name})";
        $back_part_cols[] = "{$extra_col_name_2} = VALUES({$extra_col_name_2})";
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
    echo 'no data<br>';
  }
}