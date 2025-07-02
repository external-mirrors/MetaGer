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
    let chat_prompt_form = document.querySelector("#chat-prompt");
    if (!chat_prompt_form) {
        console.error("cannot find chat prompt form");
        return;
    }

    chat_prompt_form.addEventListener("submit", async e => {
        e.preventDefault();

        let chat_element = document.querySelector("#chat");
        if (!chat_element) return;

        for await (let line of fetchLineByLine()) {
            let event = JSON.parse(line);
            switch (event.event) {
                case "message.added":
                    document.querySelector("#empty-chat")?.remove();
                    let div = document.createElement("div");
                    div.innerHTML = event.message_data_html.trim();
                    chat_element.appendChild(div.firstChild);
                    break;
                case "message.removed":
                    let element = document.getElementById(event.message_id);
                    if (element) element.remove();
                    break;
                case "message.finished":
                case "message.content.added":
                case "message.content.updated":
                    let message_id = event.message_id;
                    let message_element = document.getElementById(message_id);
                    if (message_element) {
                        let div = document.createElement("div");
                        div.innerHTML = event.message_data_html.trim();
                        message_element.replaceWith(div.firstChild);
                    }
                    break;
                case "history.updated":
                    let history = event.history;
                    let history_element = document.querySelector("input[type=hidden][name=history]");
                    if (history_element) {
                        history_element.value = history;
                    }
                    break;
                default:
                    console.warn("Unknown event:", event);
                    break;
            }
        }
    });

    const diff = (diffMe, diffBy) => diffMe.split(diffBy).join('');

    async function* fetchLineByLine(fileURL) {
        const utf8Decoder = new TextDecoder("utf-8");

        let form_data = new FormData(chat_prompt_form);
        let response = await fetch(document.location.href, {
            method: "POST",
            body: form_data,
            headers: {
                "Accept": "application/json"
            }
        });

        let prompt_element = document.querySelector("div.chat-form");
        prompt_element.querySelector("textarea").value = "";
        prompt_element.classList.add("hidden");

        let reader = response.body.getReader();
        let { value: chunk, done: readerDone } = await reader.read();
        chunk = chunk ? utf8Decoder.decode(chunk, { stream: true }) : "";

        let re = /\r?\n/g;
        let startIndex = 0;

        for (; ;) {
            let result = re.exec(chunk);
            if (!result) {
                if (readerDone) {
                    break;
                }
                let remainder = chunk.substr(startIndex);
                ({ value: chunk, done: readerDone } = await reader.read());
                chunk =
                    remainder + (chunk ? utf8Decoder.decode(chunk, { stream: true }) : "");
                startIndex = re.lastIndex = 0;
                continue;
            }
            yield chunk.substring(startIndex, result.index);
            startIndex = re.lastIndex;
        }
        if (startIndex < chunk.length) {
            // last line didn't end in a newline char
            yield chunk.substr(startIndex);
        }

        prompt_element.classList.remove("hidden");
    }
})();