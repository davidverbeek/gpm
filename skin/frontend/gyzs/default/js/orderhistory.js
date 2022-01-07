jQuery(document).ready(function () {
	// Add toggle in pending order tab

	jQuery('.pendingorders tbody').each(function () {
		var LiN = jQuery(this).find('tr').length;
		if (LiN > 5) {
			jQuery('tr', this).eq(4).nextAll().not('.last-pending').hide().addClass('toggleable');
		} else {
			jQuery('.last-pending').hide();
		}
	});

	jQuery('.view-all-order-wrap').on('click', '.more', function () {
		if (jQuery(this).hasClass('less')) {
			jQuery(this).removeClass('less');
			jQuery(this).html('Laat meer zien');
		} else {
			jQuery(this).addClass('less');
			jQuery(this).html('Laat minder zien');
		}

		jQuery('.pendingorders tbody tr.toggleable').slideToggle();
	});

	// Add toggle in complete order tab

	jQuery('.competeorders tbody').each(function () {
		var LiN = jQuery(this).find('tr').length;
		if (LiN > 5) {
			jQuery('tr', this).eq(4).nextAll().not('.last-complated').hide().addClass('toggleable');
		} else {
			jQuery('.last-complated').hide();
		}
	});

	jQuery('.view-all-order-wrap').on('click', '.more', function () {
		if (jQuery(this).hasClass('less')) {
			jQuery(this).removeClass('less');
			jQuery(this).html('Laat meer zien');
		} else {
			jQuery(this).addClass('less');
			jQuery(this).html('Laat minder zien');
		}

		jQuery('.competeorders tbody tr.toggleable').slideToggle();
	});

	// Add toggle in cancel order tab

	jQuery('.cancelorders tbody').each(function () {
		var LiN = jQuery(this).find('tr').length;
		if (LiN > 5) {
			jQuery('tr', this).eq(4).nextAll().not('.last-canceled').hide().addClass('toggleable');
		} else {
			jQuery('.last-canceled').hide();
		}
	});

	jQuery('.view-all-order-wrap').on('click', '.more', function () {
		if (jQuery(this).hasClass('less')) {
			jQuery(this).removeClass('less');
			jQuery(this).html('Laat meer zien');
		} else {
			jQuery(this).addClass('less');
			jQuery(this).html('Laat minder zien');
		}

		jQuery('.cancelorders tbody tr.toggleable').slideToggle();
	});
});