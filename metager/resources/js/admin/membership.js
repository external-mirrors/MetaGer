(async () => {
    document.querySelectorAll(".membership-deny").forEach(deny_button => {
        deny_button.addEventListener("click", e => {
            if (!confirm("Aufnahmeantrag wirklich ablehnen?")) {
                e.preventDefault();
            }
        });
    });
})();

(async () => {
    document.querySelectorAll(".reduction-deny").forEach(deny_button => {
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