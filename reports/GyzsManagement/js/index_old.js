$(document).ready(function () {

  $("#toggle-slider").click(function () {
    // for menu collapse
    let sidebar = $("#sidebar");
    if (sidebar.hasClass("sidebar-wrapper")) {
      sidebar.addClass("sidebar-toggle");
      sidebar.removeClass("sidebar-wrapper");
      $('.nav-menu .nav-link span').css('display', 'inline-block');
    } else {
      sidebar.addClass("sidebar-wrapper");
      sidebar.removeClass("sidebar-toggle");
      $('.nav-menu .nav-link span').css('display', 'none');
    }
    //   for content-wrapper width
    let contWd = $("#main-content");
    if (contWd.hasClass("content-wrapper")) {
      contWd.addClass("content-toggle");
      contWd.removeClass("content-wrapper");
    } else {
      contWd.addClass("content-wrapper");
      contWd.removeClass("content-toggle");
    }
    //   for table-wrapper width
    let tableWd = $("#data-content");
    if (tableWd.hasClass("data-wrapper")) {
      tableWd.addClass("data-toggle");
      tableWd.removeClass("data-wrapper");
    } else {
      tableWd.addClass("data-wrapper");
      tableWd.removeClass("data-toggle");
    }

    $("#header-content #navbar-brand").toggle("#gif");
    $("#header-content#gif").toggle($("#header-content #gif").attr("src", "css/gif/GYZS_black.gif"));
    let ContWda = $("#main-content");
    if (ContWda.hasClass("content-wrapper")) {
      $('#navbar-brand').css({
        "display": "block",
      });
    }
    if ($("#slider").attr("src") == "css/svg/slider_menu_blue.svg") {
      $("#slider").attr("src", "css/svg/slider_menu.svg");
    } else {
      $("#slider").attr("src", "css/svg/slider_menu_blue.svg");
    }
    let slider = $("#toggle-slider");
    if (slider.hasClass("slider-white")) {
      slider.addClass("slider-blue");
      slider.removeClass("slider-white");
    } else {
      slider.addClass("slider-white");
      slider.removeClass("slider-blue");
    }
    if ($("#brand-logo").attr("src") == "css/gif/GYZS_vertical.gif") {
      $("#brand-logo").attr("src", "css/gif/GYZS.gif");
    } else {
      $("#brand-logo").attr("src", "css/gif/GYZS_vertical.gif");
    }
    let brandLg = $("#brand-logo");
    if (brandLg.hasClass("brand-sm")) {
      brandLg.addClass("brand-bg");
      brandLg.removeClass("brand-sm");
    } else {
      brandLg.addClass("brand-sm");
      brandLg.removeClass("brand-bg");
    }
  });
  // for togggel filter bar
  $("#cog-content").click(function () {
    let filter = $("#filter-content");
    if (filter.hasClass("filter-wrapper")) {
      filter.addClass("filter-toggle");
      filter.removeClass("filter-wrapper");
    } else {
      filter.addClass("filter-wrapper");
      filter.removeClass("filter-toggle");
    }
    let filCog = $("#cog-content");
    if (filCog.hasClass("cog")) {
      filCog.addClass("cog-toggle");
      filCog.removeClass("cog");
    } else {
      filCog.addClass("cog");
      filCog.removeClass("cog-toggle");
    }
  });

  $('.nav-item.parent > .nav-link').on('click', function () {
    $(this).toggleClass('open'); // arrow rotate
    $(this).next('.sub-menu').slideToggle(200);
    $(this).parent().siblings().children().removeClass('open').next().slideUp();
    return false;
  });

  $("#main-content").on("click", function () {
    $(".nav-item.parent > .nav-link").next('.sub-menu').slideUp(200);
    $(".nav-item.parent > .nav-link").removeClass('open');
  });
  
});