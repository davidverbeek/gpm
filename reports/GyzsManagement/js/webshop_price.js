  
$(document).ready(function() {

  $("#p_s_p").click(function() {
    if($(this).html() == "+") {
      $(this).html("-");
      $(this).removeClass("p_s_p_pos");
      $(this).addClass("p_s_p_neg");
    } else {
      $(this).html("+");
      $(this).removeClass("p_s_p_neg");
      $(this).addClass("p_s_p_pos");
    }
  });

  var table = $('#example').DataTable({
      "processing": true,
      "serverSide": true,
      "pageLength": 200,
      "deferRender": true,
      "fixedHeader": true,
      "drawCallback": function( settings ) {
        
         var selected_cats = $('#hdn_selectedcategories').val();
          
         if(selected_cats) {
            $.ajax({
             url: document_root_url+'/scripts/process_data_price_management.php',
             "type": "POST",
             data: ({ selected_cats: selected_cats, type: 'category_brands'}),
             success: function(response_data){
                var resp_obj = jQuery.parseJSON(response_data);
                if(resp_obj["msg"]) {

                  $('#brand').empty().append($('<option>').val("").text("All"));
                  //$('#brand').empty();
                  var selected_opt = $("#hdn_selectedbrand").val();
                  $.each( resp_obj["msg"], function( key, value ) {
                      var selected_str = "";
                      if(selected_opt == value) {
                        selected_str = "selected";
                      }

                      $('#brand').append($('<option '+selected_str+'>').val(value).text(value));
                  });
                }      
             }
          });
        }
        
      },
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

              if(that[0][0] != 4) {
                $( 'input', this.footer() ).on( 'keyup change clear', function () {
                    if ( that.search() !== this.value ) {
                        that.search( this.value ).draw();
                    }
                } );
              } else {

                  var column = this;
                  var select = $('<select id="brand" class="search_brand" style="margin-top:-30px; margin-left:-23px; position:absolute;"><option value="">All</option></select>')
                      .appendTo( $(column.footer()).empty() )
                      .on( 'change', function () {
                            $("#hdn_selectedbrand").val(this.value);
                            column
                            .search( this.value )
                            .draw();
                      } );
                  }
          } ); 
      },
      "ajax": {
        "url": document_root_url+"/scripts/create_webshop_query.php",
        "type": "POST",
        "data": function ( d ) {
            d.categories = $('#hdn_selectedcategories').val(),
            d.hdn_filters = $('#hdn_filters').val()
            
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
  if(title != "Brand") {
   $(this).html( '<input type="text" class="txtsearch" placeholder="Search '+title+'" />' );
  }
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
  
  $("#filter_with").change(function() {
    $("#hdn_filters").val($(this).val());
    table.draw(); 
  });


/* File Exports Starts */
var btnExportFunc = function() {

  $.ajax({
  url: document_root_url+"/import_export/export_webshop_prices.php",
  beforeSend:function(){
    $("#btnexport").css("opacity",0.5);
    $("#loading-img-export").css({"display": "inline-block"});   
    $("#btnexport").off( 'click' ); 
  },
  success:function(data) {
    window.location.href = document_root_url+"/import_export/download_exported_webshop_prices.php";
    $("#btnexport").css("opacity",1);
    $("#loading-img-export").css({"display": "none"});
    $('#btnexport').on('click', btnExportFunc);
  }
});
}
$("#btnexport").on('click', btnExportFunc); 
/* File Exports Ends */



});