export async function setSettings(settings) {
    let payload = {
        type: "settings_set",
        settings: settings
    };
    return sendMessage(payload);
}

export async function removeSetting(setting_key) {
    let payload = {
        type: "settings_remove",
        setting_key: setting_key
    };
    return sendMessage(payload);
}

export async function getKeyCharge() {
    let payload = {
        type: "key",
        action: "getcharge"
    };
    return sendMessage(payload);
}

export async function makePayment(payment_request) {
    let payload = {
        type: "tokenauthorization",
        action: "pay",
        payment_request: payment_request
    };
    return sendMessage(payload);
}

export async function getToken(payment_request) {
    let payload = {
        type: "tokenauthorization",
        action: "gettoken",
        payment_request: payment_request
    };
    return sendMessage(payload);
}

export async function putToken(payment_request) {
    let payload = {
        type: "tokenauthorization",
        action: "puttoken",
        payment_request: payment_request
    };
    return sendMessage(payload);
}

async function sendMessage(payload) {
    let message_id = (new Date()).getTime();
    let message = {
        sender: "webpage",
        message_id: message_id,
        payload: payload
    };

    return new Promise(resolve => {
        window.addEventListener("message", receiveMessage);
        window.postMessage(message);

        let timeout = setTimeout(() => {
            resolve(null);
        }, 10000);

        function receiveMessage(event) {
            if (event.source !== window || event?.data?.sender !== "tokenmanager" || event?.data?.message_id !== message_id) return;
            clearTimeout(timeout);
            window.removeEventListener("message", receiveMessage);
            resolve(event.data.payload);
        }
    });
}