var current_domain = window.location.protocol+"//"+document.domain;  //without trailing slash(/)

jQuery( window ).load(function() { 

	jQuery(window).one('scroll',function() {
		
		loadSlider();		

	});
		
});

jQuery(document).ready(function() {
	
	var new_product_occured = 0;
	var home_readmore = 0;
	var home_reviews = 0;

	localStorage.setItem("loadedsliscript","0");

	jQuery("html").on("mousemove touchmove", function(e) {
		loadSliScripts();
	});
	jQuery(".form-search").on("click", function(){
		loadSliScripts();
	});

	window.addEventListener('scroll', function(e) {
				
		if(isOnScreen( jQuery('#get_new_products') ) ) { 
			if(new_product_occured == 0) {
				loadNewProducts();
			}
			new_product_occured++;
		}

		if(isOnScreen(jQuery('.meetwithgyzs'))) {
			if(home_readmore == 0) {
				homeReadMore();
			}
			home_readmore++;
		}

		if(isOnScreen(jQuery('.cstm-reviews'))) {
			if(home_reviews == 0) {
				homeFeedbackReview();
			}
			home_reviews++;
		}


	});

});


function loadSliScripts() {
	if(localStorage.getItem("loadedsliscript") == 0) {
		jQuery.when(
			jQuery.getScript(""+current_domain+"/skin/frontend/gyzs/default/js/sli/wrapper.js"),
			jQuery.getScript(""+current_domain+"/skin/frontend/gyzs/default/js/sli/sli-rac.config.js"),
			jQuery.Deferred(function( deferred ){
				jQuery( deferred.resolve );
			})
		).done(function(){
		
			setTimeout(function(){ 
				jQuery("#homepage_topsellers").html("<div id='topsellercontent' style='display:none;'>"+localStorage.getItem('top_seller_out')+"<div>");
				jQuery("#topsellercontent").fadeIn(500);
				checkStock();
			 }, 3000);
			
		
		});
		jQuery("body").append("<link>");
		   var css = jQuery("body").children(":last");
		   css.attr({
		     rel:  "stylesheet",
		     type: "text/css",
		     href: ""+current_domain+"/skin/frontend/gyzs/default/css/sli/sli-rac.css"
		});


		localStorage.setItem("loadedsliscript","1");
	}
}

function isOnScreen(elem) {
	if( elem.length == 0 ) {
		return;
	}
	var $window = jQuery(window)
	var viewport_top = $window.scrollTop()
	var viewport_height = $window.height()
	var viewport_bottom = viewport_top + viewport_height
	var $elem = jQuery(elem)
	var top = $elem.offset().top
	var height = $elem.height()
	var bottom = top + height

	return (top >= viewport_top && top < viewport_bottom) ||
	(bottom > viewport_top && bottom <= viewport_bottom) ||
	(height > viewport_height && top <= viewport_top && bottom >= viewport_bottom)
} 


function loadNewProducts() {
	jQuery.ajax({
			url: current_domain+"/hsperformance/index/getNewProducts",
			type: "POST",
			success: function(response){
				jQuery("#get_new_products").html(response);
				jQuery('.homeproductcarousel').slick({
				speed: 300,
				slidesToShow: 5,
				slidesToScroll: 1,
				draggable: false,
				prevArrow: '<button type="button" class="slick-prev slick-arrow fa fa-angle-left"></button>',
				nextArrow: '<button type="button" class="slick-next slick-arrow fa fa-angle-right"></button>',
				adaptiveHeight: true,
				responsive: [
						{
							breakpoint: 1025,
							settings: {
								slidesToShow: 4
							}
						},
						{
							breakpoint: 991,
							settings: {
								slidesToShow: 3
							}
						},
						{
							breakpoint: 767,
							settings: {
								slidesToShow: 1
							}
						},
						{
							breakpoint: 600,
							settings: {
								slidesToShow: 1
							}
						}
				]
			});
			checkStock();

		}
	});		
}


