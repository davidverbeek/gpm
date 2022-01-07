jQuery(document).ready(function () {
	jQuery(".gdprdeletlink a").click(function () {
		scrollToDiv('#gdprdeletion');
	});

	jQuery(".gdprexport a").click(function () {
		scrollToDiv('#gdprexport');
	});
});

// GDPR block scroll functionality design
//Added by DD 18-06-2018
function scrollToDiv(strSelector) {
  var fixHeaderHeight = jQuery('header').outerHeight();
  jQuery('html, body').animate({
    // remove fix header height so content displayed in visible area
    scrollTop: jQuery(strSelector).offset().top - fixHeaderHeight - 10
  }, 2000);
}