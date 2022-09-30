$(document).ready(function () {
  // "order": [[ 0, 'asc' ]],
  var table = $('#compare_dt').DataTable({
    "processing": true,
    "serverSide": true,
    "pageLength": 200,
    "deferRender": true,
    "order": [],
    "fixedHeader": true,
    "language": {
      "processing": "<span class='fa-stack fa-lg'>\n\
                      <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                </span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Processing ...",
    },
    initComplete: function () {

      // Apply the search
      this.api()
        .columns()
        .every(function () {
            var that = this;

            $('input', this.footer()).on('keyup change clear', function () {
              if (that.search() !== this.value) {
                that.search(this.value).draw();
              }
            });

            if(that[0][0] == column_index["m_buying_price"] || that[0][0] == column_index['p_buying_price'] || that[0][0] == column_index['t_buying_price'] || that[0][0] == column_index['d_buying_price'] || that[0][0] == column_index['d_buying_price'] ||  that[0][0] == column_index['m_piece'] ||  that[0][0] == column_index['p_piece'] ||  that[0][0] == column_index['t_piece'] ||  that[0][0] == column_index['d_piece']) {
              var select = $('<select  id="group_indx_'+that[0][0]+'" class="search_buying_price" style="margin-top:-30px; margin-left:-23px; position:absolute;" title="Please Select"><option value="0">All</option><option value="1">Less than OR Equal to</option><option value="2">Greater than OR Equal to</option><option value="3">Between</option></select>')
                .appendTo( $(that.footer()).empty())
                .on('change', function () {
                  var label_display = m = "";
                  m = $(this).parent("th").index();
                  if(m<7) {
                    label_display = $("tr[role='row']:eq(1)").find('th:eq('+(m-1)+')').text();
                    label_display = 'Mavis '+label_display;
                  } else if(m>=7 && m<12) {
                    label_display = $("tr[role='row']:eq(1)").find('th:eq('+(m-1)+')').text();
                    label_display = 'Polvo '+label_display;
                  } else if(m>=12 && m<17) {
                    label_display = $("tr[role='row']:eq(1)").find('th:eq('+(m-1)+')').text();
                    label_display = 'Dozon '+label_display;
                  } else if(m>=17 && m<=21) {
                    label_display = $("tr[role='row']:eq(1)").find('th:eq('+(m-1)+')').text();
                    label_display = 'Transferro '+label_display;
                  }
                  var group_filter_text = deb_column_name = "";
                  // if less than, between or greater than?
                  if($(this).val() == 1) {
                    group_filter_text = label_display+" <=";
                    make_expression = "csp.db_column <= ";
                    /* enterOk('from_debter_price'); */
                  } else if($(this).val() == 3) {
                    group_filter_text = label_display;
                    make_expression = "csp.db_column";
                    /* enterOk('to_debter_price'); */
                  } else if($(this).val() == 2) {
                    group_filter_text = label_display+" >=";
                    make_expression = "csp.db_column >= ";
                     /*  enterOk('from_debter_price'); */
                  }
                  $('#hdn_parent_debter_expression').val(make_expression);
                  $('span[id=sp_from_debter_price]').text(group_filter_text);
                  $('#to_debter_price').val('');
                  $('#from_debter_price').val('');
                  $('#hdn_parent_debter_selected').val($(this).val());
                  $('#searchDebterPriceModal').modal('show');
                  $('#searchDebterPriceModal').draggable();
                  if($(this).val() == '3') {
                    $('span#span-dash').show();
                    $('input#to_debter_price').show();
                  } else {
                    $('span#span-dash').hide();
                    $('input#to_debter_price').hide();
                  }
                  $("#hdn_filters").val(that[0][0]+'task-all-numbers-filterable-csp');
                });
          };
        });
  },

    "ajax": {
      "url": document_root_url + '/scripts/get_supplier_prices.php',
      "type": "POST",   "data": function ( d ) {
        d.hdn_group_search_text = $('#hdn_group_search_text').val();
        d.hdn_filters = $('#hdn_filters').val();
      },
    },
    "columnDefs": [
      {
        "visible": true,/** this was false, changed to true on 29-09-2022 */
        "targets": -1
      }, {
        "targets": [column_index['m_ean'], column_index['m_sku'], column_index['p_sku'], column_index['t_sku'], column_index['d_sku']],
        "orderable": false
      }, {
        "targets": [column_index['m_buying_price'], column_index['p_buying_price'], column_index['t_buying_price'],column_index['d_buying_price']],
        "className": "column_inkpr_filter"
      }
    ],
  });


  /* $("table tr th").css({
      "width": "100px"
    }); */

  //$("#compare_dt").width("100%");
  //$('.table tfoot tr').insertAfter($('.table thead tr'));
  // $('.table tfoot tr').css('display', 'none');
  // Pagination added after Table
  $(".dataTables_wrapper div:nth-child(3)").insertAfter($('.datatable'));

  $('#dtSearch').keyup(function () {
    table.search($(this).val()).draw();
  });

  $('#compare_dt tfoot th').each(function () {
    var title = $(this).text();
    $(this).html('<input type="text" class="txtsearch" placeholder="Zoek" />');
  });

  $('.table tfoot tr').insertAfter($('.table thead tr:nth-child(2)'));


  $('.table tfoot th').each(function () {
    var title = $(this).text();
    if (title != "Brand") {
      $(this).html('<input type="text" class="txtsearch" placeholder="Zoek" />');
    }
  });

  $("button#okSearchDebterPrices").click(function() {
    //create an expression
      var group_price_text = $('#from_debter_price').val();
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
      clicked_col_indx = clicked_col_indx.replace("task-all-numbers-filterable-csp", "");
      clicked_col_indx = $.trim(clicked_col_indx);
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
});