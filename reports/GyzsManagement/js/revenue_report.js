$(document).ready(function() {

  if($.cookie('is_filtered') == '1') {
      //open filter
      if ($('#filter-content').attr('class', 'filter-wrapper') && $('#cog-content').attr('class', 'cog')) {
        $('#filter-content').attr('class', 'filter-toggle');
        $('#cog-content').attr('class', 'cog-toggle');
      }
      $.cookie('from_date', $('#from').val());
      $.cookie('to_date', $('#to').val());
      getDataByDate();
      $.cookie('is_filtered','0');
  }
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

          if(that[0][0] != column_index_revenue_report["brand"] && that[0][0] != column_index_revenue_report["supplier_type"] && that[0][0] != column_index_revenue_report["carrier_level"]) {
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
          } else if(that[0][0] == column_index_revenue_report["carrier_level"]) {
            var select = $('<select id="carrier_level" class="search_carrier_level" style="margin-top:-30px; margin-left:-23px; position:absolute;"><option value="">All</option><option value="Transmission">Transmission</option><option value="Briefpost">Briefpost</option><option value="Pakketpost">Pakketpost</option></select>')
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
                .on('change', function () {
                    /*  
                      column
                      .search( this.value )
                      .draw(); */
                      $("#hdn_selectedbrand").val(this.value);
                      if( !this.value ) {
                        $('.getrevenuedatabtn').click();
                      } else {
                        lockScreen();
                        var selectedFilters = new Array();
                        selectedFilters.push(this.value);
                        var filter_type = 'get_revenue_data_by_brand';
                        var cats = $("#hdn_selectedcategories").val();
                        if (cats !== '') {
                          selectedFilters.push($("#hdn_selectedcategories").val());
                          selectedFilters.push(this.value);
                          filter_type = 'get_revenue_data_by_both';
                        }
                        uncheckboxCategories();
                        categoriesOfProducts.length = 0;
                        getRevenueDataByFilter(filter_type, selectedFilters);
                    }
                });
          }

        }); 
      },

      "columnDefs": [
            {
              "targets": [column_index_revenue_report["sku"]],
              "render": function ( data, type, row ) {  
                return '<a id="revenue_debug.php?psku='+row[column_index_revenue_report["sku"]]+'&d='+row[column_index_revenue_report["reportdate"]].replace(/ /g,'')+'" title="Debug & Repair" class="revenue_debug_link" href="#">'+data+'</a>';  
              },
              
            },
            {
              "targets": [column_index_revenue_report["name"]],
              "render": function ( data, type, row ) {
                  var cats = all_product_categories[row[column_index_revenue_report["product_id"]]];
                  return '<a style="cursor:pointer;" title="'+data+'\n'+cats+'">'+data+'</a>';
              },
              "className": "column_cat_filter"
            }
      ],

      "drawCallback": function( settings ) {
         var selected_brand = $('#hdn_selectedbrand').val();
          $.ajax({
           url: document_root_url+'/scripts/revenue_process_data.php',
           "type": "POST",
           data: ({ selected_brand: selected_brand, type: 'get_revenue_brand'}),
           success: function(response_data) {
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
              if($.isEmptyObject(categoriesOfProducts)) {
                checkboxCategories(resp_obj["msg"]["categories"]);
                unlockScreen();
                categoriesOfProducts = resp_obj["msg"]["categories"];
              }
           }
          });//end ajax
      },

      "ajax": {
        "url": document_root_url+"/scripts/create_revenue_report_query.php",
        "type": "POST",
        "data": function ( d ) {
            d.categories = $('#hdn_selectedcategories').val();
        }
      },
      "language": {
        "emptyTable": "No Record found."
      }
  });

  var tree = simTree({
    el: '#tree',
    data: list,
    check: true,
    linkParent: true,
    expand: 'expand',
    onClick: function (item) {
    },
    onChange: function (item) {
      lockScreen();
      var selectedCategories = new Array();
      var selectedFilters = new Array();
      $.each(item, function (key, value) {
        selectedCategories.push(value["id"]);
      });
      $("#hdn_selectedcategories").val(selectedCategories);
      if (selectedCategories.length !== 0) {
        // it will check if any brand is selected
        selectedFilters.push(selectedCategories.toString());
        var filter_type = 'get_revenue_data_by_category';
        if ($('#brand').val() != '') {
          selectedFilters.length = 0;
          selectedFilters.push($('#brand').val());
          selectedFilters.push(selectedCategories.toString());
          filter_type = 'get_revenue_data_by_both';
        }
        getRevenueDataByFilter(filter_type, selectedFilters);
      } else {
        if ($('#brand').val() == '') {
          $('.getrevenuedatabtn').click();
        }
      }
      unlockScreen();
    },
    done: function () {
      checkboxCategories(categoriesOfProducts);
    }

  });


  $(".getrevenuedatabtn").click(function() {
      lockScreen();
      $(this).attr("disabled", true);
      $("#hdn_selectedbrand").val('');
      uncheckboxCategories();
      categoriesOfProducts.length=0;
      getDataByDate();
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

  function getRevenueDataByFilter(process_type_name, selected_filter_arr = new Array()) {

    $.cookie('is_filtered', '1');
    var from = $("#from").val();
    var to = $("#to").val();

    if (from == "" || to == "") {
      alert("Please select from and to date");
      return false;
    }

    $("#rsp").show();

    $.ajax({
      url: document_root_url + '/scripts/revenue_process_data.php',
      method: "POST",
      data: ({ from: from, to: to, data: selected_filter_arr, type: 'get_revenue_data_by_filter', filter_name: process_type_name }),
      success: function (response_data) {
        var resp_obj = jQuery.parseJSON(response_data);

        if (resp_obj["msg"]["err"] == "error") {
          $("#get_data_fetch_errror").html("No Data Found.");
          $("#btnsync").hide();
        } else {
          table.draw();

          $("#sel_date").html(resp_obj["msg"]["date_selected"]);
          $("#tot_revenue").html(resp_obj["msg"]["total_revenue"]);
          $("#tot_bp").html(resp_obj["msg"]["total_bp"]);

          $("#tot_abs_margin").html(resp_obj["msg"]["tot_abs_margin"]);
          $("#tot_refund").html(resp_obj["msg"]["tot_refund_amount"]);

          if (resp_obj["msg"]["tot_pm_sp"] == "nan") {
            $("#tot_pm_sp").html("0,00");
          } else {
            $("#tot_pm_sp").html(resp_obj["msg"]["tot_pm_sp"]);
          }

          $("#get_data_fetch_errror").html("");
          $("#rsp").hide();
          $("#btnsync").hide();
          // $('.prev_data').hide();
        }

      }
    });
  };

  function getDataByDate() {
    var from = $("#from").val();
    var to = $("#to").val();

    if ((from == "" || to == "") && $.cookie('is_filtered') == 1) {
      from = $.cookie('from_date');
      to = $.cookie('to_date');
    }

    if (from == "" || to == "") {
      alert("Please select from and to date");
      return false;
    }

    $(this).attr("disabled", true);
    $("#rsp").show();

    $.ajax({
      url: document_root_url + '/scripts/revenue_process_data.php',
      method: "POST",
      data: ({ from: from, to: to, type: 'get_revenue_data' }),
      success: function (response_data) {
        var resp_obj = jQuery.parseJSON(response_data);

        if (resp_obj["msg"]["err"] == "error") {
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
  }//end getDataByDate()
    
  function checkGiven($li, status, flag) {
    var data = $li.data();
    if (typeof status === 'undefined') {
      status = !data.checked;
    }
    var $a = $li.children('a');
    var $check = $a.children('.sim-tree-checkbox');
    if (status === true) {
      $check.removeClass('sim-tree-semi').addClass('checked');
    } else if (status === false) {
      $check.removeClass('checked sim-tree-semi');
    } else if (status === 'semi') {
      $check.removeClass('checked').addClass('sim-tree-semi');
    }
    $li.data('checked', status);
    setParentCheck($li);
  }

  function setParentCheck(e) {
    var t,
      i = e.parent("ul"),
      s = i.parent("li"),
      n = i.children("li"),
      a = s.find(">a .sim-tree-checkbox"),
      r = [],
      d = n.length;
    s.length &&
      (e.find(">a .sim-tree-checkbox").hasClass("sim-tree-semi")
        ? doCheck(a, "semi")
        : ($.each(n, function () {
          !0 === $(this).data("checked") && r.push($(this));
        }),
          (t = r.length),
          d === t && doCheck(a, !0),
          t || doCheck(a, !1),
          t >= 1 && t < d && doCheck(a, "semi")));
  }

  function doCheck(e, t, i) {
    var s = e.closest("li"),
      n = s.data();
    void 0 === t && (t = !n.checked),
      !0 === t ? e.removeClass("sim-tree-semi").addClass("checked") : !1 === t ? e.removeClass("checked sim-tree-semi") : "semi" === t && e.removeClass("checked").addClass("sim-tree-semi"),
      s.data("checked", t),
      !0 ===/*  this.options.linkParent && */ !i && setParentCheck(s);
  }

  function toggleAllCategories(status) {
    var any_disabled = false;
    $('a>i.sim-tree-checkbox').each(function (index) {
      if ($(this).parent('a').parent('li').hasClass('disabled')) {
        any_disabled = true;
      }
      if (any_disabled)
        return false;
    });

    if (!any_disabled) {
      if (status) {
        checkIt(true, true);
      } else {
        checkIt(false, false);
      }
    }
    return true;
  }

  function checkIt(status, flag) {
    $("i.sim-tree-checkbox").each(function () {
      var $check = $(this);
      var $li = $check.closest('li');
      var $childUl, $childUlCheck;
      var data = $li.data();
      if (typeof status === 'undefined') {
        status = !data.checked;
      }

      if (status === true) {
        $('#flexCheckDefault').prop('checked', true);
        $check.removeClass('sim-tree-semi').addClass('checked');
      } else if (status === false) {
        $('#flexCheckDefault').prop('checked', false);
        $check.removeClass('checked sim-tree-semi');
      } else if (status === 'semi') {
        $check.removeClass('checked').addClass('sim-tree-semi');
      }
      $li.data('checked', status);
    });
  }

  function toggleCheckbox(new_status) {
    $('a>i.sim-tree-checkbox').each(function (index) {
      $(this).parent('a').parent('li').removeClass('disabled');
      if (!$(this).hasClass('checked')) {
        if (new_status == 'none') {
          $(this).parent('a').parent('li').addClass('disabled');
        } else {
          $(this).parent('a').parent('li').removeClass('disabled');
        }
      }
    });
  }


  $("#flexCheckDefault").change(function () {
    var current_status = $(this).prop('checked');
    var selectedFilters = new Array();
    if (categoriesOfProducts != '') {//means this is a group list
      if (current_status) { // check all hiddencategories
        lockScreen();
        var filter_type = 'get_revenue_data_by_category';
        selectedFilters.push(categoriesOfProducts.toString());
        if ($('#brand').val() != '') {
          selectedFilters.length = 0;
          selectedFilters.push($('#brand').val());
          selectedFilters.push(categoriesOfProducts.toString());
          filter_type = 'get_revenue_data_by_both';
        }
        checkboxCategories(categoriesOfProducts);
        getRevenueDataByFilter(filter_type, selectedFilters);
        unlockScreen();
      } else { //uncheck all hiddencategories
        if($('#brand').val() == '') {
          lockScreen();
          uncheckboxCategories();
          selectedFilters.push('-1');
          getRevenueDataByFilter('get_revenue_data_by_category', selectedFilters);
          unlockScreen();
        }
      }
    }
  });

  function checkboxCategories(categoriesOfProducts) {
    $.each(categoriesOfProducts, function (key, val) {
      $("#flexCheckDefault").prop('checked', true);
      if (val != 2) {
        var $li = $('li[data-id=' + val + ']');
        checkGiven($li, true, true);
        toggleCheckbox('none');

        $("[data-id=" + val + "]").children('i').first().css({ "top": "7px" });
        $("[data-id=" + val + "]").children('a').first().css({ "background-color": "#a2a3b7", "border-radius": "3px", "padding": "2px 0px 2px 16px" });
      }
    });
  }//end checkboxCategories()


  function uncheckboxCategories() {
    $.each(categoriesOfProducts, function (key, val) {
      $("#flexCheckDefault").prop('checked', false);
      if (val != 2) {
        var $li = $('li[data-id=' + val + ']');
        checkGiven($li, false, true);
        toggleCheckbox('');

        $("[data-id=" + val + "]").children('i').first().css({ "top": "7px" });
        $("[data-id=" + val + "]").children('a').first().attr('style', '');
      }
    });
  }//end uncheckboxCategories()
});

function lockScreen() {
  $("#showloader").addClass("loader");
  $(".loader_txt").show();
  return true;
}

function unlockScreen() {
  $("#showloader").removeClass("loader");
  $(".loader_txt").hide();
  return true;
}