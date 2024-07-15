/**
 * Class to gather and report anonymous statistics 
 * to our self hosted matomo instance.
 */
class Statistics {
    #load_complete = false;

    constructor() {
        let performance = window.performance.getEntriesByType('navigation')[0];
        if (performance.loadEventEnd != 0) {
            this.#init();
        } else {
            window.addEventListener("load", e => {
                let readyStateCheckInterval = setInterval(() => {
                    performance = window.performance.getEntriesByType('navigation')[0];
                    if (performance.loadEventEnd == 0) return;
                    console.log("load end");
                    clearInterval(readyStateCheckInterval);
                    this.#init();
                }, 100);
            });
        }
    }

    #init() {
        this.pageLoad();
        document.querySelectorAll("a").forEach(anchor => {
            anchor.addEventListener("click", e => this.pageLeave(e.target.href).bind(this));
        });
    }

    pageLeave(target) {
        let params = {};
        params.url = target;
        params.link = target;
        try {
            let url = new URL(target);
            if (url.host == document.location.host) return;
            this.pageLoad(params);
            navigator.sendBeacon("/stats/pl", new URLSearchParams(params));
        } catch (error) { }
    }

    pageLoad(overwrite_params = {}) {
        if (this.#load_complete) return;
        this.#load_complete = true;

        let params = {};

        // Page performance
        try {
            let performance = window.performance.getEntriesByType('navigation')[0];
            params.pf_net = performance.connectEnd - performance.connectStart;
            params.pf_srv = performance.responseStart - performance.requestStart;
            params.pf_tfr = performance.responseEnd - performance.responseStart;
            params.pf_dm1 = performance.domInteractive - performance.responseEnd;
            params.pf_dm2 = performance.domContentLoadedEventEnd - performance.domContentLoadedEventStart;
            params.pf_onl = performance.loadEventEnd - performance.loadEventStart;
        } catch (error) { }

        try {
            params.res = window.screen.width + "x" + window.screen.height;
        } catch (error) { }

        // Page URL
        try {
            params.url = document.location.href;
        } catch (error) { }

        // Page Title
        try {
            params.action_name = document.title
        } catch (error) { }

        // Referrer
        try {
            params.urlref = document.referrer;
        } catch (error) { }

        // Cookies available 
        try {
            params.cookie = navigator.cookieEnabled ? "1" : "0";
        } catch (error) { }

        params = { ...params, ...overwrite_params };

        navigator.sendBeacon("/stats/pl", new URLSearchParams(params));
    }
}
export const statistics = new Statistics();