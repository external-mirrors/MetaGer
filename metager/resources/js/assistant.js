(async () => {
    let chat_form = document.querySelector(".chat-form .input-sizer textarea");
    if (!chat_form) return;
    chat_form.addEventListener("input", updateDataset);

    updateDataset();

    function updateDataset() {
        chat_form.parentNode.dataset.value = chat_form.value;
    }
})();

(async () => {
    return; // ToDo remove
    let chat_prompt_form = document.querySelector("#chat-prompt");
    if (!chat_prompt_form) {
        console.error("cannot find chat prompt form");
        return;
    }

    chat_prompt_form.addEventListener("submit", async e => {
        e.preventDefault();
        let form_data = new FormData(chat_prompt_form);
        return fetch(document.location.href, {
            method: "POST",
            body: form_data,
            headers: {
                "Accept": "application/json"
            }
        }).then(response => {

        });
    });


})();