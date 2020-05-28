$(document).ready(function () {
    $("#filter-form, #setting-form").find("button[type=submit]").css("display", "none");
    $("#filter-form, #setting-form").find("select").on("change", function () {
        $(this).closest('form').submit();
    });
});