(async () => {
    document.querySelectorAll(".reduction-deny, .application-accept, .application-deny").forEach(deny_button => {
        deny_button.addEventListener("click", (event) => {
            event.preventDefault();
            deny_button.parentElement.querySelector("dialog").showModal()
        });
    });
    document.querySelectorAll(".close-modal").forEach(close_button => {
        close_button.addEventListener("click", event => {
            event.preventDefault();
            close_button.closest("dialog").close();
        });
    });
})();

(async () => {
    window.history.replaceState(null, "Title", window.location.href.split("?")[0]);
})();

(async () => {
    window.setInterval(() => {
        window.location.reload();
    }, 60000)
})();