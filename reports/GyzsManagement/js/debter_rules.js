$(document).ready(function () {

  /**
   * This function is toggling disabled feature of checkboxes
   * @param {
   * } new_status 
   */
  function toggleCheckbox(new_status) {
    $('a>i.sim-tree-checkbox').each(function (index) {
      if (!$(this).hasClass('checked')) {
        if (new_status == 'none') {
          $(this).parent('a').parent('li').addClass('disabled');
         // $('a#linkCategories').css('display', 'block');
        } else {
          $(this).parent('a').parent('li').removeClass('disabled');
        }
      }
    });

    //check if any disabled
    if($("i.sim-tree-checkbox").parent('a').parent('li').hasClass('disabled')) {
      $('a#linkCategories').css('display', 'block');
    } else {
      $('a#linkCategories').css('display', 'none');
    }
  }

  var tree = simTree({
    el: '#tree',
    data: list,
    check: true,
    linkParent: true,
    expand: 'expand',
    onClick: function (item) {
    },
    onChange: function (item) {
    }
  });

  setTimeout(function () {
    var simtmp = 0;
    $("ul.sim-tree ul").each(function () {
      if (simtmp < 2) {
        $(this).addClass("show");
      }
      simtmp++;
    });

    var simtreehideicontmp = 0;
    $(".sim-tree-spread").each(function () {
      if (simtreehideicontmp < 2) {
        $(this).hide();
      }
      simtreehideicontmp++;
    });

    var simtreehidetexttmp = 0;
    $("a .sim-tree-checkbox").each(function () {
      if (simtreehidetexttmp < 2) {
        $(this).parent().html("");
        $(this).hide();
      }
      simtreehidetexttmp++;
    });
    
  }, 3000);

  function showDivMessage(msg) {
    $('<div class="alert alert-success" role="alert">' + msg + '</div>').insertBefore("#data_filters1");

    window.setTimeout(function () {
      $(".alert").fadeTo(500, 0).slideUp(500, function () {
        $(this).remove();
      });
    }, 4000);
  }


  $("#btnsave").click(function () {
    var selected_group = $("#sel_debt_group").val();
    var old_cats = $("#hdn_existingcategories").val();
    var updated_cats = new Array();
    $.each($('.sim-tree-checkbox'), function (index, value) {
      if ($(this).hasClass('checked')) {
        updated_cats.push($(this).parent('a').parent('li').attr('data-id'));
      }
    });
    $("#hdn_existingcategories").val(updated_cats);
    var selected_cat_new = $("#hdn_existingcategories").val();
    if (selected_group == '') {
      alert("Please select Customer group.");
      $("#sel_debt_group").focus();
      return false;
    } else if(confirm("If categories of the debter are changed. All existing products will be unassigned and their prices will set to ZERO. Are you sure you want to continue?")) {
      $.ajax({
        url: document_root_url + '/scripts/get_category_brands.php',
        "type": "POST",
        data: ({ customer_group: selected_group, type: 'save_rule', cat_id_new: selected_cat_new, on_load_categories: old_cats }),
        success: function (response_data) {
          var resp_obj = jQuery.parseJSON(response_data);
          if (resp_obj["msg"]) {
            $('<div class="alert alert-success" role="alert">' + resp_obj["msg"] + '</div>').insertBefore("#data_filters1");

            window.setTimeout(function () {
              $(".alert").fadeTo(500, 0).slideUp(500, function () {
                $(this).remove();
              });
            }, 4000);
            $('.ddfields').val("");
            $(".sim-tree-checkbox").removeClass('checked');
            $('a#linkCategories').css('display', 'none');
            $("#flexCheckDefault").prop('checked', false);
            toggleCheckbox('');
          }
        }
      });
    }
  });

  $("#sel_debt_group").change(function () {

    //get selected customer group
    var selected_group = $(this).val();
    checkIt(false, false);
    toggleCheckbox('');
    $("#hdn_existingcategories").val('');
    $("#hdn_selectedcategories").val('');

    $.ajax({
      url: document_root_url + '/scripts/get_category_brands.php',
      "type": "POST",
      data: ({ customer_group: selected_group, type: 'get_categories' }),
      success: function (response_data) {
        var resp_obj = jQuery.parseJSON(response_data);
        if (resp_obj["msg"]) {
          // select these categories
          var categories_str = resp_obj["msg"];
          var cat_id_arr = categories_str.split(',');

          $.each(cat_id_arr, function (key, value) {
            var $li = $("li[data-id='" + value + "']");
            checkGiven($li, true, true);
            
          });
          $("#hdn_existingcategories").val(resp_obj["msg"]);
        } else {
          checkIt(false, false);
        }
        toggleCheckbox('none');
        $("#flexCheckDefault").prop('checked', false);
      }
    });

  });

  $('a#linkCategories').click(function () {
    toggleCheckbox('');
  });

  $('#btncopy').click(function() {
   var  source_group_id =  $('#parent_debt_group').val();
   var  child_group_id =  $('#child_debt_group').val();

   if(source_group_id == '') {
     alert('Please select Group to copy FROM.');
     $('#parent_debt_group').focus();
     return false;
   }else if(child_group_id == '') {
    alert('Please select Group to copy TO.');
    $('#child_debt_group').focus();
    return false;
   } else if(confirm("Existing products of TO DEBTER will be unassinged and their prices will be set to ZERO. Are you sure you want to continue?")) {
    $.post(document_root_url + '/scripts/get_category_brands.php', { source_group_id: source_group_id, destination_group_id: child_group_id, type: "copy_categories" }, function( data ) {
    var res = jQuery.parseJSON(data);
    showDivMessage(res["msg"]);
    $('.copyfields').val('');
    });
  }
  });

  $("#flexCheckDefault").change(function() { 
    var status = $(this).prop('checked');
    var any_disabled = false;
    //check all and set hidden field
    $('a>i.sim-tree-checkbox').each(function (index) {
      if ($(this).parent('a').parent('li').hasClass('disabled')) {
        // means dont work on full list
        any_disabled=true;
      } 

      if(any_disabled)
      return false;
    });

    if (!any_disabled) {
      if (status) {
        checkIt(true, true);
      } else {
        checkIt(false,false);
      }
    } 

    var cat_all_str = $("#hdn_existingcategories").val();
    var cat_all_arr = cat_all_str.split(',');

    if (status) { // check all hiddencategories
      $.each(cat_all_arr, function(key,value ) {
        var $li = $("li[data-id='" + value + "']");
        checkGiven($li, true, true);
      });
    } else {
      $.each(cat_all_arr, function(key,value ) {
        var $li = $("li[data-id='" + value + "']");
        checkGiven($li, false, false);
      });
    }
  });

  function checkIt(status, flag)
  {
    $("i.sim-tree-checkbox").each(function() {
      var $check = $(this);
      var $li = $check.closest('li');
      var $childUl, $childUlCheck;
      var data = $li.data();
      if (typeof status === 'undefined') {
        status = !data.checked;
      }

      if (status === true) {
        $check.removeClass('sim-tree-semi').addClass('checked');
      } else if (status === false) {
        $check.removeClass('checked sim-tree-semi');
      } else if (status === 'semi') {
        $check.removeClass('checked').addClass('sim-tree-semi');
      }
      $li.data('checked', status);
   });
  }

  function checkGiven($li, status, flag)
  {
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
  }
 
});