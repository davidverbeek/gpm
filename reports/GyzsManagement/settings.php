<?php
session_start();

require_once("../../app/Mage.php");
umask(0);
Mage::app();

if(!isset($_SESSION["price_id"])) {
  header("Location:index.php");
}

error_reporting(0);
?>

<!DOCTYPE html>
<html>

<?php
include "config/config.php";
include "define/constants.php";
include "layout/header.php";
?>
<style>
    body {
      font-size: 10px;
      font-family: 'poppins-regular' !important;
    }
    .form-control {
      font-size: 10px;
    }
    .sh_rev_or_val_peak {
      margin-top: 7px;
      display: block;
      margin-left: 5px;
      font-weight: bold;  
    }
  </style>
<?php


function fixArrayOfCheckboxes($check ) {
    $newChecks = array();
    for( $i = 0; $i < count( $checks ); $i++ ) {
        if( $checks[ $i ] == 'off' && $checks[ $i + 1 ] == 'on' ) {
            $newChecks[] = 'on';
            $i++;
        }
        else {
            $newChecks[] = 'off';
        }
    }
    return $newChecks;
}

 $roas_settings = array();
 $success_msg = "";
  if(isset($_REQUEST['btnsettings'])) {
    
    if(is_array($_REQUEST['roasval_lb_set_option'])) {
      $r_l_b_o = $_REQUEST['roasval_lb_set_option'][0];
      $r_l_b_v = $_REQUEST['roasval_lb_set_value'][0];
      $roas_settings['roas_lower_bound'][$r_l_b_o] = $r_l_b_v;
    }


    //$chkroasrange = fixArrayOfCheckboxes($_REQUEST['chkroasrange']);






    if(is_array($_REQUEST['roasmin']) && is_array($_REQUEST['roasmax']) && is_array($_REQUEST['roasval'])) {
      for($i=0;$i<count($_REQUEST['roasmin']);$i++) {
        $roas_settings['roas_range'][$_REQUEST['roasmin'][$i]."-".$_REQUEST['roasmax'][$i]]['r_val'] = $_REQUEST['roasval'][$i];
        $roas_settings['roas_range'][$_REQUEST['roasmin'][$i]."-".$_REQUEST['roasmax'][$i]]['r_type'] = $_REQUEST['chkroasrange'][$i];
      }
    }


    //echo "<pre>";
//print_r($roas_settings);

    if(is_array($_REQUEST['roasval_ub_set_option'])) {
      $r_u_b_o = $_REQUEST['roasval_ub_set_option'][0];
      $r_u_b_v = $_REQUEST['roasval_ub_set_value'][0];
      $roas_settings['roas_upper_bound'][$r_u_b_o] = $r_u_b_v;
    } 

    $roas_settings['transmission_shipping_cost'] = $_REQUEST['transmission_shipping_cost'];
    $roas_settings['transmission_packing_cost'] = $_REQUEST['transmission_packing_cost'];
    $roas_settings['transmission_extra_return_shipment_cost'] = $_REQUEST['transmission_extra_return_shipment_cost'];


    $roas_settings['pakketpost_shipping_cost'] = $_REQUEST['pakketpost_shipping_cost'];
    $roas_settings['pakketpost_packing_cost'] = $_REQUEST['pakketpost_packing_cost'];
    $roas_settings['pakketpost_extra_return_shipment_cost'] = $_REQUEST['pakketpost_extra_return_shipment_cost'];

    

    $roas_settings['briefpost_shipping_cost'] = $_REQUEST['briefpost_shipping_cost'];
    $roas_settings['briefpost_packing_cost'] = $_REQUEST['briefpost_packing_cost'];
    $roas_settings['briefpost_extra_return_shipment_cost'] = $_REQUEST['briefpost_extra_return_shipment_cost'];


    if(is_array($_REQUEST['shippment_revenue_order_value_peak'])){
        $roas_settings['shipment_revenue']["peak_order_value"] = $_REQUEST['shippment_revenue_order_value_peak'][0];
        $roas_settings['shipment_revenue']["transmission"]["transmission_shippment_revenue_less_then"] = $_REQUEST['transmission_shippment_revenue_less_then'];
        $roas_settings['shipment_revenue']["transmission"]["transmission_shippment_revenue_greater_then_or_equal"] = $_REQUEST['transmission_shippment_revenue_greater_then_or_equal'];
        $roas_settings['shipment_revenue']["other"]["other_shippment_revenue_less_then"] = $_REQUEST['other_shippment_revenue_less_then'];
        $roas_settings['shipment_revenue']["other"]["other_shippment_revenue_greater_then_or_equal"] = $_REQUEST['other_shippment_revenue_greater_then_or_equal'];
    }


     if(is_array($_REQUEST['empcost_ov_lb_set_option'])) {
      $eov_l_b_o = $_REQUEST['empcost_ov_lb_set_option'][0];
      $ec_l_b_v = $_REQUEST['empcost_lb_set_value'][0];
      $roas_settings['employeecost_lower_bound'][$eov_l_b_o] = $ec_l_b_v;
    }


    if(is_array($_REQUEST['empcost_ov_min']) && is_array($_REQUEST['empcost_ov_max']) && is_array($_REQUEST['empcostval'])) {
      for($i_ec=0;$i_ec<count($_REQUEST['empcost_ov_min']);$i_ec++) {
        $roas_settings['employeecost_range'][$_REQUEST['empcost_ov_min'][$i_ec]."-".$_REQUEST['empcost_ov_max'][$i_ec]] = $_REQUEST['empcostval'][$i_ec]; 
      }
    }

    if(is_array($_REQUEST['empcost_ov_ub_set_option'])) {
      $eov_u_b_o = $_REQUEST['empcost_ov_ub_set_option'][0];
      $ec_u_b_v = $_REQUEST['empcost_ub_set_value'][0];
      $roas_settings['employeecost_upper_bound'][$eov_u_b_o] = $ec_u_b_v;
    } 

    
    $roas_settings['avg_order_per_month'] = $_REQUEST['avg_order_per_month'];

  
    $roas_settings['payment_cost'] = $_REQUEST['payment_cost'];
    $roas_settings['other_company_cost'] = $_REQUEST['other_company_cost'];

    $roas_settings['individual_sku_percentage'] = $_REQUEST['individual_sku_percentage'];
    $roas_settings['category_brand_percentage'] = $_REQUEST['category_brand_percentage'];
    

    
    $roas_settings['bol_commissions_auth_url'] = $_REQUEST['bol_commissions_auth_url'];
    $roas_settings['bol_client_id'] = $_REQUEST['bol_client_id'];
    $roas_settings['bol_secret'] = $_REQUEST['bol_secret'];
    $roas_settings['bol_commissions_api_url'] = $_REQUEST['bol_commissions_api_url'];

    $roas_settings['bol_buying_percentage'] = $_REQUEST['bol_buying_percentage'];
    
    $roas_settings['bol_return_from_date'] = $_REQUEST['bol_return_from_date'];
    $roas_settings['bol_return_to_date'] = $_REQUEST['bol_return_to_date'];



    $roas_settings["exclude_bol"] = $_REQUEST['excludeBol'];

    $sql = "UPDATE pm_settings SET roas = '".serialize($roas_settings)."' WHERE id = 1";
    $conn->query($sql);

    $success_msg = '<div class="col-lg-12 alert alert-success"><strong>Success!</strong> Settings are updated</div>';
  }


