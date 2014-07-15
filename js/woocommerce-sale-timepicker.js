/*
 * Copyright 2013, Fikri Rasyid - http://fikrirasyid.com
 * License: Distributed under the terms of GNU General Public License
*/
jQuery(document).ready(function($) { 
	// Destroy existing datepicker init
	$( ".sale_price_dates_fields input" ).datetimepicker( 'destroy' );

	// Rebuilding the UX, using datetimepicker
	var dates = $( ".sale_price_dates_fields input" ).datetimepicker({
		defaultDate: "",
		dateFormat: "yy-mm-dd",
		timeFormat: "HH:mm",
		numberOfMonths: 1,
		showButtonPanel: true,
		showOn: "button",
		buttonImage: woocommerce_admin_meta_boxes.calendar_image,
		buttonImageOnly: true,
		onSelect: function( selectedDate ) {
			var option = $(this).is('#_sale_price_dates_from, .sale_price_dates_from') ? "minDate" : "maxDate";
			var date = $(this).datepicker('getDate');
			dates.not( this ).datetimepicker( "option", option, date );
		}
	});

	$( ".date-picker" ).datetimepicker({
		dateFormat: "yy-mm-dd",
		timeFormat: "HH:mm",
		numberOfMonths: 1,
		showButtonPanel: true,
		showOn: "button",
		buttonImage: woocommerce_admin_meta_boxes.calendar_image,
		buttonImageOnly: true
	});

	$( ".date-picker-field" ).datetimepicker({
		dateFormat: "yy-mm-dd",
		timeFormat: "HH:mm",
		numberOfMonths: 1,
		showButtonPanel: true,
	});	
});