(() => {
    document.getElementById("membership-deny").addEventListener("click", e => {
        if (!confirm("Aufnahmeantrag wirklich ablehnen?")) {
            e.preventDefault();
        }
    });
})();