function loadSlider() {
	jQuery.ajax({
		url: current_domain+"/hsperformance/index/getSlider",
		type: "POST",
		success: function(response){
			
			jQuery("#flexslidercontainer").html("<div id='slidercontent' style='display:none;'>"+response+"<div>");
			jQuery("#slidercontent").fadeIn(5000);
		 
		   var $flexslider = jQuery("#flexslider");
		   $flexslides = $flexslider.find('ul.slides').children('li');
		   
		   var CONFIG_SLIDESHOW = {
animation: "slide",
slideshow: true,
useCSS: false,
touch: true,
video: false,
animationLoop: true,
mousewheel: false,
smoothHeight: true,
slideshowSpeed: 6000,
animationSpeed: 600,
pauseOnAction: true,
pauseOnHover: true,
controlNav: true,
directionNav: false,
timeline: true,
height: "460"
}

			var timeline = {
	width: 0,
	interval: CONFIG_SLIDESHOW.slideshowSpeed
},
slideshowHover = false,
slideshowPause = false;


vericalCenterSlideContent = function ($slide) {
	var $content = jQuery('div.content', $slide);
	var $contentH = $content.height()
			+ parseInt($content.css('marginTop'))
			+ parseInt($content.css('marginBottom'));
	if ($slide.height() > $contentH) {
		$content.css('marginTop', Math.floor(($slide.height() - $contentH) / 2 + 30) + 'px');
	}
}

setSlideHeight = function () {
	/*update slides to include cloned li*/
	$flexslides = jQuery("#flexslider").find('ul.slides').children('li');
	if (_resizeLimit['slideshow'] <= 1 && Shopper.responsive) {
		/*iphone resolution ( <= 767 ). hide content and show small image*/
		jQuery('div.content', $flexslides).hide();
		jQuery('img.small_image', $flexslides).show();
		var maxSlideHeight = null;
		$flexslides.each(function (i, v) {
			if (jQuery('img.small_image', this).length) {
				jQuery(this).css('background-image', 'none');
				jQuery(this).height(jQuery('img.small_image', this).height());
				maxSlideHeight = Math.max(maxSlideHeight, jQuery(this).height());
			}
		});
		/*//auto height - by tallest slide*/
		$flexslides.height(maxSlideHeight);
	} else {
		jQuery('img.small_image', $flexslides).hide();
		jQuery('div.content', $flexslides).show();
		/*//restore original content margin top*/
		jQuery('div.content', $flexslides).css('marginTop', '30px');
		/*//restore bg image*/
		$flexslides.each(function (i, v) {
			jQuery(this).css('background-image', jQuery(this).attr('data-bg'));
		});
		if (CONFIG_SLIDESHOW.height != 'auto') {
			$flexslides.height(CONFIG_SLIDESHOW.height);
		} else {
			var maxSlideHeight = null;
			/*//set slide height according to height of content and image*/
			$flexslides.each(function (i, v) {
				var $imgH = jQuery(this).attr('data-img-height');
				/*//count content height*/
				var $contentH = jQuery('div.content', this).actual('height') + parseInt(jQuery('div.content', this).css('marginTop')) + parseInt(jQuery('div.content', this).css('marginBottom'));
				jQuery(this).height(Math.max($imgH, $contentH) + 'px');
				maxSlideHeight = Math.max(maxSlideHeight, jQuery(this).height());
			});
			if (CONFIG_SLIDESHOW.smoothHeight) {
				/*//smooth height*/
			} else {
				/*//auto height - by tallest slide*/
				$flexslides.height(maxSlideHeight);
			}
		}
		/*//adjust content vertical center*/
		$flexslides.each(function (i, v) {
			vericalCenterSlideContent(jQuery(this));
		});
	}
}

/* backup original images for slides */
$flexslides.each(function (i, v) {
	jQuery(this).attr('data-bg', jQuery(this).css('background-image'));
});

slideshowResize = function () {
	timeline.width = $flexslider.width();
	var resize = isResize('slideshow');
	if (resize || _resizeLimit['slideshow'] <= 1) {
		setSlideHeight();
	}
}

slideshowResize();

jQuery(window).resize(function () {
	var interval = (timeline.width - jQuery('#slide-timeline').width()) / (timeline.width / timeline.interval);
	runTimeline(interval);
	slideshowResize();
});

runTimeline = function (interval) {
	
	if (slideshowPause
		|| interval == 0
		|| CONFIG_SLIDESHOW.slideshow == false
		|| CONFIG_SLIDESHOW.timeline == false
		|| $flexslides.length < 2) {
		return;
	}

	jQuery('#slide-timeline')
		.show()
		.animate(
			{width: timeline.width + 'px'},
			interval,
			'linear',
			function () {
				jQuery(this).hide().width(0);
				jQuery('#flexslider').flexslider("next");
			}
		);
}

$flexslider.on({
	mouseenter: function () {
		slideshowPause = true;
		slideshowHover = true;
		jQuery('#slide-timeline').stop(true);
	},
	mouseleave: function () {
		slideshowPause = false;
		slideshowHover = false;
		var interval = (timeline.width - jQuery('#slide-timeline').width()) / (timeline.width / timeline.interval);
		runTimeline(interval);
	},
	touchstart: function () {
		jQuery('#slide-timeline').stop(true);
	}

});

var defaults = {
	slideshow: (CONFIG_SLIDESHOW.slideshow && CONFIG_SLIDESHOW.timeline == false ? true : false),
	initDelay: 200,
	start: function (slider) {
		setSlideHeight();
		/*//line up direction nav*/
		if (CONFIG_SLIDESHOW.smoothHeight) {
			jQuery('.flex-direction-nav a', slider).css('marginTop', (-jQuery('li.flex-active-slide', slider).height() / 2 - 40));
			var img_url = jQuery('li.flex-active-slide').attr('id');
			jQuery('li.flex-active-slide').css("background", "url(" + img_url + ") 50% 0 no-repeat");

		} else {
			jQuery('.flex-direction-nav a', slider).css('marginTop', (-jQuery('.flexslider').height() / 2 - 40));
		}
		runTimeline(timeline.interval);
	},
	before: function (slider) {
		jQuery('#slide-timeline').hide().width(0);
		jQuery('.flex-direction-nav a', slider).hide();
	},
	after: function (slider) {
		if (!slideshowHover) {
			slideshowPause = false;
		}
		if (CONFIG_SLIDESHOW.smoothHeight) {
			jQuery('.flex-direction-nav a', slider).css('marginTop', (-jQuery('li.flex-active-slide', slider).height() / 2 - 40));
			var img_url = jQuery('li.flex-active-slide').attr('id');
			jQuery('li.flex-active-slide').css("background", "url(" + img_url + ") 50% 0 no-repeat");
		}
		jQuery('.flex-direction-nav a', slider).show();
		jQuery('#slide-timeline').stop(true);
		jQuery('#slide-timeline').hide().width(0);
		runTimeline(timeline.interval);
	}
}

vars = jQuery.extend({}, CONFIG_SLIDESHOW, defaults);


$flexslider.flexslider(vars);


		}
	});
}




