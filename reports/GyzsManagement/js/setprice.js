$(document).ready(function () {

  $("#p_s_p").click(function () {
    if ($(this).html() == "+") {

      $(this).html("-");
      $(this).removeClass("p_s_p_pos");
      $(this).addClass("p_s_p_neg");
    } else {
      $(this).html("+");
      $(this).removeClass("p_s_p_neg");
      $(this).addClass("p_s_p_pos");
    }
  });


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
        $("i.sim-tree-checkbox").addClass('checked');
      } else {
        $("i.sim-tree-checkbox").removeClass('checked');
      }
    }
    return true;
  }

  var table = $('#example').DataTable({
      "processing": true,
      "serverSide": true,
      "pageLength": 200,
      "deferRender": true,
      "fixedHeader": true,
      "order": [[ column_index["mag_updated_product_cnt"], 'desc' ]],
      "drawCallback": function( settings ) {
         
         var selected_cats = getTreeCategories();
         //if(selected_cats) {
            $.ajax({
             url: document_root_url+'/scripts/process_data_price_management.php',
             "type": "POST",
             data: ({ selected_cats: selected_cats, type: 'category_brands'}),
             success: function(response_data) {
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
        //}
        enableBulkFunc();
      },
      "columnDefs": [
            {
              "targets": [column_index["name"]],
              "render": function ( data, type, row ) {
                  var cats = all_product_categories[row[column_index["product_id"]]];
                  return '<a style="cursor:pointer;" title="'+data+'\n'+cats+'">'+data+'</a>';
                  //return '<a style="cursor:pointer;" title="'+data+'">'+data+'</a>';
              },
              "className": "column_cat_filter"
            },
            {
              "targets": [column_index["selling_price"]],
              "render": function ( data, type, row ) {
                return '<input type="text" class="selling_price input_validate" default-value="'+data+'" value="'+data+'" id="sp_editable_column_'+row[column_index["product_id"]]+'" />';
              },
              "className": "editable_column sp_editable_column"
            },
            {
              "targets": [column_index["profit_percentage"]],
              "render": function ( data, type, row ) {
                return '<input type="text" class="profit_margin input_validate" default-value="'+data+'" value="'+data+'" id="pm_bp_editable_column_'+row[column_index["product_id"]]+'" />';
              },
              "className": "editable_column pm_bp_editable_column"
            },
            {
              "targets": [column_index["profit_percentage_selling_price"]],
              "render": function ( data, type, row ) {
                return '<input type="text" class="profit_margin_sp input_validate" default-value="'+data+'" value="'+data+'" id="pm_sp_editable_column_'+row[column_index["product_id"]]+'" />';
              },
              "className": "editable_column pm_sp_editable_column"
            },

            /* {
              "targets": [column_index["group_4027100_debter_selling_price"], 
              column_index["group_4027101_debter_selling_price"], 
              column_index["group_4027102_debter_selling_price"], 
              column_index["group_4027103_debter_selling_price"], 
              column_index["group_4027104_debter_selling_price"], 
              column_index["group_4027105_debter_selling_price"], 
              column_index["group_4027106_debter_selling_price"], 
              column_index["group_4027107_debter_selling_price"], 
              column_index["group_4027108_debter_selling_price"], 
              column_index["group_4027109_debter_selling_price"],
               column_index["group_4027110_debter_selling_price"]],
              "render": function ( data, type, row ) {
                return '<input type="text" class="db_sp input_validate" value="'+data+'" />';
              },
              "className": "editable_column db_sp_editable_column"
            }, */

            // Debter Selling Prices
            {
              "targets": [column_index["group_4027100_debter_selling_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('100', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_sp_span striped_span db_sp_span_100" id="db_sp_span_editable_column_100_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_sp input_validate db_sp_100" default-value="'+data+'" value="'+data+'" id="db_sp_editable_column_100_'+product_id+'" />';
                }
              },
              "className": "editable_column db_sp_editable_column db_sp_editable_column_100"
            },
            {
              "targets": [column_index["group_4027101_debter_selling_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('101', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_sp_span striped_span db_sp_span_101" id="db_sp_span_editable_column_101_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_sp input_validate db_sp_101" default-value="'+data+'" value="'+data+'" id="db_sp_editable_column_101_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_sp_editable_column db_sp_editable_column_101"
            },
            {
              "targets": [column_index["group_4027102_debter_selling_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('102', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_sp_span striped_span db_sp_span_102" id="db_sp_span_editable_column_102_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_sp input_validate db_sp_102" default-value="'+data+'" value="'+data+'" id="db_sp_editable_column_102_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_sp_editable_column db_sp_editable_column_102"
            },
            {
              "targets": [column_index["group_4027103_debter_selling_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('103', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_sp_span striped_span db_sp_span_103" id="db_sp_span_editable_column_103_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_sp input_validate db_sp_103" default-value="'+data+'" value="'+data+'" id="db_sp_editable_column_103_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_sp_editable_column db_sp_editable_column_103"
            },
            {
              "targets": [column_index["group_4027104_debter_selling_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('104', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_sp_span striped_span db_sp_span_104" id="db_sp_span_editable_column_104_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_sp input_validate db_sp_104" default-value="'+data+'" value="'+data+'" id="db_sp_editable_column_104_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_sp_editable_column db_sp_editable_column_104"
            },
            {
              "targets": [column_index["group_4027105_debter_selling_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('105', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_sp_span striped_span db_sp_span_105" id="db_sp_span_editable_column_105_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_sp input_validate db_sp_105" default-value="'+data+'" value="'+data+'" id="db_sp_editable_column_105_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_sp_editable_column db_sp_editable_column_105"
            },
            {
              "targets": [column_index["group_4027106_debter_selling_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('106', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_sp_span striped_span db_sp_span_106" id="db_sp_span_editable_column_106_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_sp input_validate db_sp_106" default-value="'+data+'" value="'+data+'" id="db_sp_editable_column_106_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_sp_editable_column db_sp_editable_column_106"
            },
            {
              "targets": [column_index["group_4027107_debter_selling_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('107', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_sp_span striped_span db_sp_span_107" id="db_sp_span_editable_column_107_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_sp input_validate db_sp_107" default-value="'+data+'" value="'+data+'" id="db_sp_editable_column_107_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_sp_editable_column db_sp_editable_column_107"
            },
            {
              "targets": [column_index["group_4027108_debter_selling_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('108', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_sp_span striped_span db_sp_span_108" id="db_sp_span_editable_column_108_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_sp input_validate db_sp_108" default-value="'+data+'" value="'+data+'" id="db_sp_editable_column_108_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_sp_editable_column db_sp_editable_column_108"
            },
            {
              "targets": [column_index["group_4027109_debter_selling_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('109', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_sp_span striped_span db_sp_span_109" id="db_sp_span_editable_column_109_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_sp input_validate db_sp_109" default-value="'+data+'" value="'+data+'" id="db_sp_editable_column_109_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_sp_editable_column db_sp_editable_column_109"
            },
            {
              "targets": [column_index["group_4027110_debter_selling_price"]],
              "render": function ( data, type, row ) {
                  var product_id = row[column_index['product_id']];
                var product_status= generateSpan('110', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_sp_span striped_span db_sp_span_110" id="db_sp_span_editable_column_110_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_sp input_validate db_sp_110" default-value="'+data+'" value="'+data+'" id="db_sp_editable_column_110_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_sp_editable_column db_sp_editable_column_110"
            },             

            // Debeter Selling prices




            /* {
              "targets": [column_index["group_4027100_margin_on_buying_price"], column_index["group_4027101_margin_on_buying_price"], column_index["group_4027102_margin_on_buying_price"],
               column_index["group_4027103_margin_on_buying_price"], 
               column_index["group_4027104_margin_on_buying_price"], 
               column_index["group_4027105_margin_on_buying_price"], 
               column_index["group_4027106_margin_on_buying_price"], 
               column_index["group_4027107_margin_on_buying_price"], 
               column_index["group_4027108_margin_on_buying_price"], 
               column_index["group_4027109_margin_on_buying_price"], 
               column_index["group_4027110_margin_on_buying_price"]],
              "render": function ( data, type, row ) {
                return '<input type="text" class="db_m_bp input_validate" value="'+data+'" />';
              },
              "className": "editable_column db_m_bp_editable_column"
            }, */

            // Debter Margin on BP
            {
              "targets": [column_index["group_4027100_margin_on_buying_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('100', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_bp_span striped_span db_m_bp_span_100" id="db_m_bp_span_editable_column_100_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_bp input_validate db_m_bp_100" default-value="'+data+'" value="'+data+'" id="db_m_bp_editable_column_100_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_bp_editable_column db_m_bp_editable_column_100"
            },
            {
              "targets": [column_index["group_4027101_margin_on_buying_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('101', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_bp_span striped_span db_m_bp_span_101" id="db_m_bp_span_editable_column_101_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_bp input_validate db_m_bp_101" default-value="'+data+'" value="'+data+'" id="db_m_bp_editable_column_101_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_bp_editable_column db_m_bp_editable_column_101"
            },
            {
              "targets": [column_index["group_4027102_margin_on_buying_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('102', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_bp_span striped_span db_m_bp_span_102" id="db_m_bp_span_editable_column_102_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_bp input_validate db_m_bp_102" default-value="'+data+'" value="'+data+'" id="db_m_bp_editable_column_102_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_bp_editable_column db_m_bp_editable_column_102"
            },
            {
              "targets": [column_index["group_4027103_margin_on_buying_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('103', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_bp_span striped_span db_m_bp_span_103" id="db_m_bp_span_editable_column_103_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_bp input_validate db_m_bp_103" default-value="'+data+'" value="'+data+'" id="db_m_bp_editable_column_103_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_bp_editable_column db_m_bp_editable_column_103"
            },
            {
              "targets": [column_index["group_4027104_margin_on_buying_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('104', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_bp_span striped_span db_m_bp_span_104" id="db_m_bp_span_editable_column_104_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_bp input_validate db_m_bp_104" default-value="'+data+'" value="'+data+'" id="db_m_bp_editable_column_104_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_bp_editable_column db_m_bp_editable_column_104"
            },
            {
              "targets": [column_index["group_4027105_margin_on_buying_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('105', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_bp_span striped_span db_m_bp_span_105" id="db_m_bp_span_editable_column_105_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_bp input_validate db_m_bp_105" default-value="'+data+'" value="'+data+'" id="db_m_bp_editable_column_105_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_bp_editable_column db_m_bp_editable_column_105"
            },
            {
              "targets": [column_index["group_4027106_margin_on_buying_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('106', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_bp_span striped_span db_m_bp_span_106" id="db_m_bp_span_editable_column_106_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_bp input_validate db_m_bp_106" default-value="'+data+'" value="'+data+'" id="db_m_bp_editable_column_106_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_bp_editable_column db_m_bp_editable_column_106"
            },
            {
              "targets": [column_index["group_4027107_margin_on_buying_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('107', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_bp_span striped_span db_m_bp_span_107" id="db_m_bp_span_editable_column_107_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_bp input_validate db_m_bp_107" default-value="'+data+'" value="'+data+'" id="db_m_bp_editable_column_107_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_bp_editable_column db_m_bp_editable_column_107"
            },
            {
              "targets": [column_index["group_4027108_margin_on_buying_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('108', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_bp_span striped_span db_m_bp_span_108" id="db_m_bp_span_editable_column_108_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_bp input_validate db_m_bp_108" default-value="'+data+'" value="'+data+'" id="db_m_bp_editable_column_108_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_bp_editable_column db_m_bp_editable_column_108"
            },
            {
              "targets": [column_index["group_4027109_margin_on_buying_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('109', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_bp_span striped_span db_m_bp_span_109" id="db_m_bp_span_editable_column_109_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_bp input_validate db_m_bp_109" default-value="'+data+'" value="'+data+'" id="db_m_bp_editable_column_109_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_bp_editable_column db_m_bp_editable_column_109"
            },
            {
              "targets": [column_index["group_4027110_margin_on_buying_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('110', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_bp_span striped_span db_m_bp_span_110" id="db_m_bp_span_editable_column_110_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_bp input_validate db_m_bp_110" default-value="'+data+'" value="'+data+'" id="db_m_bp_editable_column_110_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_bp_editable_column db_m_bp_editable_column_110"
            },
            // Debter Margin on BP


            /* {
              "targets": [column_index["group_4027100_margin_on_selling_price"], column_index["group_4027101_margin_on_selling_price"], column_index["group_4027102_margin_on_selling_price"], column_index["group_4027103_margin_on_selling_price"], column_index["group_4027104_margin_on_selling_price"], column_index["group_4027105_margin_on_selling_price"], column_index["group_4027106_margin_on_selling_price"], column_index["group_4027107_margin_on_selling_price"], column_index["group_4027108_margin_on_selling_price"], column_index["group_4027109_margin_on_selling_price"], column_index["group_4027110_margin_on_selling_price"]],
              "render": function ( data, type, row ) {
                return '<input type="text" class="db_m_sp input_validate" value="'+data+'" />';
              },
              "className": "editable_column db_m_sp_editable_column"
            }, */

            // Debter Margin on SP
            {
              "targets": [column_index["group_4027100_margin_on_selling_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('100', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_sp_span striped_span db_m_sp_span_100" id="db_m_sp_span_editable_column_100_'+product_id+'" >'+data+'</span>';
                } else {
                return '<input type="text" class="db_m_sp input_validate db_m_sp_100" default-value="'+data+'" value="'+data+'" id="db_m_sp_editable_column_100_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_sp_editable_column db_m_sp_editable_column_100"
            },
            
            {
              "targets": [column_index["group_4027101_margin_on_selling_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('101', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_sp_span striped_span db_m_sp_span_101" id="db_m_sp_span_editable_column_101_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_sp input_validate db_m_sp_101" default-value="'+data+'" value="'+data+'" id="db_m_sp_editable_column_101_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_sp_editable_column db_m_sp_editable_column_101"
            },

            {
              "targets": [column_index["group_4027102_margin_on_selling_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('102', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_sp_span striped_span db_m_sp_span_102" id="db_m_sp_span_editable_column_102_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_sp input_validate db_m_sp_102" default-value="'+data+'" value="'+data+'" id="db_m_sp_editable_column_102_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_sp_editable_column db_m_sp_editable_column_102"
            },
            {
              "targets": [column_index["group_4027103_margin_on_selling_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('103', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_sp_span striped_span db_m_sp_span_103" id="db_m_sp_span_editable_column_103_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_sp input_validate db_m_sp_103" default-value="'+data+'" value="'+data+'" id="db_m_sp_editable_column_103_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_sp_editable_column db_m_sp_editable_column_103"
            },
            {
              "targets": [column_index["group_4027104_margin_on_selling_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('104', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_sp_span striped_span db_m_sp_span_104" id="db_m_sp_span_editable_column_104_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_sp input_validate db_m_sp_104" default-value="'+data+'" value="'+data+'" id="db_m_sp_editable_column_104_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_sp_editable_column db_m_sp_editable_column_104"
            },
            {
              "targets": [column_index["group_4027105_margin_on_selling_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('104', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_sp_span striped_span db_m_sp_span_104" id="db_m_sp_span_editable_column_104_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_sp input_validate db_m_sp_105" default-value="'+data+'" value="'+data+'" id="db_m_sp_editable_column_105_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_sp_editable_column db_m_sp_editable_column_105"
            },
            {
              "targets": [column_index["group_4027106_margin_on_selling_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('106', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_sp_span striped_span db_m_sp_span_106" id="db_m_sp_span_editable_column_106_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_sp input_validate db_m_sp_106" default-value="'+data+'" value="'+data+'" id="db_m_sp_editable_column_106_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_sp_editable_column db_m_sp_editable_column_106"
            },
            {
              "targets": [column_index["group_4027107_margin_on_selling_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('107', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_sp_span striped_span db_m_sp_span_107" id="db_m_sp_span_editable_column_107_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_sp input_validate db_m_sp_107" default-value="'+data+'" value="'+data+'" id="db_m_sp_editable_column_107_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_sp_editable_column db_m_sp_editable_column_107"
            },
            {
              "targets": [column_index["group_4027108_margin_on_selling_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('108', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_sp_span striped_span db_m_sp_span_108" id="db_m_sp_span_editable_column_108_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_sp input_validate db_m_sp_108" default-value="'+data+'" value="'+data+'" id="db_m_sp_editable_column_108_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_sp_editable_column db_m_sp_editable_column_108"
            },
            {
              "targets": [column_index["group_4027109_margin_on_selling_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('109', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_sp_span striped_span db_m_sp_span_109" id="db_m_sp_span_editable_column_109_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_sp input_validate db_m_sp_109" default-value="'+data+'" value="'+data+'" id="db_m_sp_editable_column_109_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_sp_editable_column db_m_sp_editable_column_109"
            },
            {
              "targets": [column_index["group_4027110_margin_on_selling_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('110', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_m_sp_span striped_span db_m_sp_span_110" id="db_m_sp_span_editable_column_110_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_m_sp input_validate db_m_sp_110" default-value="'+data+'" value="'+data+'" id="db_m_sp_editable_column_110_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_m_sp_editable_column db_m_sp_editable_column_110"
            },
            // Debter Margin on SP
            /*
            {
              "targets": [column_index["group_4027100_discount_on_grossprice_b_on_deb_selling_price"], 
              column_index["group_4027101_discount_on_grossprice_b_on_deb_selling_price"], 
              column_index["group_4027102_discount_on_grossprice_b_on_deb_selling_price"], 
              column_index["group_4027103_discount_on_grossprice_b_on_deb_selling_price"], 
              column_index["group_4027104_discount_on_grossprice_b_on_deb_selling_price"], 
              column_index["group_4027105_discount_on_grossprice_b_on_deb_selling_price"], 
              column_index["group_4027106_discount_on_grossprice_b_on_deb_selling_price"], 
              column_index["group_4027107_discount_on_grossprice_b_on_deb_selling_price"], 
              column_index["group_4027108_discount_on_grossprice_b_on_deb_selling_price"], 
              column_index["group_4027109_discount_on_grossprice_b_on_deb_selling_price"], 
              column_index["group_4027110_discount_on_grossprice_b_on_deb_selling_price"]],
              "render": function ( data, type, row ) {
                return '<input type="text" class="db_d_gp input_validate" value="'+data+'" />';
              },
              "className": "editable_column db_d_gp_editable_column"
            },
                */

            //  Debter Discount on Gross Price

            {
              "targets": [column_index["group_4027100_discount_on_grossprice_b_on_deb_selling_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('100', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_d_gp_span striped_span db_d_gp_span_100" id="db_d_gp_span_editable_column_100_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_d_gp input_validate db_d_gp_100" default-value="'+data+'" value="'+data+'" id="db_d_gp_editable_column_100_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_d_gp_editable_column db_d_gp_editable_column_100"
            },
            {
              "targets": [column_index["group_4027101_discount_on_grossprice_b_on_deb_selling_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('101', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_d_gp_span striped_span db_d_gp_span_101" id="db_d_gp_span_editable_column_101_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_d_gp input_validate db_d_gp_101" default-value="'+data+'" value="'+data+'" id="db_d_gp_editable_column_101_'+row[column_index["product_id"]]+'" />';
                }

              },
              "className": "editable_column db_d_gp_editable_column db_d_gp_editable_column_101"
            },
            {
              "targets": [column_index["group_4027102_discount_on_grossprice_b_on_deb_selling_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('102', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_d_gp_span striped_span db_d_gp_span_102" id="db_d_gp_span_editable_column_102_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_d_gp input_validate db_d_gp_102" default-value="'+data+'" value="'+data+'" id="db_d_gp_editable_column_102_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_d_gp_editable_column db_d_gp_editable_column_102"
            },
            {
              "targets": [column_index["group_4027103_discount_on_grossprice_b_on_deb_selling_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('103', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_d_gp_span striped_span db_d_gp_span_103" id="db_d_gp_span_editable_column_103_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_d_gp input_validate db_d_gp_103" default-value="'+data+'" value="'+data+'" id="db_d_gp_editable_column_103_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_d_gp_editable_column db_d_gp_editable_column_103"
            },
            {
              "targets": [column_index["group_4027104_discount_on_grossprice_b_on_deb_selling_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('104', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_d_gp_span striped_span db_d_gp_span_104" id="db_d_gp_span_editable_column_104_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_d_gp input_validate db_d_gp_104" default-value="'+data+'" value="'+data+'" id="db_d_gp_editable_column_104_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_d_gp_editable_column db_d_gp_editable_column_104"
            },
            {
              "targets": [column_index["group_4027105_discount_on_grossprice_b_on_deb_selling_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('105', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_d_gp_span striped_span db_d_gp_span_105" id="db_d_gp_span_editable_column_105_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_d_gp input_validate db_d_gp_105" default-value="'+data+'" value="'+data+'" id="db_d_gp_editable_column_105_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_d_gp_editable_column db_d_gp_editable_column_105"
            },
            {
              "targets": [column_index["group_4027106_discount_on_grossprice_b_on_deb_selling_price"]],
              "render": function ( data, type, row ) {
                 var product_id = row[column_index['product_id']];
                var product_status= generateSpan('106', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_d_gp_span striped_span db_d_gp_span_106" id="db_d_gp_span_editable_column_106_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_d_gp input_validate db_d_gp_106" default-value="'+data+'" value="'+data+'" id="db_d_gp_editable_column_106_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_d_gp_editable_column db_d_gp_editable_column_106"
            },
            {
              "targets": [column_index["group_4027107_discount_on_grossprice_b_on_deb_selling_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('107', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_d_gp_span striped_span db_d_gp_span_107" id="db_d_gp_span_editable_column_107_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_d_gp input_validate db_d_gp_107" default-value="'+data+'" value="'+data+'" id="db_d_gp_editable_column_107_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_d_gp_editable_column db_d_gp_editable_column_107"
            },
            {
              "targets": [column_index["group_4027108_discount_on_grossprice_b_on_deb_selling_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('108', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_d_gp_span striped_span db_d_gp_span_108" id="db_d_gp_span_editable_column_108_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_d_gp input_validate db_d_gp_108" default-value="'+data+'" value="'+data+'" id="db_d_gp_editable_column_108_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_d_gp_editable_column db_d_gp_editable_column_108"
            },
            {
              "targets": [column_index["group_4027109_discount_on_grossprice_b_on_deb_selling_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('109', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_d_gp_span striped_span db_d_gp_span_109" id="db_d_gp_span_editable_column_109_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_d_gp input_validate db_d_gp_109" default-value="'+data+'" value="'+data+'" id="db_d_gp_editable_column_109_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_d_gp_editable_column db_d_gp_editable_column_109"
            },
            {
              "targets": [column_index["group_4027110_discount_on_grossprice_b_on_deb_selling_price"]],
              "render": function ( data, type, row ) {
                var product_id = row[column_index['product_id']];
                var product_status= generateSpan('110', product_id, data);
                if (product_status == 'no') {
                  return '<span class="db_d_gp_span striped_span db_d_gp_span_110" id="db_d_gp_span_editable_column_110_'+product_id+'" >'+data+'</span>';
                } else {
                  return '<input type="text" class="db_d_gp input_validate db_d_gp_110" default-value="'+data+'" value="'+data+'" id="db_d_gp_editable_column_110_'+row[column_index["product_id"]]+'" />';
                }
              },
              "className": "editable_column db_d_gp_editable_column db_d_gp_editable_column_110"
            },

            //  Debter Discount on Gross Price

            {
              "targets": [column_index["discount_on_gross_price"]],
              "render": function ( data, type, row ) {
                  return '<input type="text" class="discount_on_gross input_validate" default-value="'+data+'" value="'+data+'" id="discount_on_gross_editable_column_'+row[column_index["product_id"]]+'"/>';  
                },
              "className": "editable_column discount_on_gross_editable_column"
            },
            {
              "targets": [column_index["webshop_supplier_gross_price"], column_index["webshop_supplier_buying_price"], column_index["webshop_idealeverpakking"], column_index["webshop_afwijkenidealeverpakking"], column_index["gyzs_buying_price"]],
              "className": "webshop_columns"
            },
            {
              "targets": [column_index["sku"],column_index["ean"]],
              "orderable": false
            },
            {
              "targets": [column_index["is_updated"],column_index["product_id"],column_index["is_activated"],column_index["mag_updated_product_cnt"]],
              "visible": false,
              "searchable": false
            },
            {
              "targets": [column_index["sku"]],
              "render": function ( data, type, row ) {
                    var magento_notify_price_changed = "";
                    if(parseInt(row[column_index["mag_updated_product_cnt"]]) != 0) {
                      magento_notify_price_changed = '<i class="fas fa-bell" style="font-size:14px; color:#3a3d99; position:relative;"><span class="position-absolute badge rounded-pill bg-danger">'+row[column_index["mag_updated_product_cnt"]]+'</span></i>';
                    }
                  return '<a id="pricelogs.php?pid='+row[column_index["product_id"]]+'&s='+row[column_index["sku"]]+'&e='+row[column_index["ean"]]+'" title="View Buying/Selling Price History" class="history_link">'+data+' '+magento_notify_price_changed+'</a>';  
              },
              "className": "mag_buying_change"
            }
      ],
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
                      $("#chkall").prop('checked', false);
                      $("#check_all_cnt").html(0);
                });

              if(that[0][0] == column_index["supplier_type"]) {
                  var select = $('<select id="supplier_type" class="search_supplier" style="margin-top:-30px; margin-left:-23px; position:absolute;"><option value="">All</option><option value="Mavis">Mavis</option><option value="Gyzs">Gyzs</option><option value="Transferro">Transferro</option></select>')
                      .appendTo( $(that.footer()).empty())
                      .on( 'change', function () {
                            that
                            .search( this.value )
                            .draw();
                      } );
                } else if ((that[0][0] >= column_index["selling_price"] && that[0][0] <= column_index["discount_on_gross_price"]) || (that[0][0] >= column_index["group_4027100_debter_selling_price"] && that[0][0] <= column_index["group_4027110_discount_on_grossprice_b_on_deb_selling_price"])) {
                   var select = $('<select id="group_indx_'+that[0][0]+'" class="search_group_dd" style="width:92px"><option value="0">All</option><option value="1">Less than OR Equal to</option><option value="2">Greater than OR Equal to</option><option value="3">Between</option></select>')
                      .appendTo( $(that.footer()).empty())
                      .on( 'change', function () {
                        let action_dd = $(this).attr('id');

                        $( ".search_group_dd" ).each(function() {
                          let reset_dd = $(this).attr('id');
                          if($(this).val() != "0" && reset_dd != action_dd) {
                            $('#'+reset_dd).val('0');
                            $('#'+reset_dd+' option[value="4"]').remove();
                            $('select'+'#'+reset_dd).removeAttr('title');
                          }
                        });
                        if($(this).val() == 0) {
                          $("#hdn_filters").val('');
                          let clicked_dd = $(this).attr('id');
                          $('#'+clicked_dd+' option[value="4"]').remove();
                          $('select'+'#'+clicked_dd).removeAttr('title');
                          table.draw();
                          return true;
                        }
                        var getclassclicked = ($(this).closest('.editable_column').attr("class")).split(" ");
                        let label_display ="";
                        if((getclassclicked.length > 2) && getclassclicked[2].includes("db_m_bp_editable_column")) {
                          let m = $(this).parent("th").index();
                          label_display  = $("tr[role='row']").find('th:eq('+m+')').text();
                        }

                        if((getclassclicked.length > 2) && getclassclicked[2].includes("db_sp_editable_column")) {
                          let m = $(this).parent("th").index();
                          label_display  = $("tr[role='row']").find('th:eq('+m+')').text();
                        }

                        if((getclassclicked.length > 2) && getclassclicked[2].includes("db_d_gp_editable_column")) {
                          let m = $(this).parent("th").index();
                          label_display  = $("tr[role='row']").find('th:eq('+m+')').text();
                        }

                        if((getclassclicked.length > 2) && getclassclicked[2].includes("db_m_sp_editable_column")) {
                          let m = $(this).parent("th").index();
                          label_display  = $("tr[role='row']").find('th:eq('+m+')').text();
                        }

                        if((getclassclicked.length > 1) && getclassclicked[1].includes("sp_editable_column")) {
                          let c = $(this).parent("th").index();
                          label_display  = $("tr[role='row']").find('th:eq('+c+')').text();
                        }

                        if((getclassclicked.length > 1) && getclassclicked[1].includes("pm_sp_editable_column")) {
                          let m = $(this).parent("th").index();
                          label_display  = $("tr[role='row']").find('th:eq('+m+')').text();
                        }

                        if((getclassclicked.length > 1) && getclassclicked[1].includes("pm_bp_editable_column")) {
                          let m = $(this).parent("th").index();
                          label_display  = $("tr[role='row']").find('th:eq('+m+')').text();
                        }

                        if((getclassclicked.length > 1) && getclassclicked[1].includes("discount_on_gross_editable_column")) {
                          let m = $(this).parent("th").index();
                          label_display  = $("tr[role='row']").find('th:eq('+m+')').text();
                        }

                        var group_filter_text = deb_column_name = "";
                        $("#to_debter_price").unbind("keypress");
                        $( "#from_debter_price").unbind("keypress");
                        if($(this).val() == 1) {
                          group_filter_text = label_display+" <=";
                          make_expression = "pmd.db_column <= ";
                          enterOk('from_debter_price');
                        } else if($(this).val() == 3) {
                          group_filter_text = label_display;
                          make_expression = "pmd.db_column";
                          enterOk('to_debter_price');
                        } else if($(this).val() == 2) {
                          group_filter_text = label_display+" >=";
                          make_expression = "pmd.db_column >= ";
                          enterOk('from_debter_price');
                        }
                           // set modal fields
                        $('span[id=sp_from_debter_price]').text(group_filter_text);
                        $('#to_debter_price').val('');
                        $('#from_debter_price').val('');
                        $('#hdn_parent_debter_selected').val($(this).val());
                        $('#searchDebterPriceModal').modal('show');
                        $('#searchDebterPriceModal').draggable();
                        $('#hdn_parent_debter_expression').val(make_expression);
                        if($(this).val() == '3') {
                          $('span#span-dash').show();
                          $('input#to_debter_price').show();
                        } else {
                          $('span#span-dash').hide();
                          $('input#to_debter_price').hide();
                        }
                        $("#hdn_filters").val(that[0][0]);
                      });
                } else if(that[0][0] != column_index["brand"]) {
                $( 'input', this.footer() ).on( 'keyup change clear', function () {
                    if ( that.search() !== this.value ) {

                        if(that[0][0] != column_index["is_updated"]) {
                          that
                            .search( this.value )
                            .draw();
                        }
                    }
                } );
              } else {
                  var column = this;
                  var select = $('<select id="brand" class="search_brand" style="margin-top:-30px; margin-left:-63px; position:absolute;"><option value="">All</option></select>')
                      .appendTo( $(column.footer()).empty() )
                      .on( 'change', function () {
                            $("#hdn_selectedbrand").val(this.value);
                            column
                            .search( this.value )
                            .draw();

                            $("#chkall").prop('checked', false);
                            $("#check_all_cnt").html(0);
                      });
              }
          }); 
      },
      "rowCallback": function( row, data ) {

        //console.log(column_index["debter"]);

         /* if(data[column_index["updated_product_cnt"]] > 0 || data[column_index["older_updated_product_cnt"]] > 0) {
          $badge = "";
          if(data[column_index["updated_product_cnt"]] > 0) {
            $badge = '<span class="badge badge-notify" style="font-size:10px;">'+data[column_index["updated_product_cnt"]]+'</span>';
          }

          $('td:eq('+(column_index["supplier_type"] - 1)+')', row).prepend('<a href="#pricelogModal" data-remote="pricelogs.php?pid='+data[column_index["product_id"]]+'&s='+data[column_index["sku"]]+'&e='+data[column_index["ean"]]+'" data-toggle="modal" data-target="#pricelogModal"><button class="btnhistory btn btn-info btn-xs" id="'+data[column_index["product_id"]]+'"><i class="fa fa-history"></i> ('+data[column_index["older_updated_product_cnt"]]+')</button>'+$badge+'</a>');
        } */

        //if(data[column_index["updated_product_cnt"]] > 0) {
          if( parseFloat(data[column_index["buying_price"]]) < parseFloat(data[column_index["gyzs_buying_price"]]) ) {
           $('td:eq('+(column_index["supplier_type"] - 1)+')', row).append('&nbsp;<img src="images/euro_down.gif" width="auto" height="17" title="Buying price is decreased\n'+parseFloat(data[column_index["gyzs_buying_price"]])+' ==> '+parseFloat(data[column_index["buying_price"]])+'" style="position:absolute; margin-left:4px; cursor:pointer;" />');
          } else if( parseFloat(data[column_index["buying_price"]]) > parseFloat(data[column_index["gyzs_buying_price"]]) ) {
           $('td:eq('+(column_index["supplier_type"] - 1)+')', row).append('&nbsp;<img src="images/euro_up.gif" width="auto" height="17" title="Buying price is increased\n'+parseFloat(data[column_index["gyzs_buying_price"]])+' ==> '+parseFloat(data[column_index["buying_price"]])+'" style="position:absolute; margin-left:4px; cursor:pointer;" />');
          }

          if( parseFloat(data[column_index["selling_price"]]) < parseFloat(data[column_index["gyzs_selling_price"]]) ) {
           $('td:eq('+(column_index["supplier_type"] - 1)+')', row).append('&nbsp;<img src="images/euro_down_sp.gif" width="auto" height="17" title="Selling price is decreased\n'+parseFloat(data[column_index["gyzs_selling_price"]])+' ==> '+parseFloat(data[column_index["selling_price"]])+'" style="position:absolute; margin-left:30px; cursor:pointer;" />');
          } else if( parseFloat(data[column_index["selling_price"]]) > parseFloat(data[column_index["gyzs_selling_price"]]) ) {
           $('td:eq('+(column_index["supplier_type"] - 1)+')', row).append('&nbsp;<img src="images/euro_up_sp.gif" width="auto" height="17" title="Selling price is increased\n'+parseFloat(data[column_index["gyzs_selling_price"]])+' ==> '+parseFloat(data[column_index["selling_price"]])+'" style="position:absolute; margin-left:30px; cursor:pointer;" />');
          }
       // }


        if(data[column_index["is_activated"]] == 1) {
          $('td:eq('+(column_index["supplier_type"] - 1)+')', row).prepend('<img src="images/activated.png" style="position:absolute;left:29px; width:18px; height:18px; cursor:pointer;" title="Will be LIVE in next cycle" />&nbsp;');
        }
        
        var hdn_percentage_increase_column = $("#hdn_percentage_increase_column").val();

        if(data[column_index["is_updated"]] == 1) {
          $node = this.api().row(row).nodes().to$();
           $node.addClass('row_updated')
        }
        if(data[column_index["percentage_increase"]] > 0) {
          $(row).find('td:eq('+hdn_percentage_increase_column+')').css('color', 'green');
        } else if(data[column_index["percentage_increase"]] < 0) {
          $(row).find('td:eq('+hdn_percentage_increase_column+')').css('color', 'red');
        }

        var ischecked= $("#chkall").is(':checked');
        if(ischecked) {
          $(row).addClass("selected");
        } else {
          $(row).removeClass("selected");
        }
      },

      "ajax": {
        "url": document_root_url+"/scripts/create_query.php",
        "type": "POST",
        "data": function ( d ) {
          var selected_categories ='-1';
            if ($('a>i.sim-tree-checkbox').hasClass('checked')) {
              updated_cats = new Array();
               $.each($('.sim-tree-checkbox'), function (index, value) {
                if ($(this).hasClass('checked')) {
                  updated_cats.push($(this).parent('a').parent('li').attr('data-id'));
                }
              });
              selected_categories = updated_cats.toString();
            }
            d.categories =  selected_categories,
            d.showupdated = $('#hdn_showupdated').val(),
            d.hdn_filters = $('#hdn_filters').val(),
            d.hdn_stijging_text = $('#hdn_stijging_text').val(),
            d.hdn_group_search_text = $('#hdn_group_search_text').val()
        }
      }
  });


  $(document).on("keyup", ".form-control, .input_validate" , function(evt) {
     var current_class = $(this).attr("class").split(" ");
     if(current_class[0] != "discount_on_gross" && current_class[0] != "db_d_gp") {
      var self = $(this);
      self.val(self.val().replace(/[^0-9\.]/g, ''));
      if ((evt.which != 46 || self.val().indexOf('.') != -1) && (evt.which < 48 || evt.which > 57)) 
      {
        evt.preventDefault();
      }
     }
  });

  $(document).on("click", ".history_link" , function() { 
    var link = $(this).attr("id");
    $('.history-container').load(link,function(result){
      $('#pricelogModal').modal('toggle');
    });
  });



  /* Enable Bulk Starts */
    var enableBulkFunc = function() {
      var ischecked = $("#chkbulkupdates").is(':checked');
      if(ischecked) {
        $(".editable_column").css("cssText", "background-color: #c5c7c9 !important;");
        $('.striped_span').css("color", "rgb(84, 84, 84)");
        
        $(".sp_editable_column input").attr("disabled","disabled");
        $(".pm_bp_editable_column input").attr("disabled","disabled");
        $(".pm_sp_editable_column input").attr("disabled","disabled");
        $(".discount_on_gross_editable_column input").attr("disabled","disabled");

        // For Debter
        var deb_cnt = 100;
        for(deb_cnt = 100;deb_cnt<=110;deb_cnt++) {
          var grouptdclass = "db_sp_editable_column_"+deb_cnt;
          var grouptdinput = "db_sp_editable_column_"+deb_cnt+" input";

          var grouptdclassmbp = "db_m_bp_editable_column_"+deb_cnt;
          var grouptdinputmbp = "db_m_bp_editable_column_"+deb_cnt+" input";

          var grouptdclassmsp = "db_m_sp_editable_column_"+deb_cnt;
          var grouptdinputmsp = "db_m_sp_editable_column_"+deb_cnt+" input";

          var grouptdclassdgp = "db_d_gp_editable_column_"+deb_cnt;
          var grouptdinputdgp = "db_d_gp_editable_column_"+deb_cnt+" input";

          $("."+grouptdclass+"").css("cssText", "background-color: #c5c7c9 !important;");
          $("."+grouptdinput+"").attr("disabled","disabled");

          $("."+grouptdclassmbp+"").css("cssText", "background-color: #c5c7c9 !important;");
          $("."+grouptdinputmbp+"").attr("disabled","disabled");

          $("."+grouptdclassmsp+"").css("cssText", "background-color: #c5c7c9 !important;");
          $("."+grouptdinputmsp+"").attr("disabled","disabled");

          $("."+grouptdclassdgp+"").css("cssText", "background-color: #c5c7c9 !important;");
          $("."+grouptdinputdgp+"").attr("disabled","disabled");
        }
        
        // For Debter

      } else {
        $(".editable_column").css("cssText", "background-color: #ffffcc !important;");
        $(".sp_editable_column input").removeAttr("disabled");
        $(".pm_bp_editable_column input").removeAttr("disabled");
        $(".pm_sp_editable_column input").removeAttr("disabled");
        $(".discount_on_gross_editable_column input").removeAttr("disabled");
        $('.striped_span').css("color", "black");

        // For Debter
         var deb_cnt = 100;
        for(deb_cnt = 100;deb_cnt<=110;deb_cnt++) {
          var grouptdclass = "db_sp_editable_column_"+deb_cnt;
          var grouptdinput = "db_sp_editable_column_"+deb_cnt+" input";

          var grouptdclassmbp = "db_m_bp_editable_column_"+deb_cnt;
          var grouptdinputmbp = "db_m_bp_editable_column_"+deb_cnt+" input";

          var grouptdclassmsp = "db_m_sp_editable_column_"+deb_cnt;
          var grouptdinputmsp = "db_m_sp_editable_column_"+deb_cnt+" input";

          var grouptdclassdgp = "db_d_gp_editable_column_"+deb_cnt;
          var grouptdinputdgp = "db_d_gp_editable_column_"+deb_cnt+" input";
          $("."+grouptdclass+"").css("cssText", "background-color: #90EE90 !important;");
          $("."+grouptdinput+"").removeAttr("disabled","disabled");
          $('.striped_span').parent('td').css("cssText", "background-color: #dee2e6 !important;");
          
          $("."+grouptdclassmbp+"").css("cssText", "background-color: #7AC3FF !important;");
          $("."+grouptdinputmbp+"").removeAttr("disabled","disabled");

          $("."+grouptdclassmsp+"").css("cssText", "background-color: #FFFD6E !important;");
          $("."+grouptdinputmsp+"").removeAttr("disabled","disabled");

          $("."+grouptdclassdgp+"").css("cssText", "background-color: #FF6B6B !important;");
          $("."+grouptdinputdgp+"").removeAttr("disabled","disabled");
        } 
        // For Debter

      }
      setColWidths();
    }
    $("#chkbulkupdates").on('click', enableBulkFunc); 
    /* Enable Bulk Ends */

    /* set col width */
    var setColWidths = function() {
       $("table tr th").css({
          "width": "100px"
        });
        $("#example").width("100%"); 
    }
    /* set col width */


  var current_index;
  var current_val;
  var current_last_index;

  $(document).on("click", ".editable_column" , function() {
   
   var ischecked = $("#chkbulkupdates").is(':checked');
  //$(this).closest(".editable_column").find('input')[0].select();
   if(ischecked) {

    const debter_groups = ["100","101","102","103","104","105","106","107","108","109","110"];

      var getclassclicked =  getclassclicked_for_span = [];
      if($(this).closest('.editable_column').find('input').length > 0) {
        var getclassclicked = ($(this).closest('.editable_column').find('input').attr("class")).split(" ");
      } else if($(this).closest('.editable_column').find('span').length > 0) {
        var getclassclicked_of_span =  ($(this).closest('.editable_column').find('span').attr("class")).split(" ");
      }
      
      $(".editable_column").css("cssText", "background-color: #c5c7c9 !important;");
      
      var index = $(this).closest('tr').index();
      var product_id = table.cells({ row: index, column: column_index["product_id"] }).data()[0];
            

      if(getclassclicked[0] == "selling_price") {
        $(".sp_editable_column").css("cssText", "background-color: #ffffcc !important;");
        
        $(".sp_editable_column input").removeAttr("disabled");
        $(".pm_bp_editable_column input").attr("disabled","disabled");
        $(".pm_sp_editable_column input").attr("disabled","disabled");
        $(".discount_on_gross_editable_column input").attr("disabled","disabled");
        $(this).closest('.sp_editable_column').find('input')[0].select();
        $(this).closest('tr').addClass("selected");

        $.each(debter_groups, function( debindex, debvalue ) {
          var remtdinput = "db_sp_editable_column_"+debvalue+" input";
          var remtdinputdbmbp = "db_m_bp_editable_column_"+debvalue+" input";
          var remtdinputdbmsp = "db_m_sp_editable_column_"+debvalue+" input";
          var remtdinputdbdgp = "db_d_gp_editable_column_"+debvalue+" input";

          $("."+remtdinput+"").attr("disabled","disabled");
          $("."+remtdinputdbmbp+"").attr("disabled","disabled");
          $("."+remtdinputdbmsp+"").attr("disabled","disabled");
          $("."+remtdinputdbdgp+"").attr("disabled","disabled");
        });

        $(".selling_price" ).each(function( index ) {
          $(this).removeClass("current_index");
        });

        $(this).closest('.sp_editable_column').find('input').addClass('current_index');
        $(".selling_price" ).each(function( index ) {
          if($(this).hasClass("current_index")) {
            current_index = index;
            current_val = $( this ).val();
          } 
        });
      } else if(getclassclicked[0] == "profit_margin") {
        $(".pm_bp_editable_column").css("cssText", "background-color: #ffffcc !important;");
        $(".pm_bp_editable_column input").removeAttr("disabled");
        $(".sp_editable_column input").attr("disabled","disabled");
        $(".pm_sp_editable_column input").attr("disabled","disabled");
        $(".discount_on_gross_editable_column input").attr("disabled","disabled");
        $(this).closest('.pm_bp_editable_column').find('input')[0].select();

        $(this).closest('tr').addClass("selected");

        $.each(debter_groups, function( debindex, debvalue ){
          var remtdinput = "db_sp_editable_column_"+debvalue+" input";
          var remtdinputdbmbp = "db_m_bp_editable_column_"+debvalue+" input";
          var remtdinputdbmsp = "db_m_sp_editable_column_"+debvalue+" input";
          var remtdinputdbdgp = "db_d_gp_editable_column_"+debvalue+" input";

          $("."+remtdinput+"").attr("disabled","disabled");
          $("."+remtdinputdbmbp+"").attr("disabled","disabled");
          $("."+remtdinputdbmsp+"").attr("disabled","disabled");
          $("."+remtdinputdbdgp+"").attr("disabled","disabled");
        });

        $(".profit_margin" ).each(function( index ) {
          $(this).removeClass("current_index");
        });

        $(this).closest('.pm_bp_editable_column').find('input').addClass('current_index');
        $(".profit_margin" ).each(function( index ) {
          if($(this).hasClass("current_index")) {
            current_index = index;
            current_val = $( this ).val();
          } 
        });

        
      } else if(getclassclicked[0] == "profit_margin_sp") {
        $(".pm_sp_editable_column").css("cssText", "background-color: #ffffcc !important;");
        $(".pm_sp_editable_column input").removeAttr("disabled");
        $(".sp_editable_column input").attr("disabled","disabled");
        $(".pm_bp_editable_column input").attr("disabled","disabled");
        $(".discount_on_gross_editable_column input").attr("disabled","disabled");
        $(this).closest('.pm_sp_editable_column').find('input')[0].select();

        $(this).closest('tr').addClass("selected");

        $.each(debter_groups, function( debindex, debvalue ){
          var remtdinput = "db_sp_editable_column_"+debvalue+" input";
          var remtdinputdbmbp = "db_m_bp_editable_column_"+debvalue+" input";
          var remtdinputdbmsp = "db_m_sp_editable_column_"+debvalue+" input";
          var remtdinputdbdgp = "db_d_gp_editable_column_"+debvalue+" input";

          $("."+remtdinput+"").attr("disabled","disabled");
          $("."+remtdinputdbmbp+"").attr("disabled","disabled");
          $("."+remtdinputdbmsp+"").attr("disabled","disabled");
          $("."+remtdinputdbdgp+"").attr("disabled","disabled");
        });

        $(".profit_margin_sp" ).each(function( index ) {
          $(this).removeClass("current_index");
        });

        $(this).closest('.pm_sp_editable_column').find('input').addClass('current_index');
        $(".profit_margin_sp" ).each(function( index ) {
          if($(this).hasClass("current_index")) {
            current_index = index;
            current_val = $( this ).val();
          } 
        });

      } else if(getclassclicked[0] == "discount_on_gross") {
        $(".discount_on_gross_editable_column").css("cssText", "background-color: #ffffcc !important;");
        $(".discount_on_gross_editable_column input").removeAttr("disabled");
        $(".sp_editable_column input").attr("disabled","disabled");
        $(".pm_bp_editable_column input").attr("disabled","disabled");
        $(".pm_sp_editable_column input").attr("disabled","disabled");
        $(this).closest('.discount_on_gross_editable_column').find('input')[0].select();
        $(this).closest('tr').addClass("selected");

        $.each(debter_groups, function( debindex, debvalue ){
          var remtdinput = "db_sp_editable_column_"+debvalue+" input";
          var remtdinputdbmbp = "db_m_bp_editable_column_"+debvalue+" input";
          var remtdinputdbmsp = "db_m_sp_editable_column_"+debvalue+" input";
          var remtdinputdbdgp = "db_d_gp_editable_column_"+debvalue+" input";

          $("."+remtdinput+"").attr("disabled","disabled");
          $("."+remtdinputdbmbp+"").attr("disabled","disabled");
          $("."+remtdinputdbmsp+"").attr("disabled","disabled");
          $("."+remtdinputdbdgp+"").attr("disabled","disabled");
        });

        $(".discount_on_gross" ).each(function( index ) {
          $(this).removeClass("current_index");
        });

        $(this).closest('.discount_on_gross_editable_column').find('input').addClass('current_index');
        $(".discount_on_gross" ).each(function( index ) {
          if($(this).hasClass("current_index")) {
            current_index = index;
            current_val = $( this ).val();
          } 
        });
      } else if(getclassclicked[0] == "db_sp") {
        var getclicked_debter_grp = getclassclicked[2].split("_");
        var grouptdclass = "db_sp_editable_column_"+getclicked_debter_grp[2];
        var grouptdinput = "db_sp_editable_column_"+getclicked_debter_grp[2]+" input";
       
        $('input').parent("td."+grouptdclass+"").css("cssText", "background-color: #ffffcc !important;");
        $("th."+grouptdclass+"").css("cssText", "background-color: #ffffcc !important;");

        $(this).closest('tr').addClass("selected");
        $("."+grouptdinput+"").removeAttr("disabled");
        $(".sp_editable_column input").attr("disabled","disabled");
        $(".pm_bp_editable_column input").attr("disabled","disabled");
        $(".pm_sp_editable_column input").attr("disabled","disabled");
        $(".discount_on_gross_editable_column input").attr("disabled","disabled");
        $(".db_m_bp_editable_column_"+getclicked_debter_grp[2]+" input").attr("disabled","disabled");
        $(".db_m_sp_editable_column_"+getclicked_debter_grp[2]+" input").attr("disabled","disabled");
        $(".db_d_gp_editable_column_"+getclicked_debter_grp[2]+" input").attr("disabled","disabled");

        debter_groups.splice(debter_groups.indexOf(getclicked_debter_grp[2]),1);

        $.each(debter_groups, function( debindex, debvalue ){
          var remtdinput = "db_sp_editable_column_"+debvalue+" input";
          var remtdinputdbmbp = "db_m_bp_editable_column_"+debvalue+" input";
          var remtdinputdbmsp = "db_m_sp_editable_column_"+debvalue+" input";
          var remtdinputdbdgp = "db_d_gp_editable_column_"+debvalue+" input";

          $("."+remtdinput+"").attr("disabled","disabled");
          $("."+remtdinputdbmbp+"").attr("disabled","disabled");
          $("."+remtdinputdbmsp+"").attr("disabled","disabled");
          $("."+remtdinputdbdgp+"").attr("disabled","disabled");
        });

        $(this).closest("."+grouptdclass+"").find('input')[0].select();
        $(".db_sp_"+getclicked_debter_grp[2]+"" ).each(function( index ) {
          $(this).removeClass("current_index");
        });

        $(this).closest("."+grouptdclass+"").find('input').addClass('current_index');
        $(".db_sp_"+getclicked_debter_grp[2]+"" ).each(function( index ) {
          if($(this).hasClass("current_index")) {
            current_index = index;
            current_val = $( this ).val();
          } 
        });
      } else if(getclassclicked[0] == "db_m_bp") {
        var getclicked_debter_grp = getclassclicked[2].split("_");
        var grouptdclass = "db_m_bp_editable_column_"+getclicked_debter_grp[3];
        var grouptdinput = "db_m_bp_editable_column_"+getclicked_debter_grp[3]+" input";
       
        $('input').parent("td."+grouptdclass+"").css("cssText", "background-color: #ffffcc !important;");
        $("th."+grouptdclass+"").css("cssText", "background-color: #ffffcc !important;");
        $(this).closest('tr').addClass("selected");

        $("."+grouptdinput+"").removeAttr("disabled");
        $(".sp_editable_column input").attr("disabled","disabled");
        $(".pm_bp_editable_column input").attr("disabled","disabled");
        $(".pm_sp_editable_column input").attr("disabled","disabled");
        $(".discount_on_gross_editable_column input").attr("disabled","disabled");
        $(".db_sp_editable_column_"+getclicked_debter_grp[3]+" input").attr("disabled","disabled");
        $(".db_m_sp_editable_column_"+getclicked_debter_grp[3]+" input").attr("disabled","disabled");
        $(".db_d_gp_editable_column_"+getclicked_debter_grp[3]+" input").attr("disabled","disabled");

        debter_groups.splice(debter_groups.indexOf(getclicked_debter_grp[3]),1);
        $.each(debter_groups, function( debindex, debvalue ){
          var remtdinputdbmbp = "db_m_bp_editable_column_"+debvalue+" input";
          var remtdinput = "db_sp_editable_column_"+debvalue+" input";
          var remtdinputdbmsp = "db_m_sp_editable_column_"+debvalue+" input";
          var remtdinputdbdgp = "db_d_gp_editable_column_"+debvalue+" input";

          $("."+remtdinput+"").attr("disabled","disabled");
          $("."+remtdinputdbmbp+"").attr("disabled","disabled");
          $("."+remtdinputdbmsp+"").attr("disabled","disabled");
          $("."+remtdinputdbdgp+"").attr("disabled","disabled");
        });
        
        $(this).closest("."+grouptdclass+"").find('input')[0].select();
        $(".db_m_bp_"+getclicked_debter_grp[3]+"" ).each(function( index ) {
          $(this).removeClass("current_index");
        });

        $(this).closest("."+grouptdclass+"").find('input').addClass('current_index');
        $(".db_m_bp_"+getclicked_debter_grp[3]+"" ).each(function( index ) {
          if($(this).hasClass("current_index")) {
            current_index = index;
            current_val = $( this ).val();
          } 
        });
      } else if(getclassclicked[0] == "db_m_sp") {
        var getclicked_debter_grp = getclassclicked[2].split("_");
        var grouptdclass = "db_m_sp_editable_column_"+getclicked_debter_grp[3];
        var grouptdinput = "db_m_sp_editable_column_"+getclicked_debter_grp[3]+" input";
        $(this).closest('tr').addClass("selected");

        $('input').parent("td."+grouptdclass+"").css("cssText", "background-color: #ffffcc !important;");
        $("th."+grouptdclass+"").css("cssText", "background-color: #ffffcc !important;");

        $("."+grouptdinput+"").removeAttr("disabled");
        $(".sp_editable_column input").attr("disabled","disabled");
        $(".pm_bp_editable_column input").attr("disabled","disabled");
        $(".pm_sp_editable_column input").attr("disabled","disabled");
        $(".discount_on_gross_editable_column input").attr("disabled","disabled");
        $(".db_sp_editable_column_"+getclicked_debter_grp[3]+" input").attr("disabled","disabled");
        $(".db_m_bp_editable_column_"+getclicked_debter_grp[3]+" input").attr("disabled","disabled");
        $(".db_d_gp_editable_column_"+getclicked_debter_grp[3]+" input").attr("disabled","disabled");

        debter_groups.splice(debter_groups.indexOf(getclicked_debter_grp[3]),1);
        $.each(debter_groups, function( debindex, debvalue ){
          var remtdinput = "db_sp_editable_column_"+debvalue+" input";
          var remtdinputdbmbp = "db_m_bp_editable_column_"+debvalue+" input";
          var remtdinputdbmsp = "db_m_sp_editable_column_"+debvalue+" input";
          var remtdinputdbdgp = "db_d_gp_editable_column_"+debvalue+" input";

          $("."+remtdinput+"").attr("disabled","disabled");
          $("."+remtdinputdbmbp+"").attr("disabled","disabled");
          $("."+remtdinputdbmsp+"").attr("disabled","disabled");
          $("."+remtdinputdbdgp+"").attr("disabled","disabled");
        });
        
        $(this).closest("."+grouptdclass+"").find('input')[0].select();
        $(".db_m_sp_"+getclicked_debter_grp[3]+"" ).each(function( index ) {
          $(this).removeClass("current_index");
        });

        $(this).closest("."+grouptdclass+"").find('input').addClass('current_index');
        $(".db_m_sp_"+getclicked_debter_grp[3]+"" ).each(function( index ) {
          if($(this).hasClass("current_index")) {
            current_index = index;
            current_val = $( this ).val();
          } 
        });
      } else if(getclassclicked[0] == "db_d_gp") {
        var getclicked_debter_grp = getclassclicked[2].split("_");
        var grouptdclass = "db_d_gp_editable_column_"+getclicked_debter_grp[3];
        var grouptdinput = "db_d_gp_editable_column_"+getclicked_debter_grp[3]+" input";

        $('input').parent("td."+grouptdclass+"").css("cssText", "background-color: #ffffcc !important;");
        $("th."+grouptdclass+"").css("cssText", "background-color: #ffffcc !important;");
        $(this).closest('tr').addClass("selected");

        $("."+grouptdinput+"").removeAttr("disabled");
        $(".sp_editable_column input").attr("disabled","disabled");
        $(".pm_bp_editable_column input").attr("disabled","disabled");
        $(".pm_sp_editable_column input").attr("disabled","disabled");
        $(".discount_on_gross_editable_column input").attr("disabled","disabled");
        $(".db_sp_editable_column_"+getclicked_debter_grp[3]+" input").attr("disabled","disabled");
        $(".db_m_bp_editable_column_"+getclicked_debter_grp[3]+" input").attr("disabled","disabled");
        $(".db_m_sp_editable_column_"+getclicked_debter_grp[3]+" input").attr("disabled","disabled");

        debter_groups.splice(debter_groups.indexOf(getclicked_debter_grp[3]),1);
        $.each(debter_groups, function( debindex, debvalue ){
          var remtdinput = "db_sp_editable_column_"+debvalue+" input";
          var remtdinputdbmbp = "db_m_bp_editable_column_"+debvalue+" input";
          var remtdinputdbmsp = "db_m_sp_editable_column_"+debvalue+" input";
          var remtdinputdbdgp = "db_d_gp_editable_column_"+debvalue+" input";

          $("."+remtdinput+"").attr("disabled","disabled");
          $("."+remtdinputdbmbp+"").attr("disabled","disabled");
          $("."+remtdinputdbmsp+"").attr("disabled","disabled");
          $("."+remtdinputdbdgp+"").attr("disabled","disabled");
        });
        
        $(this).closest("."+grouptdclass+"").find('input')[0].select();
        $(".db_d_gp_"+getclicked_debter_grp[3]+"" ).each(function( index ) {
          $(this).removeClass("current_index");
        });

        $(this).closest("."+grouptdclass+"").find('input').addClass('current_index');
        $(".db_d_gp_"+getclicked_debter_grp[3]+"" ).each(function( index ) {
          if($(this).hasClass("current_index")) {
            current_index = index;
            current_val = $( this ).val();
          } 
        });
      }
      if(getclassclicked_of_span[0] == "db_sp_span") {
        var getclicked_debter_grp = getclassclicked_of_span[2].split("_");
        var grouptdclass = "db_sp_editable_column_"+getclicked_debter_grp[3];
        $('input').parent("td."+grouptdclass+"").css("cssText", "background-color: #ffffcc !important;");
        $("th."+grouptdclass+"").css("cssText", "background-color: #ffffcc !important;");

        var grouptdinput = "db_sp_editable_column_"+getclicked_debter_grp[3]+" input";
        $(this).closest('tr').addClass("selected");
        $("."+grouptdinput+"").removeAttr("disabled");
        $(".sp_editable_column input").attr("disabled","disabled");
        $(".pm_bp_editable_column input").attr("disabled","disabled");
        $(".pm_sp_editable_column input").attr("disabled","disabled");
        $(".discount_on_gross_editable_column input").attr("disabled","disabled");
        $(".db_m_bp_editable_column_"+getclicked_debter_grp[3]+" input").attr("disabled","disabled");
        $(".db_m_sp_editable_column_"+getclicked_debter_grp[3]+" input").attr("disabled","disabled");
        $(".db_d_gp_editable_column_"+getclicked_debter_grp[3]+" input").attr("disabled","disabled");

        debter_groups.splice(debter_groups.indexOf(getclicked_debter_grp[3]),1);

        $.each(debter_groups, function( debindex, debvalue ){
          var remtdinput = "db_sp_editable_column_"+debvalue+" input";
          var remtdinputdbmbp = "db_m_bp_editable_column_"+debvalue+" input";
          var remtdinputdbmsp = "db_m_sp_editable_column_"+debvalue+" input";
          var remtdinputdbdgp = "db_d_gp_editable_column_"+debvalue+" input";

          $("."+remtdinput+"").attr("disabled","disabled");
          $("."+remtdinputdbmbp+"").attr("disabled","disabled");
          $("."+remtdinputdbmsp+"").attr("disabled","disabled");
          $("."+remtdinputdbdgp+"").attr("disabled","disabled");
        });
        $(this).closest("."+grouptdclass+"").find('input')[0].select();
        $(".db_sp_"+getclicked_debter_grp[3]+"" ).each(function( index ) {
          $(this).removeClass("current_index");
        });

        $(this).closest("."+grouptdclass+"").find('input').addClass('current_index');
        $(".db_sp_"+getclicked_debter_grp[3]+"" ).each(function( index ) {
          if($(this).hasClass("current_index")) {
            current_index = index;
            current_val = $( this ).val();
          }
        });
      } else if(getclassclicked_of_span[0] == "db_m_bp_span") {
        var getclicked_debter_grp = getclassclicked_of_span[2].split("_");
        var grouptdclass = "db_m_bp_editable_column_"+getclicked_debter_grp[4];
        $('input').parent("td."+grouptdclass+"").css("cssText", "background-color: #ffffcc !important;");
        $("th."+grouptdclass+"").css("cssText", "background-color: #ffffcc !important;");

        var grouptdinput = "db_m_bp_editable_column_"+getclicked_debter_grp[4]+" input";
        $(this).closest('tr').addClass("selected");

        $("."+grouptdinput+"").removeAttr("disabled");
        $(".sp_editable_column input").attr("disabled","disabled");
        $(".pm_bp_editable_column input").attr("disabled","disabled");
        $(".pm_sp_editable_column input").attr("disabled","disabled");
        $(".discount_on_gross_editable_column input").attr("disabled","disabled");
        $(".db_sp_editable_column_"+getclicked_debter_grp[4]+" input").attr("disabled","disabled");
        $(".db_m_sp_editable_column_"+getclicked_debter_grp[4]+" input").attr("disabled","disabled");
        $(".db_d_gp_editable_column_"+getclicked_debter_grp[4]+" input").attr("disabled","disabled");

        debter_groups.splice(debter_groups.indexOf(getclicked_debter_grp[4]),1);
        $.each(debter_groups, function( debindex, debvalue ){
          var remtdinputdbmbp = "db_m_bp_editable_column_"+debvalue+" input";
          var remtdinput = "db_sp_editable_column_"+debvalue+" input";
          var remtdinputdbmsp = "db_m_sp_editable_column_"+debvalue+" input";
          var remtdinputdbdgp = "db_d_gp_editable_column_"+debvalue+" input";

          $("."+remtdinput+"").attr("disabled","disabled");
          $("."+remtdinputdbmbp+"").attr("disabled","disabled");
          $("."+remtdinputdbmsp+"").attr("disabled","disabled");
          $("."+remtdinputdbdgp+"").attr("disabled","disabled");
        });
        
        $(this).closest("."+grouptdclass+"").find('input')[0].select();
        $(".db_m_bp_"+getclicked_debter_grp[4]+"" ).each(function( index ) {
          $(this).removeClass("current_index");
        });

        $(this).closest("."+grouptdclass+"").find('input').addClass('current_index');
        $(".db_m_bp_"+getclicked_debter_grp[4]+"" ).each(function( index ) {
          if($(this).hasClass("current_index")) {
            current_index = index;
            current_val = $( this ).val();
          }
        });
      } else if(getclassclicked_of_span[0] == "db_m_sp_span") {
        var getclicked_debter_grp = getclassclicked_of_span[2].split("_");
        var grouptdclass = "db_m_sp_editable_column_"+getclicked_debter_grp[4];
        $('input').parent("td."+grouptdclass+"").css("cssText", "background-color: #ffffcc !important;");
        $("th."+grouptdclass+"").css("cssText", "background-color: #ffffcc !important;");

        var grouptdinput = "db_m_sp_editable_column_"+getclicked_debter_grp[4]+" input";
        $(this).closest('tr').addClass("selected");

        $("."+grouptdinput+"").removeAttr("disabled");
        $(".sp_editable_column input").attr("disabled","disabled");
        $(".pm_bp_editable_column input").attr("disabled","disabled");
        $(".pm_sp_editable_column input").attr("disabled","disabled");
        $(".discount_on_gross_editable_column input").attr("disabled","disabled");
        $(".db_sp_editable_column_"+getclicked_debter_grp[4]+" input").attr("disabled","disabled");
        $(".db_m_sp_editable_column_"+getclicked_debter_grp[4]+" input").attr("disabled","disabled");
        $(".db_d_gp_editable_column_"+getclicked_debter_grp[4]+" input").attr("disabled","disabled");

        debter_groups.splice(debter_groups.indexOf(getclicked_debter_grp[4]),1);
        $.each(debter_groups, function( debindex, debvalue ){
          var remtdinputdbmbp = "db_m_bp_editable_column_"+debvalue+" input";
          var remtdinput = "db_sp_editable_column_"+debvalue+" input";
          var remtdinputdbmsp = "db_m_sp_editable_column_"+debvalue+" input";
          var remtdinputdbdgp = "db_d_gp_editable_column_"+debvalue+" input";

          $("."+remtdinput+"").attr("disabled","disabled");
          $("."+remtdinputdbmbp+"").attr("disabled","disabled");
          $("."+remtdinputdbmsp+"").attr("disabled","disabled");
          $("."+remtdinputdbdgp+"").attr("disabled","disabled");
        });
        
        $(this).closest("."+grouptdclass+"").find('input')[0].select();
        $(".db_m_bp_"+getclicked_debter_grp[4]+"" ).each(function( index ) {
          $(this).removeClass("current_index");
        });

        $(this).closest("."+grouptdclass+"").find('input').addClass('current_index');
        $(".db_m_bp_"+getclicked_debter_grp[4]+"" ).each(function( index ) {
          if($(this).hasClass("current_index")) {
            current_index = index;
            current_val = $( this ).val();
          }
        });

      } else if(getclassclicked_of_span[0] == "db_d_gp_span") {
        var getclicked_debter_grp = getclassclicked_of_span[2].split("_");
        var grouptdclass = "db_d_gp_editable_column_"+getclicked_debter_grp[4];
        $('input').parent("td."+grouptdclass+"").css("cssText", "background-color: #ffffcc !important;");
        $("th."+grouptdclass+"").css("cssText", "background-color: #ffffcc !important;");

        var grouptdinput = "db_d_gp_editable_column_"+getclicked_debter_grp[4]+" input";
        $(this).closest('tr').addClass("selected");

        $("."+grouptdinput+"").removeAttr("disabled");
        $(".sp_editable_column input").attr("disabled","disabled");
        $(".pm_bp_editable_column input").attr("disabled","disabled");
        $(".pm_sp_editable_column input").attr("disabled","disabled");
        $(".discount_on_gross_editable_column input").attr("disabled","disabled");
        $(".db_sp_editable_column_"+getclicked_debter_grp[4]+" input").attr("disabled","disabled");
        $(".db_m_bp_editable_column_"+getclicked_debter_grp[4]+" input").attr("disabled","disabled");
        $(".db_m_sp_editable_column_"+getclicked_debter_grp[4]+" input").attr("disabled","disabled");

        debter_groups.splice(debter_groups.indexOf(getclicked_debter_grp[4]),1);
        $.each(debter_groups, function( debindex, debvalue ){
          var remtdinput = "db_sp_editable_column_"+debvalue+" input";
          var remtdinputdbmbp = "db_m_bp_editable_column_"+debvalue+" input";
          var remtdinputdbmsp = "db_m_sp_editable_column_"+debvalue+" input";
          var remtdinputdbdgp = "db_d_gp_editable_column_"+debvalue+" input";

          $("."+remtdinput+"").attr("disabled","disabled");
          $("."+remtdinputdbmbp+"").attr("disabled","disabled");
          $("."+remtdinputdbmsp+"").attr("disabled","disabled");
          $("."+remtdinputdbdgp+"").attr("disabled","disabled");
        });
        
        $(this).closest("."+grouptdclass+"").find('input')[0].select();
        $(".db_d_gp_"+getclicked_debter_grp[4]+"" ).each(function( index ) {
          $(this).removeClass("current_index");
        });
        $(this).closest("."+grouptdclass+"").find('input').addClass('current_index');
        $(".db_d_gp_"+getclicked_debter_grp[4]+"" ).each(function( index ) {
          if($(this).hasClass("current_index")) {
            current_index = index;
            current_val = $( this ).val();
          }
        });
      }
      setColWidths();
    }
  });


  // Bulk update
  $(document).on("click",".selling_price", function(event) {
    var ischecked_blkupdates = $("#chkbulkupdates").is(':checked');
    if(ischecked_blkupdates) {
      if(event.ctrlKey) {
        current_last_index = $('.selling_price').index(this);
        for(c=Math.min(current_index,current_last_index);c<=Math.max(current_index,current_last_index);c++) {
          $('.selling_price').each(function(spctrlIdx, spctrlVal) {
            if(c == spctrlIdx) { 
              if(c == current_index) {  
                current_val = $(spctrlVal).val();
              }
              $(spctrlVal).val(current_val);
              $('#example tbody tr').eq(c).addClass("selected");
            }
          });
        }
      }

      if(event.altKey) {
        current_last_index = $('.selling_price').index(this);
        for(r=Math.min(current_index,current_last_index);r<=Math.max(current_index,current_last_index);r++) {
          $('.selling_price').each(function(spIdx, spVal) {
            if(r == spIdx) {
              $(spVal).val($(spVal).attr('default-value')); 
            }
          });
        }
      }
    }
  });

  $(document).on("click",".profit_margin", function(event) {
    var ischecked_blkupdates = $("#chkbulkupdates").is(':checked');
    if(ischecked_blkupdates) {
      if(event.ctrlKey) {
        current_last_index = $('.profit_margin').index(this);
        for(c=Math.min(current_index,current_last_index);c<=Math.max(current_index,current_last_index);c++) {
          $('.profit_margin').each(function(spctrlIdx, spctrlVal) {
            if(c == spctrlIdx) {
              if(c == current_index) {  
                current_val = $(spctrlVal).val();
              }
              $(spctrlVal).val(current_val); 
               $('#example tbody tr').eq(c).addClass("selected");
            }
          });
        }
      }

      if(event.altKey) {
        current_last_index = $('.profit_margin').index(this);
        for(r=Math.min(current_index,current_last_index);r<=Math.max(current_index,current_last_index);r++) {
          $('.profit_margin').each(function(spIdx, spVal) {
            if(r == spIdx) {
              $(spVal).val($(spVal).attr('default-value')); 
            }
          });
        }
      }
    }
  });

  $(document).on("click",".profit_margin_sp", function(event) {
    var ischecked_blkupdates = $("#chkbulkupdates").is(':checked');
    if(ischecked_blkupdates) {
      if(event.ctrlKey) {
        current_last_index = $('.profit_margin_sp').index(this);
        for(c=Math.min(current_index,current_last_index);c<=Math.max(current_index,current_last_index);c++) {
          $('.profit_margin_sp').each(function(spctrlIdx, spctrlVal) {
            if(c == spctrlIdx) {
              if(c == current_index) {  
                current_val = $(spctrlVal).val();
              }
              $(spctrlVal).val(current_val); 
               $('#example tbody tr').eq(c).addClass("selected");
            }
          });
        }
      }

      if(event.altKey) {
        current_last_index = $('.profit_margin_sp').index(this);
        for(r=Math.min(current_index,current_last_index);r<=Math.max(current_index,current_last_index);r++) {
          $('.profit_margin_sp').each(function(spIdx, spVal) {
            if(r == spIdx) {
              $(spVal).val($(spVal).attr('default-value')); 
            }
          });
        }
      }
    }
  });

  $(document).on("click",".discount_on_gross", function(event) {
    var ischecked_blkupdates = $("#chkbulkupdates").is(':checked');
    if(ischecked_blkupdates) {
      if(event.ctrlKey) {
        current_last_index = $('.discount_on_gross').index(this);
        for(c=Math.min(current_index,current_last_index);c<=Math.max(current_index,current_last_index);c++) {
          $('.discount_on_gross').each(function(spctrlIdx, spctrlVal) {
            if(c == spctrlIdx) {
              if(c == current_index) {  
                current_val = $(spctrlVal).val();
              }
              $(spctrlVal).val(current_val); 
               $('#example tbody tr').eq(c).addClass("selected");
            }
          });
        }
      }

      if(event.altKey) {
        current_last_index = $('.discount_on_gross').index(this);
        for(r=Math.min(current_index,current_last_index);r<=Math.max(current_index,current_last_index);r++) {
          $('.discount_on_gross').each(function(spIdx, spVal) {
            if(r == spIdx) {
              $(spVal).val($(spVal).attr('default-value')); 
            }
          });
        }
      }
    }
  });


  $(document).on("click",".db_sp", function(event) {
    var ischecked_blkupdates = $("#chkbulkupdates").is(':checked');
    if(ischecked_blkupdates) {

      var getclassclicked = ($(this).attr("class")).split(" ");
      var getclicked_debter_grp = getclassclicked[2].split("_");
      
      if(event.ctrlKey) {
        current_last_index = $('.db_sp_'+getclicked_debter_grp[2]+'').index(this);
        for(c=Math.min(current_index,current_last_index);c<=Math.max(current_index,current_last_index);c++) {
          $('.db_sp_'+getclicked_debter_grp[2]+'').each(function(spctrlIdx, spctrlVal) {
            if(c == spctrlIdx) {
              if(c == current_index) {  
                current_val = $(spctrlVal).val();
              }
              $(spctrlVal).val(current_val); 
               $('#example tbody tr').eq(c).addClass("selected");
            }
          });
        }
      }

      if(event.altKey) {
        current_last_index = $('.db_sp_'+getclicked_debter_grp[2]+'').index(this);
        for(r=Math.min(current_index,current_last_index);r<=Math.max(current_index,current_last_index);r++) {
          $('.db_sp_'+getclicked_debter_grp[2]+'').each(function(spIdx, spVal) {
            if(r == spIdx) {
              $(spVal).val($(spVal).attr('default-value')); 
            }
          });
        }
      }
    }
  });

  $(document).on("click",".db_m_bp", function(event) {
    var ischecked_blkupdates = $("#chkbulkupdates").is(':checked');
    if(ischecked_blkupdates) {

      var getclassclicked = ($(this).attr("class")).split(" ");
      var getclicked_debter_grp = getclassclicked[2].split("_");
      
      if(event.ctrlKey) {
        current_last_index = $('.db_m_bp_'+getclicked_debter_grp[3]+'').index(this);
        for(c=Math.min(current_index,current_last_index);c<=Math.max(current_index,current_last_index);c++) {
          $('.db_m_bp_'+getclicked_debter_grp[3]+'').each(function(spctrlIdx, spctrlVal) {
            if(c == spctrlIdx) {
              if(c == current_index) {  
                current_val = $(spctrlVal).val();
              }
              $(spctrlVal).val(current_val); 
               $('#example tbody tr').eq(c).addClass("selected");
            }
          });
        }
      }

      if(event.altKey) {
        current_last_index = $('.db_m_bp_'+getclicked_debter_grp[3]+'').index(this);
        for(r=Math.min(current_index,current_last_index);r<=Math.max(current_index,current_last_index);r++) {
          $('.db_m_bp_'+getclicked_debter_grp[3]+'').each(function(spIdx, spVal) {
            if(r == spIdx) {
              $(spVal).val($(spVal).attr('default-value')); 
            }
          });
        }
      }
    }
  });

  $(document).on("click",".db_m_sp", function(event) {
    var ischecked_blkupdates = $("#chkbulkupdates").is(':checked');
    if(ischecked_blkupdates) {

      var getclassclicked = ($(this).attr("class")).split(" ");
      var getclicked_debter_grp = getclassclicked[2].split("_");
      
      if(event.ctrlKey) {
        current_last_index = $('.db_m_sp_'+getclicked_debter_grp[3]+'').index(this);
        for(c=Math.min(current_index,current_last_index);c<=Math.max(current_index,current_last_index);c++) {
          $('.db_m_sp_'+getclicked_debter_grp[3]+'').each(function(spctrlIdx, spctrlVal) {
            if(c == spctrlIdx) {
              if(c == current_index) {  
                current_val = $(spctrlVal).val();
              }
              $(spctrlVal).val(current_val); 
               $('#example tbody tr').eq(c).addClass("selected");
            }
          });
        }
      }

      if(event.altKey) {
        current_last_index = $('.db_m_sp_'+getclicked_debter_grp[3]+'').index(this);
        for(r=Math.min(current_index,current_last_index);r<=Math.max(current_index,current_last_index);r++) {
          $('.db_m_sp_'+getclicked_debter_grp[3]+'').each(function(spIdx, spVal) {
            if(r == spIdx) {
              $(spVal).val($(spVal).attr('default-value')); 
            }
          });
        }
      }
    }
  });

  $(document).on("click",".db_d_gp", function(event) {
    var ischecked_blkupdates = $("#chkbulkupdates").is(':checked');
    if(ischecked_blkupdates) {

      var getclassclicked = ($(this).attr("class")).split(" ");
      var getclicked_debter_grp = getclassclicked[2].split("_");
      
      if(event.ctrlKey) {
        current_last_index = $('.db_d_gp_'+getclicked_debter_grp[3]+'').index(this);
        for(c=Math.min(current_index,current_last_index);c<=Math.max(current_index,current_last_index);c++) {
          $('.db_d_gp_'+getclicked_debter_grp[3]+'').each(function(spctrlIdx, spctrlVal) {
            if(c == spctrlIdx) {
              if(c == current_index) {  
                current_val = $(spctrlVal).val();
              }
              $(spctrlVal).val(current_val); 
               $('#example tbody tr').eq(c).addClass("selected");
            }
          });
        }
      }

      if(event.altKey) {
        current_last_index = $('.db_d_gp_'+getclicked_debter_grp[3]+'').index(this);
        for(r=Math.min(current_index,current_last_index);r<=Math.max(current_index,current_last_index);r++) {
          $('.db_d_gp_'+getclicked_debter_grp[3]+'').each(function(spIdx, spVal) {
            if(r == spIdx) {
              $(spVal).val($(spVal).attr('default-value')); 
            }
          });
        }
      }
    }
  });

  // Bulk update
  

  $("#btnimport").click(function () {
    $("#formFile").val("");
  });


  $("#filter_with").change(function() {
    if($(this).val() != 10 && $(this).val() != 11 && $(this).val() != 12 ) {
      $("#hdn_filters").val($(this).val());
      table.draw(); 
    } else  {

      var stijging_filter_text = "";
      if($(this).val() == 10) {
        stijging_filter_text = "Stijging <=";
      } else if($(this).val() == 11) {
        stijging_filter_text = "Stijging between below 2 values (Enter 2 values seperated by |||)";
      } else if($(this).val() == 12) {
        stijging_filter_text = "Stijging >=";
      }

      var Stijging_text = prompt(stijging_filter_text);

      if(Stijging_text.length == 0) {
        alert("Field should not be blank");
        return false;
      }

      if (Stijging_text != null) {
        $("#hdn_stijging_text").val(Stijging_text);
        $("#hdn_filters").val($(this).val());
        table.draw(); 
      }
    }
  });

  $("#chkall").change(function() {
    var ischecked= $(this).is(':checked');
    if(ischecked) {
      $.ajax({
         url: document_root_url+'/scripts/process_data_price_management.php',
         method:"POST",
         data: ({ 
                  type: 'check_all_func'
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


   // Setup - add a text input to each footer cell
    $("#example tfoot th").each(function () {
        var title = $(this).text();
        //$(this).html('<input type="text" placeholder="Search ' + title + '" />');
    });
  
  $('#dtSearch').keyup(function () {
      $("#chkall").prop('checked', false);
      $("#check_all_cnt").html(0);
      table.search($(this).val()).draw();
  })
  
  $('#length_change').change(function () {
    table.page.len($(this).val()).draw();
  });


$(".show_cols").each(function () {
  //$(this).prop('checked', true);
});

var cols_selected = [];

$(".show_cols").change(function() {
  var ischecked= $(this).is(':checked');
  var checkedval = $(this).val();

  if(ischecked) {
    table.column(checkedval).visible(true);
    cols_selected.push(checkedval);
  } else {
    table.column(checkedval).visible(false);
  }
  $("table tr th").css({
    "width": "100px"
  });
  $("#example").width("100%");
  createCookie('column_cookie', JSON.stringify(cols_selected));
});

$(".show_cols_all").change(function() {
  var ischecked= $(this).is(':checked');
  
  if(ischecked) {
    $(".show_cols").each(function () {
      $(this).prop('checked', true);
      var checkedval = $(this).val();
      table.column(checkedval).visible(true);
    });
  } else {
    $(".show_cols").each(function () {
      $(this).prop('checked', false);
      var checkedval = $(this).val();
      table.column(checkedval).visible(false);
    });
  }
  $("table tr th").css({
    "width": "100px"
  });
  $("#example").width("100%");
});


// For Debter Individual
$(".show_deb_cols").change(function() {
  var ischecked= $(this).is(':checked');
  var checkedval = $(this).val();

  if(ischecked) {
    table.column(checkedval).visible(true);
    cols_selected.push(checkedval);
  } else {
    table.column(checkedval).visible(false);
  }
  $("table tr th").css({
    "width": "100px"
  });
  $("#example").width("100%");
  enableBulkFunc();
});


// For Debter Individual


// For Debter

$(".show_cols_dsp").each(function () {
  //$(this).prop('checked', true);
});

$(".show_cols_all_dsp").change(function() {
  var ischecked= $(this).is(':checked');
  if(ischecked) {
    $(".show_cols_dsp").each(function () {
      $(this).prop('checked', true);
      var checkedval = $(this).val();
      table.column(checkedval).visible(true);
    });
  } else {
    $(".show_cols_dsp").each(function () {
      $(this).prop('checked', false);
      var checkedval = $(this).val();
      table.column(checkedval).visible(false);
    });
  }
  $("table tr th").css({
    "width": "100px"
  });
  $("#example").width("100%");
  enableBulkFunc();
});


$(".show_cols_dmbp").each(function () {
 // $(this).prop('checked', true);
});

$(".show_cols_all_dmbp").change(function() {
  var ischecked= $(this).is(':checked');
  if(ischecked) {
    $(".show_cols_dmbp").each(function () {
      $(this).prop('checked', true);
      var checkedval = $(this).val();
      table.column(checkedval).visible(true);
    });
  } else {
    $(".show_cols_dmbp").each(function () {
      $(this).prop('checked', false);
      var checkedval = $(this).val();
      table.column(checkedval).visible(false);
    });
  }
  $("table tr th").css({
    "width": "100px"
  });
  $("#example").width("100%");
  enableBulkFunc();
});


$(".show_cols_dmsp").each(function () {
  //$(this).prop('checked', true);
});

$(".show_cols_all_dmsp").change(function() {
  var ischecked= $(this).is(':checked');
  if(ischecked) {
    $(".show_cols_dmsp").each(function () {
      $(this).prop('checked', true);
      var checkedval = $(this).val();
      table.column(checkedval).visible(true);
    });
  } else {
    $(".show_cols_dmsp").each(function () {
      $(this).prop('checked', false);
      var checkedval = $(this).val();
      table.column(checkedval).visible(false);
    });
  }
  $("table tr th").css({
    "width": "100px"
  });
  $("#example").width("100%");
  enableBulkFunc();
});

$(".show_cols_ddgp").each(function () {
 // $(this).prop('checked', true);
});

$(".show_cols_all_ddgp").change(function() {
  var ischecked= $(this).is(':checked');
  if(ischecked) {
    $(".show_cols_ddgp").each(function () {
      $(this).prop('checked', true);
      var checkedval = $(this).val();
      table.column(checkedval).visible(true);
    });
  } else {
    $(".show_cols_ddgp").each(function () {
      $(this).prop('checked', false);
      var checkedval = $(this).val();
      table.column(checkedval).visible(false);
    });
  }
  $("table tr th").css({
    "width": "100px"
  });
  $("#example").width("100%");
  enableBulkFunc();
});

// For Debter

$('#example tbody').on("click",".column_cat_filter a",function(e) {
  var index = $(this).closest('tr').index();
  var product_id = table.cells({ row: index, column: column_index["product_id"] }).data()[0];
  checkIt(false, false);
  var $li = $("li[data-id='" + product_category_id[product_id] + "']");
  checkGiven($li, true, true);
  table.ajax.reload( null, false ); 
});


$("#btnactivateimmed").click(function () {
  if(confirm("The updated products prices will be reflected on the webshop Immediately. Are you sure you want to continue?")) {
     $('<div class="alert alert-primary loader_all_export">Please wait <img src="images/loader.gif" /></div>').insertBefore("#data_filters");

      $.ajax({
         url: document_root_url+'/scripts/process_data_price_management.php',
         method:"POST",
         data: ({ 
                  type: 'immediate_update'
               }),
         success: function(response_data){
            var resp_obj = jQuery.parseJSON(response_data);
            if(resp_obj["msg"]) {
                table.ajax.reload( null, false );
                $('.loader_all_export').remove();
                $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");
                table.ajax.reload( null, false );
                window.setTimeout(function() {
                    $(".alert").fadeTo(500, 0).slideUp(500, function(){
                        $(this).remove(); 
                    });
                }, 4000);

            }      
         }
      });
  } 
});

$("#btnrefreshmagento").click(function() {
 if(confirm("This will Re-index Price and will clear magento's cache. Are you sure you want to proceed?")) {
  $('<div class="alert alert-primary loader_all_export">Please wait <img src="images/loader.gif" /></div>').insertBefore("#data_filters");
    $.ajax({
         url: document_root_url+'/scripts/process_data_price_management.php',
         method:"POST",
         data: ({ 
                  type: 'refresh_magento'
               }),
         success: function(response_data){
            var resp_obj = jQuery.parseJSON(response_data);
            if(resp_obj["msg"]) {
                table.ajax.reload( null, false );
                $('.loader_all_export').remove();
                $('<div class="alert alert-primary alert-dismissible fade show" role="alert">'+resp_obj["msg"]+'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>').insertBefore("#data_filters");

            }      
         }
    });
 }  
});

function createCookie(name, value, days) {
    var expires;
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    }
    else {
        expires = "";
    }
    document.cookie = name + "=" + value + expires + "; path=/";
}

function getCookie(c_name) {
    if (document.cookie.length > 0) {
        c_start = document.cookie.indexOf(c_name + "=");
        if (c_start != -1) {
            c_start = c_start + c_name.length + 1;
            c_end = document.cookie.indexOf(";", c_start);
            if (c_end == -1) {
                c_end = document.cookie.length;
            }
            return unescape(document.cookie.substring(c_start, c_end));
        }
    }
    return "";
}

 

 /* var isMouseDown = false;
  var isSelected;
  $('#example tbody').on('mousedown', 'tr', function(e){
     isMouseDown = true;
     $(this).toggleClass("selected");
     isSelected = $(this).hasClass("selected");
     //return false; 
  });

 $('#example tbody').on('mouseover', 'tr', function(e){
     if(isSelected) {
      if (isMouseDown) {
        $(this).toggleClass("selected", isSelected);
      }
    }
  });

  $(document).on('mouseup', '#example tbody', function(e){
     isSelected = false;
  }); */

  var lastChecked;

  //////////////////////////////////////////
// Row Selection
//////////////////////////////////////////
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

// Debter Code Starts //
$('#example tbody').on("keyup",".db_sp",function(e) {
  var keyCode = e.keyCode || e.which;

  if (keyCode == 9) {  
       $('#example tbody tr').eq($(this).closest('tr').index()).addClass("selected");
    }

  if (keyCode == 13) { 
    var ischecked = $("#chkbulkupdates").is(':checked');
    if(ischecked) {

      // Multiple Debter
      

      var getclassclicked = ($(this).attr("class")).split(" ");
      var getclicked_debter_grp = getclassclicked[2].split("_");
      var debter_digit = getclicked_debter_grp[2];
      var debter_number = "4027"+getclicked_debter_grp[2];


      //var selected_debter_column_index = "group_"+debter_number+"_debter_selling_price";
      
      var record_selected = table.rows('.selected').data().length;
      var multipledebtersellingPrices = Array();
        $.each( table.rows('.selected').data(), function( key, value ) {
          if(parseInt(value[column_index["group_"+debter_number+"_debter_selling_price"]]) != parseInt($("#db_sp_editable_column_"+debter_digit+"_"+value[column_index["product_id"]]).val())) {
              multipledebtersellingPrices[key] = {
                "product_id": value[column_index["product_id"]],
                "sku": value[column_index["sku"]],
                "supplier_gross_price": value[column_index["supplier_gross_price"]],
                "buying_price": value[column_index["buying_price"]],
                "deb_selling_price": $("#db_sp_editable_column_"+debter_digit+"_"+value[column_index["product_id"]]).val()
            };
          }
        });

        
        if(parseInt(multipledebtersellingPrices.filter(String).length) > 0) {
            if(confirm("Are you sure you want to edit Verkpr("+debter_number+") of "+multipledebtersellingPrices.filter(String).length+" products?")) {
              $.ajax({
                 url: document_root_url+'/scripts/process_data_price_management.php',
                 method:"POST",
                 data: ({ multipledebtersellingPrices: multipledebtersellingPrices, type: 'multiple_update_debter_selling_price',debter_number: debter_number}),
                 success: function(response_data){
                    var resp_obj = jQuery.parseJSON(response_data);
                    if(resp_obj["msg"]) {
                        table.ajax.reload( null, false );
                        $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");
                        window.setTimeout(function() {
                          $(".alert").fadeTo(500, 0).slideUp(500, function(){
                              $(this).remove(); 
                          });
                      }, 4000);
                    }      
                 }
              });
            }
          }

        // Multiple Debter

    } else { 
      var th = $('#example th').eq($(this).closest('td').index());
      var th_class = th.attr("class").split(" ");
      var debter_number = th_class[0];
      var debter_selling_price = $(this).val();
    
      var index = $(this).closest('tr').index();
      var product_id = table.cells({ row: index, column: column_index["product_id"] }).data()[0];
      var pmd_buying_price = table.cells({ row: index, column: column_index["buying_price"] }).data()[0];
      var supplier_gross_price = table.cells({ row: index, column: column_index["supplier_gross_price"] }).data()[0];

      $.ajax({
           url: document_root_url+'/scripts/process_data_price_management.php',
           method:"POST",
           data: ({ 
                    type: 'update_debter_selling_price',
                    product_id: product_id,
                    pmd_buying_price: pmd_buying_price,
                    debter_selling_price: debter_selling_price,
                    supplier_gross_price: supplier_gross_price,
                    debter_number: debter_number
                 }),
           success: function(response_data){
              var resp_obj = jQuery.parseJSON(response_data);
              if(resp_obj["msg"]) {
                  table.ajax.reload( null, false );
              }      
           }
        });
      }
   } 
});

$('#example tbody').on("keyup",".db_m_bp",function(e) {
  var keyCode = e.keyCode || e.which;

  if (keyCode == 9) {  
    $('#example tbody tr').eq($(this).closest('tr').index()).addClass("selected");
  }

  if (keyCode == 13) { 

    var ischecked = $("#chkbulkupdates").is(':checked');
    if(ischecked) {

      // Multiple Debter

      var getclassclicked = ($(this).attr("class")).split(" ");
      var getclicked_debter_grp = getclassclicked[2].split("_");
      var debter_digit = getclicked_debter_grp[3];
      var debter_number = "4027"+getclicked_debter_grp[3];

      
      var record_selected = table.rows('.selected').data().length;
      var multipledebprofitMargins = Array();

        $.each( table.rows('.selected').data(), function( key, value ) { 
          if(parseInt(value[column_index["group_"+debter_number+"_margin_on_buying_price"]]) != parseInt($("#db_m_bp_editable_column_"+debter_digit+"_"+value[column_index["product_id"]]).val())) {
            multipledebprofitMargins[key] = {
                "product_id": value[column_index["product_id"]],
                "sku": value[column_index["sku"]],
                "supplier_gross_price": value[column_index["supplier_gross_price"]],
                "buying_price": value[column_index["buying_price"]],
                "deb_m_bp": $("#db_m_bp_editable_column_"+debter_digit+"_"+value[column_index["product_id"]]).val()
            };
          }
        });

        
        if(parseInt(multipledebprofitMargins.filter(String).length) > 0) {
            if(confirm("Are you sure you want to edit Marge Inkpr %("+debter_number+") of "+multipledebprofitMargins.filter(String).length+" products?")) {
              $.ajax({
                 url: document_root_url+'/scripts/process_data_price_management.php',
                 method:"POST",
                 data: ({ multipledebprofitMargins: multipledebprofitMargins, type: 'multiple_update_debter_margin_on_buying_price',debter_number: debter_number}),
                 success: function(response_data){
                    var resp_obj = jQuery.parseJSON(response_data);
                    if(resp_obj["msg"]) {
                        table.ajax.reload( null, false );
                        $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");
                        window.setTimeout(function() {
                          $(".alert").fadeTo(500, 0).slideUp(500, function(){
                              $(this).remove(); 
                          });
                      }, 4000);
                    }      
                 }
              });
            }
          }

      // Multiple Debter


    } else {
    
    var th = $('#example th').eq($(this).closest('td').index());
    var th_class = th.attr("class").split(" ");
    var debter_number = th_class[0];
    
    
    var index = $(this).closest('tr').index();
    var product_id = table.cells({ row: index, column: column_index["product_id"] }).data()[0];
    var pmd_buying_price = table.cells({ row: index, column: column_index["buying_price"] }).data()[0];
    var supplier_gross_price = table.cells({ row: index, column: column_index["supplier_gross_price"] }).data()[0];
    var debter_margin_on_buying_price = $(this).val();


    $.ajax({
         url: document_root_url+'/scripts/process_data_price_management.php',
         method:"POST",
         data: ({ 
                  type: 'update_debter_margin_on_buying_price',
                  product_id: product_id,
                  pmd_buying_price: pmd_buying_price,
                  debter_margin_on_buying_price: debter_margin_on_buying_price,
                  supplier_gross_price: supplier_gross_price,
                  debter_number: debter_number
               }),
         success: function(response_data){
            var resp_obj = jQuery.parseJSON(response_data);
            if(resp_obj["msg"]) {
                table.ajax.reload( null, false );
            }      
         }
      });
    }
  } 
});

$('#example tbody').on("keyup",".db_m_sp",function(e) {
  var keyCode = e.keyCode || e.which;

  if (keyCode == 9) {  
    $('#example tbody tr').eq($(this).closest('tr').index()).addClass("selected");
  }

  if (keyCode == 13) { 
    
    var ischecked = $("#chkbulkupdates").is(':checked');
    if(ischecked) {
      // Multiple Debter

      var getclassclicked = ($(this).attr("class")).split(" ");
      var getclicked_debter_grp = getclassclicked[2].split("_");
      var debter_digit = getclicked_debter_grp[3];
      var debter_number = "4027"+getclicked_debter_grp[3];      
      var record_selected = table.rows('.selected').data().length;
      var multipledebprofitMarginsOnSP = Array();

      $.each( table.rows('.selected').data(), function( key, value ) {
        if(parseInt(value[column_index["group_"+debter_number+"_margin_on_selling_price"]]) != parseInt($("#db_m_sp_editable_column_"+debter_digit+"_"+value[column_index["product_id"]]).val())) {
         multipledebprofitMarginsOnSP[key] = {
              "product_id": value[column_index["product_id"]],
              "sku": value[column_index["sku"]],
              "supplier_gross_price": value[column_index["supplier_gross_price"]],
              "buying_price": value[column_index["buying_price"]],
              "deb_m_sp": $("#db_m_sp_editable_column_"+debter_digit+"_"+value[column_index["product_id"]]).val()
          };
        }
      });

      
      if(parseInt(multipledebprofitMarginsOnSP.filter(String).length) > 0) {
            if(confirm("Are you sure you want to edit Marge Verkpr %("+debter_number+") of "+multipledebprofitMarginsOnSP.filter(String).length+" products?")) {
              $.ajax({
                 url: document_root_url+'/scripts/process_data_price_management.php',
                 method:"POST",
                 data: ({ multipledebprofitMarginsOnSP: multipledebprofitMarginsOnSP, type: 'multiple_update_debter_margin_on_selling_price',debter_number: debter_number}),
                 success: function(response_data){
                    var resp_obj = jQuery.parseJSON(response_data);
                    if(resp_obj["msg"]) {
                        table.ajax.reload( null, false );
                        $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");
                        window.setTimeout(function() {
                          $(".alert").fadeTo(500, 0).slideUp(500, function(){
                              $(this).remove(); 
                          });
                      }, 4000);
                    }      
                 }
              });
            }
          }
      // Multiple Debter      
    } else {
    var th = $('#example th').eq($(this).closest('td').index());
    var th_class = th.attr("class").split(" ");
    var debter_number = th_class[0];
    
    
    var index = $(this).closest('tr').index();
    var product_id = table.cells({ row: index, column: column_index["product_id"] }).data()[0];
    var pmd_buying_price = table.cells({ row: index, column: column_index["buying_price"] }).data()[0];
    var supplier_gross_price = table.cells({ row: index, column: column_index["supplier_gross_price"] }).data()[0];
    var debter_margin_on_selling_price = $(this).val();


    $.ajax({
         url: document_root_url+'/scripts/process_data_price_management.php',
         method:"POST",
         data: ({ 
                  type: 'update_debter_margin_on_selling_price',
                  product_id: product_id,
                  pmd_buying_price: pmd_buying_price,
                  debter_margin_on_selling_price: debter_margin_on_selling_price,
                  supplier_gross_price: supplier_gross_price,
                  debter_number: debter_number
               }),
         success: function(response_data){
            var resp_obj = jQuery.parseJSON(response_data);
            if(resp_obj["msg"]) {
                table.ajax.reload( null, false );
            }      
         }
      });
    }
  } 
});

$('#example tbody').on("keyup",".db_d_gp",function(e) {
  var keyCode = e.keyCode || e.which;

  if (keyCode == 9) {  
    $('#example tbody tr').eq($(this).closest('tr').index()).addClass("selected");
  }

  if (keyCode == 13) { 

    var ischecked = $("#chkbulkupdates").is(':checked');
    if(ischecked) {
      // Multiple Debter
      var getclassclicked = ($(this).attr("class")).split(" ");
      var getclicked_debter_grp = getclassclicked[2].split("_");
      var debter_digit = getclicked_debter_grp[3];
      var debter_number = "4027"+getclicked_debter_grp[3];      
      var record_selected = table.rows('.selected').data().length;
      var multipledebDiscountOnGP = Array();


      $.each( table.rows('.selected').data(), function( key, value ) {
        if(parseInt(value[column_index["group_"+debter_number+"_discount_on_grossprice_b_on_deb_selling_price"]]) != parseInt($("#db_d_gp_editable_column_"+debter_digit+"_"+value[column_index["product_id"]]).val())) {
         multipledebDiscountOnGP[key] = {
              "product_id": value[column_index["product_id"]],
              "sku": value[column_index["sku"]],
              "supplier_gross_price": value[column_index["supplier_gross_price"]],
              "buying_price": value[column_index["buying_price"]],
              "deb_d_gp": $("#db_d_gp_editable_column_"+debter_digit+"_"+value[column_index["product_id"]]).val()
          };
        }
      });

      
      if(parseInt(multipledebDiscountOnGP.filter(String).length) > 0) {
        if(confirm("Are you sure you want to edit Korting Brutpr %("+debter_number+") of "+multipledebDiscountOnGP.filter(String).length+" products?")) {
          $.ajax({
             url: document_root_url+'/scripts/process_data_price_management.php',
             method:"POST",
             data: ({ multipledebDiscountOnGP: multipledebDiscountOnGP, type: 'multiple_update_debter_discount_on_gross_price',debter_number: debter_number}),
             success: function(response_data){
                var resp_obj = jQuery.parseJSON(response_data);
                if(resp_obj["msg"]) {
                    table.ajax.reload( null, false );
                    $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");
                    window.setTimeout(function() {
                      $(".alert").fadeTo(500, 0).slideUp(500, function(){
                          $(this).remove(); 
                      });
                  }, 4000);
                }      
             }
          });
        }
      }

      // Multiple Debter      
    } else {
    var th = $('#example th').eq($(this).closest('td').index());
    var th_class = th.attr("class").split(" ");
    var debter_number = th_class[0];
    
    
    var index = $(this).closest('tr').index();
    var product_id = table.cells({ row: index, column: column_index["product_id"] }).data()[0];
    var pmd_buying_price = table.cells({ row: index, column: column_index["buying_price"] }).data()[0];
    var supplier_gross_price = table.cells({ row: index, column: column_index["supplier_gross_price"] }).data()[0];
    var debter_discount_on_gross_price = $(this).val();


      $.ajax({
         url: document_root_url+'/scripts/process_data_price_management.php',
         method:"POST",
         data: ({ 
                  type: 'update_debter_discount_on_gross_price',
                  product_id: product_id,
                  pmd_buying_price: pmd_buying_price,
                  debter_discount_on_gross_price: debter_discount_on_gross_price,
                  supplier_gross_price: supplier_gross_price,
                  debter_number: debter_number
               }),
         success: function(response_data){
            var resp_obj = jQuery.parseJSON(response_data);
            if(resp_obj["msg"]) {
                table.ajax.reload( null, false );
            }      
         }
      });
    }
  } 
});
// Debter Code Ends //





  $('#example tbody').on("keyup",".selling_price",function(e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode == 9) {  
       $('#example tbody tr').eq($(this).closest('tr').index()).addClass("selected");
    }

    if (keyCode == 13) {
      var ischecked = $("#chkbulkupdates").is(':checked');
      if(ischecked) {
        // Bulk Update Feature

          var record_selected = table.rows('.selected').data().length;
          var multipleSellingPrices = Array();
          $.each( table.rows('.selected').data(), function( key, value ) {
            if(parseInt(value[column_index["selling_price"]]) != parseInt($("#sp_editable_column_"+value[column_index["product_id"]]).val())) {
              multipleSellingPrices[key] = {
                  "product_id": value[column_index["product_id"]],
                  "sku": value[column_index["sku"]],

                  "webshop_supplier_buying_price": value[column_index["webshop_supplier_buying_price"]],
                  "webshop_supplier_gross_price": value[column_index["webshop_supplier_gross_price"]],
                  "webshop_idealeverpakking": value[column_index["webshop_idealeverpakking"]],
                  "webshop_afwijkenidealeverpakking": value[column_index["webshop_afwijkenidealeverpakking"]],
                  "gyzs_selling_price": value[column_index["gyzs_selling_price"]],

                  "buying_price": value[column_index["buying_price"]],
                  "supplier_gross_price": value[column_index["supplier_gross_price"]],
                  "idealeverpakking": value[column_index["idealeverpakking"]],
                  "afwijkenidealeverpakking": value[column_index["afwijkenidealeverpakking"]],
                  "selling_price": $("#sp_editable_column_"+value[column_index["product_id"]]).val() 
              };
            } 
          });

          if(parseInt(multipleSellingPrices.filter(String).length) > 0) {
            if(confirm("Are you sure you want to edit PM Vkpr of "+multipleSellingPrices.filter(String).length+" products?")) {
              $.ajax({
                 url: document_root_url+'/scripts/process_data_price_management.php',
                 method:"POST",
                 data: ({ multipleSellingPrices: multipleSellingPrices, type: 'update_multiple_selling_prices'}),
                 success: function(response_data){
                    var resp_obj = jQuery.parseJSON(response_data);
                    if(resp_obj["msg"]) {
                        table.ajax.reload( null, false );
                        $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");
                        window.setTimeout(function() {
                          $(".alert").fadeTo(500, 0).slideUp(500, function(){
                              $(this).remove(); 
                          });
                      }, 4000);
                    }      
                 }
              });
            }
          }
        // Bulk Update Feature
      } else {
       var index = $(this).closest('tr').index();
       var product_id = table.cells({ row: index, column: column_index["product_id"] }).data()[0];
       var product_price = parseFloat($(this).val());

       var buying_price = table.cells({ row: index, column: column_index["gyzs_buying_price"] }).data()[0];
       var webshop_supplier_gross_price = table.cells({ row: index, column: column_index["webshop_supplier_gross_price"] }).data()[0];
       var webshop_idealeverpakking = table.cells({ row: index, column: column_index["webshop_idealeverpakking"] }).data()[0];
       var webshop_afwijkenidealeverpakking = table.cells({ row: index, column: column_index["webshop_afwijkenidealeverpakking"] }).data()[0];
       var webshop_selling_price = table.cells({ row: index, column: column_index["gyzs_selling_price"] }).data()[0];
       
       
       var pmd_buying_price = table.cells({ row: index, column: column_index["buying_price"] }).data()[0];
       var supplier_gross_price = table.cells({ row: index, column: column_index["supplier_gross_price"] }).data()[0];
       var idealeverpakking = table.cells({ row: index, column: column_index["idealeverpakking"] }).data()[0]
       var afwijkenidealeverpakking = table.cells({ row: index, column: column_index["afwijkenidealeverpakking"] }).data()[0];
       var prev_selling_price = table.cells({ row: index, column: column_index["selling_price"] }).data()[0];
       
       if(prev_selling_price == product_price.toFixed(4)) {
         return false;
       }
       
      
       $.ajax({
         url: document_root_url+'/scripts/process_data_price_management.php',
         method:"POST",
         data: ({ product_price: product_price,
                  type: 'update_selling_price',
                  product_id: product_id,
                  
                  buying_price: buying_price,
                  webshop_supplier_gross_price: webshop_supplier_gross_price,
                  webshop_idealeverpakking: webshop_idealeverpakking,
                  webshop_afwijkenidealeverpakking: webshop_afwijkenidealeverpakking,
                  webshop_selling_price: webshop_selling_price,

                  pmd_buying_price: pmd_buying_price,
                  supplier_gross_price: supplier_gross_price,
                  idealeverpakking: idealeverpakking,
                  afwijkenidealeverpakking : afwijkenidealeverpakking
               }),
         success: function(response_data){
            var resp_obj = jQuery.parseJSON(response_data);
            if(resp_obj["msg"]) {
                table.ajax.reload( null, false );
                
            }      
         }
      });
     }
    } 
  });

 $('#example tbody').on("keyup",".profit_margin",function(e) {
    var keyCode = e.keyCode || e.which;

    if (keyCode == 9) {  
       $('#example tbody tr').eq($(this).closest('tr').index()).addClass("selected");
    }

    if (keyCode == 13) {
      var ischecked = $("#chkbulkupdates").is(':checked');
      if(ischecked) {
        // Bulk Update Feature
          var multipleprofitMargins = Array();
          var record_selected = table.rows('.selected').data().length;
          $.each( table.rows('.selected').data(), function( key, value ) {
            if(parseInt(value[column_index["profit_percentage"]]) != parseInt($("#pm_bp_editable_column_"+value[column_index["product_id"]]).val())) {
              multipleprofitMargins[key] = {
                  "product_id": value[column_index["product_id"]],
                  "sku": value[column_index["sku"]],

                  "webshop_supplier_buying_price": value[column_index["webshop_supplier_buying_price"]],
                  "webshop_supplier_gross_price": value[column_index["webshop_supplier_gross_price"]],
                  "webshop_idealeverpakking": value[column_index["webshop_idealeverpakking"]],
                  "webshop_afwijkenidealeverpakking": value[column_index["webshop_afwijkenidealeverpakking"]],
                  "gyzs_selling_price": value[column_index["gyzs_selling_price"]],

                  "buying_price": value[column_index["buying_price"]],
                  "supplier_gross_price": value[column_index["supplier_gross_price"]],
                  "idealeverpakking": value[column_index["idealeverpakking"]],
                  "afwijkenidealeverpakking": value[column_index["afwijkenidealeverpakking"]],
                  "selling_price":  value[column_index["selling_price"]],

                  "profit_percentage":  $("#pm_bp_editable_column_"+value[column_index["product_id"]]).val() 
              };
            }
          });

          if(parseInt(multipleprofitMargins.filter(String).length) > 0) {
            if(confirm("Are you sure you want to edit Marge Inkpr % of "+multipleprofitMargins.filter(String).length+" products?")) {
              $.ajax({
                 url: document_root_url+'/scripts/process_data_price_management.php',
                 method:"POST",
                 data: ({ multipleprofitMargins: multipleprofitMargins, type: 'update_multiple_profit_margin'}),
                 success: function(response_data){
                    var resp_obj = jQuery.parseJSON(response_data);
                    if(resp_obj["msg"]) {
                        table.ajax.reload( null, false );
                        $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");
                        window.setTimeout(function() {
                          $(".alert").fadeTo(500, 0).slideUp(500, function(){
                              $(this).remove(); 
                          });
                      }, 4000);
                    }      
                 }
              });
            }
          }
        // Bulk Update Feature
      } else {
       var index = $(this).closest('tr').index();
       var product_id = table.cells({ row: index, column: column_index["product_id"] }).data()[0];
       var profit_margin = parseFloat($(this).val());

       var buying_price = table.cells({ row: index, column: column_index["gyzs_buying_price"] }).data()[0];
       var webshop_supplier_gross_price = table.cells({ row: index, column: column_index["webshop_supplier_gross_price"] }).data()[0];
       var webshop_idealeverpakking = table.cells({ row: index, column: column_index["webshop_idealeverpakking"] }).data()[0];
       var webshop_afwijkenidealeverpakking = table.cells({ row: index, column: column_index["webshop_afwijkenidealeverpakking"] }).data()[0];
       var webshop_selling_price = table.cells({ row: index, column: column_index["gyzs_selling_price"] }).data()[0];

       var pmd_buying_price = table.cells({ row: index, column: column_index["buying_price"] }).data()[0];
       var supplier_gross_price = table.cells({ row: index, column: column_index["supplier_gross_price"] }).data()[0];
       var idealeverpakking = table.cells({ row: index, column: column_index["idealeverpakking"] }).data()[0];
       var afwijkenidealeverpakking = table.cells({ row: index, column: column_index["afwijkenidealeverpakking"] }).data()[0];
       var prev_profit_margin = table.cells({ row: index, column: column_index["profit_percentage"] }).data()[0];
       

       if(prev_profit_margin == profit_margin.toFixed(4)) {
         return false;
       }


       $.ajax({
         url: document_root_url+'/scripts/process_data_price_management.php',
         method:"POST",
         data: ({ profit_margin: profit_margin,
                  type: 'update_profit_margin',
                  product_id: product_id,

                  buying_price: buying_price,
                  webshop_supplier_gross_price: webshop_supplier_gross_price,
                  webshop_idealeverpakking: webshop_idealeverpakking,
                  webshop_afwijkenidealeverpakking: webshop_afwijkenidealeverpakking,
                  webshop_selling_price:webshop_selling_price,
                  
                  pmd_buying_price: pmd_buying_price,  
                  supplier_gross_price: supplier_gross_price,
                  idealeverpakking: idealeverpakking,
                  afwijkenidealeverpakking : afwijkenidealeverpakking
                  
                }),
         success: function(response_data){
            var resp_obj = jQuery.parseJSON(response_data);
            if(resp_obj["msg"]) {
                table.ajax.reload( null, false );
                
            }      
         }
      });
    }
  } 
 });

$('#example tbody').on("keyup",".profit_margin_sp",function(e) {
    var keyCode = e.keyCode || e.which; 

    if (keyCode == 9) {  
       $('#example tbody tr').eq($(this).closest('tr').index()).addClass("selected");
    }


    if (keyCode == 13) { 
       
      var ischecked = $("#chkbulkupdates").is(':checked');
      if(ischecked) {
        // Bulk Update Feature
          var multipleprofitMarginsSP = Array();
        var record_selected = table.rows('.selected').data().length;
          $.each( table.rows('.selected').data(), function( key, value ) {
            if(parseInt(value[column_index["profit_percentage_selling_price"]]) != parseInt($("#pm_sp_editable_column_"+value[column_index["product_id"]]).val())) {
             multipleprofitMarginsSP[key] = {
                  "product_id": value[column_index["product_id"]],
                  "sku": value[column_index["sku"]],

                  "webshop_supplier_buying_price": value[column_index["webshop_supplier_buying_price"]],
                  "webshop_supplier_gross_price": value[column_index["webshop_supplier_gross_price"]],
                  "webshop_idealeverpakking": value[column_index["webshop_idealeverpakking"]],
                  "webshop_afwijkenidealeverpakking": value[column_index["webshop_afwijkenidealeverpakking"]],
                  "gyzs_selling_price": value[column_index["gyzs_selling_price"]],

                  
                  "buying_price": value[column_index["buying_price"]],
                  "supplier_gross_price": value[column_index["supplier_gross_price"]],
                  "idealeverpakking": value[column_index["idealeverpakking"]],
                  "afwijkenidealeverpakking": value[column_index["afwijkenidealeverpakking"]],
                  "selling_price":  value[column_index["selling_price"]],
                  "profit_percentage_selling_price":  $("#pm_sp_editable_column_"+value[column_index["product_id"]]).val() 
              };
            }
          });

          
          if(parseInt(multipleprofitMarginsSP.filter(String).length) > 0) {
            if(confirm("Are you sure you want to edit Marge Verkpr % of "+multipleprofitMarginsSP.filter(String).length+" products?")) {
              $.ajax({
                 url: document_root_url+'/scripts/process_data_price_management.php',
                 method:"POST",
                 data: ({ multipleprofitMarginsSP: multipleprofitMarginsSP, type: 'update_multiple_profit_margin_on_sp'}),
                 success: function(response_data){
                    var resp_obj = jQuery.parseJSON(response_data);
                    if(resp_obj["msg"]) {
                        table.ajax.reload( null, false );
                        $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");
                        window.setTimeout(function() {
                          $(".alert").fadeTo(500, 0).slideUp(500, function(){
                              $(this).remove(); 
                          });
                      }, 4000);
                    }      
                 }
              });
            }
          }  

        // Bulk Update Feature
      } else {  
       var index = $(this).closest('tr').index();
       var product_id = table.cells({ row: index, column: column_index["product_id"] }).data()[0];
       var profit_margin_sp = parseFloat($(this).val());


       var buying_price = table.cells({ row: index, column: column_index["gyzs_buying_price"] }).data()[0];
       var webshop_supplier_gross_price = table.cells({ row: index, column: column_index["webshop_supplier_gross_price"] }).data()[0];
       var webshop_idealeverpakking = table.cells({ row: index, column: column_index["webshop_idealeverpakking"] }).data()[0];
       var webshop_afwijkenidealeverpakking = table.cells({ row: index, column: column_index["webshop_afwijkenidealeverpakking"] }).data()[0];
       var webshop_selling_price = table.cells({ row: index, column: column_index["gyzs_selling_price"] }).data()[0];
       

       var pmd_buying_price = table.cells({ row: index, column: column_index["buying_price"] }).data()[0];
       var supplier_gross_price = table.cells({ row: index, column: column_index["supplier_gross_price"] }).data()[0];
       var idealeverpakking = table.cells({ row: index, column: column_index["idealeverpakking"] }).data()[0];
       var afwijkenidealeverpakking = table.cells({ row: index, column: column_index["afwijkenidealeverpakking"] }).data()[0];

       var prev_profit_margin_sp = table.cells({ row: index, column: column_index["profit_percentage_selling_price"] }).data()[0];

       if(prev_profit_margin_sp == profit_margin_sp.toFixed(4)) {
         return false;
       }


       $.ajax({
         url: document_root_url+'/scripts/process_data_price_management.php',
         method:"POST",
         data: ({ profit_margin_sp: profit_margin_sp,
                  type: 'update_profit_margin_sp',
                  product_id: product_id,
                  
                  buying_price: buying_price,
                  webshop_supplier_gross_price: webshop_supplier_gross_price,
                  webshop_idealeverpakking: webshop_idealeverpakking,
                  webshop_afwijkenidealeverpakking: webshop_afwijkenidealeverpakking,
                  webshop_selling_price:webshop_selling_price,

                  
                  pmd_buying_price: pmd_buying_price,
                  supplier_gross_price: supplier_gross_price,
                  idealeverpakking: idealeverpakking,
                  afwijkenidealeverpakking : afwijkenidealeverpakking
                }),
         success: function(response_data){
            var resp_obj = jQuery.parseJSON(response_data);
            if(resp_obj["msg"]) {
                table.ajax.reload( null, false );
                
            }      
         }
       });
      }
    } 
 });
 


 $('#example tbody').on("keyup",".discount_on_gross",function(e) {
    var keyCode = e.keyCode || e.which;

    if (keyCode == 9) {  
       $('#example tbody tr').eq($(this).closest('tr').index()).addClass("selected");
    }

    if (keyCode == 13) { 
       
       var ischecked = $("#chkbulkupdates").is(':checked');
       if(ischecked) {
          // Bulk Update Feature
          var multiplediscountPercentages = Array();
          var record_selected = table.rows('.selected').data().length;
            $.each( table.rows('.selected').data(), function( key, value ) {
              if(parseInt(value[column_index["discount_on_gross_price"]]) != parseInt($("#discount_on_gross_editable_column_"+value[column_index["product_id"]]).val())) {
                multiplediscountPercentages[key] = {
                    "product_id": value[column_index["product_id"]],
                    "sku": value[column_index["sku"]],
                    
                    "webshop_supplier_buying_price": value[column_index["webshop_supplier_buying_price"]],
                    "webshop_supplier_gross_price": value[column_index["webshop_supplier_gross_price"]],
                    "webshop_idealeverpakking": value[column_index["webshop_idealeverpakking"]],
                    "webshop_afwijkenidealeverpakking": value[column_index["webshop_afwijkenidealeverpakking"]],
                    "gyzs_selling_price": value[column_index["gyzs_selling_price"]],

                    "buying_price": value[column_index["buying_price"]],
                    "supplier_gross_price": value[column_index["supplier_gross_price"]],
                    "idealeverpakking": value[column_index["idealeverpakking"]],
                    "afwijkenidealeverpakking": value[column_index["afwijkenidealeverpakking"]],
                    "selling_price":  value[column_index["selling_price"]],
                    "discount_on_gross_price":  $("#discount_on_gross_editable_column_"+value[column_index["product_id"]]).val() 
                };
              }
            });

            
            if(parseInt(multiplediscountPercentages.filter(String).length) > 0) {
            if(confirm("Are you sure you want to edit Korting Brupr % of "+multiplediscountPercentages.filter(String).length+" products?")) {
              $.ajax({
                 url: document_root_url+'/scripts/process_data_price_management.php',
                 method:"POST",
                 data: ({ multiplediscountPercentages: multiplediscountPercentages, type: 'update_multiple_discount'}),
                 success: function(response_data){
                    var resp_obj = jQuery.parseJSON(response_data);
                    if(resp_obj["msg"]) {
                        table.ajax.reload( null, false );
                        $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");
                        window.setTimeout(function() {
                          $(".alert").fadeTo(500, 0).slideUp(500, function(){
                              $(this).remove(); 
                          });
                      }, 4000);
                    }      
                 }
              });
            }
          }
        // Bulk Update Feature
       } else {
       var index = $(this).closest('tr').index();
       var product_id = table.cells({ row: index, column: column_index["product_id"] }).data()[0];
       var discount_percentage = parseFloat($(this).val());
       
       var buying_price = table.cells({ row: index, column: column_index["gyzs_buying_price"] }).data()[0];
       var webshop_supplier_gross_price = table.cells({ row: index, column: column_index["webshop_supplier_gross_price"] }).data()[0];
       var webshop_idealeverpakking = table.cells({ row: index, column: column_index["webshop_idealeverpakking"] }).data()[0];
       var webshop_afwijkenidealeverpakking = table.cells({ row: index, column: column_index["webshop_afwijkenidealeverpakking"] }).data()[0];
       var webshop_selling_price = table.cells({ row: index, column: column_index["gyzs_selling_price"] }).data()[0];

       
       var pmd_buying_price = table.cells({ row: index, column: column_index["buying_price"] }).data()[0];
       var supplier_gross_price = table.cells({ row: index, column: column_index["supplier_gross_price"] }).data()[0];
       var idealeverpakking = table.cells({ row: index, column: column_index["idealeverpakking"] }).data()[0];
       var afwijkenidealeverpakking = table.cells({ row: index, column: column_index["afwijkenidealeverpakking"] }).data()[0];

       var prev_discount_on_gross = table.cells({ row: index, column: column_index["discount_on_gross_price"] }).data()[0];

        if(prev_discount_on_gross == discount_percentage.toFixed(4)) {
         return false;
        }
       

       $.ajax({
         url: document_root_url+'/scripts/process_data_price_management.php',
         method:"POST",
         data: ({ discount_percentage: discount_percentage,
                  type: 'update_discount',
                  product_id: product_id,
                  
                  buying_price: buying_price,
                  webshop_supplier_gross_price: webshop_supplier_gross_price,
                  webshop_idealeverpakking: webshop_idealeverpakking,
                  webshop_afwijkenidealeverpakking: webshop_afwijkenidealeverpakking,
                  webshop_selling_price: webshop_selling_price,


                  pmd_buying_price: pmd_buying_price,
                  supplier_gross_price: supplier_gross_price,
                  idealeverpakking: idealeverpakking,
                  afwijkenidealeverpakking : afwijkenidealeverpakking
                }),
         success: function(response_data){
            var resp_obj = jQuery.parseJSON(response_data);
            if(resp_obj["msg"]) {
                table.ajax.reload( null, false );

            }      
         }
      });
    }
  } 
});

 $('#btnsellingprice, #btnprofitmargin, #btndiscount, #btnprofitmarginsp').click( function () {
    var record_selected = table.rows('.selected').data().length;
    $('#setsellingprice').val("");
    $('#setprofitmargin').val("");
    $('#setdiscount').val("");
    $("#p_s_p").html("+");
    $("#p_s_p").removeClass("p_s_p_neg");
    $("#p_s_p").addClass("p_s_p_pos");
    if(record_selected == 0) {
        alert("Please select record first!!");
        return false;
    } else {
      if($(this).attr("id") == "btnsellingprice") {
        $('#SellingPriceModal').modal('toggle');
      } else if($(this).attr("id") == "btnprofitmargin") {
        $('#ProfitMarginModal').modal('toggle');
      } else if($(this).attr("id") == "btnprofitmarginsp") {
        $('#ProfitMarginSPModal').modal('toggle');
      } else if($(this).attr("id") == "btndiscount") {
        $('#DiscountModal').modal('toggle');
      }
    }
  });

 $('#btnactivate').click( function () {
    $.ajax({
     url: document_root_url+'/scripts/process_data_price_management.php',
     data: ({ type: 'check_activate'}),
     success: function(response_data){
        var resp_obj = jQuery.parseJSON(response_data);
        if(resp_obj["msg"]) {
          if(resp_obj["msg"] == 0) {
            alert("Data is not updated yet!! First, please update any data");
            return false;
          } else {
            // table.ajax.reload( null, false );
            $('#ActivateModal').modal('toggle');
            $('#total_updated_records').html("<b>"+resp_obj["msg"]+"</b>");
          } 
        }      
      }
    });
 });

 $('#confirmedActivated').click(function() {

   $("#ActivateModal").css("opacity",0.8);
   $("#loading-img").css({"display": "block"});

   $.ajax({
     url: document_root_url+'/scripts/process_data_price_management.php',
     data: ({ type: 'confirm_activate'}),
     success: function(response_data){
        var resp_obj = jQuery.parseJSON(response_data);
        if(resp_obj["msg"]) {
          if(resp_obj["msg"] == "Success") {
            $("#ActivateModal").css("opacity",1);
            $("#loading-img").css({"display": "none"});
            $('#ActivateModal').modal('toggle');
            table.ajax.reload( null, false );
          } 
        }      
      }
    });
 });

$('#SellingPriceModal, #ProfitMarginModal, #DiscountModal, #modalHistory, #DebterModal').draggable({
        handle: ".modal-header"
 });

 $('#setsellingprice').on("keyup",function(e) {
    var keyCode = e.keyCode || e.which; 
    if (keyCode == 13) {
        $(".update_loader").show(); 
        var selling_percentage = $("#setsellingprice").val();
        var sellingPrices = Array();
        var positive_or_negative = $("#p_s_p").html();
        var sel_cust_group = $("#sel_cust_group").val();
        var isAllChecked = 0;
        if($("#chkall").is(':checked')) {
          var isAllChecked = 1;
        }


        if(sel_cust_group == "") {
          var record_selected = table.rows('.selected').data().length;
          $.each( table.rows('.selected').data(), function( key, value ) {
             sellingPrices[key] = {
                  "product_id": value[column_index["product_id"]],
                  "sku": value[column_index["sku"]],

                  "webshop_supplier_buying_price": value[column_index["webshop_supplier_buying_price"]],
                  "webshop_supplier_gross_price": value[column_index["webshop_supplier_gross_price"]],
                  "webshop_idealeverpakking": value[column_index["webshop_idealeverpakking"]],
                  "webshop_afwijkenidealeverpakking": value[column_index["webshop_afwijkenidealeverpakking"]],
                  "gyzs_selling_price": value[column_index["gyzs_selling_price"]],

                  "buying_price": value[column_index["buying_price"]],
                  "supplier_gross_price": value[column_index["supplier_gross_price"]],
                  "idealeverpakking": value[column_index["idealeverpakking"]],
                  "afwijkenidealeverpakking": value[column_index["afwijkenidealeverpakking"]],
                  "selling_price":  value[column_index["selling_price"]]

              };
          });

          $.ajax({
           url: document_root_url+'/scripts/process_data_price_management.php',
           method:"POST",
           data: ({ sellingPrices: sellingPrices, type: 'bulk_update_selling_price', positive_or_negative: positive_or_negative, isAllChecked: isAllChecked, selling_percentage: selling_percentage}),
           success: function(response_data){
              var resp_obj = jQuery.parseJSON(response_data);
              if(resp_obj["msg"]) {
                  table.ajax.reload( null, false );
                  $(".update_loader").hide(); 
                  $('#SellingPriceModal').modal('toggle');
                  $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");
                  window.setTimeout(function() {
                    $(".alert").fadeTo(500, 0).slideUp(500, function(){
                        $(this).remove(); 
                    });
                }, 4000);
              }      
           }
        });
      } else {
        // For Debter
        var debter_number = $("#sel_cust_group option:selected").text();
        var selected_debter_column_index = "group_"+debter_number+"_debter_selling_price";
        
        var record_selected = table.rows('.selected').data().length;
          $.each( table.rows('.selected').data(), function( key, value ) {
             sellingPrices[key] = {
                  "product_id": value[column_index["product_id"]],
                  "sku": value[column_index["sku"]],
                  "supplier_gross_price": value[column_index["supplier_gross_price"]],
                  "buying_price": value[column_index["buying_price"]],
                  [selected_debter_column_index] :  value[column_index[selected_debter_column_index]]

              };
          });

          $.ajax({
           url: document_root_url+'/scripts/process_data_price_management.php',
           method:"POST",
           data: ({ sellingPrices: sellingPrices, type: 'bulk_update_debter_selling_price', positive_or_negative: positive_or_negative, debter_number: debter_number, selling_percentage: selling_percentage, isAllChecked: isAllChecked}),
           success: function(response_data){
              var resp_obj = jQuery.parseJSON(response_data);
              if(resp_obj["msg"]) {
                  table.ajax.reload( null, false );
                  $(".update_loader").hide(); 
                  $('#SellingPriceModal').modal('toggle');
                  $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");
                  window.setTimeout(function() {
                    $(".alert").fadeTo(500, 0).slideUp(500, function(){
                        $(this).remove(); 
                    });
                }, 4000);
              }      
            }
          });
      }
    }
 });

 $('#setprofitmargin').on("keyup",function(e) {
    var keyCode = e.keyCode || e.which; 
    if (keyCode == 13) {
        $(".update_loader").show(); 
        var profit_margin = $("#setprofitmargin").val();
        var profitMargins = Array();
        var sel_cust_group = $("#sel_cust_group_pm_bp").val();
        var isAllChecked = 0;
        if($("#chkall").is(':checked')) {
          var isAllChecked = 1;
        }

        if(sel_cust_group == "") {
          var record_selected = table.rows('.selected').data().length;
          $.each( table.rows('.selected').data(), function( key, value ) {
             profitMargins[key] = {
                  "product_id": value[column_index["product_id"]],
                  "sku": value[column_index["sku"]],

                  "webshop_supplier_buying_price": value[column_index["webshop_supplier_buying_price"]],
                  "webshop_supplier_gross_price": value[column_index["webshop_supplier_gross_price"]],
                  "webshop_idealeverpakking": value[column_index["webshop_idealeverpakking"]],
                  "webshop_afwijkenidealeverpakking": value[column_index["webshop_afwijkenidealeverpakking"]],
                  "gyzs_selling_price": value[column_index["gyzs_selling_price"]],

                  "buying_price": value[column_index["buying_price"]],
                  "supplier_gross_price": value[column_index["supplier_gross_price"]],
                  "idealeverpakking": value[column_index["idealeverpakking"]],
                  "afwijkenidealeverpakking": value[column_index["afwijkenidealeverpakking"]],
                  "selling_price":  value[column_index["selling_price"]]

              };
          });

          $.ajax({
           url: document_root_url+'/scripts/process_data_price_management.php',
           method:"POST",
           data: ({ profitMargins: profitMargins, type: 'bulk_update_profit_margin', isAllChecked: isAllChecked, profit_margin: profit_margin}),
           success: function(response_data){
              var resp_obj = jQuery.parseJSON(response_data);
              if(resp_obj["msg"]) {
                  table.ajax.reload( null, false );
                  $(".update_loader").hide(); 
                  $('#ProfitMarginModal').modal('toggle');
                  $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");
                  window.setTimeout(function() {
                    $(".alert").fadeTo(500, 0).slideUp(500, function(){
                        $(this).remove(); 
                    });
                }, 4000);
              }      
           }
        });
      } else {
        // For Debter
          
            var debter_number = $("#sel_cust_group_pm_bp option:selected").text();
            
            var record_selected = table.rows('.selected').data().length;
              $.each( table.rows('.selected').data(), function( key, value ) {
                 profitMargins[key] = {
                      "product_id": value[column_index["product_id"]],
                      "sku": value[column_index["sku"]],
                      "supplier_gross_price": value[column_index["supplier_gross_price"]],
                      "buying_price": value[column_index["buying_price"]]
                  };
              });

              $.ajax({
               url: document_root_url+'/scripts/process_data_price_management.php',
               method:"POST",
               data: ({ profitMargins: profitMargins, type: 'bulk_update_debter_margin_on_buying_price', debter_number: debter_number, profit_margin: profit_margin, isAllChecked: isAllChecked}),
               success: function(response_data){
                  var resp_obj = jQuery.parseJSON(response_data);
                  if(resp_obj["msg"]) {
                      table.ajax.reload( null, false );
                      $(".update_loader").hide(); 
                      $('#ProfitMarginModal').modal('toggle');
                      $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");

                      window.setTimeout(function() {
                        $(".alert").fadeTo(500, 0).slideUp(500, function(){
                            $(this).remove(); 
                        });
                      }, 4000);


                  }      
                }
              });

      }
    }
 });

$('#setprofitmarginsp').on("keyup",function(e) {
    var keyCode = e.keyCode || e.which; 
    if (keyCode == 13) { 
        $(".update_loader").show(); 
        var profit_margin = $("#setprofitmarginsp").val();
        var profitMargins = Array();
        var sel_cust_group = $("#sel_cust_group_pm_sp").val();

        var isAllChecked = 0;
        if($("#chkall").is(':checked')) {
          var isAllChecked = 1;
        }

        if(sel_cust_group == "") {
          var record_selected = table.rows('.selected').data().length;
          $.each( table.rows('.selected').data(), function( key, value ) {
             profitMargins[key] = {
                  "product_id": value[column_index["product_id"]],
                  "sku": value[column_index["sku"]],

                  "webshop_supplier_buying_price": value[column_index["webshop_supplier_buying_price"]],
                  "webshop_supplier_gross_price": value[column_index["webshop_supplier_gross_price"]],
                  "webshop_idealeverpakking": value[column_index["webshop_idealeverpakking"]],
                  "webshop_afwijkenidealeverpakking": value[column_index["webshop_afwijkenidealeverpakking"]],
                  "gyzs_selling_price": value[column_index["gyzs_selling_price"]],

                  
                  "buying_price": value[column_index["buying_price"]],
                  "supplier_gross_price": value[column_index["supplier_gross_price"]],
                  "idealeverpakking": value[column_index["idealeverpakking"]],
                  "afwijkenidealeverpakking": value[column_index["afwijkenidealeverpakking"]],
                  "selling_price":  value[column_index["selling_price"]]  
                  
              };
          });

          $.ajax({
           url: document_root_url+'/scripts/process_data_price_management.php',
           method:"POST",
           data: ({ profitMargins: profitMargins, type: 'bulk_update_profit_margin_on_selling_price',isAllChecked: isAllChecked, profit_margin: profit_margin}),
           success: function(response_data){
              var resp_obj = jQuery.parseJSON(response_data);
              if(resp_obj["msg"]) {
                  table.ajax.reload( null, false );
                  $(".update_loader").hide(); 
                  $('#ProfitMarginSPModal').modal('toggle');
                  $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");
                  window.setTimeout(function() {
                    $(".alert").fadeTo(500, 0).slideUp(500, function(){
                        $(this).remove(); 
                    });
                }, 4000);
              }      
           }
        });
      } else {
        // For Debter
          
            var debter_number = $("#sel_cust_group_pm_sp option:selected").text();
            
            var record_selected = table.rows('.selected').data().length;
              $.each( table.rows('.selected').data(), function( key, value ) {
                 profitMargins[key] = {
                      "product_id": value[column_index["product_id"]],
                      "sku": value[column_index["sku"]],
                      "supplier_gross_price": value[column_index["supplier_gross_price"]],
                      "buying_price": value[column_index["buying_price"]]
                  };
              });

              $.ajax({
               url: document_root_url+'/scripts/process_data_price_management.php',
               method:"POST",
               data: ({ profitMargins: profitMargins, type: 'bulk_update_debter_profit_margin_on_selling_price', debter_number: debter_number, profit_margin: profit_margin,isAllChecked: isAllChecked}),
               success: function(response_data){
                  var resp_obj = jQuery.parseJSON(response_data);
                  if(resp_obj["msg"]) {
                      table.ajax.reload( null, false );
                      $(".update_loader").hide(); 
                      $('#ProfitMarginSPModal').modal('toggle');
                      $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");

                      window.setTimeout(function() {
                        $(".alert").fadeTo(500, 0).slideUp(500, function(){
                            $(this).remove(); 
                        });
                      }, 4000);
                  }      
                }
              });

      }
    }
 });





 $('#setdiscount').on("keyup",function(e) {
    var keyCode = e.keyCode || e.which; 
    if (keyCode == 13) { 
        $(".update_loader").show(); 
        var discount_percentage = $("#setdiscount").val();
        var discountPercentages = Array();
        var sel_cust_group = $("#sel_cust_group_d_gp").val();

        var isAllChecked = 0;
        if($("#chkall").is(':checked')) {
          var isAllChecked = 1;
        }

        if(sel_cust_group == "") {
            var record_selected = table.rows('.selected').data().length;
            $.each( table.rows('.selected').data(), function( key, value ) {
               discountPercentages[key] = {
                    "product_id": value[column_index["product_id"]],
                    "sku": value[column_index["sku"]],
                    
                    "webshop_supplier_buying_price": value[column_index["webshop_supplier_buying_price"]],
                    "webshop_supplier_gross_price": value[column_index["webshop_supplier_gross_price"]],
                    "webshop_idealeverpakking": value[column_index["webshop_idealeverpakking"]],
                    "webshop_afwijkenidealeverpakking": value[column_index["webshop_afwijkenidealeverpakking"]],
                    "gyzs_selling_price": value[column_index["gyzs_selling_price"]],

                    "buying_price": value[column_index["buying_price"]],
                    "supplier_gross_price": value[column_index["supplier_gross_price"]],
                    "idealeverpakking": value[column_index["idealeverpakking"]],
                    "afwijkenidealeverpakking": value[column_index["afwijkenidealeverpakking"]],
                    "selling_price":  value[column_index["selling_price"]]

                };
            });

            $.ajax({
             url: document_root_url+'/scripts/process_data_price_management.php',
             method:"POST",
             data: ({ discountPercentages: discountPercentages, type: 'bulk_update_discount', isAllChecked: isAllChecked, discount_percentage: discount_percentage}),
             success: function(response_data){
                var resp_obj = jQuery.parseJSON(response_data);
                if(resp_obj["msg"]) {
                    table.ajax.reload( null, false );
                    $(".update_loader").hide(); 
                    $('#DiscountModal').modal('toggle');
                    $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");
                    
                    window.setTimeout(function() {
                      $(".alert").fadeTo(500, 0).slideUp(500, function(){
                          $(this).remove(); 
                      });
                    }, 4000);
                }      
             }
          });
        } else {

          // For Debter
            var debter_number = $("#sel_cust_group_d_gp option:selected").text();
            
            var record_selected = table.rows('.selected').data().length;
              $.each( table.rows('.selected').data(), function( key, value ) {
                 discountPercentages[key] = {
                      "product_id": value[column_index["product_id"]],
                      "sku": value[column_index["sku"]],
                      "supplier_gross_price": value[column_index["supplier_gross_price"]],
                      "buying_price": value[column_index["buying_price"]]
                  };
              });

              $.ajax({
               url: document_root_url+'/scripts/process_data_price_management.php',
               method:"POST",
               data: ({ discountPercentages: discountPercentages, type: 'bulk_update_debter_discount_on_gross_price', debter_number: debter_number, discount_percentage: discount_percentage, isAllChecked: isAllChecked}),
               success: function(response_data){
                  var resp_obj = jQuery.parseJSON(response_data);
                  if(resp_obj["msg"]) {
                      table.ajax.reload( null, false );
                      $(".update_loader").hide(); 
                      $('#DiscountModal').modal('toggle');
                      $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");

                      window.setTimeout(function() {
                        $(".alert").fadeTo(500, 0).slideUp(500, function(){
                            $(this).remove(); 
                        });
                      }, 4000);


                  }      
                }
              });
        }
    }
 });

$('.table tfoot th').each( function () {
  var title = $(this).text();
  if(title != "Brand") {
   $(this).html( '<input type="text" class="txtsearch" placeholder="Zoek" />' );
  }
 });
     

  $('.table tfoot tr').insertAfter($('.table thead tr'));
  
  table.column(column_index["webshop_supplier_gross_price"]).visible(false);
  table.column(column_index["webshop_supplier_buying_price"]).visible(false);
  table.column(column_index["webshop_idealeverpakking"]).visible(false);
  table.column(column_index["webshop_afwijkenidealeverpakking"]).visible(false);
  //table.column(column_index["gyzs_buying_price"]).visible(true);

  $("#chk_gyzs").change(function() {
    var ischecked= $(this).is(':checked');
    if(ischecked) {
      $("#hdn_percentage_increase_column").val("22");
      $("#hdn_log_column").val("23");

      table.column(column_index["webshop_supplier_gross_price"]).visible(true);
      table.column(column_index["webshop_supplier_buying_price"]).visible(true);
      table.column(column_index["webshop_idealeverpakking"]).visible(true);
      table.column(column_index["webshop_afwijkenidealeverpakking"]).visible(true);
      table.column(column_index["gyzs_buying_price"]).visible(true);
    } else {
      table.column(column_index["webshop_supplier_gross_price"]).visible(false);
      table.column(column_index["webshop_supplier_buying_price"]).visible(false);
      table.column(column_index["webshop_idealeverpakking"]).visible(false);
      table.column(column_index["webshop_afwijkenidealeverpakking"]).visible(false);
      table.column(column_index["gyzs_buying_price"]).visible(false);
    }
  }); 


  // Set Default Columns
  $(".show_cols").each(function () {
      var checkedval = $(this).val();
      table.column(checkedval).visible(false);
  });

  $(".show_deb_cols").each(function () {
      var checkedval = $(this).val();
      table.column(checkedval).visible(false);
  });

  $(".open_by_default").each(function () {
      $(this).prop('checked', true);
      var checkedval = $(this).val();
      table.column(checkedval).visible(true);
  });
  $("table tr th").css({
    "width": "100px"
  });
  $("#example").width("100%");

   

// Set Default Columns



/*
  $("#btnshowupdated").click(function() {
  //var oTable = $('#example').dataTable();
  //oTable.fnSort([ [column_index["updated_product_cnt"],'DESC']] );
  $("#hdn_showupdated").val("1");
  //table.draw(); 
  table.order([column_index["updated_product_cnt"], 'desc'], [column_index["older_updated_product_cnt"], 'desc']).draw();

  });
 */
  

    /* File Upload Starts */

    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });

    var timer;

    $('#import_prices').on('submit', function(event){
         
        $('#message').html("<img src='images/loader.gif' />");
        timer = window.setInterval(show_progress, 1000);

        event.preventDefault();
        $.ajax({
          url: document_root_url+"/import_export/upload.php",
          method:"POST",
          data: new FormData(this),
          dataType:"json",
          contentType:false,
          cache:false,
          processData:false,
          beforeSend:function(){
            $('#import').attr('disabled','disabled');
            $('#import').val('Importing');
          },
          success:function(data)
          {
            if(data.success)
            {
              /*
              $('#total_data').html(data.total_line);

              $('#hdn_processfilename_n').val(data.process_file_name_n);

              start_import(); */
              
              
            }
            if(data.error)
            {
              window.clearInterval(timer);
              $('#message').html('<div class="alert alert-danger" style="font-size:12px;">'+data.error+'</div>');
              $('#import').attr('disabled',false);
              $('#import').val('Import');
            } 
          }
        })
    });






    $('body').on('click', function () {
                /*if ($(".sim-tree").css('background-color', '')) {
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
                } */
            });
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
  $.each(all_updated_categories, function(key,val) {
    //console.log(key+"==="+val);
    if(val != 2) {
    $("[data-id="+val+"]").children('i').first().css({"top":"7px"});
    $("[data-id="+val+"]").children('a').first().css({"background-color":"#a2a3b7","border-radius":"3px","padding":"2px 0px 2px 16px"});
    }
  });   

 }, 3000);

$('.fa-envelope-open').click(function () {
    if(confirm("Are you sure you want to mark all as read?")) {
      $.ajax({
        url: document_root_url+'/scripts/process_data_price_management.php',
        method:"POST",
        data: ({type: 'read_all'}),
        success: function(response_data){
          table.ajax.reload( null, false );
        }
      });
    }
});

  
$("#chkavges").change(function() {
    var ischecked= $(this).is(':checked');
    if(ischecked) {
      $("#showloader").addClass("loader");
      $(".loader_txt").show();
      $.ajax({
         url: document_root_url+'/scripts/process_data_price_management.php',
         method:"POST",
         data: ({ 
                  type: 'get_averages'
               }),
         success: function(response_data){
            var resp_obj = jQuery.parseJSON(response_data);
            if(resp_obj["msg"]) {
                $("#showloader").removeClass("loader");
                $(".loader_txt").hide();
                $('.chav').prop('checked', true);
                table.ajax.reload( null, false );
                //$("#check_all_cnt").html(resp_obj["msg"]);
                table.column(column_index["avg_category"]).visible(true);
                table.column(column_index["avg_brand"]).visible(true);
                table.column(column_index["avg_per_category_per_brand"]).visible(true);

                $("table tr th").css({
                  "width": "100px"
                });
                $("#example").width("100%");
            }      
         }
      });
    } else {
      //table.ajax.reload( null, false );
      //$("#check_all_cnt").html(0);
      table.column(column_index["avg_category"]).visible(false);
      table.column(column_index["avg_brand"]).visible(false);
      table.column(column_index["avg_per_category_per_brand"]).visible(false);
      $('.chav').prop('checked', false);

    }

  });


 
  

  function show_progress()
  {
    $.ajax({
      url: document_root_url+"/import_export/progress.php",
      method:"POST",
      success:function(data)
      {
        var data = jQuery.parseJSON(data);
        

        $('#message').html("");
        $('#import').hide();
        $(".progress").show();
        $(".progress").html('<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="'+data["percentage"]+'" aria-valuemin="0" aria-valuemax="100" style="width: '+data["percentage"]+'%">'+data["percentage"]+'%</div>');
        


        if (data["percentage"] == 100) {
          window.clearInterval(timer); 
          $.ajax({url: document_root_url+"/import_export/unlink.php"});
          $(".progress").hide();
          $('#import').show();
          $('#import').attr('disabled',false);
          $('#import').val('Import');

          if(data["er_imp"]) {
            $('#import_errors').css('display', 'block');
            $("#import_errors").html("");
            var check_import_errors = 0;
            $.each( data["er_imp"], function( key, value ) {
               //import_summary

               if(key == "er_summary") {
                $("#message").html('<div class="alert alert-success" role="alert" style="font-size:11px;">'+value+'</div>');
               } else {
                $("#import_errors").append(value);
                check_import_errors++;
              }

            });
            if(check_import_errors == 0) {
              $('#import_errors').css('display', 'none');
            }

          }
          table.ajax.reload( null, false );
        }
        
        /*
        // If the process is completed, we should stop the checking process.
        if (data["percent"] == 100) {
          //$("#msg_progress_complete").html("Completed");
          

          if(data["er_imp"]) {
            $('#import_errors').css('display', 'block');
            $("#import_errors").html("");
            $.each( data["er_imp"], function( key, value ) {
               //import_summary

               if(key == "er_summary") {
                $("#import_summary").html(value);
               } else {
                $("#import_errors").append(value);
              }

            });
          }
          window.clearInterval(timer);
          $('#import').val('Imported');
          $(".progress").remove();
          $("#msg_progress_complete").remove();
          $.ajax({url: document_root_url+"/import_export/unlink.php"});
        } */ 
      }
    })
  }

  function generateSpan(group_number, product_id, data) {
    var group_name_product = 'no';
    if(debter_product_data[group_number]) {
        var debter_name_product_ids = debter_product_data[group_number] ;
        var product_list_arr = debter_name_product_ids.split(',');
        if(product_list_arr.indexOf(product_id) != -1) {
          group_name_product = 'yes';
        }
    }
    return group_name_product;
  }
    /* File Upload Ends */

    /* File Exports Starts */
      var btnExportFunc = function() {
//window.location.href = document_root_url+"/import_export/export.php";

        $.ajax({
        url: document_root_url+"/import_export/export.php",
        beforeSend:function(){
          $("#btnexport").css("opacity",0.5);
          $("#loading-img-export").css({"display": "inline-block"});   
          $("#btnexport").off( 'click' );
        },
        success:function(data) {
          window.location.href = document_root_url+"/import_export/download_exported.php";
          $("#btnexport").css("opacity",1);
          $("#loading-img-export").css({"display": "none"});
          $('#btnexport').on('click', btnExportFunc);
        }
      });
    }
    $("#btnexport").on('click', btnExportFunc); 
    /* File Exports Ends */


    $('#btnundo').click( function () {
    var record_selected = table.rows('.selected').data().length;
    if(record_selected == 0) {
        alert("Please select record first!!");
        return false;
    }
        $('#UndoModal').modal('toggle'); 
    });


  $('#confirmundo').click(function() {
   $("#UndoModal").css("opacity",0.8);
   $("#loading-img-undo").css({"display": "block"});
   $(this).attr('disabled','disabled');
   

    var all_selected_records = Array();

    var record_selected = table.rows('.selected').data().length;
    $.each( table.rows('.selected').data(), function( key, value ) {
       all_selected_records[key] = {
            "product_id": value[column_index["product_id"]],
            "sku": value[column_index["sku"]],
            
            "webshop_supplier_buying_price": value[column_index["webshop_supplier_buying_price"]],
            "webshop_supplier_gross_price": value[column_index["webshop_supplier_gross_price"]],
            "webshop_idealeverpakking": value[column_index["webshop_idealeverpakking"]],
            "webshop_afwijkenidealeverpakking": value[column_index["webshop_afwijkenidealeverpakking"]],
            "gyzs_selling_price": value[column_index["gyzs_selling_price"]],
            "gyzs_buying_price" : value[column_index["gyzs_buying_price"]],

            "buying_price": value[column_index["buying_price"]],
            "supplier_gross_price": value[column_index["supplier_gross_price"]],
            "idealeverpakking": value[column_index["idealeverpakking"]],
            "afwijkenidealeverpakking": value[column_index["afwijkenidealeverpakking"]],
            "selling_price":  value[column_index["selling_price"]]
        };
    });

    var isAllChecked = 0;
    if($("#chkall").is(':checked')) {
      var isAllChecked = 1;
    }


   $.ajax({
     url: document_root_url+'/scripts/process_data_price_management.php',
     method:"POST",
     data: ({ all_selected_records: all_selected_records, type: 'undo_selling_price', isAllChecked: isAllChecked}),
     success: function(response_data){
        var resp_obj = jQuery.parseJSON(response_data);
        if(resp_obj["msg"]) {
          if(resp_obj["msg"]) {
            $("#UndoModal").css("opacity",1);
            $("#loading-img-undo").css({"display": "none"});
            $('#confirmundo').attr('disabled',false);
            $('#UndoModal').modal('toggle');
            $('<div class="alert alert-success" role="alert">'+resp_obj["msg"]+'</div>').insertBefore("#data_filters");

                      window.setTimeout(function() {
                        $(".alert").fadeTo(500, 0).slideUp(500, function(){
                            $(this).remove(); 
                        });
                      }, 4000);

                      $("#chkall").prop('checked', false);
                      $("#check_all_cnt").html(0);
                      table.ajax.reload( null, false );
          } 
        }      

      }
    });
  });
  $(".show_deb_cols, .show_cols_all_dsp, .show_cols_all_dmbp, .show_cols_all_dmsp, .show_cols_all_ddgp").change(function () {
    if ($(".show_deb_cols").is(':checked')) {
      $("label[for='btnDebCategories']").css('display', 'block');
    } else {
      $("#btnDebCategories").trigger( "click" );
      $("label[for='btnDebCategories']").css('display', 'none');
    };
  });

$("#btnDebCategories").click(function () {
    var selected_group = new Array();
    $('.show_cols_dmbp, .show_cols_dmsp, .show_cols_ddgp, .show_cols_dsp').each(function (index) {
      if ($(this).is(':checked')) {
        selected_group.push($(this).attr('name'));
      }
    });
    if ($.isEmptyObject(selected_group)) {
      $("#hdn_selectedcategories").val('');
      checkIt(true, true);
      $("i.sim-tree-checkbox").parent('a').parent('li').removeClass('disabled');
      $("#flexCheckDefault").attr("disabled", false);
      table.draw();
    } else {
      var selected_group_str = selected_group.toString();
      // ajax it
      var request = $.ajax({
        url: document_root_url + '/scripts/get_category_brands.php',
        method: "POST",
        data: ({ customer_group: selected_group_str, type: 'multiple_group_query' }),
        dataType: "json",
        beforeSend:function(){
          $("#btnDebCategories").css("opacity",0.5);
          $("#btnDebCategories").find('span.loading-img-update').css({"display": "inline-block"});
          $("#btnDebCategories").attr('disabled','disabled');
        }

      });

      request.done(function (response_data) {
        var resp_obj = response_data;
        $("#hdn_selectedcategories").val(resp_obj["msg"]);
        checkIt(false, false);
        if (resp_obj["msg"]) { // means status = checked
          $('#flexCheckDefault').prop('checked', true);
          var cat_id_arr = resp_obj["msg"].split(',');
          $.each(cat_id_arr, function (key, value) {
            var $li = $('li[data-id=' + value + ']');
            checkGiven($li, true, true);
          });
          toggleCheckbox('none');
        } else { // remove category filter
          $("i.sim-tree-checkbox").parent('a').parent('li').addClass('disabled');
          $("#flexCheckDefault").attr("disabled", true);
        }
        $("#btnDebCategories").css("opacity",1);
        $("#btnDebCategories").find('span.loading-img-update').css({"display": "none"});
        $('#btnDebCategories').removeAttr('disabled');
        table.draw();
      });

    request.fail(function (jqXHR, textStatus) {
      alert("Request failed: " + textStatus);
    });
    }//end else
  });

$("#flexCheckDefault").change(function () {
    var current_status = $(this).prop('checked');
    var cat_all_str = $("#hdn_selectedcategories").val();
    if ($('input.show_deb_cols').is(":checked") && cat_all_str != '' && cat_all_str != -1) {//means this is a group list
      var cat_all_arr = cat_all_str.split(',');
      if (current_status) { // check all hiddencategories

        $.each(cat_all_arr, function (key, value) {
          var $li = $('li[data-id=' + value + ']');
          checkGiven($li, true, true);
        });
      } else { //uncheck all hiddencategories
        $.each(cat_all_arr, function (key, value) {
          var $li = $('li[data-id=' + value + ']');
          checkGiven($li, false, true);
        });
      }
    } else if (current_status) {
      toggleAllCategories(current_status);
    } else {
      toggleAllCategories(current_status);
    }
    table.draw();
  });

  
  $('#reset_btn_id').on('click', function () {
    $('.txtsearch').val('');
    $('#brand').val('');
    $('#supplier_type').val('');
    $('#dtSearch').val('');
    $('#filter_with').val('');
    $('#hdn_selectedbrand').val('');
    $('#hdn_filters').val('');
    table.search('').columns().search('').draw();
  });

  var tree = simTree({
    el: '#tree',
    data: list,
    check: true,
    linkParent: true,
    expand: 'expand',
    checked: 'checked',

    onClick: function (item) {
    },
    onChange: function (item) { 
      $("#hdn_showupdated").val("0");
      $("#chkall").prop('checked', false);
      $("#check_all_cnt").html(0);
      table.draw();
    },
    done: function () {
      $("#flexCheckDefault").prop('checked', true);
      toggleAllCategories(true);
      table.draw();
    }
  });

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
        checkIt(false,false);
      }
    }
    return true;
  }
  
  function getTreeCategories() {
    var selected_categories="";
    if ($('a>i.sim-tree-checkbox').hasClass('checked')) {
      updated_cats = new Array();
      $.each($('.sim-tree-checkbox'), function (index, value) {
        if ($(this).hasClass('checked')) {
        updated_cats.push($(this).parent('a').parent('li').attr('data-id'));
        }
      });
      selected_categories = updated_cats.toString();
    }
    return selected_categories;
  }//end getTreeCategories()

  $('#searchDebterPriceModal').modal({ show: false});

  $("button#okSearchDebterPrices").click(function()
  {
    var group_price_text = parseFloat($('#from_debter_price').val());
      if(group_price_text.length == 0) {
        alert("Field should not be blank");
        return false;
      }
      if($('#hdn_parent_debter_selected').val() == 3 && $('#to_debter_price').val() == '') {
        alert("Field should not be blank.");
        return false;
      }

      var myArray = parseFloat($('#to_debter_price').val());
      if($('#hdn_parent_debter_selected').val() == 3 && myArray < group_price_text) {
        alert("Second value should be greater than First value");
        return false;
      }
      $(this).attr('disabled', 'disabled');
      let result = new_option_text = '';
      make_expression = $("#hdn_parent_debter_expression").val();
      if($('#hdn_parent_debter_selected').val() == 3) {
        new_option_text = "Between "+group_price_text+" AND "+myArray;
        result = make_expression.concat(" "+new_option_text);
      } else if($('#hdn_parent_debter_selected').val() == 1) {
        new_option_text = "Less than OR Equal to "+group_price_text;
        result = make_expression.concat(group_price_text);
      } else if($('#hdn_parent_debter_selected').val() == 2) {
        new_option_text = "Greater than OR Equal to "+group_price_text;
        result = make_expression.concat(group_price_text);
      }
    $("#hdn_group_search_text").val(result);
    let clicked_col_indx = $("#hdn_filters").val();
    $('#searchDebterPriceModal').modal("toggle");
    $(this).removeAttr("disabled");
    var make_id = 'group_indx_'+clicked_col_indx;

    if($("#"+make_id+" option[value=4]").length > 0) {
      $('#'+make_id+' option[value="4"]').remove();
      $('select'+'#'+make_id).removeAttr('title');
    }
    $('#'+make_id).append($('<option>', { value : 4 }).text(new_option_text));
    $('select'+'#'+make_id).val("4").change();
    $('select'+'#'+make_id).attr('title', new_option_text);
    table.draw();
    return true;
  });

  function enterOk(textbox_id) {
    $('#'+textbox_id).keypress(function(event){
      var keycode = (event.keyCode ? event.keyCode : event.which);
      if(keycode == '13') {
        $("button#okSearchDebterPrices").trigger("click");
      }
    });
  }

});