function startLoading(button_id, spinner_id, icon_id = "") {
    $("#" + button_id).prop("disabled", true);
    if (icon_id != "") {
        $("#" + icon_id).hide();
    }
    $("#" + spinner_id).show();
}

function endLoading(button_id, spinner_id, icon_id = "") {
    $("#" + button_id).prop("disabled", false);
    if (icon_id != "") {
        $("#" + icon_id).show();
    }
    $("#" + spinner_id).hide();
}