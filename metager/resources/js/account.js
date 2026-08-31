/**
 * Das Konto, angereichert.
 *
 * Guthaben, Verfallsdaten, Pakete, QR-Code und Lesezeichen stehen im Markup —
 * die Seite funktioniert ohne eine Zeile hiervon. Was diese Datei tut, sind
 * genau zwei Dinge, die ohne Javascript nicht gehen:
 *
 *  1. Der Kopierknopf am Lesezeichen-URL. Er steht `hidden` in der Vorlage,
 *     weil ein Knopf ohne Zwischenablage nichts tut; das Feld daneben lässt
 *     sich von Hand markieren. Wortgleich zu key-create.js, und aus demselben
 *     Grund.
 *  2. Der Dialog für ein weiteres Gerät. Der Anmeldecode ist zehn Sekunden
 *     gültig, also kann er nicht im Markup stehen — er würde bei jedem
 *     Seitenaufbau geholt, auch von jemandem, der ihn gar nicht will, und wäre
 *     abgelaufen, bevor jemand ihn abgetippt hat.
 *  3. Die PayPal-Kachel auf der Zahlungswahl (checkout/index.blade.php).
 *     PayPal ist die einzige Zahlart, deren Seite ein SDK braucht — eine
 *     Kachel, die zu einer funktionslosen Seite führt, ist schlechter als
 *     keine Kachel, deshalb steht sie `hidden` da und wird nur hier
 *     aufgedeckt. Nicht /konto/aufladen/<menge>/paypal selbst: das SDK lädt
 *     erst dort (resources/js/checkout-paypal.js), diese Kachel führt nur
 *     hin.
 *
 * Die Texte stehen in data-Attributen und in der Vorlage, nicht hier: sie sind
 * übersetzt, und die Seite wird in zwölf Sprachen ausgeliefert.
 */

enhanceCopyButtons();
enhanceTransferDialog();
revealPaypalTile();

/**
 * Die Kopierknöpfe.
 *
 * Aufgedeckt nur, wenn es eine Zwischenablage gibt — sie ist an einen sicheren
 * Kontext gebunden, und wo es sie nicht gibt, ist das `readonly`-Feld daneben
 * die Antwort.
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
 * Der Dialog, der ein zweites Gerät anmeldet.
 *
 * Der Code steht zehn Sekunden im Redis des Keyservers und wird bei jeder
 * Abfrage verlängert — deshalb darf im Sekundentakt gefragt werden, ohne dass
 * er jemandem unter den Fingern wegläuft. **Wechselt er trotzdem, ist der alte
 * verbraucht**: dann hat sich gerade jemand damit angemeldet, und der Dialog
 * geht zu, statt eine Zahl zu zeigen, die nicht mehr funktioniert. Genau dafür
 * wird überhaupt nachgefragt.
 *
 * Aus pass/public/js/key.js übernommen. Was dort fehlte: der Dialog ließ sich
 * nur über seinen eigenen Knopf schließen — Escape schloss ihn, ohne das
 * Nachfragen zu beenden, und die Abfrage lief dann weiter, bis die Seite
 * verlassen wurde.
 */
function enhanceTransferDialog() {
    const dialog = document.getElementById("account-transfer");
    const open = document.getElementById("account-transfer-open");
    const close = document.getElementById("account-transfer-close");
    const output = document.getElementById("account-transfer-code");
    const failed = document.getElementById("account-transfer-failed");

    // `showModal` fehlt in Browsern ohne <dialog>. Dann bleibt der Knopf
    // verborgen: ein Dialog, der nicht aufgeht, ist schlechter als keiner.
    if (!dialog || !open || !close || !output || !failed
        || typeof dialog.showModal !== "function") {
        return;
    }

    const url = dialog.dataset.codeUrl;
    const waiting = output.textContent;
    let timer = null;
    let shown = null;

    open.hidden = false;

    open.addEventListener("click", () => {
        output.textContent = waiting;
        failed.hidden = true;
        shown = null;
        dialog.showModal();
        poll();
        timer = setInterval(poll, 1000);
    });

    close.addEventListener("click", () => dialog.close());

    // Escape schließt einen <dialog> von sich aus, ohne den Knopf zu berühren.
    // Ohne dieses Ereignis liefe das Nachfragen danach weiter — einmal pro
    // Sekunde, bis die Seite verlassen wird.
    dialog.addEventListener("close", stop);

    function stop() {
        if (timer !== null) {
            clearInterval(timer);
            timer = null;
        }
    }

    async function poll() {
        let code;
        try {
            const response = await fetch(url, {
                headers: { Accept: "application/json" },
                // Der Code hängt am Schlüssel-Cookie; ohne dies schickt fetch
                // es bei manchen Einstellungen nicht mit.
                credentials: "same-origin",
            });
            code = response.ok ? (await response.json()).code : null;
        } catch {
            code = null;
        }

        if (typeof code !== "string" || code === "") {
            stop();
            output.textContent = waiting;
            failed.hidden = false;
            return;
        }

        if (shown === null) {
            shown = code;
            output.textContent = code;
            return;
        }

        // Ein anderer Code heißt: der gezeigte ist verbraucht. Zu, bevor
        // jemand eine Zahl abtippt, die nicht mehr gilt.
        if (shown !== code) {
            dialog.close();
        }
    }
}

function revealPaypalTile() {
    const tile = document.getElementById("checkout-paypal-tile");
    if (tile) {
        tile.hidden = false;
    }
}
