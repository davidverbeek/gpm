<?php

include "dbconfig.php";

$sql = "SELECT * FROM pm_settings WHERE id = 1";
$result = $conn->query($sql);
$settings_data = $result->fetch_assoc();
$settings_data['roas'] = unserialize($settings_data["roas"]);


/* Get Shipping and Packing Costs Starts */
//$shipping_packing_costs["Transmission"] = 10.5 + 1.55;
//$shipping_packing_costs["Pakketpost"] = 4.8 + 0.9;
//$shipping_packing_costs["Briefpost"] = 3 + 0.6;
$shipping_packing_costs["Transmission"] = $settings_data['roas']['transmission_shipping_cost'] + $settings_data['roas']['transmission_packing_cost'];
$shipping_packing_costs["Pakketpost"] = $settings_data['roas']['pakketpost_shipping_cost'] + $settings_data['roas']['pakketpost_packing_cost'];
$shipping_packing_costs["Briefpost"] = $settings_data['roas']['briefpost_shipping_cost'] + $settings_data['roas']['briefpost_packing_cost'];
/* Get Shipping and Packing Costs Ends */

/* Get Shipment Revenue Starts */
//$shpment_reveenue_wo["Transmission"] = 11.98;
//$shpment_reveenue_wo["Not_Transmission"] = 5.33;
$shpment_reveenue_wo["Transmission"] = $settings_data['roas']['shipment_revenue']["transmission"]["transmission_shippment_revenue_less_then"];
$shpment_reveenue_wo["Not_Transmission"] = $settings_data['roas']['shipment_revenue']["other"]["other_shippment_revenue_less_then"];
/* Get Shipment Revenue Ends */

/* Get Employee Costs Starts */
//$employee_cost_wo = 1.15;
$get_lowest_emp_cost = array_values($settings_data['roas']['employeecost_lower_bound']);
$employee_cost_wo = $get_lowest_emp_cost[0];
/* Get Employee Costs Ends */

//$payment_cost = 0.7;
//$other_company_cost = 5;
$payment_cost = $settings_data['roas']['payment_cost'];
$other_company_cost = $settings_data['roas']['other_company_cost'];

$individual_sku_percentage = $settings_data['roas']['individual_sku_percentage'];
$category_brand_percentage = $settings_data['roas']['category_brand_percentage'];

?>