$sql = "SELECT * FROM pm_settings WHERE id = 1";
$result = $conn->query($sql);
$settings_data = $result->fetch_assoc();
$settings_data['roas'] = unserialize($settings_data["roas"]);


$roas_lb_cnt = 0;
if(is_array($settings_data['roas']['roas_lower_bound'])) {
  $roas_lb_cnt = 1;
  foreach($settings_data['roas']['roas_lower_bound'] as $lk=>$lv) {
    $lower_min = $lk;
    $lower_value = $lv;
  }
}

$roas_ub_cnt = 0;
if(is_array($settings_data['roas']['roas_upper_bound'])) {
  $roas_ub_cnt = 1;
  foreach($settings_data['roas']['roas_upper_bound'] as $uk=>$uv) {
    $upper_min = $uk;
    $upper_value = $uv;
  }
}

//echo "<pre>";
//print_r($settings_data['roas']);



$roas_range_cnt = 1;
if(is_array($settings_data['roas']['roas_range'])) {
  
  $roas_range_cnt = count($settings_data['roas']['roas_range']);
  
  $roas_range_index = 0;

  $roas_range_data_min = array();
  $roas_range_data_max = array();
  $roas_range_data_value = array();
  foreach($settings_data['roas']['roas_range'] as $range=>$value) {
    $lu_range = explode("-", $range); 
    $roas_range_data_min[$roas_range_index] = $lu_range[0];  
    $roas_range_data_max[$roas_range_index] = $lu_range[1];
    $roas_range_data_value[$roas_range_index] = $value['r_val'];
    $roas_range_data_type[$roas_range_index] = $value['r_type'];
    $roas_range_index++;
  }
}

//echo "<pre>";
//print_r($roas_range_data_type);




if(is_array($settings_data['roas']['employeecost_lower_bound'])) {
  foreach($settings_data['roas']['employeecost_lower_bound'] as $lkov=>$lvec) {
    $lower_min_empcost_ov = $lkov;
    $lower_value_empcost = $lvec;
  }
}

if(is_array($settings_data['roas']['employeecost_upper_bound'])) {
  foreach($settings_data['roas']['employeecost_upper_bound'] as $ukov=>$uvec) {
    $upper_min_empcost_ov = $ukov;
    $upper_value_empcost = $uvec;
  }
}

$ec_range_cnt = 1;
if(is_array($settings_data['roas']['employeecost_range'])) {
  
  $ec_range_cnt = count($settings_data['roas']['employeecost_range']);
  
  $ec_range_index = 0;

  $empcost_ov_range_data_min = array();
  $empcost_ov_range_data_max = array();
  $empcost_range_data_value = array();
  foreach($settings_data['roas']['employeecost_range'] as $ec_range=>$ec_value) {
    $ec_lu_range = explode("-", $ec_range); 
    $empcost_ov_range_data_min[$ec_range_index] = $ec_lu_range[0];  
    $empcost_ov_range_data_max[$ec_range_index] = $ec_lu_range[1];
    $empcost_range_data_value[$ec_range_index] = $ec_value;
    $ec_range_index++;
  }
}




?>

