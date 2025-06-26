(async () => {
    let chat_form = document.querySelector(".chat-form .input-sizer textarea");
    if (!chat_form) return;
    chat_form.addEventListener("input", updateDataset);

    updateDataset();

    function updateDataset() {
        chat_form.parentNode.dataset.value = chat_form.value;
    }
})();