import { afterEach, describe, expect, it } from "vitest";

import { PLACEHOLDER, dropBrokenImage, watchBrokenImages } from "./images";

function result() {
    const div = document.createElement("div");
    div.className = "result";
    div.innerHTML = `<div class="result-body with-image">
        <div class="result-description">Beschreibung</div>
        <div class="result-image"><a href="#"><img src="http://localhost/meta/picture?data=x" alt=""></a></div>
        <div class="result-footer"></div>
    </div>`;
    document.body.append(div);
    return div;
}

function card() {
    const div = document.createElement("div");
    div.className = "inline-result";
    div.innerHTML = `<a class="result-image" href="#"><img src="http://localhost/meta/picture?data=y" alt=""></a>`;
    document.body.append(div);
    return div;
}

afterEach(() => {
    document.body.innerHTML = "";
});

describe("a result picture that does not load", () => {
    it("leaves a result without a picture", () => {
        const hit = result();

        dropBrokenImage(hit.querySelector("img"));

        expect(hit.querySelector(".result-image")).toBeNull();
        expect(hit.querySelector(".result-body").classList.contains("with-image")).toBe(false);
    });

    it("puts the placeholder in a news or video card", () => {
        const news = card();

        dropBrokenImage(news.querySelector("img"));

        expect(news.querySelector("img").getAttribute("src")).toBe(PLACEHOLDER);
    });

    it("does not loop when the placeholder itself fails", () => {
        const news = card();
        const img = news.querySelector("img");
        img.src = PLACEHOLDER;
        const before = img.src;

        dropBrokenImage(img);

        expect(img.src).toBe(before);
    });

    it("is caught however late it fails, load-more's results included", () => {
        watchBrokenImages(document);
        const late = result();

        late.querySelector("img").dispatchEvent(new Event("error"));

        expect(late.querySelector(".result-image")).toBeNull();
    });

    it("leaves pictures elsewhere on the page alone", () => {
        watchBrokenImages(document);
        const logo = document.createElement("img");
        logo.src = "/img/metager.svg";
        document.body.append(logo);

        logo.dispatchEvent(new Event("error"));

        expect(logo.isConnected).toBe(true);
    });
});
