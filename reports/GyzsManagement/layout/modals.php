<!-- Modal For Setting Selling % -->
<?php 
 include "config/dbconfig.php";
 $sql = "SELECT * FROM price_management_customer_groups ORDER BY magento_id";
 $all_customer_groups = array();

 if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
      $all_customer_groups[$row['magento_id']] = $row['customer_group_name'];
    }
  }

?>
<div class="modal fade" id="SellingPriceModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="exampleModalLabel">Bulk Update Selling Price</h6><div class="update_loader">(Please wait...<span class="loading-img-update" style="display: inline-block;"></span>)</div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

              <div style="margin-bottom:10px;">
                <select id="sel_cust_group" name="sel_cust_group" class="form-select" style="font-size: 12px;">
                  <option value="">Select customer group</option>
                  <?php foreach($all_customer_groups as $g_id=>$g_value) { ?>
                    <option value="<?php echo $g_id; ?>"><?php echo $g_value; ?></option>
                  <?php } ?>  
                </select>
              </div>

       <div class="input-group input-group-sm mb-3">
              
              <div class="input-group-prepend">
                <span class="input-group-text" style="font-size: 12px;">Percentage Over Selling Price</span> 
              </div>
              <div id="p_s_p" class="p_s_p_pos">+</div>
              <input type="text" class="form-control" id="setsellingprice" aria-label="Small" aria-describedby="inputGroup-sizing-sm" style="font-size: 12px;">
              <div class="input-group-append">
                <span class="input-group-text" id="basic-addon3" style="font-size: 12px;">%</span>
              </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal For Setting Selling % -->

<!-- Modal For Setting Profit Margin % -->
<div class="modal fade" id="ProfitMarginModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Bulk Update Profit Margin</h5><div class="update_loader">(Please wait...<span class="loading-img-update" style="display: inline-block;"></span>)</div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <div style="margin-bottom:10px;">
                <select id="sel_cust_group_pm_bp" name="sel_cust_group_pm_bp" class="form-select" style="font-size: 12px;">
                  <option value="">Select customer group</option>
                  <?php foreach($all_customer_groups as $g_id=>$g_value) { ?>
                    <option value="<?php echo $g_id; ?>"><?php echo $g_value; ?></option>
                  <?php } ?>  
                </select>
              </div>

        <div class="input-group input-group-sm mb-3">
              <div class="input-group-prepend">
                <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 12px;">Percentage Over Buying Price</span>
              </div>
              <input type="text" class="form-control" id="setprofitmargin" aria-label="Small" aria-describedby="inputGroup-sizing-sm" style="font-size: 12px;">
              <div class="input-group-append">
                <span class="input-group-text" id="basic-addon2" style="font-size: 12px;">%</span>
              </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal For Setting Profit Margin % -->


<!-- Modal For Setting Profit Margin % -->
<div class="modal fade" id="ProfitMarginSPModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Bulk Update Profit Margin</h5><div class="update_loader">(Please wait...<span class="loading-img-update" style="display: inline-block;"></span>)</div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <div style="margin-bottom:10px;">
                <select id="sel_cust_group_pm_sp" name="sel_cust_group_pm_sp" class="form-select" style="font-size: 12px;">
                  <option value="">Select customer group</option>
                  <?php foreach($all_customer_groups as $g_id=>$g_value) { ?>
                    <option value="<?php echo $g_id; ?>"><?php echo $g_value; ?></option>
                  <?php } ?>  
                </select>
              </div>

        <div class="input-group input-group-sm mb-3">
              <div class="input-group-prepend">
                <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 12px;">Percentage Over Selling Price</span>
              </div>
              <input type="text" class="form-control" id="setprofitmarginsp" aria-label="Small" aria-describedby="inputGroup-sizing-sm" style="font-size: 12px;">
              <div class="input-group-append">
                <span class="input-group-text" id="basic-addon2" style="font-size: 12px;">%</span>
              </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal For Setting Profit Margin % -->


<!-- Modal For Setting Discount % -->
<div class="modal fade" id="DiscountModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Bulk Update Discount</h5><div class="update_loader">(Please wait...<span class="loading-img-update" style="display: inline-block;"></span>)</div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <div style="margin-bottom:10px;">
                <select id="sel_cust_group_d_gp" name="sel_cust_group_d_gp" class="form-select" style="font-size: 12px;">
                  <option value="">Select customer group</option>
                  <?php foreach($all_customer_groups as $g_id=>$g_value) { ?>
                    <option value="<?php echo $g_id; ?>"><?php echo $g_value; ?></option>
                  <?php } ?>  
                </select>
              </div>

        <div class="input-group input-group-sm mb-3">
              <div class="input-group-prepend">
                <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 12px;">Discount Over Supplier Gross Price</span>
              </div>
              <input type="text" class="form-control" id="setdiscount" aria-label="Small" aria-describedby="inputGroup-sizing-sm" style="font-size: 12px;">
              <div class="input-group-append">
                <span class="input-group-text" id="basic-addon2" style="font-size: 12px;">%</span>
              </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal For Setting Discount % -->

<!-- Modal For History -->
<!-- <div class="modal fade" id="modalHistory" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="max-width: 800px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Supplier Updates <span class="clshistorytitle">(Sku - <span id="his_sku"></span>, Ean - <span id="his_ean"></span>)</span></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
       
      </div>
    </div>
  </div>
</div> -->
<!-- Modal For History -->

