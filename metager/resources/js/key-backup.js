/**
 * Die beiden Dinge, die der Block zum Aufbewahren ohne Javascript nicht kann.
 *
 * Ausgelagert, als /membership/success denselben Block bekam
 * (resources/views/parts/key-backup.blade.php): der Aufnahmeantrag erstellt im
 * ersten Schritt still einen Schlüssel, und die Erfolgsseite ist für viele
 * Mitglieder die einzige Gelegenheit, ihn zu sehen.
 *
 * Was hier *nicht* steht, ist die Nachfrage vor dem zweiten Schlüssel — die
 * gehört zur Seite zum Erstellen und nur dorthin (resources/js/key-create.js).
 *
 * Die Texte stehen in data-Attributen und nicht hier: sie sind übersetzt, und
 * die Seiten werden in zwölf Sprachen ausgeliefert.
 */

/**
 * Die Kopierknöpfe.
 *
 * Aufgedeckt nur, wenn es eine Zwischenablage gibt — sie ist an einen sicheren
 * Kontext gebunden, und wo es sie nicht gibt, ist das `readonly`-Feld daneben
 * die Antwort. Nach dem Kopieren sagt der Knopf es kurz und heißt dann wieder,
 * wie er hieß; er behält seine Breite dabei nicht, aber die Seite springt
 * darunter nicht, weil er in seiner eigenen Zeile steht.
 */
export function enhanceCopyButtons() {
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
 * Probiert wird es mit einem eigenen Namen und nicht mit `key`: ein Versuch,
 * der den Schlüssel schreibt und dann feststellt, dass er nicht angekommen ist,
 * hätte ihn im gelungenen Fall an einer Stelle gesetzt, an der ihn niemand
 * gesetzt haben wollte — das Cookie setzt der Server.
 */
export function warnAboutMissingCookies() {
    const warning = document.getElementById("key-create-no-cookies");
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
