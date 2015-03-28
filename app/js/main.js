var loc_panel = null;
var settings_panel = null;
var help_panel = null;

var get_user = function () {
    return null;
}

var is_logged_in = function (user) {
    return (user !== null);
}

var show_panel = function (panel, args) {
}

var check_login = function (panel) {
    var user = get_user();
    if (is_logged_in(user)) {
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
