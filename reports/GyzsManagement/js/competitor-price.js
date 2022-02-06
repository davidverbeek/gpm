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
                      $("#chkall").prop('checked', false);
                      $("#check_all_cnt").html(0);
                });
          } ); 
      },
      "ajax": {
        "url": document_root_url+"/scripts/create_competitor_query.php",
        "type": "POST"
      }
  });

});