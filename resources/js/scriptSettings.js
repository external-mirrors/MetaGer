document.addEventListener("DOMContentLoaded", (event) => {
    document.querySelectorAll("#setting-form select").forEach((element) => {
        element.onchange = (e) => {
            e.srcElement.form.submit();
        };
    });
});