import { setSettings } from "./messaging";

(() => {
    window.setTimeout(() => {
        if (document.querySelector("#plugin-btn") == null) {
            // Only for plugin users...
            document.querySelectorAll("#languages a").forEach(anchor => {
                anchor.addEventListener("click", e => {
                    e.preventDefault();
                    let href = anchor.href;
                    // The interface language, in the cookie that means only
                    // that. It used to be written to web_setting_m, the web
                    // fokus's market filter, which is why picking a language
                    // also picked a search region.
                    let new_lang = anchor.hreflang;
                    setSettings({ mg_locale: new_lang }).then(() => {
                        document.location.href = href;
                    });
                });
            });
        }
    }, 250);
})();