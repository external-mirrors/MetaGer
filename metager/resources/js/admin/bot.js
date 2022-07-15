document.querySelectorAll("div.user input, div.user select").forEach(element => {
    element.addEventListener("change", event => {
        element.form.submit();
    });

});