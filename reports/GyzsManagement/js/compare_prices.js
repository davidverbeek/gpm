$(document).ready(function () {
  // "order": [[ 0, 'asc' ]],
  var table = $('#compare_dt').DataTable({
    "processing": true,
    "serverSide": true,
    "pageLength": 200,
    "deferRender": true,
    "order": [],
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
        });
    },

    "ajax": {
      "url": document_root_url + '/scripts/get_supplier_prices.php',
      "type": "POST",
    },
    "columnDefs": [
      {
        "visible": false,
        "targets": -1
      }, {
        "targets": [column_index['m_ean'], column_index['m_sku'], column_index['p_sku'], column_index['t_sku'], column_index['d_sku']],
        "orderable": false
      },
    ]

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


});