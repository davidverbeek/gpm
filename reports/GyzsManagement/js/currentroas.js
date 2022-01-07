  
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
          $(".dataTables_wrapper div:nth-child(3)").insertAfter($('.datatable'));

          if(that[0][0] != column_index_roas_current_log["brand"]) {
            $( 'input', this.footer() ).on( 'keyup change clear', function () {
                if ( that.search() !== this.value ) {     
                  that
                      .search( this.value )
                      .draw();
                }
            });
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

       "rowCallback": function( row, data ) {
        if(data[column_index_roas_current_log["performance"]] == "Over Performance") {
          $(row).addClass("overperforming");
        } else if(data[column_index_roas_current_log["performance"]] == "Under Performance") {
          $(row).addClass("underperforming"); 
        }
       },

      "drawCallback": function( settings ) {
        
         var selected_cats = $('#hdn_selectedcategories').val();
         var selected_brand = $('#hdn_selectedbrand').val();


          $.ajax({
           url: document_root_url+'/scripts/process_data.php',
           "type": "POST",
           data: ({ selected_cats: selected_cats, selected_brand: selected_brand, from: 'currentroas', type: 'average_roas'}),
           success: function(response_data){
              var resp_obj = jQuery.parseJSON(response_data);
             
                if(resp_obj["msg"]["avg_all_roas"]) {
                  $(".avg_roas").html(resp_obj["msg"]["avg_all_roas"]);
                  $('#helpavgroasall').html(resp_obj["msg"]["avg_all_roas"]+" ("+resp_obj["msg"]["avg_all_roas_help"]+")");
                }


                if(resp_obj["msg"]["avg_all_end_roas"]) {
                  $(".avg_end_roas").html(resp_obj["msg"]["avg_all_end_roas"]);
                  $('#helpavgendroasall').html(resp_obj["msg"]["avg_all_end_roas"]+" ("+resp_obj["msg"]["avg_all_end_roas_help"]+")");
                }

                if(resp_obj["msg"]["avg_all_roas_with_google_kosten"]) {
                  $(".avg_roas_with_google_kosten").html(resp_obj["msg"]["avg_all_roas_with_google_kosten"]);
                  $("#helpavgroasadwords").html(resp_obj["msg"]["avg_all_roas_with_google_kosten"]+" ("+resp_obj["msg"]["avg_all_roas_with_google_kosten_help"]+")")
                }

                if(resp_obj["msg"]["avg_all_end_roas_with_google_kosten"]) {
                  $(".avg_end_roas_with_google_kosten").html(resp_obj["msg"]["avg_all_end_roas_with_google_kosten"]);
                  $("#helpavgendroasadwords").html(resp_obj["msg"]["avg_all_end_roas_with_google_kosten"]+" ("+resp_obj["msg"]["avg_all_end_roas_with_google_kosten_help"]+")")
                }
             

                if(resp_obj["msg"]["avg_google_roas"]) {
                  $(".avg_roas_google").html(resp_obj["msg"]["avg_google_roas"]);
                  $("#helpavgroasgoogle").html(resp_obj["msg"]["avg_google_roas"]);
                }

              

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
      "columnDefs": [
            {
              "targets": [column_index_roas_current_log["roas_cal"]],
              "render": function ( data, type, row ) {
               // return '<a href="#roasCalModal" data-remote="roas_cal.php?sku='+row[1]+'&aopm='+row[column_index_roas_current_log["average_order_per_month"]]+'" data-toggle="modal" data-target="#roasCalModal"><button class="btnhistory btn btn-info btn-xs"><i class="fa fa-calculator"></i></button></a>';
               return '<a id="roas_cal.php?sku='+row[1]+'&aopm='+row[column_index_roas_current_log["average_order_per_month"]]+'" class="roas_calc"><button class="btnhistory btn btn-xs" style="background-color:#323585; color:#ffffff;"><i class="fa fa-calculator"></i></button></a>';  
              },
              "searchable": false
            },
          {
              "targets": [column_index_roas_current_log["return_general"]],
              "render": function ( data, type, row ) {
                return '<a href="#" data-toggle="tooltip" data-placement="top" data-html="true" title="'+row[column_index_roas_current_log["return_help"]]+'">'+row[column_index_roas_current_log["return_general"]]+'</a>';               
              }
          },

          {
              "targets": [column_index_roas_current_log["return_bol"]],
              "render": function ( data, type, row ) {
                return '<a href="#" data-toggle="tooltip" data-placement="top" data-html="true" title="'+row[column_index_roas_current_log["return_help"]]+'">'+row[column_index_roas_current_log["return_bol"]]+'</a>';
              }
          },

          {
              "targets": [column_index_roas_current_log["return_nobol"]],
              "render": function ( data, type, row ) {
                return '<a href="#" data-toggle="tooltip" data-placement="top" data-html="true" title="'+row[column_index_roas_current_log["return_help"]]+'">'+row[column_index_roas_current_log["return_nobol"]]+'</a>';
              }
          },
          {
              "targets": [column_index_roas_current_log["return_order_general"]],
              "render": function ( data, type, row ) {
                return '<a href="#" data-toggle="tooltip" data-placement="top" data-html="true" title="'+row[column_index_roas_current_log["return_order_help"]]+'">'+row[column_index_roas_current_log["return_order_general"]]+'</a>';               
              }
          },

          {
              "targets": [column_index_roas_current_log["return_order_bol"]],
              "render": function ( data, type, row ) {
                return '<a href="#" data-toggle="tooltip" data-placement="top" data-html="true" title="'+row[column_index_roas_current_log["return_order_help"]]+'">'+row[column_index_roas_current_log["return_order_bol"]]+'</a>';
              }
          },

          {
              "targets": [column_index_roas_current_log["return_order_nobol"]],
              "render": function ( data, type, row ) {
                return '<a href="#" data-toggle="tooltip" data-placement="top" data-html="true" title="'+row[column_index_roas_current_log["return_order_help"]]+'">'+row[column_index_roas_current_log["return_order_nobol"]]+'</a>';
              }
          },
          {
              "targets": [column_index_roas_current_log["roas_per_cat_per_brand"]],
              "render": function ( data, type, row ) {
                var individual_sku_percentage = settings['roas']['individual_sku_percentage'];
                var category_brand_percentage = settings['roas']['category_brand_percentage'];
                var roas_per_cat_per_brand_help = row[column_index_roas_current_log["roas_per_cat_per_brand_help"]].split("|||");
                var help_text = "("+row[column_index_roas_current_log["roas_target"]]+"*("+individual_sku_percentage+"/100)) + ("+row[column_index_roas_current_log["avg_per_cat_per_brand"]]+"*("+category_brand_percentage+"/100))";
                var final_help_text = "";              

                if(roas_per_cat_per_brand_help[0].length == 0) {
                  final_help_text = help_text;
                } else if(roas_per_cat_per_brand_help[0] == "Debug1") {
                  final_help_text = roas_per_cat_per_brand_help[1]+"\r\n"+help_text;                  
                } else {
                  final_help_text = roas_per_cat_per_brand_help[1];
                }

                
                return '<a href="#" data-toggle="tooltip" data-placement="top" data-html="true" title="'+final_help_text+'">'+row[column_index_roas_current_log["roas_per_cat_per_brand"]]+'</a>';
              }
          }
      ],  
      "ajax": {
        "url": document_root_url+"/scripts/create_currentroasquery.php",
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




$(document).on("click", ".roas_calc" , function() { 
    var link = $(this).attr("id");
    $('.history-container-roas').load(link,function(result){
      $('#roasCalModal').modal('toggle');
    });
});


$('body').on('click', function () {
                if ($(".sim-tree").css('background-color', '')) {
                    $(".sim-tree").css({
                        "background-color": "none",
                        "box-shadow": "none"
                    });
                }
                if ($('.sidebar-wrapper #sim-tree li a span').css('position', 'relative') && $('.sidebar-toggle #sim-tree li a span').css('position', 'absolute')) {
                    $('.sidebar-wrapper #sim-tree li a span').css({
                        "position": "absolute",
                        "opacity": "0",
                        "left": "-100px",
                    });
                    $('.sidebar-toggle #sim-tree li a span').css({
                        "position": "relative",
                        "opacity": "1",
                        "left": "0",
                    })
                }
                if ($('.sim-tree li ul').attr('class', '') &&
                    $('.sim-tree li:first-child > i:first-child:not(i.hidden)').attr('class') == 'sim-tree-spread sim-icon-d') {
                    $('.sim-tree li ul.show').remove();
                    $('.sim-tree li:first-child > i:first-child:not(i.hidden)').attr('class', 'sim-tree-spread sim-icon-r');
                }
            });
            $('#main-content').on('click', function () {
                if ($('#filter-content').attr('class', 'filter-toggle') && $('#cog-content').attr('class', 'cog-toggle')) {
                    $('#filter-content').attr('class', 'filter-wrapper');
                    $('#cog-content').attr('class', 'cog');
                }
            });


$('.table tfoot th').each( function () {
    var title = $(this).text();
    $(this).html( '<input type="text" class="txtsearch" placeholder="Search '+title+'" />' );
 });

 $('.table tfoot tr').insertAfter($('.table thead tr'));

/* $('#roasCalModal').on('show.bs.modal', function(e) {
    var button = $(e.relatedTarget);
    var modal = $(this);
    $(".modal-body").html("");
    modal.find('.modal-body').load(button.data("remote"));
  }); */

 $(".getroasbtn").click(function() {

    var roasfrom = $("#from").val();
    var roasto = $("#to").val();
    var hdnexlbol = $("#hdnexlbol").val();

    if(roasfrom == "" || roasto == "") {
      alert("Please select from and to date");
      return false;
    }

    //$("#hdn_show_avg_roas").val("1");

    $(this).attr("disabled", true);
    $("#rsp").show();

    $.ajax({
       url: document_root_url+'/scripts/process_data.php',
       method:"POST",
       data: ({ roasfrom: roasfrom,  roasto: roasto, hdnexlbol: hdnexlbol, type: 'get_roas'}),
       success: function(response_data){
          var resp_obj = jQuery.parseJSON(response_data);
          //console.log(resp_obj);
          //return false;

          if(resp_obj["msg"]["err"] == "error") {
            $("#roas_fetch_errror").html("Roas insertion error.");
            $(".getroasbtn").attr("disabled", false);
          } else {
            table.draw();
            $("#roas_fetch_errror").html("");
            //$(".avg_roas").html(resp_obj["msg"]["avg_roas_all"]);
            //$(".avg_roas_with_google_kosten").html(resp_obj["msg"]["roas_avg_with_google_kosten"]);
            
            $("#rsp").hide();
            $(".getroasbtn").attr("disabled", false);
           // $('div.dataTables_wrapper thead,div.dataTables_wrapper tbody, div.dataTables_info, div.dataTables_paginate, div.dataTables_length, div.dataTables_filter').show();
           $('.prev_data').hide();
          }

       }
    });
 });


  $('#setroasdate').click( function () {
    var roasfrom = $("#from").val();
    var roasto = $("#to").val();
    if(roasfrom == "" || roasto == "") {
      return false;
    }
    $('#SetRoasDateModal').modal('toggle');
    $('#roas_n_date').html("<b>"+roasfrom+" To "+roasto+"</b>");
 });

 $("#confirmedNewRoasDate").click(function() {
    var roasfrom = $("#from").val();
    var roasto = $("#to").val();

    $.ajax({
       url: document_root_url+'/scripts/process_data.php',
       method:"POST",
       data: ({ roasfrom: roasfrom,  roasto: roasto, type: 'set_roas_date'}),
       success: function(response_data){
          var resp_obj = jQuery.parseJSON(response_data);
          if(resp_obj["msg"] == "success") {
            $("#new_live_feed_date_set").html(resp_obj["f_date"]+' To '+resp_obj["t_date"]);
            $('#SetRoasDateModal').modal('toggle');
          }
       }
    });


 });


  /* File Exports Starts */
      var btnExportRoasFunc = function() {

         var roasfrom = $("#from").val();
         var roasto = $("#to").val();
         var hdnexlbol = $("#hdnexlbol").val();

      $.ajax({
        beforeSend:function(){
          $("#btnexport_roas").css("opacity",0.5);
          $("#loading-img-export").css({"display": "block"});   
          $("#btnexport_roas").off( 'click' ); 
        },
        success:function(data) {
          window.location.href = document_root_url+"/import_export/export_roas.php?type=roascurrent&roasfrom="+roasfrom+"&roasto="+roasto+"&hdnexlbol="+hdnexlbol+"";
          $("#btnexport_roas").css("opacity",1);
          $("#loading-img-export").css({"display": "none"});
          $('#btnexport_roas').on('click', btnExportRoasFunc);
        }
      });
    }
    $("#btnexport_roas").on('click', btnExportRoasFunc); 
    /* File Exports Ends */
 
//$('div.dataTables_wrapper thead,div.dataTables_wrapper tbody, div.dataTables_info, div.dataTables_paginate, div.dataTables_length, div.dataTables_filter').hide();

 $.ajax({
     url: document_root_url+'/scripts/process_data.php',
     method:"POST",
     data: ({ type: 'get_roas_date'}),
     success: function(response_data){
       
        var resp_obj = jQuery.parseJSON(response_data);
        
        var from_date = resp_obj["new_roas_feed_from_date"];
        var to_date = resp_obj["new_roas_feed_to_date"];

        $("#from").val(from_date);
        $("#to").val(to_date);
        //$('.getroasbtn').trigger('click');

        
        var live_feed_from_date = resp_obj["live_roas_feed_from_date"];
        var live_feed_to_date = resp_obj["live_roas_feed_to_date"];


        $("#live_feed_date").html(live_feed_from_date+' To '+live_feed_to_date);
        $("#new_live_feed_date_set").html(from_date+' To '+to_date); 
     }
  });


  $('.filter_per').on('click', function() {
     table
        .columns(column_index_roas_current_log["performance"])
        .search($(this).html())
        .draw();
  }); 

  $('.filter_per_reset').on('click', function() {  
    table
     .search( '' )
     .columns().search( '' )
     .draw();
  });

  $('.avghelp').click( function () {
    $('#AverageRoasModal').modal('toggle');
  });

$('#AverageRoasModal').draggable({
        handle: ".modal-header"
 });
  

});
