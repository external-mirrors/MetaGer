const { setSettings } = require("./messaging");

(() => {
    window.setTimeout(() => {
        if (document.querySelector("#plugin-btn") == null) {
            // Only for plugin users...
            document.querySelectorAll("#languages a").forEach(anchor => {
                anchor.addEventListener("click", e => {
                    e.preventDefault();
                    let href = anchor.href;
                    let new_lang = anchor.hreflang;
                    new_lang = new_lang.replace("-", "_");
                    setSettings({ web_setting_m: new_lang }).then(() => {
                        document.location.href = href;
                    });
                });
            });
        }
    }, 250);
})();