<body class="fl-width">
  <main>
    
    <!-- Sidebar -->
       <?php //include "layout/left.php"; ?>
    <!-- End of Sidebar -->

    <section class="content-toggle" id="main-content">
      <!-- Topbar -->
       <?php include "layout/top.php"; ?>
      <!-- End of Topbar --> 


      <!-- main-content -->
      <div class="data-toggle overflow-hidden position-fixed" id="data-content">
        <div class="container-fluid">
          <!-- main-card-header -->
          
          <!-- start-form-body -->
          <form id="frmsettings" method="post" action="settings.php" class="needs-validation" novalidate>
          <div class="row">
           
            

            <div class="col-lg-12">
              <div class="card shadow mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">
                    Update Settings
                  </h6>
                </div>
                <div class="card-body">

                  <?php echo $success_msg; ?>

                  <div class="form-group">
                    <div class="card">
                      <div class="card-header bg-primary text-white">Set Roas</div>
                      <div class="card-body">
                        <div class="form-group row control-group-roas-lb after-roas-lb">  
                          <div class="col-sm-2"> 
                              <button class="btn btn-success add-roas-lb btn-sm" type="button" style="line-height: 1.3;"><i class="fa fa-plus" aria-hidden="true"></i> Add Lower Bound</button>
                          </div>
                        </div>

                        <?php if($roas_lb_cnt == 1) { ?>
                          <div class="form-group row control-group-roas-lb">  
                              <div class="col-sm-0.8" style="margin-top:7px;">If ROAS is < </div>
                              <div class="col-sm-2">
                                 <input type="number" step="0.01" name="roasval_lb_set_option[]" class="form-control" placeholder="min" value="<?php echo $lower_min; ?>" required>
                                 <div class="valid-feedback">Looks Good!.</div>
                                 <div class="invalid-feedback">Enter Min Roas</div>
                              </div>
                              <div class="col-sm-0.2" style="margin-top:7px;">then</div>
                              <div class="col-sm-2">
                                <input type="number" step="0.01" name="roasval_lb_set_value[]" class="form-control" placeholder="set roas" value="<?php echo $lower_value; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Enter Roas</div>
                              </div>
                              <div class="col-sm-2"> 
                                  <button class="btn btn-danger remove-lb btn-sm" type="button" style="line-height: 1.3;"><i class="fas fa-times"></i> Remove</button>
                              </div>
                          </div>
                        <?php } ?>  


                        <div class="form-group row control-group after-add-more">  
                              <div class="col-sm-0.8" style="margin-top:7px;">If ROAS is >=</div>
                              <div class="col-sm-2">
                                 <input type="number" step="0.01" name="roasmin[]" class="form-control" placeholder="min" value="<?php echo $roas_range_data_min[0]; ?>" required>
                                 <div class="valid-feedback">Looks Good!.</div>
                                 <div class="invalid-feedback">Enter Min Roas</div>
                              </div>
                              <div class="col-sm-0.2" style="margin-top:7px;">and < </div> 
                             
                              <div class="col-sm-2">
                                <input type="number" step="0.01" name="roasmax[]" class="form-control" placeholder="max" value="<?php echo $roas_range_data_max[0]; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Enter Max Roas</div>
                              </div>
                              <div class="col-sm-0.2" style="margin-top:7px;">then</div>
                              <div class="col-sm-2">
                                
                                  <div class="increment" style="float: left; width: 8px; margin-top: 5px;"><?php if($roas_range_data_type[0] == "increment") { ?> + <?php }?> </div>
                                  <div style="float: left; width: 95%;">
                                    <input type="number" step="0.01" name="roasval[]" class="form-control" placeholder="set roas" value="<?php echo $roas_range_data_value[0]; ?>" required>
                                    <div class="valid-feedback">Looks Good!.</div>
                                    <div class="invalid-feedback">Enter Roas</div>
                                  </div>
                               
                              </div>

                              <div class="col-sm-0.5">
                                <input type="checkbox" name="chkroasrange[]" <?php if($roas_range_data_type[0] == "fixed") { ?> checked="checked" <?php } ?> class="chkroas" style="margin-top: 7px;" value="<?php echo $roas_range_data_type[0]; ?>" />&nbsp;Fixed Value
                               
                              </div> 

                              <div class="col-sm-2"> 
                                  <button class="btn btn-success add-more btn-sm" type="button" style="line-height: 1.3;"><i class="fa fa-plus" aria-hidden="true"></i> Add Range</button>
                              </div>
                        </div>

                        <?php if($roas_range_cnt > 1) { 
                            for($rd=1;$rd<=($roas_range_cnt-1);$rd++) {
                          ?>


                          <div class="form-group row control-group">  
                              <div class="col-sm-0.8" style="margin-top:7px;">If ROAS is >=</div>
                              <div class="col-sm-2">
                                 <input type="number" step="0.01" name="roasmin[]" class="form-control" placeholder="min" value="<?php echo $roas_range_data_min[$rd]; ?>" required>
                                 <div class="valid-feedback">Looks Good!.</div>
                                 <div class="invalid-feedback">Enter Min Roas</div>
                              </div>
                              <div class="col-sm-0.2" style="margin-top:7px;">and < </div> 
                              <div class="col-sm-2">
                                <input type="number" step="0.01" name="roasmax[]" class="form-control" placeholder="max" value="<?php echo $roas_range_data_max[$rd]; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Enter Max Roas</div>
                              </div>
                              <div class="col-sm-0.2" style="margin-top:7px;">then</div>
                              <div class="col-sm-2">
                                <div class="increment" style="float: left; width: 8px; margin-top: 5px;"><?php if($roas_range_data_type[$rd] == "increment") { ?> + <?php }?></div>
                                <div style="float: left; width: 95%;">
                                  <input type="number" step="0.01" name="roasval[]" class="form-control" placeholder="set roas" value="<?php echo $roas_range_data_value[$rd]; ?>" required>
                                  <div class="valid-feedback">Looks Good!.</div>
                                  <div class="invalid-feedback">Enter Roas</div>
                                </div>
                              </div>

                              <div class="col-sm-0.5">
                               

                                <input type="checkbox" name="chkroasrange[]" <?php if($roas_range_data_type[$rd] == "fixed") { ?> checked="checked" <?php } ?> class="chkroas" style="margin-top: 7px;"  value="<?php echo $roas_range_data_type[$rd]; ?>"/>&nbsp;Fixed Value
                                
                              </div>

                              <div class="col-sm-2"> 
                                  <button class="btn btn-danger remove btn-sm" type="button" style="line-height: 1.3;"><i class="fas fa-times"></i> Remove</button>
                              </div>
                          </div>


                        <?php } } ?>  


                       
                        <div class="form-group row control-group-roas-ub after-roas-ub">  
                          <div class="col-sm-2"> 
                              <button class="btn btn-success add-roas-ub btn-sm" type="button" style="line-height: 1.3;"><i class="fa fa-plus" aria-hidden="true"></i> Add Upper Bound</button>
                          </div>
                        </div>

                        <?php if($roas_ub_cnt == 1) { ?>
                          <div class="form-group row control-group-roas-ub">  
                              <div class="col-sm-0.8" style="margin-top:7px;">If ROAS is >= </div>
                              <div class="col-sm-2">
                                 <input type="number" step="0.01" name="roasval_ub_set_option[]" class="form-control" placeholder="min" value="<?php echo $upper_min; ?>" required>
                                 <div class="valid-feedback">Looks Good!.</div>
                                 <div class="invalid-feedback">Enter Min Roas</div>
                              </div>
                              <div class="col-sm-0.2" style="margin-top:7px;">then</div>
                              <div class="col-sm-2">
                                <input type="number" step="0.01" name="roasval_ub_set_value[]" class="form-control" placeholder="set roas" value="<?php echo $upper_value; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Enter Roas</div>
                              </div>
                              <div class="col-sm-2"> 
                                  <button class="btn btn-danger remove-ub btn-sm" type="button" style="line-height: 1.3;"><i class="fas fa-times"></i> Remove</button>
                              </div>
                          </div>
                        <?php } ?>  

                        




                      </div>
                    </div>
                  </div>


                  <div class="form-group">
                    <div class="card">
                      <div class="card-header bg-primary text-white">Transmission</div>
                      <div class="card-body">
                        <div class="form-group row">
                              <label class="col-sm-2 col-form-label">Shipping Cost</label> 
                              <div class="col-sm-10">
                                <input type="number" step="0.01" class="form-control form-control-user" placeholder="Transmission Shipping Cost" name="transmission_shipping_cost" value="<?php echo $settings_data['roas']["transmission_shipping_cost"]; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Enter Shipping Cost. (Eg 10.5)</div>
                              </div>
                          </div>
                          <div class="form-group row">
                              <label class="col-sm-2 col-form-label">Packing Cost</label> 
                              <div class="col-sm-10">
                                <input type="number" step="0.01" class="form-control form-control-user" placeholder="Transmission Packing Cost" name="transmission_packing_cost" value="<?php echo $settings_data['roas']["transmission_packing_cost"]; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Enter Packing Cost. (Eg 1.55)</div>
                              </div>
                          </div>

                          <div class="form-group row">
                              <label class="col-sm-2 col-form-label">Extra Return Shipment Cost</label> 
                              <div class="col-sm-10">
                                <input type="number" step="0.01" class="form-control form-control-user" placeholder="Transmission Extra Return Shipment Cost" name="transmission_extra_return_shipment_cost" value="<?php echo $settings_data['roas']["transmission_extra_return_shipment_cost"]; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Extra Return Shipment Cost. (Eg 12.5)</div>
                              </div>
                          </div>


                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="card">
                      <div class="card-header bg-primary text-white">Pakketpost</div>
                      <div class="card-body">
                        <div class="form-group row">
                              <label class="col-sm-2 col-form-label">Shipping Cost</label> 
                              <div class="col-sm-10">
                                <input type="number" step="0.01" class="form-control form-control-user" placeholder="Pakketpost Shipping Cost" name="pakketpost_shipping_cost" value="<?php echo $settings_data['roas']["pakketpost_shipping_cost"]; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Enter Shipping Cost. (Eg 4.8)</div>
                              </div>
                          </div>
                          <div class="form-group row">
                              <label class="col-sm-2 col-form-label">Packing Cost</label> 
                              <div class="col-sm-10">
                                <input type="number" step="0.01" class="form-control form-control-user" placeholder="Pakketpost Packing Cost" name="pakketpost_packing_cost" value="<?php echo $settings_data['roas']["pakketpost_packing_cost"]; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Enter Packing Cost. (Eg 0.9)</div>
                              </div>
                          </div>

                          <div class="form-group row">
                              <label class="col-sm-2 col-form-label">Extra Return Shipment Cost</label> 
                              <div class="col-sm-10">
                                <input type="number" step="0.01" class="form-control form-control-user" placeholder="Pakketpost Extra Return Shipment Cost" name="pakketpost_extra_return_shipment_cost" value="<?php echo $settings_data['roas']["pakketpost_extra_return_shipment_cost"]; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Extra Return Shipment Cost. (Eg 4.5)</div>
                              </div>
                          </div>


                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="card">
                      <div class="card-header bg-primary text-white">Briefpost</div>
                      <div class="card-body">
                        <div class="form-group row">
                              <label class="col-sm-2 col-form-label">Shipping Cost</label> 
                              <div class="col-sm-10">
                                <input type="number" step="0.01" class="form-control form-control-user" placeholder="Briefpost Shipping Cost" name="briefpost_shipping_cost" value="<?php echo $settings_data['roas']["briefpost_shipping_cost"]; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Enter Shipping Cost. (Eg 3)</div>
                              </div>
                          </div>
                          <div class="form-group row">
                              <label class="col-sm-2 col-form-label">Packing Cost</label> 
                              <div class="col-sm-10">
                                <input type="number" step="0.01" class="form-control form-control-user" placeholder="Briefpost Packing Cost" name="briefpost_packing_cost" value="<?php echo $settings_data['roas']["briefpost_packing_cost"]; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Enter Packing Cost. (Eg 0.6)</div>
                              </div>
                          </div>

                          <div class="form-group row">
                              <label class="col-sm-2 col-form-label">Extra Return Shipment Cost</label> 
                              <div class="col-sm-10">
                                <input type="number" step="0.01" class="form-control form-control-user" placeholder="Briefpost Extra Return Shipment Cost" name="briefpost_extra_return_shipment_cost" value="<?php echo $settings_data['roas']["briefpost_extra_return_shipment_cost"]; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Extra Return Shipment Cost. (Eg 4.5)</div>
                              </div>
                          </div>
                      </div>
                    </div>
                  </div>


                  <div class="form-group">
                    <div class="card">
                      <div class="card-header bg-primary text-white">Shippment Revenue</div>
                      <div class="card-body">
                        <div class="form-group row">
                              <label class="col-sm-2 col-form-label">Order Value</label> 
                              <div class="col-sm-10">
                                <input type="number" step="0.01" class="form-control form-control-user shp_rev_ord_peak" placeholder="Shippment Revenue Order Value" name="shippment_revenue_order_value_peak[]" value="<?php echo $settings_data["roas"]["shipment_revenue"]["peak_order_value"]; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Enter Order Value. (Eg 82.64)</div>
                              </div>
                         </div>     

                          <div class="form-group row">
                              <div class="col-sm-6">
                                <div class="card">
                                  <div class="card-header">Transmission</div>
                                  <div class="card-body">
                                      
                                        <div class="form-group row">
                                          <div class="col-sm-1.5" style="margin-top:7px;">If Order Value is < </div>
                                          <div class="col-sm-0.2">
                                             <span class="sh_rev_or_val_peak"><?php echo $settings_data["roas"]["shipment_revenue"]["peak_order_value"]; ?></span>
                                          </div>
                                          <div class="col-sm-0.2" style="margin-top:7px;">&nbsp;then</div>
                                          <div class="col-sm-9">
                                            <input type="number" step="0.01" name="transmission_shippment_revenue_less_then" class="form-control" placeholder="set shipment revenue" value="<?php echo $settings_data["roas"]["shipment_revenue"]["transmission"]["transmission_shippment_revenue_less_then"]; ?>" required>
                                            <div class="valid-feedback">Looks Good!.</div>
                                            <div class="invalid-feedback">Enter Shipment Revenue (Eg 11.98)</div>
                                          </div>
                                      </div>

                                      <div class="form-group row">
                                          <div class="col-sm-1.5" style="margin-top:7px;">If Order Value is >= </div>
                                          <div class="col-sm-0.2">
                                             <span class="sh_rev_or_val_peak"><?php echo $settings_data["roas"]["shipment_revenue"]["peak_order_value"]; ?></span>
                                          </div>
                                          <div class="col-sm-0.2" style="margin-top:7px;">&nbsp;then</div>
                                          <div class="col-sm-9">
                                            <input type="number" step="0.01" name="transmission_shippment_revenue_greater_then_or_equal" class="form-control" placeholder="set shipment revenue" value="<?php echo $settings_data["roas"]["shipment_revenue"]["transmission"]["transmission_shippment_revenue_greater_then_or_equal"]; ?>" required>
                                            <div class="valid-feedback">Looks Good!.</div>
                                            <div class="invalid-feedback">Enter Shipment Revenue (Eg 8.68)</div>
                                          </div>
                                      </div>    
                                  </div>
                                </div>
                            </div>

                             <div class="col-sm-6">
                                <div class="card">
                                  <div class="card-header">Pakketpost / Briefpost</div>
                                  <div class="card-body">
                                      
                                        <div class="form-group row">
                                          <div class="col-sm-1.5" style="margin-top:7px;">If Order Value is < </div>
                                          <div class="col-sm-0.2">
                                             <span class="sh_rev_or_val_peak"><?php echo $settings_data["roas"]["shipment_revenue"]["peak_order_value"]; ?></span>
                                          </div>
                                          <div class="col-sm-0.2" style="margin-top:7px;">&nbsp;then</div>
                                          <div class="col-sm-9">
                                            <input type="number" step="0.01" name="other_shippment_revenue_less_then" class="form-control" placeholder="set shipment revenue" value="<?php echo $settings_data["roas"]["shipment_revenue"]["other"]["other_shippment_revenue_less_then"]; ?>" required>
                                            <div class="valid-feedback">Looks Good!.</div>
                                            <div class="invalid-feedback">Enter Shipment Revenue (Eg 5.33)</div>
                                          </div>
                                      </div>

                                      <div class="form-group row">
                                          <div class="col-sm-1.5" style="margin-top:7px;">If Order Value is >= </div>
                                          <div class="col-sm-0.2">
                                             <span class="sh_rev_or_val_peak"><?php echo $settings_data["roas"]["shipment_revenue"]["peak_order_value"]; ?></span>
                                          </div>
                                          <div class="col-sm-0.2" style="margin-top:7px;">&nbsp;then</div>
                                          <div class="col-sm-9">
                                            <input type="number" step="0.01" name="other_shippment_revenue_greater_then_or_equal" class="form-control" placeholder="set shipment revenue" value="<?php echo $settings_data["roas"]["shipment_revenue"]["other"]["other_shippment_revenue_greater_then_or_equal"]; ?>" required>
                                            <div class="valid-feedback">Looks Good!.</div>
                                            <div class="invalid-feedback">Enter Shipment Revenue (Eg 0)</div>
                                          </div>
                                      </div>    
                                  </div>
                                </div>
                            </div>
                         </div>
                      </div>
                    </div>
                  </div>


                 

                  <div class="form-group">
                    <div class="card">
                      <div class="card-header bg-primary text-white">Employee Cost</div>
                        <div class="card-body">
                          
                          <div class="form-group row control-group-empcost">  
                            <div class="col-sm-0.8" style="margin-top:7px;">If Order Value is < </div>
                            <div class="col-sm-2">
                               <input type="number" step="0.01" name="empcost_ov_lb_set_option[]" class="form-control" placeholder="min" value="<?php echo $lower_min_empcost_ov; ?>" required>
                               <div class="valid-feedback">Looks Good!.</div>
                               <div class="invalid-feedback">Enter Order Value</div>
                            </div>
                            <div class="col-sm-0.2" style="margin-top:7px;">then</div>
                            <div class="col-sm-2">
                              <input type="number" step="0.01" name="empcost_lb_set_value[]" class="form-control" placeholder="set employee cost" value="<?php echo $lower_value_empcost; ?>" required>
                              <div class="valid-feedback">Looks Good!.</div>
                              <div class="invalid-feedback">Enter Employee Cost</div>
                            </div>
                          </div>


                          <div class="form-group row control-group-empcost after-add-more-empcost">  
                              <div class="col-sm-0.8" style="margin-top:7px;">If Order Value is >=</div>
                              <div class="col-sm-2">
                                 <input type="number" step="0.01" name="empcost_ov_min[]" class="form-control" placeholder="min" value="<?php echo $empcost_ov_range_data_min[0]; ?>" required>
                                 <div class="valid-feedback">Looks Good!.</div>
                                 <div class="invalid-feedback">Enter Min Order Value</div>
                              </div>
                              <div class="col-sm-0.2" style="margin-top:7px;">and < </div> 
                              <div class="col-sm-2">
                                <input type="number" step="0.01" name="empcost_ov_max[]" class="form-control" placeholder="max" value="<?php echo $empcost_ov_range_data_max[0]; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Enter Max Order Value</div>
                              </div>
                              <div class="col-sm-0.2" style="margin-top:7px;">then</div>
                              <div class="col-sm-2">
                                <input type="number" step="0.01" name="empcostval[]" class="form-control" placeholder="set employee cost" value="<?php echo $empcost_range_data_value[0]; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Enter Employee Cost</div>
                              </div>
                              <div class="col-sm-2"> 
                                  <button class="btn btn-success add-more-empcost btn-sm" type="button" style="line-height: 1.3;"><i class="fa fa-plus" aria-hidden="true"></i> Add Range</button>
                              </div>
                          </div>


                          <?php if($ec_range_cnt > 1) { 
                            for($rd_ec=1;$rd_ec<=($ec_range_cnt-1);$rd_ec++) {
                          ?>

                          <div class="form-group row control-group-empcost">  
                              <div class="col-sm-0.8" style="margin-top:7px;">If Order Value is >=</div>
                              <div class="col-sm-2">
                                 <input type="number" step="0.01" name="empcost_ov_min[]" class="form-control" placeholder="min" value="<?php echo $empcost_ov_range_data_min[$rd_ec]; ?>" required>
                                 <div class="valid-feedback">Looks Good!.</div>
                                 <div class="invalid-feedback">Enter Min Order Value</div>
                              </div>
                              <div class="col-sm-0.2" style="margin-top:7px;">and < </div> 
                              <div class="col-sm-2">
                                <input type="number" step="0.01" name="empcost_ov_max[]" class="form-control" placeholder="max" value="<?php echo $empcost_ov_range_data_max[$rd_ec]; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Enter Max Order Value</div>
                              </div>
                              <div class="col-sm-0.2" style="margin-top:7px;">then</div>
                              <div class="col-sm-2">
                                <input type="number" step="0.01" name="empcostval[]" class="form-control" placeholder="set employee cost" value="<?php echo $empcost_range_data_value[$rd_ec]; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Enter Employee Cost</div>
                              </div>
                              <div class="col-sm-2"> 
                                <button class="btn btn-danger remove-empcost btn-sm" type="button" style="line-height: 1.3;"><i class="fas fa-times"></i> Remove</button>
                              </div>
                          </div>

                        <?php } }?>


                          <div class="form-group row control-group-empcost">  
                              <div class="col-sm-0.8" style="margin-top:7px;">If Order Value is >= </div>
                              <div class="col-sm-2">
                                 <input type="number" step="0.01" name="empcost_ov_ub_set_option[]" class="form-control" placeholder="min" value="<?php echo $upper_min_empcost_ov; ?>" required>
                                 <div class="valid-feedback">Looks Good!.</div>
                                 <div class="invalid-feedback">Enter Order Value</div>
                              </div>
                              <div class="col-sm-0.2" style="margin-top:7px;">then</div>
                              <div class="col-sm-2">
                                <input type="number" step="0.01" name="empcost_ub_set_value[]" class="form-control" placeholder="set employee cost" value="<?php echo $upper_value_empcost; ?>" required>
                                <div class="valid-feedback">Looks Good!.</div>
                                <div class="invalid-feedback">Enter Employee Cost</div>
                              </div>   
                          </div>



                        </div>
                    </div>
                  </div> 



                  <div class="form-group">
                    <div class="card">
                      <div class="card-header bg-primary text-white">Bol</div>
                      <div class="card-body">
                          
                          <div class="form-group row">
                              <label class="col-sm-2 col-form-label">Authentication Api Url</label> 
                              <div class="col-sm-10">
                                <input class="form-control form-control-user" placeholder="Bol Commissions Authentication Url" name="bol_commissions_auth_url" value="<?php echo $settings_data['roas']["bol_commissions_auth_url"]; ?>" required>
                              </div>
                          </div>                          

                          <div class="form-group row">
                              <label class="col-sm-2 col-form-label">Client ID</label> 
                              <div class="col-sm-10">
                                <input class="form-control form-control-user" placeholder="Bol Client ID" name="bol_client_id" value="<?php echo $settings_data['roas']["bol_client_id"]; ?>" required>
                              </div>
                          </div>
                          <div class="form-group row">
                              <label class="col-sm-2 col-form-label">Secret</label> 
                              <div class="col-sm-10">
                                <input class="form-control form-control-user" placeholder="Bol Secret" name="bol_secret" value="<?php echo $settings_data['roas']["bol_secret"]; ?>" required>
                              </div>
                          </div>

                          <div class="form-group row">
                              <label class="col-sm-2 col-form-label">Commissions Api Url</label> 
                              <div class="col-sm-10">
                                <input class="form-control form-control-user" placeholder="Bol Commissions Api Url" name="bol_commissions_api_url" value="<?php echo $settings_data['roas']["bol_commissions_api_url"]; ?>" required>
                              </div>
                          </div>

                          <div class="form-group row">
                            <label for="bol_buying_percentage" class="col-sm-2 col-form-label">Bol Buying Percentage</label> 
                            <div class="col-sm-10">
                              <input type="number" step="0.01" class="form-control form-control-user" placeholder="Bol Buying Percentage" name="bol_buying_percentage" value="<?php echo $settings_data['roas']["bol_buying_percentage"]; ?>" required>
                              <div class="valid-feedback">Looks Good!.</div>
                              <div class="invalid-feedback">Enter bol_buying_percentage. (Eg 15)</div>
                            </div>
                          </div>

                          <div class="form-group row">
                              <label class="col-sm-2 col-form-label">Return From Date</label> 
                              <div class="col-sm-10">
                                <input class="form-control form-control-user" placeholder="Bol Return From Date" name="bol_return_from_date" value="<?php echo $settings_data['roas']["bol_return_from_date"]; ?>" required> (Date Format :- YYYY-MM-DD)
                              </div>
                          </div>

                          <div class="form-group row">
                              <label class="col-sm-2 col-form-label">Return To Date</label> 
                              <div class="col-sm-10">
                                <input class="form-control form-control-user" placeholder="Bol Return To Date" name="bol_return_to_date" value="<?php echo $settings_data['roas']["bol_return_to_date"]; ?>" required> (Date Format :- YYYY-MM-DD)
                              </div>
                          </div>


                      </div>
                    </div>
                  </div>            



                  
                  <div class="form-group row">
                      <label class="col-sm-2 col-form-label">Average Order Per Month</label> 
                      <div class="col-sm-10">
                        <select name="avg_order_per_month" id="avg_order_per_month" class="custom-select custom-select-sm form-control form-control-sm">
                          <option value="1" <?php if($settings_data['roas']['avg_order_per_month'] == 1) { ?>  selected  <?php } ?> > >= 1</option>
                          <option value="0.75" <?php if($settings_data['roas']['avg_order_per_month'] == 0.75) { ?>  selected  <?php } ?> > >= 0.75</option>
                          <option value="0.50" <?php if($settings_data['roas']['avg_order_per_month'] == 0.50) { ?>  selected  <?php } ?> > >= 0.50</option>
                          <option value="0.25" <?php if($settings_data['roas']['avg_order_per_month'] == 0.25) { ?>  selected  <?php } ?> > >= 0.25</option>
                        </select>
                      </div>
                  </div>



                  <div class="form-group row">
                      <label for="payment_cost" class="col-sm-2 col-form-label">Payment Cost</label> 
                      <div class="col-sm-10">
                        <input type="number" step="0.01" class="form-control form-control-user" placeholder="Payment Cost" name="payment_cost" value="<?php echo $settings_data['roas']["payment_cost"]; ?>" required>
                        <div class="valid-feedback">Looks Good!.</div>
                        <div class="invalid-feedback">Enter Payment Cost. (Eg 0.7)</div>
                      </div>
                  </div>

                  <div class="form-group row">
                      <label for="other_company_cost" class="col-sm-2 col-form-label">Other Company Cost</label> 
                      <div class="col-sm-10">
                        <input type="number" step="0.01" class="form-control form-control-user" placeholder="Other Company Cost" name="other_company_cost" value="<?php echo $settings_data['roas']["other_company_cost"]; ?>" required>
                        <div class="valid-feedback">Looks Good!.</div>
                        <div class="invalid-feedback">Enter Other Company Cost. (Eg 5)</div>
                      </div>
                  </div>

                   <div class="form-group row">
                      <label for="individual_sku_percentage" class="col-sm-2 col-form-label">Individual SKU Percentage</label> 
                      <div class="col-sm-10">
                        <input type="number" step="0.01" class="form-control form-control-user" placeholder="Individual SKU Percentage" name="individual_sku_percentage" value="<?php echo $settings_data['roas']["individual_sku_percentage"]; ?>" required>
                        <div class="valid-feedback">Looks Good!.</div>
                        <div class="invalid-feedback">Enter Individual SKU Percentage. (Eg 30)</div>
                      </div>
                  </div>

                   <div class="form-group row">
                      <label for="category_brand_percentage" class="col-sm-2 col-form-label">Category Brand Percentage</label> 
                      <div class="col-sm-10">
                        <input type="number" step="0.01" class="form-control form-control-user" placeholder="Category Brand Percentage" name="category_brand_percentage" value="<?php echo $settings_data['roas']["category_brand_percentage"]; ?>" required>
                        <div class="valid-feedback">Looks Good!.</div>
                        <div class="invalid-feedback">Enter category_brand_percentage. (Eg 70)</div>
                      </div>
                  </div>

                  


                  <div>
                      <input type="checkbox" id="excludeBol" name="excludeBol" <?php if($settings_data['roas']["exclude_bol"] == 1) { ?> checked="checked" <?php } ?> value="1" style="margin-top:5px;">
                      <label>Exclude Bol orders in Roas calculation</label>
                  </div>

                 

                  
                  
                  <!-- <div>
                    <label for="from">From</label> <input type="text" id="from" name="from"> <label for="to">to</label> <input type="text" id="to" name="to">
                  
                  </div>

                  <div style="margin-top: 5px;">
                    <button type="button" class="btn btn-primary getroasbtn" style="font-size: 12px;">Get Roas</button>
                    &nbsp;<span id="rsp" style="display: none;">Please wait...</span>
                  </div> --> 

                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-6">
              <input type="submit" name="btnsettings" id="btnsettings" class="btn btn-success"  value="Save Settings" />
            </div>
          </div>


        </form>


        <!-- Copy Fields For Roas Range -->
        <div class="copy" style="display: none;">
          <div class="form-group row control-group">  
              <div class="col-sm-0.8" style="margin-top:7px;">If ROAS is >=</div>
              <div class="col-sm-2">
                 <input type="number" step="0.01" name="roasmin[]" class="form-control" placeholder="min" required>
                 <div class="valid-feedback">Looks Good!.</div>
                 <div class="invalid-feedback">Enter Min Roas</div>
              </div>
              <div class="col-sm-0.2" style="margin-top:7px;">and < </div> 
              <div class="col-sm-2">
                <input type="number" step="0.01" name="roasmax[]" class="form-control" placeholder="max" required>
                <div class="valid-feedback">Looks Good!.</div>
                <div class="invalid-feedback">Enter Max Roas</div>
              </div>
              <div class="col-sm-0.2" style="margin-top:7px;">then</div>
              <div class="col-sm-2">
                <div class="increment" style="float: left; width: 8px; margin-top: 5px;">&nbsp;</div>
                <div style="float: left; width: 95%;">
                  <input type="number" step="0.01" name="roasval[]" class="form-control" placeholder="set roas" required>
                  <div class="valid-feedback">Looks Good!.</div>
                  <div class="invalid-feedback">Enter Roas</div>
                </div>
              </div>
              <div class="col-sm-0.5">
                <input type="checkbox" name="chkroasrange[]" checked="checked" class="chkroas" style="margin-top: 7px;" value="fixed" />&nbsp;Fixed Value
                
              </div> 
              <div class="col-sm-2"> 
                  <button class="btn btn-danger remove btn-sm" type="button" style="line-height: 1.3;"><i class="fas fa-times"></i> Remove</button>
              </div>
          </div>  
        </div>
        <!-- Copy Fields For Roas Range -->


        <!-- Copy Fields For Roas Lower Bound -->
        <div class="copy-roas-lb" style="display: none;">
          <div class="form-group row control-group-roas-lb">  
              <div class="col-sm-0.8" style="margin-top:7px;">If ROAS is < </div>
              <div class="col-sm-2">
                 <input type="number" step="0.01" name="roasval_lb_set_option[]" class="form-control" placeholder="min" required>
                 <div class="valid-feedback">Looks Good!.</div>
                 <div class="invalid-feedback">Enter Min Roas</div>
              </div>
              <div class="col-sm-0.2" style="margin-top:7px;">then</div>
              <div class="col-sm-2">
                <input type="number" step="0.01" name="roasval_lb_set_value[]" class="form-control" placeholder="set roas" required>
                <div class="valid-feedback">Looks Good!.</div>
                <div class="invalid-feedback">Enter Roas</div>
              </div>
              <div class="col-sm-2"> 
                  <button class="btn btn-danger remove-lb btn-sm" type="button" style="line-height: 1.3;"><i class="fas fa-times"></i> Remove</button>
              </div>
          </div>
          <input type="hidden" id="roas-lb-cnt" value="<?php echo $roas_lb_cnt; ?>">  
        </div>
        <!-- Copy Fields For Roas Lower Bound -->


        <!-- Copy Fields For Roas Lower Bound -->
        <div class="copy-roas-ub" style="display: none;">
          <div class="form-group row control-group-roas-ub">  
              <div class="col-sm-0.8" style="margin-top:7px;">If ROAS is >= </div>
              <div class="col-sm-2">
                 <input type="number" step="0.01" name="roasval_ub_set_option[]" class="form-control" placeholder="min" required>
                 <div class="valid-feedback">Looks Good!.</div>
                 <div class="invalid-feedback">Enter Min Roas</div>
              </div>
              <div class="col-sm-0.2" style="margin-top:7px;">then</div>
              <div class="col-sm-2">
                <input type="number" step="0.01" name="roasval_ub_set_value[]" class="form-control" placeholder="set roas" required>
                <div class="valid-feedback">Looks Good!.</div>
                <div class="invalid-feedback">Enter Roas</div>
              </div>
              <div class="col-sm-2"> 
                  <button class="btn btn-danger remove-ub btn-sm" type="button" style="line-height: 1.3;"><i class="fas fa-times"></i> Remove</button>
              </div>
          </div>
          <input type="hidden" id="roas-ub-cnt" value="<?php echo $roas_ub_cnt; ?>">  
        </div>
        <!-- Copy Fields For Roas Lower Bound -->


         <!-- Copy Fields For Employee Cost Range -->
        <div class="copy-empcost" style="display: none;">
          <div class="form-group row control-group-empcost">  
              <div class="col-sm-0.8" style="margin-top:7px;">If Order Value is >=</div>
              <div class="col-sm-2">
                 <input type="number" step="0.01" name="empcost_ov_min[]" class="form-control" placeholder="min" required>
                 <div class="valid-feedback">Looks Good!.</div>
                 <div class="invalid-feedback">Enter Order Value</div>
              </div>
              <div class="col-sm-0.2" style="margin-top:7px;">and < </div> 
              <div class="col-sm-2">
                <input type="number" step="0.01" name="empcost_ov_max[]" class="form-control" placeholder="max" required>
                <div class="valid-feedback">Looks Good!.</div>
                <div class="invalid-feedback">Enter Order Value</div>
              </div>
              <div class="col-sm-0.2" style="margin-top:7px;">then</div>
              <div class="col-sm-2">
                <input type="number" step="0.01" name="empcostval[]" class="form-control" placeholder="set employee cost" required>
                <div class="valid-feedback">Looks Good!.</div>
                <div class="invalid-feedback">Enter Employee Cost</div>
              </div>
              <div class="col-sm-2"> 
                  <button class="btn btn-danger remove-empcost btn-sm" type="button" style="line-height: 1.3;"><i class="fas fa-times"></i> Remove</button>
              </div>
          </div>  
        </div>
        <!-- Copy Fields For Roas Range -->



        </div>
      </div>
    </section>
  </main>
