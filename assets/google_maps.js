var map;
var geocoder;

function load() {
  if (GBrowserIsCompatible()) {
	geocoder = new GClientGeocoder();
	map = new GMap2(document.getElementById('clients_map'));
	//map.addControl(new GSmallMapControl());
	//map.addControl(new GMapTypeControl());
	map.setCenter(new GLatLng(40, 0), 1);
	map.setUIToDefault();
	map.enableRotation();
  }
}

function searchLocations() {
 var address = document.getElementById('addressInput').value;
 geocoder.getLatLng(address, function(latlng) {
   if (!latlng) {
	 alert(address + ' not found');
   } else {
	 searchLocationsNear(latlng);
   }
 });
}

// ajax.php?mode=googleXML&lat=26.716&lng=-80.209&radius=2500
function searchLocationsNear(center) {
 var type_id = document.getElementById('type_id').value;
 var radius = document.getElementById('radiusSelect').value;
 var searchUrl = 'ajax&mode=googleXML&lat=' + center.lat() + '&lng=' + center.lng() + '&radius=' + radius;
 if(type_id) searchUrl += '&type_id='+type_id;
 GDownloadUrl(searchUrl, function(data) {
   var xml = GXml.parse(data);
   var markers = xml.documentElement.getElementsByTagName('marker');
   map.clearOverlays();

   var sidebar = document.getElementById('clients_sidebar');
   sidebar.innerHTML = '';
   if (markers.length == 0) {
	 sidebar.innerHTML = 'No results found.';
	 map.setCenter(new GLatLng(40, -100), 4);
	 return;
   }

   var bounds = new GLatLngBounds();
   for (var i = 0; i < markers.length; i++) {
	 var clientname = markers[i].getAttribute('name');
	 var address = markers[i].getAttribute('address');
	 var phone = markers[i].getAttribute('phone');
	 var website = markers[i].getAttribute('website');
	 var email = markers[i].getAttribute('email');
	 var type_id = markers[i].getAttribute('type_id');
	 var distance = parseFloat(markers[i].getAttribute('distance'));
	 var point = new GLatLng(parseFloat(markers[i].getAttribute('lat')),
							 parseFloat(markers[i].getAttribute('lng')));

	 var marker = createMarker(point, clientname, address, phone,website,email,type_id);
	 map.addOverlay(marker);
	var sidebarEntry = createSidebarEntry(marker, clientname, address, phone, distance,website,email,type_id);
	 sidebar.appendChild(sidebarEntry);
	 bounds.extend(point);
   }
   map.setCenter(bounds.getCenter(), map.getBoundsZoomLevel(bounds));
 });
}

function createMarker(point, clientname, address,phone,website,email,type_id) {
	var marker = new GMarker(point);

	var html = '<ul class="googleMapMarker">';
	html += '<li class="clientname"><strong>'+clientname+'</strong></li>';
	html += '<li class="type">'+type_id+'</li>';
	html += '<li class="address">'+address+'</li>';
	html += '<li class="phone">'+phone+'</li>';
	html += '<li class="website"><a href="'+website+'" target="_blank">'+website.replace('http://','')+'</a></li>';
	html += '<li class="email"><a href="mailto:'+email+'">'+email+'</a></li>';
	html += '</ul><br clear="all" /><br clear="all" />';

	//html = '<ul><li>hello</li></ul>';
	GEvent.addListener(marker, 'click', function() {
		marker.openInfoWindowHtml(html);
	});
	return marker;
}

function createSidebarEntry(marker, name, address, phone, distance,website,email,type_id) {
  var div = document.createElement('div');

  	var html = '<strong>'+name+'</strong> (' + distance.toFixed(1) + ' km) <br />'+type_id+'<br />'+address+'<br />'+phone+'<br /><a href="'+website+'" target="_blank">'+website.replace('http://','')+'</a><br /><a href="mailto:'+email+'">'+email+'</a><br />';
 // var html = '<strong>' + name + '</strong> (' + distance.toFixed(1) + ' km)<br/>' + address + '<br /> '+phone;
  div.innerHTML = html;
  div.style.cursor = 'pointer';
  div.style.marginBottom = '5px';
  div.style.padding = '5px';
  GEvent.addDomListener(div, 'click', function() {
	GEvent.trigger(marker, 'click');
	div.style.backgroundColor = '#eee';
  });
  GEvent.addDomListener(div, 'mouseover', function() {
	div.style.backgroundColor = '#eee';
  });
  GEvent.addDomListener(div, 'mouseout', function() {
	div.style.backgroundColor = '#fff';
  });
  return div;
}