/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/*jslint devel: true, bitwise: true, regexp: true, browser: true, unparam: true, evil: true, white: true, nomen: true, plusplus: true, maxerr: 50, indent: 4 */
/*global jQuery, Node, google */

(function($) {
	"use strict";

	/**
	 * Client side integration wrapper with Google maps.
	 */
	$.extend(true, $.sitegear, {
		initialiseGoogleMap: function($elem, initialView, markers, options, autoShowInfoWindow) {
			var defaultMapOptions = { mapTypeId: 'hybrid' },
				map = new google.maps.Map($elem[0], $.extend(true, defaultMapOptions, options.map, {
					zoom: initialView.zoom,
					center: new google.maps.LatLng(initialView.latitude, initialView.longitude)
				})),
				displayedInfoWindow = null,
				// Show the info window for the given item.
				showInfoWindow = function(marker) {
					if (displayedInfoWindow !== null) {
						displayedInfoWindow.setMap(null);
					}
					map.setCenter(marker.latlng);
					map.setZoom(marker.zoom);
					marker.infoWindow.open(map, marker.marker);
					displayedInfoWindow = marker.infoWindow;
				},
				// Setup the given marker.
				setup = function(marker) {
					var content,
						extraOptions = $.isPlainObject(marker.options) ? marker.options : {};
					marker.latlng = new google.maps.LatLng(marker.latitude, marker.longitude);
					marker.marker = new google.maps.Marker($.extend(true, {}, options.marker, extraOptions, {
						position: marker.latlng,
						map: map
					}));
					content = marker.name ? '<div class="marker-name">' + marker.name + '</div>' : '';
					content += marker.description ? '<div class="marker-description">' + marker.description + '</div>' : '';
					marker.infoWindow = new google.maps.InfoWindow($.extend(true, {}, options.infoWindow, {
						content: content
					}));
					google.maps.event.addListener(marker.marker, 'click', function() {
						showInfoWindow(marker);
					});
					google.maps.event.addListener(marker.infoWindow, 'closeclick', function() {
						map.setCenter(new google.maps.LatLng(initialView.latitude, initialView.longitude));
						map.setZoom(initialView.zoom);
					});
					return marker;
				};
			// Setup each marker, storing the modified item back into the array.
			$.each(markers, function(index, marker) {
				markers[index] = setup(marker);
			});
			if (autoShowInfoWindow) {
				showInfoWindow(markers[0]);
			}
		}
	});
}(jQuery));
