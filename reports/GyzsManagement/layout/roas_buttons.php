<style>
  .marq_class_item {
    padding: 5px;
  }
  .avghelp {
    margin-left: 5px;
  }
  .filter_date td {
    padding: 5px;
}
</style>


<div class="perfomance-card card">
    <div class="hl-title tital-c2">
        <span class="border-hl"></span>
        <span class="perfomance-title title">Perfomance</span>
    </div>
    <div class="data data-perfomance d-flex flex-column position-relative"> 
      <div style="padding: 5px;">
            <span class="filter_per up">Under Performance</span>
            <span class="filter_per op">Over Performance</span>
            <span class="filter_per_reset"><i class="fas fa-undo"></i></span>
      </div>
    </div>
</div>

<div class="average-card card">
    <div class="hl-title tital-c1">
        <span class="border-hl"></span>
        <span class="average-title title">Averages</span>
        <a class="average-popup avghelp" href="#">?</a>
    </div>
    <div class="data data-average">

      <div class="avg_roas marq_class_item"></div>
      <div class="avg_end_roas marq_class_item"></div>
      <div class="avg_roas_with_google_kosten marq_class_item"></div>
      <div class="avg_end_roas_with_google_kosten marq_class_item"></div>
      <div class="avg_roas_google marq_class_item"></div>

          <!--
        <div class="average google avg_roas"></div>
        <div class="average adword"></div>
        <div class="average adword-end-roas"></div>
        <div class="average all-roas"></div>
        <div class="average all-end-roas"></div> -->

    </div>
</div>


<div class=" set-price-card card">  
  <div class="hl-title tital-c3">
    <span class="border-hl"></span>
    <span class="colum-title title">Get Roas</span>
  </div>
  
  <div>
    <div class="data data-price d-flex row">
      <?php
        if($hdnexlbol == 1) {
          $bol_txt = "Bol Orders are EXCLUDED";
        } else {
          $bol_txt = "All Orders Including Bol";
        }
        ?>
      <div style="margin-bottom:10px; color: #E60D17;">Note: <?php echo $bol_txt; ?> </div>

      <div>
        <table class="table table-bordered filter_date">
            <tr>
              <td><label for="from">From</label></td>
              <td><input type="text" id="from" name="from" class="form-control"></td>
            </tr>
            <tr>
              <td><label for="to">To</label></td>
              <td><input type="text" id="to" name="to" class="form-control"></td>
            </tr>
            <tr>
              <td colspan="2">
                  <button type="button" class="btn btn-purple getroasbtn" style="font-size: 12px; margin:0px;">Get Roas</button>          
              </td>
            </tr>
            <tr>
              <td colspan="2">
                  <span id="rsp" style="display: none;">ROAS is calculating on the fly, so it will take some time please be patient...</span>    
                  <div style="margin-top: 5px; color: #c10000;" id="roas_fetch_errror"></div>          
              </td>
            </tr>

        </table>
      </div>

    </div>          
  </div>
</div>


<div class=" set-price-card card">  
  <div class="hl-title tital-c3">
    <span class="border-hl"></span>
    <span class="colum-title title">Set Roas</span>
  </div>
  
  <div>
    <div class="data data-price d-flex row">
      
      <table class="table table-bordered filter_date">
        <tr>
          <td>
              <div style="margin-bottom:10px; color: #E60D17;">New Live Feed Date Set :- <span id="new_live_feed_date_set"></span></div>      
          </td>
        </tr>

        <tr>
          <td>
            <div>
              <a href="#" class="btn btn-success btn-icon-split" id="setroasdate" style="margin:0px;">
                <span class="icon text-white-50">
                  <i class="fa fa-check-circle" style="font-size:16px;color:#d2f4e8;"></i>
                </span>
                <span class="text">Set Roas Date</span>
              </a>
            </div>         
          </td>
        </tr>

      </table>
               
    </div>          
  </div>
</div>

