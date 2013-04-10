var map, polys = [];
var mapOptions = {
	zoom: 4,
	center: new google.maps.LatLng(42.16,-100.72),
	mapTypeId: google.maps.MapTypeId.ROADMAP
};
var openinfowindow = null; //to only have one infowindow at a time

map = new google.maps.Map(document.getElementById('fusion_retailers_map'), mapOptions);
jQuery.get(fusion_maps_vars.states_xml, {}, function(data) {
	jQuery(data).find("state").each(function() {
		var state = this;
		var statename = state.getAttribute('name');
		var color = fusion_maps_vars.colors[statename];
		var points = this.getElementsByTagName("point");
		var pts = [];
		for (var i = 0; i < points.length; i++) {
			pts[i] = new google.maps.LatLng(parseFloat(points[i].getAttribute("lat")), parseFloat(points[i].getAttribute("lng")));
		}
		var poly = new google.maps.Polygon({
			paths: pts,
			strokeColor: '#000000',
			strokeOpacity:1,
			strokeWeight:1,
			fillColor: color,
			fillOpacity: 0.2
		});
		polys.push(poly);
		poly.setMap(map);

		var infowindow = new google.maps.InfoWindow({
			content: fusion_maps_vars.state_info[statename]
		});

		google.maps.event.addListener( poly, "click", function( evt ) {
			if ( openinfowindow ) {
				openinfowindow.close();
			}
			infowindow.open(map);
			infowindow.setPosition(evt.latLng);
			openinfowindow = infowindow;
		});
	});
});