<div class="row">
    <div class="col-lg-6">
      <div class="card shadow mb-4">
        <div class="card-header py-1">
          <h6 class="m-0 font-weight-bold text-primary">
            Get Data
          </h6>
        </div>
        <div class="card-body">

          <div>
            <label for="from">From</label> <input type="text" id="from" name="from"> <label for="to">to</label> <input type="text" id="to" name="to">
          </div>

          <div style="margin-top: 5px;">
            <button type="button" class="btn btn-primary getrevenuedatabtn" style="font-size: 12px;">Get Data</button>
            &nbsp;<span id="rsp" style="display: none;">Getting data on the fly, so it will take some time please be patient...</span>
          </div> 

          <div style="margin-top: 5px; color: #c10000;" id="get_data_fetch_errror"></div>          
        </div>
      </div>
    </div>


    

    <div class="col-lg-6">
      <div class="card shadow mb-4">
        <div class="card-header py-1">
          <h6 class="m-0 font-weight-bold text-primary">
            Export Data
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

</div>