<!-- Logout Modal-->
  <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="font-size:12px;">Select "Logout" below if you are ready to end your current session.</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <a class="btn btn-primary" href="logout.php">Logout</a>
        </div>
      </div>
    </div>
  </div>
<!-- Logout Modal-->

<!-- Modal For Activate -->
  <div class="modal fade" id="ActivateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Ready to GO LIVE with this pricing?</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="font-size: 10px;">
        <div id="loading-img" style="display:none; position: absolute;"></div>
        By clicking on below "Yes" button the selling price of <span id="total_updated_records"></span> updated records will be queued to GO LIVE in the NEXT CYCLE.</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <a class="btn btn-primary" href="#" id="confirmedActivated">Yes</a>
        </div>
      </div>
    </div>
  </div>
<!-- Modal For Activate -->


<!-- Modal For Activate -->
  <div class="modal fade" id="ImportModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="font-size:14px;">
          Want to Import Prices?
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="card">
            <div class="card-header" style="font-size:12px;">Import XLSX File </div>
            <div class="card-body">
                
                <div id="message"></div>
                <form id="import_prices" method="POST" enctype="multipart/form-data" class="form-horizontal">
                  

                  <!-- <div class="custom-file mb-3">
                    <input type="file" class="custom-file-input" id="file" name="file">
                    <label class="custom-file-label" for="customFile">Choose file</label>
                  </div> -->

                  <div class="custom-file mb-3">
                    <input class="form-control" type="file" id="formFile" id="file" name="file">
                  </div>



                  <div class="form-group">
                    <input type="hidden" name="hidden_field" value="1" />
                    <input type="submit" name="import" id="import" class="btn btn-primary text" value="Import" style="margin:0px;" />
                  </div>



                </form>
                 <div class="form-group" id="process">
                      <div class="progress" style="display:none;"></div>
                      <div id="import_errors" style="display:none;"></div>
                </div>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </div>
<!-- Modal For Activate -->

<!-- Modal for price logs  -->
<div class="modal fade" id="pricelogModal" tabindex="-1" role="dialog">
  <div class="modal-dialog  modal-dialog-zoom" style="max-width: 1500px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Updates Log <span class="clshistorytitle">(Sku - <span id="his_sku"></span>, Ean - <span id="his_ean"></span>)</span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="history-container">Remote file content will be loaded here</div>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal for price logs  -->


<!-- Modal For EC DeliveryTime -->
  <div class="modal fade" id="ECdeliverytimeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">EC DELIVERY TIME</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="font-size: 10px;">
        <div id="loading-img-ec" style="display:none; position: absolute;"></div>
        By clicking on below "Yes" button the EC Delivery Time of SELECTED SKU's will be UPDATED with <b><span id="ec_updated_ec_del_time"></span></b>.</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="confirmedUpdateecdeliverytime">Yes</button>
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
<!-- Modal For EC DeliveryTime-->

<!-- Modal For Undo -->
  <div class="modal fade" id="UndoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Undo Selling Price</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="font-size: 10px;">
        <div id="loading-img-undo" style="display:none; position: absolute;"></div>
        By clicking on below "Yes" button the previous Selling Price will be set for all the SELECTED SKU's.</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="confirmundo">Yes</button>
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
<!-- Modal For Undo-->

<!-- Modal For EC DeliveryTime BE -->
  <div class="modal fade" id="ECdeliverytimeBEModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">EC DELIVERY TIME BE</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="font-size: 10px;">
        <div id="loading-img-ec-be" style="display:none; position: absolute;"></div>
        By clicking on below "Yes" button the EC Delivery Time BE of SELECTED SKU's will be UPDATED with <b><span id="ec_updated_ec_del_time_be"></span></b>.</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="confirmedUpdateecdeliverytimebe">Yes</button>
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
<!-- Modal For EC DeliveryTime BE-->



<!-- Modal for Revenue logs  -->
<div class="modal fade" id="rDebugModal" tabindex="-1" role="dialog">
  <div class="modal-dialog  modal-dialog-zoom" style="max-width: 1500px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
          <h5 class="modal-title">Ordered Data For <span class="clshistorytitle">(Sku - <span id="rev_deb_sku"></span>, Date - <span id="rev_deb_date"></span>)</span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="revenue-debug-container">Remote file content will be loaded here</div>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal for Revenue logs  -->



<!-- The Modal -->
<div class="modal fade" id="searchDebterPriceModal" >
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h6 class="modal-title" id="FilterModalLabel">Search By Debter Selling Price</h6><div class="update_loader">(Please wait...<span class="loading-img-update" style="display: inline-block;"></span>)</div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
            <div class="input-group input-group-sm mb-3">
              <div class="input-group-prepend">
                <span class="input-group-text" style="font-size: 12px;" id="sp_from_debter_price">From value</span>
              </div>
              <input type="text" class="form-control" id="from_debter_price" aria-label="Small" aria-describedby="inputGroup-sizing-sm" style="font-size: 12px;">
            </div>
            <div class="input-group input-group-sm mb-3" id="div-to-price">
              <div class="input-group-prepend">
                <span class="input-group-text" style="font-size: 12px;">To value</span>
              </div>
              <input type="text" class="form-control" id="to_debter_price" aria-label="Small" aria-describedby="inputGroup-sizing-sm" style="font-size: 12px;">
            </div>

            <input type="hidden" name="hdn_parent_debter_selected" id="hdn_parent_debter_selected">
            <input type="hidden" name="hdn_parent_debter_expression" id="hdn_parent_debter_expression">
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="okSearchDebterPrices" >OK</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>