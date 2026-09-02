import { format } from "./voucher/code-format";

/**
 * Der Gutschein einlösen — /c, angereichert.
 *
 * Ein Bündel für vier Seiten (resources/views/voucher/*.blade.php), und jede
 * bringt nur einen Teil dessen mit, was hier steht: das Codefeld gibt es auf
 * der Eingabeseite, die Kopierknöpfe und die Cookie-Frage nur auf der Seite
 * nach dem Einlösen. Deshalb prüft hier jede der drei Zugaben zuerst, ob es
 * ihren Griff überhaupt gibt.
 *
 * Zugaben sind sie alle drei: der Gutschein wird ohne eine Zeile hiervon
 * eingelöst. Ein Code, der ohne Bindestriche getippt wurde, ist derselbe Code —
 * das normalisiert der Server, und er muss es, weil es diese Datei ohne
 * Javascript nicht gibt.
 */

enhanceCodeInput();
enhanceCopyButtons();
warnAboutMissingCookies();

/**
 * Die Schreibhilfe im Codefeld.
 *
 * Aus dem Keymanager übernommen. Sie macht aus jeder Eingabe dieselbe Gestalt —
 * groß geschrieben, in Vierergruppen —, damit ein Code, der von einer Karte
 * abgelesen wird, so im Feld steht wie auf der Karte. Was dabei gerechnet
 * werden muss, steht in voucher/code-format.js, wo es zu prüfen ist.
 */
function enhanceCodeInput() {
    const input = document.getElementById("voucher-code");
    if (!input) {
        return;
    }

    const codeLength = parseInt(input.dataset.codeLength, 10) || 10;

    input.addEventListener("input", () => {
        const raw = input.value;
        const caret = input.selectionStart ?? raw.length;
        const next = format(raw, caret, codeLength);

        // Nur anfassen, wenn sich etwas ändert: das Feld neu zu beschriften
        // setzt den Cursor ans Ende, und das wieder geradezurücken ist bei
        // jedem Tastendruck eine Bewegung, die niemand braucht.
        if (next.value === raw) {
            return;
        }

        input.value = next.value;
        input.setSelectionRange(next.caret, next.caret);
    });
}

/**
 * Die Kopierknöpfe.
 *
 * Wortgleich zu resources/js/key-create.js, weil das Markup daneben es auch
 * ist: `[data-copies]` nennt das Feld, `data-done` die Beschriftung danach,
 * und `hidden` steht in der Vorlage. Aufgedeckt nur, wenn es eine
 * Zwischenablage gibt — sie ist an einen sicheren Kontext gebunden, und wo es
 * sie nicht gibt, ist das `readonly`-Feld daneben die Antwort.
 */
function enhanceCopyButtons() {
    if (!navigator.clipboard || typeof navigator.clipboard.writeText !== "function") {
        return;
    }

    for (const button of document.querySelectorAll("[data-copies]")) {
        const source = document.getElementById(button.dataset.copies);
        if (!source) {
            continue;
        }

        button.hidden = false;

        // Einmal gemerkt und nicht bei jedem Klick: zweimal kurz hintereinander
        // geklickt, und die Beschriftung, auf die zurückgestellt wird, wäre
        // „Kopiert“.
        const label = button.textContent;

        button.addEventListener("click", async () => {
            try {
                await navigator.clipboard.writeText(source.value);
            } catch {
                // Verweigert oder nicht erlaubt. Dann markieren wir wenigstens,
                // was der Knopf kopiert hätte — von dort ist es ein Tastendruck.
                source.focus();
                source.select();
                return;
            }

            button.textContent = button.dataset.done;
            setTimeout(() => {
                button.textContent = label;
            }, 2000);
        });
    }
}

/**
 * Ob dieser Browser ein Cookie behält.
 *
 * Hier wiegt die Frage schwerer als auf der Seite zum Erstellen: Wer einen
 * Gutschein einlöst, hat gerade einen Schlüssel bekommen, den er nirgends
 * abgeholt hat und nirgends wiederfindet, wenn dieser Browser ihn nicht
 * behält. Der Absatz steht deshalb neben dem Schlüsselfeld und nicht am Ende.
 *
 * Probiert wird es mit einem eigenen Namen und nicht mit `key`: ein Versuch,
 * der den Schlüssel schreibt, hätte ihn im gelungenen Fall an einer Stelle
 * gesetzt, an der ihn niemand gesetzt haben wollte.
 */
function warnAboutMissingCookies() {
    const warning = document.getElementById("voucher-no-cookies");
    if (!warning) {
        return;
    }

    const name = "cookie_test";
    document.cookie = `${name}=1; Max-Age=60; Path=/; SameSite=Lax`;

    if (document.cookie.indexOf(`${name}=1`) !== -1) {
        document.cookie = `${name}=; Max-Age=0; Path=/; SameSite=Lax`;
        return;
    }

    warning.hidden = false;
}
