<!-- Modal for roas calculations  -->
<div class="modal fade" id="roasCalModal" tabindex="-1" role="dialog">
  <div class="modal-dialog  modal-dialog-zoom" style="max-width: 1500px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Roas Calculations <span class="clshistorytitle">(Sku - <span id="roas_sku"></span>)</span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="history-container-roas">Remote file content will be loaded here</div>   
      </div>
    </div>
  </div>
</div>
<!-- Modal for roas calculations  -->

<!-- Modal For Set Roas Date -->
  <div class="modal fade" id="SetRoasDateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Ready to GO LIVE with this Roas Date?</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
        <div id="loading-img" style="display:none; position: absolute;"></div>
        By clicking on below "Yes" button Roas will be calculated from <span id="roas_n_date"></span> and will GO LIVE in the NEXT CYCLE.</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
          <a class="btn btn-primary" href="#" id="confirmedNewRoasDate">Yes</a>
        </div>
      </div>
    </div>
  </div>
<!-- Modal For Set Roas Date -->


<!-- Modal for uploading google roas xlsx file -->
  <div class="modal fade" id="SetGoogleRoasFileModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        
         <form id="google_roas_xlsx_form" method="POST" enctype="multipart/form-data" class="form-horizontal">

        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Upload Google Roas</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="font-size: 10px;">
            <div class="alert" id="fileuploaderror" style="display: none;"></div>

            <div class="form-group row" style="margin-bottom:10px;">
                <label class="col-sm-2 col-form-label">From Date</label> 
                <div class="col-sm-10">
                  <input type="text" name="from" class="form-control form-control-user" id="from">
                </div>
            </div>

             <div class="form-group row" style="margin-bottom:10px;">
                <label class="col-sm-2 col-form-label">To Date</label> 
                <div class="col-sm-10">
                  <input type="text" name="to" class="form-control form-control-user" id="to">
                </div>
            </div>

            <div class="form-group row" style="margin-bottom:10px;">
              <label class="col-sm-2 col-form-label">Xlsx File</label> 
              <div class="col-sm-10">
                  
                <div class="mb-3">
                  <input class="form-control form-control-sm" type="file" id="file_google_roas_xlsx" name="file_google_roas_xlsx">
                </div>

              </div>  
            </div>

        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" type="submit">Save</button>
          <input type="hidden" name="type" value="google_roas_xlsx" />
        </div>

      </form>

      </div>
    </div>
  </div>
<!-- Modal for uploading google roas xlsx file -->


<!-- Modal For Explaining Average Roas-->
  <div class="modal fade" id="AverageRoasModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Average Roas</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="font-size: 10px;">
          

          <div>
             <p style="font-size: 13px;">1) <span id="helpavgroasall"></span></p> 
             <p>SUM OF Roas Per Category Per Brand  /  Total Number of Sku's</p>
          </div>

          <div>
             <p style="font-size: 13px;">2) <span id="helpavgendroasall"></span></p> 
             <p>SUM OF End Roas  /  Total Number of Sku's</p>
          </div>

          <div>
             <p style="font-size: 13px;">3) <span id="helpavgroasadwords"></span></p> 
             <p>SUM OF Roas Per Category Per Brand of those Sku's for which Google Kosten is > 0  /  Total Number of Sku's for which Google Kosten is > 0 </p>
          </div>

           <div>
             <p style="font-size: 13px;">4) <span id="helpavgendroasadwords"></span></p> 
             <p>SUM OF End Roas of those Sku's for which Google Kosten is > 0  /  Total Number of Sku's for which Google Kosten is > 0 </p>
          </div>


          <div>
             <p style="font-size: 13px;">5) <span id="helpavgroasgoogle"></span></p> 
             <p>SUM OF Conv.waarde / SUM OF Kosten columns from activated google roas file</p>
          </div>

        </div>
      </div>
    </div>
  </div>
<!-- Modal For Explaining Average Roas -->