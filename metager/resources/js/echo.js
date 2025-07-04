import Echo from "laravel-echo";

(async () => {
    window.Pusher = require("pusher-js");
    window.Echo = new Echo({
        broadcaster: "reverb",
        key: "METAGER_TESTING", // ToDo use a real key
        wsHost: window.location.hostname,
        wsPort: window.location.port,
        enabledTransports: ['ws', 'wss'],
        forceTLS: window.location.protocol === "https:" ? true : false,
    });

    window.Echo.private("App.Models.Authorization.Key.f5d1278e-8109-4dd9-be1e-4197e04873b9").listen("KeyChanged", (e) => {
        console.log("Authorization key updated:", e);
    });

})();