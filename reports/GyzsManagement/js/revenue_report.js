$(document).ready(function() {
  var table = $('#example').DataTable({
      "processing": true,
      "serverSide": true,
      "pageLength": 200,
      "deferRender": true,
      "fixedHeader": true,
      "order": [[ column_index_revenue_report["vericale_som_percentage"], 'asc' ]],
      initComplete: function () {
        // Apply the search
        $(".table .tfootsum").insertAfter($('.table tbody'));


        this.api().columns().every( function () {
          var that = this;
          $('.table .tfoothead tr').insertAfter($('.table thead tr'));
          // Pagination added after Table
          $(".dataTables_wrapper div:nth-child(3)").insertAfter($('.datatable'));

          if(that[0][0] != column_index_revenue_report["brand"] && that[0][0] != column_index_revenue_report["supplier_type"]) {
            $( 'input', this.footer() ).on( 'keyup change clear', function () {
                if ( that.search() !== this.value ) {     
                  that
                      .search( this.value )
                      .draw();
                }
            });
          }  else if(that[0][0] == column_index_revenue_report["supplier_type"]) {
            var select = $('<select id="supplier_type" class="search_supplier" style="margin-top:-30px; margin-left:-23px; position:absolute;"><option value="">All</option><option value="Mavis">Mavis</option><option value="Gyzs">Gyzs</option><option value="Transferro">Transferro</option></select>')
                .appendTo( $(that.footer()).empty())
                .on( 'change', function () {
                      that
                      .search( this.value )
                      .draw();
                } );
          } else {

                var column = this;
                var select = $('<select id="brand" class="search_brand"><option value="">All</option></select>')
                .appendTo( $(column.footer()).empty() )
                .on( 'change', function () {
                      $("#hdn_selectedbrand").val(this.value);
                      column
                      .search( this.value )
                      .draw();
                } );

          }

        }); 
      },

      "columnDefs": [
            {
              "targets": [column_index_revenue_report["sku"]],
              "render": function ( data, type, row ) {  
                return '<a id="revenue_debug.php?psku='+row[column_index_revenue_report["sku"]]+'&d='+row[column_index_revenue_report["reportdate"]].replace(/ /g,'')+'" title="Debug & Repair" class="revenue_debug_link" href="#">'+data+'</a>';  
              },
              
            }
      ],

      "drawCallback": function( settings ) {
         var selected_brand = $('#hdn_selectedbrand').val();


          $.ajax({
           url: document_root_url+'/scripts/revenue_process_data.php',
           "type": "POST",
           data: ({ selected_brand: selected_brand, type: 'get_revenue_brand'}),
           success: function(response_data){
              var resp_obj = jQuery.parseJSON(response_data);
             
               if(resp_obj["msg"]["brands"]) {
                $('#brand').empty().append($('<option>').val("").text("All"));
                //$('#brand').empty();
                var selected_opt = $("#hdn_selectedbrand").val();
                $.each( resp_obj["msg"]["brands"], function( key, value ) {
                    var selected_str = "";
                    if(selected_opt == value) {
                      selected_str = "selected";
                    }

                    $('#brand').append($('<option '+selected_str+'>').val(value).text(value));
                });
              } 
           }
          });
    


      },

      "ajax": {
        "url": document_root_url+"/scripts/create_revenue_report_query.php",
        "type": "POST",
        "data": function ( d ) {
            d.categories = $('#hdn_selectedcategories').val()
        }
      }
  });



  var tree = simTree({
  el: '#tree',
  data: list,
  check: true,
  linkParent: true,
  expand:'expand',
  onClick: function (item) {  
  },
  onChange: function (item) {

    var selectedCategories = new Array();
    $.each( item, function( key, value ) {
      selectedCategories.push(value["id"]);
    });
    $("#hdn_selectedcategories").val(selectedCategories);
    table.draw(); 
  }
  });


  $(".getrevenuedatabtn").click(function() {

    var from = $("#from").val();
    var to = $("#to").val();
    
    if(from == "" || to == "") {
      alert("Please select from and to date");
      return false;
    }

    $(this).attr("disabled", true);
    $("#rsp").show();

    $.ajax({
       url: document_root_url+'/scripts/revenue_process_data.php',
       method:"POST",
       data: ({ from: from,  to: to, type: 'get_revenue_data'}),
       success: function(response_data){
          var resp_obj = jQuery.parseJSON(response_data);
          
          if(resp_obj["msg"]["err"] == "error") {
            $("#get_data_fetch_errror").html("Data insertion error.");
            $(".getrevenuedatabtn").attr("disabled", false);
          } else {
            table.draw();

            if ($('#filter-content').attr('class', 'filter-toggle') && $('#cog-content').attr('class', 'cog-toggle')) {
              $('#filter-content').attr('class', 'filter-wrapper');
              $('#cog-content').attr('class', 'cog');
            }
            
            $("#sel_date").html(resp_obj["msg"]["date_selected"]);
            $("#tot_revenue").html(resp_obj["msg"]["total_revenue"]);
            $("#tot_bp").html(resp_obj["msg"]["total_bp"]);

            $("#tot_abs_margin").html(resp_obj["msg"]["tot_abs_margin"]);
            $("#tot_refund").html(resp_obj["msg"]["tot_refund_amount"]);

            $("#tot_pm_sp").html(resp_obj["msg"]["tot_pm_sp"]);

            $("#get_data_fetch_errror").html("");
            $("#rsp").hide();
            $(".getrevenuedatabtn").attr("disabled", false);
           // $('.prev_data').hide();
          }

       }
    });
 });

  $("#btnsync").click(function() { 
    var from = $("#from").val();
    var to = $("#to").val();
    
    if(from == "" || to == "") {
      alert("Please select from and to date");
      return false;
    } 

    if(confirm("Are you sure you want to apply sort order from "+from+" to "+to+" date?")) {
      $("#magsyncstatus").show();
      $("#magsyncstatus").html("Updating sort order from "+from+" to "+to+" date. Please be patient <img src='images/loader.gif' title='Please wait' />");
      $(this).attr("disabled", true);

      $.ajax({
       url: document_root_url+'/scripts/revenue_process_data.php',
       method:"POST",
       data: ({ type: 'set_magento_sort_order'}),
       success: function(response_data){
        var resp_obj = jQuery.parseJSON(response_data);
        if(resp_obj["msg"] == "success") {
          $("#magsyncstatus").html("Successfully updated the sort order");
          $("#btnsync").attr("disabled", false);
        } else {
          $("#magsyncstatus").html("Something went wrong please try again. Error:-"+resp_obj["msg"]+"");
          $("#btnsync").attr("disabled", false);
        }
       }
      });
    }
  });





$('.table .tfoothead th').each( function () {
    var title = $(this).text();
    $(this).html( '<input type="text" class="txtsearch" placeholder="'+title+'" />' );
 });

 $('.table .tfoothead tr').insertAfter($('.table thead tr'));

  $('#main-content').on('click', function () {
    if ($('#filter-content').attr('class', 'filter-toggle') && $('#cog-content').attr('class', 'cog-toggle')) {
      $('#filter-content').attr('class', 'filter-wrapper');
      $('#cog-content').attr('class', 'cog');
    }
  });

  setTimeout(function(){ 
  var simtmp = 0;
  $("ul.sim-tree ul").each(function () {
    if(simtmp < 2) {
      $(this).addClass("show");
    }
    simtmp++;
  });

  var simtreehideicontmp = 0;
  $(".sim-tree-spread").each(function () {
    if(simtreehideicontmp < 2) {
      $(this).hide();
    }
    simtreehideicontmp++;
  });

  var simtreehidetexttmp = 0;
  $("a .sim-tree-checkbox").each(function () {
    if(simtreehidetexttmp < 2) {
      $(this).parent().html("");
      $(this).hide();
    }
    simtreehidetexttmp++;
  });     

  }, 3000);

  $(document).on("click", ".revenue_debug_link" , function() { 
    var link = $(this).attr("id");
    $('.revenue-debug-container').load(link,function(result){
      $('#rDebugModal').modal('toggle');
    });
  });

  /* File Exports Starts */
      var btnExportFunc = function() {

        $.ajax({
        url: document_root_url+"/import_export/revenue_export.php",
        beforeSend:function(){
          $("#btnexport").css("opacity",0.5);
          $("#loading-img-export").css({"display": "inline-block"});   
          $("#btnexport").off( 'click' ); 
        },
        success:function(data) {
          window.location.href = document_root_url+"/import_export/download_revenue_exported.php";
          $("#btnexport").css("opacity",1);
          $("#loading-img-export").css({"display": "none"});
          $('#btnexport').on('click', btnExportFunc);
        }
      });
    }
    $("#btnexport").on('click', btnExportFunc); 
    /* File Exports Ends */

});