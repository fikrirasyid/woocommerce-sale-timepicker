/*
 * Copyright 2013, Fikri Rasyid - http://fikrirasyid.com
 * License: Distributed under the terms of GNU General Public License
*/
jQuery(document).ready(function($) { 
	// Destroy existing datepicker init
	$( ".sale_price_dates_fields input" ).datetimepicker( 'destroy' );

	// Update the pattern attribute to accept datetime
	// $( ".sale_price_dates_fields input" ).attr({ 'pattern' : '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01]) (0[1-9]|1[023]):(0[1-9]|1[059])' });
	// $( ".sale_price_dates_fields input" ).removeAttr( 'pattern' );

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

			// var instance = $( this ).data( "datepicker" ),
			// 	date = $.datepicker.parseDate(
			// 		instance.settings.dateFormat ||
			// 		$.datepicker._defaults.dateFormat,
			// 		selectedDate, instance.settings );
			var date = $(this).datepicker('getDate');

			console.log( selectedDate );
	
			console.log( date );
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