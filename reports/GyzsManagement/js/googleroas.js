  
$(document).ready(function() {
  var table = $('#example').DataTable({
      "processing": true,
      "serverSide": true,
      "pageLength": 200,
      'order': [0, 'desc'],
      "deferRender": true,
      "fixedHeader": true,
      initComplete: function () {
          // Apply the search
          this.api().columns().every( function () {
             var that = this;
               $('.table tfoot tr').insertAfter($('.table thead tr'));
                  // Pagination added after Table
                $(".dataTables_wrapper div:nth-child(3)").insertAfter($('.datatable'));
                $( 'input', this.footer() ).on( 'keyup change clear', function () {
                  if ( that.search() !== this.value ) {     
                    that
                        .search( this.value )
                        .draw();
                  }
                });

          } ); 
      },
      "columnDefs": [
            {
              "targets": [3],
              "render": function ( data, type, row ) {
                return  '<a href="'+document_root_url+'/pm_logs/google_roas/'+data+'" targets="_blank">'+data+'</a>';  
              },
            },
            {
              "targets": [4],
              "render": function ( data, type, row ) {
                var is_checked = "";
                if(data == 1) {
                  is_checked = "checked";
                }
                
                return  '<input class="radactivesheet" type="radio" id="'+row[0]+'" name="activesheet" value="'+row[0]+'" '+is_checked+'>';  
              },
            }
      ],  
      "ajax": {
        "url": document_root_url+"/scripts/create_googleroasquery.php",
        "type": "POST"
      }
  });

  $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });

  $('#google_roas_xlsx_form').on('submit', function(event){
        
    var from = $("#from").val();
    var to = $("#to").val();
    var xlsxfile = $('#file_google_roas_xlsx').val(); 
    
    $("#fileuploaderror").removeClass("alert-danger");
    $("#fileuploaderror").show();
    $("#fileuploaderror").html("<img src='"+document_root_url+"/images/loader.gif' />");

    if(from == "") {
      $("#fileuploaderror").show();
      $("#fileuploaderror").addClass("alert-danger");
      $("#fileuploaderror").html("Please Enter From Date");
      return false;
    }
    if(to == "") {
      $("#fileuploaderror").show();
      $("#fileuploaderror").addClass("alert-danger");
      $("#fileuploaderror").html("Please Enter To Date");
      return false;
    }
    if(xlsxfile == "") {
      $("#fileuploaderror").show();
      $("#fileuploaderror").addClass("alert-danger");
      $("#fileuploaderror").html("Please Select Xlsx File");
      return false;
    }


        event.preventDefault();
        $.ajax({
          url: document_root_url+'/scripts/process_data.php',
          method:"POST",
          data: new FormData(this),
          dataType:"json",
          contentType:false,
          cache:false,
          processData:false,
          beforeSend:function(){
           
          },
          success:function(response_data)
          {
            
             //var resp_obj = jQuery.parseJSON(response_data);
             if(response_data["msg"]["error"]) {
                $("#fileuploaderror").addClass("alert-danger");
                $("#fileuploaderror").html(response_data["msg"]["error"]);
             } else {
                $('#SetGoogleRoasFileModal').modal('toggle'); 
                table.draw();
                $("#fileactivatedmsg").show();
                $("#fileactivatedmsg").removeClass("alert-danger");
                $("#fileactivatedmsg").removeClass("alert-success");
                $("#fileactivatedmsg").html("<img src='"+document_root_url+"/images/loader.gif' />");
                
                $("#fileactivatedmsg").addClass("alert-success");
                $("#fileactivatedmsg").html("File uploaded successfully");  
             }
          }
        })
    });

    $(document).on("click", ".radactivesheet", function(){
      if(confirm("Are you sure you want to activate it?")){
          
         $("#fileactivatedmsg").removeClass("alert-danger");
         $("#fileactivatedmsg").removeClass("alert-success");
         $("#fileactivatedmsg").show();
         $("#fileactivatedmsg").html("<img src='"+document_root_url+"/images/loader.gif' />");

          $.ajax({
           url: document_root_url+'/scripts/process_data.php',
           method:"POST",
           data: ({ id: $(this).attr("id"),
                    type: 'activate_roas_xlsx'
                 }),
           success: function(response_data){
              var resp_obj = jQuery.parseJSON(response_data);

              if(resp_obj["stat"] == 1) {
                $("#fileactivatedmsg").show();
                $("#fileactivatedmsg").removeClass("alert-danger");
                $("#fileactivatedmsg").addClass("alert-success");
                $("#fileactivatedmsg").html(resp_obj["msg"]);
                
              } else {
                $("#fileactivatedmsg").show();
                $("#fileactivatedmsg").removeClass("alert-success");
                $("#fileactivatedmsg").addClass("alert-danger");
                $("#fileactivatedmsg").html(resp_obj["msg"]); 
              }   
              table.draw();
           }
        });
      } else {
        return false;
      }
    });


    $('.table tfoot th').each( function () {
      var title = $(this).text();
      $(this).html( '<input type="text" class="txtsearch" placeholder="Search '+title+'" />' );
    });
    $('.table tfoot tr').insertAfter($('.table thead tr'));


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

});
