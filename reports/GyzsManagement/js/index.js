$(document).ready(function () {
  $("#toggle-slider").click(function () {
    // for menu collapse
    let sidebar = $("#sidebar");
    if (sidebar.hasClass("sidebar-wrapper")) {
      sidebar.addClass("sidebar-toggle");
      sidebar.removeClass("sidebar-wrapper");
    } else {
      sidebar.addClass("sidebar-wrapper");
      sidebar.removeClass("sidebar-toggle");
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
      $("#brand-logo").attr("src", "css/gif/GYZS1.gif");
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
      filter.removeClass("filter-wrapper");
    } else {
      filter.addClass("filter-wrapper");
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

  $('.nav-item.parent > .nav-link').on('mouseenter', function () {
    $(this).addClass('open');
    $(this).next('.sub-menu').animate({
      opacity: 1,
      top: "30",
    }, 150);

    $(this).next('.sub-menu').css({
      "display": "block",
    });
  });

  $('.nav-item > .nav-link').on('mouseenter', function () {
    $(this).parent().siblings().children().removeClass('open').next().animate({
      opacity: 0,
      top: "15",
    }, 50, function () {
      $(this).css({
        "display": "none",
      });
    });
  });

  $(".head-tabel, #sidebar").on("mouseenter", function () {
    $(".nav-item.parent > .nav-link").next('.sub-menu').animate({
      opacity: 0,
      top: "15",
    }, 50, function () {
      $(this).css({
        "display": "none",
      });
    });

    $(".nav-item.parent > .nav-link").removeClass('open');
  });
});