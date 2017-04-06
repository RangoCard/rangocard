/**
 * @author Rangocard
 */
var geocoder = new google.maps.Geocoder();

function getPosition(userId) {
    // var cookieValue = document.cookie.replace(new RegExp("(?:(?:^|.*;)\\s*" + 'userLocated'+userId + "\\s*\\=\\s*([^;]*).*$)|^.*$"), "$1");
    // if (!cookieValue) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                    var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                    geocoder.geocode({'latLng': latlng}, function (data, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            // var add = data[1].formatted_address;
                            var local = {
                                'city': data[0].address_components[3].long_name,
                                'state': data[0].address_components[5].short_name,
                                'district': data[0].address_components[2].long_name,
                                'street': data[0].address_components[1].long_name
                            };
                            sendLocalization(userId, local);
                        }
                    });
                },
                function (failure) {
                }
            );
        }
    // }
}

function sendLocalization(userId, localization) {
    $.post(currentLocalUrl, {localization: localization}, function (response) {
        if (response.success) {
            // document.cookie = 'userLocated'+userId+'=true;max-age='+86400+';';
        }
    });
}