</body>

<script>
// Disable form submissions if there are invalid fields
(function() {
  'use strict';
  window.addEventListener('load', function() {
    // Get the forms we want to add validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        console.log(form.checkValidity());
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        } 
        
        form.classList.add('was-validated');

         
         $(".chkroas").each(function() {  
              if($(this).prop("checked") == false){
                $(this).prop('checked', true);
              } 
          });
      }, false);
    });
  }, false);
})();

  $(document).ready(function() {

      /* Add Roas Range */
      $(".add-more").click(function(){ 
          var html = $(".copy").html();
          $(".after-add-more").after(html);
      });
      $("body").on("click",".remove",function(){ 
          $(this).parents(".control-group").remove();
      });
      /* Add Roas Range */

      /* Add Roas Lower Bound */
      $(".add-roas-lb").click(function(){
          var lb_cnt_vals = $("#roas-lb-cnt").val();
          if(lb_cnt_vals == 0) {
            var lb_html = $(".copy-roas-lb").html();
            $(".after-roas-lb").after(lb_html);
          }
          $("#roas-lb-cnt").val(1); 
      });
      $("body").on("click",".remove-lb",function(){ 
          $(this).parents(".control-group-roas-lb").remove();
          $("#roas-lb-cnt").val(0); 
      });
      /* Add Roas Lower Bound */


      /* Add Roas Upper Bound */
      $(".add-roas-ub").click(function(){
          var ub_cnt_vals = $("#roas-ub-cnt").val();
          if(ub_cnt_vals == 0) {
            var ub_html = $(".copy-roas-ub").html();
            $(".after-roas-ub").after(ub_html);
          }
          $("#roas-ub-cnt").val(1); 
      });
      $("body").on("click",".remove-ub",function(){ 
          $(this).parents(".control-group-roas-ub").remove();
          $("#roas-ub-cnt").val(0); 
      });
      /* Add Roas Upper Bound */


      /* Add Employee Cost Range */
      $(".add-more-empcost").click(function(){ 
          var html = $(".copy-empcost").html();
          $(".after-add-more-empcost").after(html);
      });
      $("body").on("click",".remove-empcost",function(){ 
          $(this).parents(".control-group-empcost").remove();
      });
      /* Add Employee Cost Range */



     window.setTimeout(function() {
        $(".alert").slideToggle();
      }, 4000);

      $(".shp_rev_ord_peak").on("keyup", function() {
          $(".sh_rev_or_val_peak").html(this.value);
      });


      $("body").on("click",".chkroas",function(){ 
        if($(this).prop("checked") == true){
          $(this).closest('.control-group').children('div').find( ".increment" ).html("&nbsp;");
          $(this).val("fixed");
        } else {
          $(this).closest('.control-group').children('div').find( ".increment" ).html("+");
          $(this).val("increment");
        }
          //$(this).parents(".control-group-roas-lb").remove();
          //$("#roas-lb-cnt").val(0); 
      });


    });


</script>

</body>
</html>

