function geoFindMe() {

  if (!navigator.geolocation){
    $('#location').html("<p>Geolocation is not supported by your browser</p>");
    return;
  }

  function success(position) {
    var latitude  = position.coords.latitude;
    var longitude = position.coords.longitude;

    $('#location').html('<p>Latitude is ' + latitude + '° <br>Longitude is ' + longitude + '°</p>');

    var mapsrc = "https://maps.googleapis.com/maps/api/staticmap?center=" + latitude + "," + longitude + "&zoom=13&size=300x300&sensor=false";

    $('#location').append($("<img>").attr('src', mapsrc));
  };

  function error() {
    $('#location').html("Unable to retrieve your location");
  };

  $('#location').html("<p>Locating…</p>");

  navigator.geolocation.getCurrentPosition(success, error);
}

$().ready(function(){
  geoFindMe();
})