<div class=" set-price-card card">  
  <div class="hl-title tital-c3">
    <span class="border-hl"></span>
    <span class="colum-title title">Export Roas</span>
  </div>
  
  <div>
    <div class="data data-price d-flex row">
      
      <a href="#" class="btn btn-purple btn-icon-split" id="btnexport_roas" style="position: relative; width: 118px;">
            <span class="icon text-white-50">
              <i class="fas fa-file-export" style="font-size:12px;color:#d2f4e8;"></i>
            </span>
            <span id="loading-img-export" style="display:none; position: absolute;"></span>
            <span class="text">Export Roas</span>
          </a>
                   
    </div>          
  </div>
</div>

<!--

<div class="row">
    <div class="col-lg-4">
      <div class="card shadow mb-4">
        <div class="card-header py-1">
          <h6 class="m-0 font-weight-bold text-primary">
            Get Roas
            
          </h6>

        </div>
        <div class="card-body">

            <?php
            if($hdnexlbol == 1) {
              $bol_txt = "Bol Orders are EXCLUDED";
            } else {
              $bol_txt = "All Orders Including Bol";
            }
            ?>
          <div style="margin-bottom:10px; color: #E60D17; font-weight: bold; ">Note: <?php echo $bol_txt; ?> </div>

          <div>
            <label for="from">From</label> <input type="text" id="from" name="from"> <label for="to">to</label> <input type="text" id="to" name="to">
            
          </div>

          <div style="margin-top: 5px;">
            <button type="button" class="btn btn-primary getroasbtn" style="font-size: 12px;">Get Roas</button>
            &nbsp;<span id="rsp" style="display: none;">ROAS is calculating on the fly, so it will take some time please be patient...</span>
          </div> 

          <div style="margin-top: 5px; color: #c10000;" id="roas_fetch_errror"></div>          
        </div>
      </div>
    </div>


    <div class="col-lg-3">
      <div class="card shadow mb-4">
        <div class="card-header py-1">
          <h6 class="m-0 font-weight-bold text-primary">
            Averages <i style="float: right; cursor:pointer; font-size: 18px;" class="fa fa-question-circle avghelp text-primary" aria-hidden="true"></i>
          </h6>
        </div>
        <div class="card-body">
           
          <label class="avg_roas marq_class_item text-primary"></label>&nbsp|&nbsp<label class="avg_end_roas marq_class_item text-primary"></label>&nbsp|&nbsp
          <label class="avg_roas_with_google_kosten marq_class_item text-primary"></label>&nbsp|&nbsp
          <label class="avg_end_roas_with_google_kosten marq_class_item text-primary"></label>&nbsp|&nbsp
          <label class="avg_roas_google marq_class_item text-primary"></label>
      
        </div>
      </div>
    </div>



    <div class="col-lg-3">
      <div class="card shadow mb-4">
        <div class="card-header py-1">
          <h6 class="m-0 font-weight-bold text-primary">
            Set Roas
          </h6>
        </div>
        <div class="card-body">
          <div style="margin-bottom:10px; color: #E60D17; font-weight: bold; ">New Live Feed Date Set :- <span id="new_live_feed_date_set"></span></div>

          <div>
            <a href="#" class="btn btn-success btn-icon-split" id="setroasdate">
            <span class="icon text-white-50">
              <i class="fa fa-check-circle" style="font-size:16px;color:green"></i>
            </span>
            <span class="text">Set Roas Date</span>
          </a>
          </div>

        </div>
      </div>
    </div>


    <div class="col-lg-2">
      <div class="card shadow mb-4">
        <div class="card-header py-1">
          <h6 class="m-0 font-weight-bold text-primary">
            Export Roas
          </h6>
        </div>
        <div class="card-body">
          <a href="#" class="btn btn-primary btn-icon-split" id="btnexport_roas" style="position: relative;">
            <span class="icon text-white-50">
              <i class="fas fa-file-export" style="font-size:12px;"></i>
            </span>
            <span id="loading-img-export" style="display:none; position: absolute;"></span>
            <span class="text">Export Roas</span>
          </a>
        </div>
      </div>
    </div>

</div> -->