  
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

          if(that[0][0] != column_index_roas_feed_log["brand"]) {
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
        if(data[column_index_roas_feed_log["performance"]] == "Over Performance") {
          $(row).addClass("overperforming");
        } else if(data[column_index_roas_feed_log["performance"]] == "Under Performance") {
          $(row).addClass("underperforming"); 
        }
       },

       "drawCallback": function( settings ) {
        
         var selected_cats = $('#hdn_selectedcategories').val();
         var selected_brand = $('#hdn_selectedbrand').val();

          $.ajax({
           url: document_root_url+'/scripts/process_data.php',
           "type": "POST",
           data: ({ selected_cats: selected_cats, selected_brand: selected_brand, from: 'feedroas', type: 'average_roas'}),
           success: function(response_data){
              var resp_obj = jQuery.parseJSON(response_data);
             
                if(resp_obj["msg"]["avg_all_roas"]) {
                  $(".avg_roas").html(resp_obj["msg"]["avg_all_roas"]);
                }


                if(resp_obj["msg"]["avg_all_end_roas"]) {
                  $(".avg_end_roas").html(resp_obj["msg"]["avg_all_end_roas"]);
                }

                if(resp_obj["msg"]["avg_all_roas_with_google_kosten"]) {
                  $(".avg_roas_with_google_kosten").html(resp_obj["msg"]["avg_all_roas_with_google_kosten"]);
                }

                if(resp_obj["msg"]["avg_all_end_roas_with_google_kosten"]) {
                  $(".avg_end_roas_with_google_kosten").html(resp_obj["msg"]["avg_all_end_roas_with_google_kosten"]);
                }
             

                if(resp_obj["msg"]["avg_google_roas"]) {
                  $(".avg_roas_google").html(resp_obj["msg"]["avg_google_roas"]);
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
              "targets": [column_index_roas_feed_log["return_general"]],
              "render": function ( data, type, row ) {
                return '<a href="#" data-toggle="tooltip" data-placement="top" data-html="true" title="'+row[column_index_roas_feed_log["return_help"]]+'">'+row[column_index_roas_feed_log["return_general"]]+'</a>';               
              }
          },

          {
              "targets": [column_index_roas_feed_log["return_bol"]],
              "render": function ( data, type, row ) {
                return '<a href="#" data-toggle="tooltip" data-placement="top" data-html="true" title="'+row[column_index_roas_feed_log["return_help"]]+'">'+row[column_index_roas_feed_log["return_bol"]]+'</a>';
              }
          },

          {
              "targets": [column_index_roas_feed_log["return_nobol"]],
              "render": function ( data, type, row ) {
                return '<a href="#" data-toggle="tooltip" data-placement="top" data-html="true" title="'+row[column_index_roas_feed_log["return_help"]]+'">'+row[column_index_roas_feed_log["return_nobol"]]+'</a>';
              }
          },
          {
              "targets": [column_index_roas_feed_log["return_order_general"]],
              "render": function ( data, type, row ) {
                return '<a href="#" data-toggle="tooltip" data-placement="top" data-html="true" title="'+row[column_index_roas_feed_log["return_order_help"]]+'">'+row[column_index_roas_feed_log["return_order_general"]]+'</a>';               
              }
          },

          {
              "targets": [column_index_roas_feed_log["return_order_bol"]],
              "render": function ( data, type, row ) {
                return '<a href="#" data-toggle="tooltip" data-placement="top" data-html="true" title="'+row[column_index_roas_feed_log["return_order_help"]]+'">'+row[column_index_roas_feed_log["return_order_bol"]]+'</a>';
              }
          },

          {
              "targets": [column_index_roas_feed_log["return_order_nobol"]],
              "render": function ( data, type, row ) {
                return '<a href="#" data-toggle="tooltip" data-placement="top" data-html="true" title="'+row[column_index_roas_feed_log["return_order_help"]]+'">'+row[column_index_roas_feed_log["return_order_nobol"]]+'</a>';
              }
          },
          {
              "targets": [column_index_roas_feed_log["roas_per_cat_per_brand"]],
              "render": function ( data, type, row ) {
                var individual_sku_percentage = settings['roas']['individual_sku_percentage'];
                var category_brand_percentage = settings['roas']['category_brand_percentage'];
                var roas_per_cat_per_brand_help = row[column_index_roas_feed_log["roas_per_cat_per_brand_help"]].split("|||");
                var help_text = "("+row[column_index_roas_feed_log["roas_target"]]+"*("+individual_sku_percentage+"/100)) + ("+row[column_index_roas_feed_log["avg_per_cat_per_brand"]]+"*("+category_brand_percentage+"/100))";
                var final_help_text = "";              

                if(roas_per_cat_per_brand_help[0].length == 0) {
                  final_help_text = help_text;
                } else if(roas_per_cat_per_brand_help[0] == "Debug1") {
                  final_help_text = roas_per_cat_per_brand_help[1]+"\r\n"+help_text;                  
                } else {
                  final_help_text = roas_per_cat_per_brand_help[1];
                }

                
                return '<a href="#" data-toggle="tooltip" data-placement="top" data-html="true" title="'+final_help_text+'">'+row[column_index_roas_feed_log["roas_per_cat_per_brand"]]+'</a>';
              }
          }
      ],  
      "ajax": {
        "url": document_root_url+"/scripts/create_feedroasquery.php",
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
  
  $('.table tfoot th').each( function () {
    var title = $(this).text();
    $(this).html( '<input type="text" class="txtsearch" placeholder="Search '+title+'" />' );
 });

  $('.table tfoot tr').insertAfter($('.table thead tr'));

  $.ajax({
   url: document_root_url+'/scripts/process_data.php',
   method:"POST",
   data: ({ type: 'get_roas_date'}),
   success: function(response_data){
      var resp_obj = jQuery.parseJSON(response_data);
      var live_feed_from_date = resp_obj["live_roas_feed_from_date"];
      var live_feed_to_date = resp_obj["live_roas_feed_to_date"];
      $("#live_feed_date").html(live_feed_from_date+' To '+live_feed_to_date);
      $("#cron_run_date").html(resp_obj["last_cron_run_date"]);
   }
});


  $('.filter_per').on('click', function() {
     table
        .columns(column_index_roas_feed_log["performance"])
        .search($(this).html())
        .draw();
  }); 

  $('.filter_per_reset').on('click', function() {  
    table
     .search( '' )
     .columns().search( '' )
     .draw();
  });


});
