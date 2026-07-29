<?php

return [
    /*
     * Base URL of the metager-chat service, without a trailing slash.
     *
     * This is where service discovery lives now. It used to be an nginx `location /chat` block
     * proxying straight to the service; with the chat UI rendered by MetaGer itself, the browser
     * never talks to metager-chat and Laravel addresses it directly instead. Same two mechanisms
     * as before, one layer up:
     *
     *   production  http://chat-master.chat:80   (k8s service DNS, Helm release "chat-master")
     *   local dev   http://192.168.5.202:3000    (static IP on the shared metager_net network)
     */
    "url" => env("CHAT_URL", "http://192.168.5.202:3000"),

    /*
     * Shared secret proving a request came through MetaGer rather than reaching the service
     * directly on the network. Mirrors the existing `event_authorization` pattern above.
     * Must match metager-chat's own METAGER_SHARED_SECRET.
     */
    "shared_secret" => env("CHAT_SHARED_SECRET", "no-auth"),

    /*
     * How long to wait for the chat service to *start* responding. Deliberately short: this is
     * connection + first byte, not generation time, which is unbounded by design.
     */
    "connect_timeout" => (int) env("CHAT_CONNECT_TIMEOUT", 10),

    /*
     * Health check used to decide whether the chat focus is offered at all. The timeout is
     * intentionally tiny — this call sits on the search request path via available_foki, so it
     * must never become a source of latency. Failures are cached too, so a down backend doesn't
     * mean a 1s penalty on every request.
     */
    "health_timeout" => (float) env("CHAT_HEALTH_TIMEOUT", 1.0),
    "health_cache_seconds" => (int) env("CHAT_HEALTH_CACHE_SECONDS", 15),

    /*
     * The model catalog changes only when config/models.json in metager-chat is redeployed, so it
     * can be cached far more aggressively than health.
     */
    "models_cache_seconds" => (int) env("CHAT_MODELS_CACHE_SECONDS", 300),
];
