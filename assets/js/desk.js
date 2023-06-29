import 'leaflet';
import logoPath from '../images/desktop_plan.png';

const apiBaseUrl = '/fr/desk/api';

let map = null;
let bounds = [];

function loadDesktop() {

	let nodeDomDate = document.getElementById('selectedDate');

	$.ajax({
		url: apiBaseUrl + "/list?selectedDate=" + nodeDomDate.value
	}).then(function (data) {
		data.forEach(function (elem) {
			if (typeof elem.available == 'undefined') {
				elem.available = 1;
			}
			L.circle([elem.x, elem.y], {
				color: getAvailableColor(elem),
				weight: 1,
				desktop: elem
			})
				.addTo(map)
				.on('click', function (it) {
					$.ajax({
						url: apiBaseUrl + "/scan/" + it.target.options.desktop.id + "?selectedDate=" + nodeDomDate.value
					}).then(function (data) {
						if (data.success == false) {
							alert(data.result);
						} else {
							it.target.options.desktop.available = !it.target.options.desktop.available;
							it.target.options.desktop.me = true;
							it.target.options.color = getAvailableColor(it.target.options.desktop);
							it.target.setStyle(it.target.options);
						}
					});
				});
		});
	});
	map.fitBounds(bounds);
}

function getAvailableColor(desktop) {
	let deskColor = 'green';
	if (!desktop.available) {
		if (!desktop.me) {
			deskColor = 'red';
		} else {
			deskColor = 'blue';
		}
	}

	return deskColor;
}

document.addEventListener('DOMContentLoaded', function () {
	map = L.map('map', {crs: L.CRS.Simple});
	bounds = [[0, 0], [610, 1280]]; // Set the image resolution as the boundary
	L.imageOverlay(logoPath, bounds).addTo(map); // Set the background image

	loadDesktop();

	let nodeDomDate = document.getElementById('selectedDate');
	nodeDomDate.addEventListener('change', function(){
		let nodeDomFormDate = document.getElementById('dateSelectionForm');
		nodeDomFormDate.submit();
	})
}, false);
