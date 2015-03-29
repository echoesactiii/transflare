var loc_panel = $('#loc_panel');
var settings_panel = $('#settings_panel');
var help_panel = $('#help_panel');
var login_panel = $('#login_panel');

var api_url = "http://transflare.org/api";

var verify_session = function () {
    $.ajax({
        type: "GET",
        url: api_url + "/authentication",
        beforeSend: function(xhr, settings){
            xhr.setRequestHeader("X-Token", apiToken); // Todo: apiToken needs to get the token from localstorage.
            xhr.setRequestHeader("Content-Type", "application/json");
        },
        success: function (data){
            // Decode JSON in variable 'data' and the user data will be there (avatar URL, username, email address)
        },
        error: function (xhr){
            alert($.parseJSON(xhr.responseText).error);
        }
    });
}

var login = function (username, password) {
    var postdata = new Array();
    postdata['username'] = username;
    postdata['password'] = password;

    $.ajax({
        type: "POST",
        url: api_url + "/authentication",
        data: JSON.stringify(postdata),
        beforeSend: function(xhr, settings){
            xhr.setRequestHeader("Content-Type", "application/json");
        },
        success: function (data){
            // Decode JSON in variable 'data' and get session token, store it in localstorage.
        },
        error: function (xhr){
            alert($.parseJSON(xhr.responseText).error);
        }
    });
}

var show_panel = function (panel) {
    console.log("show_panel");
    panel.modal('show');
}

var get_user = function () {
    return localStorage.getItem('user_token');
}

var is_logged_in = function (user) {
    return (user !== null && validate_token(user));
}

var validate_token = function (token) {
    // TODO: AJAX
    return true;
}

var check_login = function (panel) {
    var user = get_user();
    if (is_logged_in(user)) {
        console.log("is_logged_in");
        show_panel(panel);
    } else {
        show_panel(login, panel);
    }
}

$(document).ready(function () {
    $("#nav_loc").click(function () { check_login(loc_panel) });
    $("#nav_settings").click(function () { check_login(settings_panel) });
    $("#nav_help").click(function () { check_login(help_panel) });
})