jQuery(window).on('load', function () {

	function setCookie(cname, cvalue, exdays) {
		var d = new Date();
		d.setTime(d.getTime() + (exdays * 60 * 60 * 1000));
		var expires = "expires=" + d.toGMTString();
		document.cookie = cname + "=" + cvalue + "; " + expires + ";path=/";
	}

	function getCookie(cname) {
		var name = cname + "=";
		var ca = document.cookie.split(';');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ')
				c = c.substring(1);
			if (c.indexOf(name) == 0) {
				return c.substring(name.length, c.length);
			}
		}
		return "";
	}
	
});

function homeReadMore() {
	jQuery('.more').readmore({
		speed: 1500,
		collapsedHeight: 130,
		moreLink: "<a href='#' class='morelink'>" + (Translator.translate("Read more")) + "</a>",
		lessLink: "<a href='#' class='morelink'>" + (Translator.translate("Read less")) + "</a>",
		afterToggle: function (trigger, element, expanded) {
			var ht = jQuery('header.fixed').height() + 150;
			if (!expanded) {
				jQuery('html, body').animate({
					scrollTop: jQuery('.information').offset().top - ht
				}, 300);
				jQuery('.morelink').removeClass('opened');
			} else {
				jQuery('.morelink').addClass('opened');
			}
		}
	});
}

function homeFeedbackReview() {
	jQuery('#feedbackslider.feedback').slick({
			speed: 600,
			slidesToShow: 3,
			slidesToScroll: 1,
			draggable: false,
			prevArrow: '<button type="button" class="slick-prev slick-arrow fa fa-angle-left"></button>',
			nextArrow: '<button type="button" class="slick-next slick-arrow fa fa-angle-right"></button>',
			adaptiveHeight: true,
			responsive: [
				{
					breakpoint: 1025,
					settings: {
						slidesToShow: 2
					}
				},
				{
					breakpoint: 991,
					settings: {
						slidesToShow: 1
					}
				},
				{
					breakpoint: 767,
					settings: {
						slidesToShow: 1
					}
				}
			]
	});
}