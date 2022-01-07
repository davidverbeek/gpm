$(document).ready(function() {
  var table = $('#example').DataTable({
      "processing": true,
      "serverSide": true,
      "pageLength": 200,
      "deferRender": true,
      "fixedHeader": true,
      initComplete: function () {
          // Apply the search
          this.api().columns().every( function () {
              var that = this;

               $('.table tfoot tr').insertAfter($('.table thead tr'));
                  // Pagination added after Table
                  $(".dataTables_wrapper div:nth-child(3)").insertAfter($('.datatable'));
                  $("input", this.footer()).on("keyup change clear", function () {
                      if (that.search() !== this.value) {
                          that.search(this.value).draw();
                      }   
                });

                if(that[0][0] == 3) {
                  var select = $('<select id="supplier_type" class="search_supplier" style="margin-top:-30px; margin-left:-51px; position:absolute;"><option value="">All</option><option value="Mavis">Mavis</option><option value="Gyzs">Gyzs</option><option value="Transferro">Transferro</option><option value="GWarehouse">GWarehouse</option></select>')
                      .appendTo( $(that.footer()).empty())
                      .on( 'change', function () {
                            that
                            .search( this.value )
                            .draw();

                            $("#chkall_sup").prop('checked', false);
                            $("#check_all_cnt").html(0);

                      } );
                }


              
          }); 
      },
      "rowCallback": function( row, data ) {
        var ischecked= $("#chkall_sup").is(':checked');
        if(ischecked) {
          $(row).addClass("selected");
        } else {
          $(row).removeClass("selected");
        }
      },

      "createdRow": function (row, data, rowIndex) {
        $.each($('td', row), function (colIndex) {
            // For example, adding data-* attributes to the cell
            if(colIndex == 2) {
              $(this).attr('title', data[2]);
            }
            if(colIndex == 7) {
              $(this).attr('title', data[7]);
            }
            if(colIndex == 8) {
              $(this).attr('title', data[8]);
            }

        });
      },
      "columnDefs": [
        {
          "targets": [0],
          "render": function ( data, type, row ) {
            return data;
          },
          "className": "details-control"
        }
      ],
      "ajax": {
        "url": document_root_url+"/scripts/create_bol_minpricequery.php",
        "type": "POST"
      }
  });


  $('.table tfoot th').each( function () {
    var title = $(this).text();
     $(this).html( '<input type="text" class="txtsearch" placeholder="Search '+title+'" />' );
   });

  // Add event listener for opening and closing details
  $('#example tbody').on('click', 'td.details-control', function () {
    var tr = $(this).closest('tr');
    var row = table.row( tr );

    if ( row.child.isShown() ) {
        // This row is already open - close it
        row.child.hide();
        tr.removeClass('shown');
    }
    else {
        // Open this row
        //console.log(row.data()[5]);
        //row.child( format(row.data()) ).show();
        row.child(row.data()[11]).show();
        tr.addClass('shown');
    }
  });

  function format ( d ) {
    // `d` is the original data object for the row
    return d.product_bol_minimum_price_cal;
  }


  var lastChecked;

  $('#example tbody').on("click","tr", function(event) {
  
  $("#chkall").prop('checked', false);
  $("#check_all_cnt").html(0);

  if(!lastChecked) {
  lastChecked = this;
  }

  if(event.shiftKey) {
    var start = $('#example tbody tr').index(this);
    var end = $('#example tbody tr').index(lastChecked);

   // console.log($(lastChecked).hasClass('selected'));

    
    for(i=Math.min(start,end);i<=Math.max(start,end);i++) {
      if ($(lastChecked).hasClass('selected')){
        $('#example tbody tr').eq(i).addClass("selected");
      } else {
        $('#example tbody tr').eq(i).removeClass("selected");
      }
    }
  } else if ((event.metaKey || event.ctrlKey)){
    $(this).toggleClass('selected');
  } else {
    $(this).toggleClass('selected');
  }

    lastChecked = this;
  });

  $("#select_ec_deliverytime").change(function() {
    
    var record_selected = table.rows('.selected').data().length;
    if(record_selected == 0) {
        alert("Please select record first!!");
        return false;
    }

    if($(this).val() != "") {
      //$("#ec_updated_records").html(record_selected);
      $("#ec_updated_ec_del_time").html($("#select_ec_deliverytime option:selected").text());
      $('#ECdeliverytimeModal').modal('toggle');
    }
  });


  $("#select_ec_deliverytime_be").change(function() {
    
    var record_selected = table.rows('.selected').data().length;
    if(record_selected == 0) {
        alert("Please select record first!!");
        return false;
    }

    if($(this).val() != "") {
      //$("#ec_updated_records").html(record_selected);
      $("#ec_updated_ec_del_time_be").html($("#select_ec_deliverytime_be option:selected").text());
      $('#ECdeliverytimeBEModal').modal('toggle');
    }
  });

  $('#confirmedUpdateecdeliverytime').click(function() {

   $("#ECdeliverytimeModal").css("opacity",0.8);
   $("#loading-img-ec").css({"display": "block"});
   $(this).attr('disabled','disabled');
   

    var all_selected_records = Array();

    var record_selected = table.rows('.selected').data().length;
    $.each( table.rows('.selected').data(), function( key, value ) {
       all_selected_records[key] = {
            "product_id": value[0],
            "sku": value[1]
        };
    });

    var ec_delivery_time = $("#select_ec_deliverytime").val();
    var selected_supplier = $("#supplier_type").val();

    var isAllChecked = 0;
    if($("#chkall_sup").is(':checked')) {
      var isAllChecked = 1;
    }


   $.ajax({
     url: document_root_url+'/scripts/process_data_bol_minimum.php',
     method:"POST",
     data: ({ all_selected_records: all_selected_records, ec_delivery_time: ec_delivery_time, type: 'confirm_ecdtime_update', isAllChecked: isAllChecked, selected_supplier: selected_supplier}),
     success: function(response_data){
        var resp_obj = jQuery.parseJSON(response_data);
        if(resp_obj["msg"]) {
          if(resp_obj["msg"]) {
            $("#ECdeliverytimeModal").css("opacity",1);
            $("#loading-img-ec").css({"display": "none"});
            $('#confirmedUpdateecdeliverytime').attr('disabled',false);
            $('#ECdeliverytimeModal').modal('toggle');

            $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");

                      window.setTimeout(function() {
                        $(".alert").fadeTo(500, 0).slideUp(500, function(){
                            $(this).remove(); 
                        });
                      }, 4000);

                      $("#chkall_sup").prop('checked', false);
                      $("#check_all_cnt").html(0);
                      table.ajax.reload( null, false );

            
          } 
        }      
      }
    });
 });


 $('#confirmedUpdateecdeliverytimebe').click(function() {

   $("#ECdeliverytimeBEModal").css("opacity",0.8);
   $("#loading-img-ec-be").css({"display": "block"});
   $(this).attr('disabled','disabled');
   

    var all_selected_records = Array();

    var record_selected = table.rows('.selected').data().length;
    $.each( table.rows('.selected').data(), function( key, value ) {
       all_selected_records[key] = {
            "product_id": value[0],
            "sku": value[1]
        };
    });

    var ec_delivery_time_be = $("#select_ec_deliverytime_be").val();
    var selected_supplier = $("#supplier_type").val();

    var isAllChecked = 0;
    if($("#chkall_sup").is(':checked')) {
      var isAllChecked = 1;
    }


   $.ajax({
     url: document_root_url+'/scripts/process_data_bol_minimum.php',
     method:"POST",
     data: ({ all_selected_records: all_selected_records, ec_delivery_time_be: ec_delivery_time_be, type: 'confirm_ecdtime_be_update', isAllChecked: isAllChecked, selected_supplier: selected_supplier}),
     success: function(response_data){
        var resp_obj = jQuery.parseJSON(response_data);
        if(resp_obj["msg"]) {
          if(resp_obj["msg"]) {
            $("#ECdeliverytimeBEModal").css("opacity",1);
            $("#loading-img-ec-be").css({"display": "none"});
            $('#confirmedUpdateecdeliverytimebe').attr('disabled',false);
            $('#ECdeliverytimeBEModal').modal('toggle');

            $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");

                      window.setTimeout(function() {
                        $(".alert").fadeTo(500, 0).slideUp(500, function(){
                            $(this).remove(); 
                        });
                      }, 4000);

                      $("#chkall_sup").prop('checked', false);
                      $("#check_all_cnt").html(0);
                      table.ajax.reload( null, false );

            
          } 
        }      
      }
    });
 }); 


 $("#chkall_sup").change(function() {
    var ischecked= $(this).is(':checked');
    var selected_supplier = $("#supplier_type").val();
    if(ischecked) {
      $.ajax({
         url: document_root_url+'/scripts/process_data_bol_minimum.php',
         method:"POST",
         data: ({ 
                  type: 'check_all_func_ec',
                  selected_supplier: selected_supplier
               }),
         success: function(response_data){
            var resp_obj = jQuery.parseJSON(response_data);
            if(resp_obj["msg"]) {
                table.ajax.reload( null, false );
                $("#check_all_cnt").html(resp_obj["msg"]);
            }      
         }
      });
    } else {
      table.ajax.reload( null, false );
      $("#check_all_cnt").html(0);
    }

  });
  

  $('#main-content').on('click', function () {
                if ($('#filter-content').attr('class', 'filter-toggle') && $('#cog-content').attr('class', 'cog-toggle')) {
                    $('#filter-content').attr('class', 'filter-wrapper');
                    $('#cog-content').attr('class', 'cog');
                }
            });



});

  
