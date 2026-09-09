import { scrambled } from "./key-create/scramble";
import { enhanceCopyButtons, warnAboutMissingCookies } from "./key-backup";

/**
 * Die Seite zum Erstellen eines Schlüssels, angereichert.
 *
 * Der Schlüssel steht schon im Markup — die Seite funktioniert ohne eine Zeile
 * hiervon. Was diese Datei tut, ist deshalb nicht „die Seite aufbauen“, sondern
 * genau zwei Dinge, die ohne Javascript nicht gehen oder nicht sollen:
 *
 *  1. **Den Schlüssel wieder wegblenden und einen Knopf davorstellen.** Das ist
 *     kein Zierrat und keine Ladeanzeige: Es ist die Nachfrage, bevor jemand
 *     einen *zweiten* Schlüssel bekommt. Wer sein Cookie verloren hat, hat kein
 *     Konto verloren — sein Guthaben hängt am alten Schlüssel, und ein neuer
 *     bekommt ein eigenes, getrenntes. Ohne Javascript fehlt diese Nachfrage,
 *     und das ist der richtige Kompromiss: eine Seite, die einen Schlüssel
 *     zeigt, ist besser als eine, die ohne Skript gar keinen hergibt.
 *  2. Den Trommelwirbel beim Aufdecken — und den nur, wenn niemand um weniger
 *     Bewegung gebeten hat.
 *
 * Die Kopierknöpfe und die Frage, ob dieser Browser ein Cookie behält, gehören
 * zum Block zum Aufbewahren und stehen deshalb in ./key-backup.js —
 * /membership/success zeigt denselben Block und braucht dasselbe.
 *
 * Die Texte stehen in data-Attributen und nicht hier: sie sind übersetzt, und
 * die Seite wird in zwölf Sprachen ausgeliefert.
 */

const card = document.getElementById("key-create");
const field = document.getElementById("new-key");
const start = document.getElementById("key-create-start");

if (card && field && start) {
    guardTheSecondKey();
}

enhanceCopyButtons();
warnAboutMissingCookies();

/**
 * Die Nachfrage vor dem zweiten Schlüssel.
 *
 * Der Zustand wird erst umgestellt, wenn der Knopf hängt. Andersherum wäre ein
 * Fehler in der Zeile darunter eine Seite mit einem Knopf, der nichts tut, und
 * ohne Schlüssel.
 */
function guardTheSecondKey() {
    const key = field.value;

    start.addEventListener("click", () => {
        if (prefersLessMotion()) {
            reveal();
            return;
        }

        card.dataset.state = "working";
        roll(key, reveal);
    });

    // Der Fokus muss mitkommen: er stand auf einem Knopf, den es nach dem
    // Klick nicht mehr gibt, und läge sonst wieder am Seitenanfang. Auf dem
    // Feld liest ein Screenreader Beschriftung und Wert vor — also genau das,
    // was sich gerade geändert hat.
    function reveal() {
        card.dataset.state = "ready";
        field.focus();
    }

    card.dataset.state = "offer";
}

/** Ob jemand darum gebeten hat, dass sich weniger bewegt. */
function prefersLessMotion() {
    return (
        typeof window.matchMedia === "function"
        && window.matchMedia("(prefers-reduced-motion: reduce)").matches
    );
}

/**
 * Der Trommelwirbel.
 *
 * Er tut so, als würde hier gewürfelt, und das ist eine Aussage über etwas, das
 * längst geschehen ist — der Schlüssel steht seit dem Seitenaufbau im Feld. Was
 * er trotzdem ehrlich zeigt, ist etwas Wahres: dieser Schlüssel ist gewürfelt
 * und nicht aus einem Namen abgeleitet, es gibt kein Konto, das dahinter
 * angelegt würde, und niemand hat ihn ausgesucht.
 *
 * Wie die Zeichenkette dabei aussieht — und dass am Ende der echte Schlüssel
 * dasteht — entscheidet key-create/scramble.js, wo es zu prüfen ist.
 *
 * @param {string} key Der echte Schlüssel, der am Ende dastehen muss.
 * @param {() => void} done
 */
function roll(key, done) {
    const runtime = 1200;
    const started = Date.now();

    const timer = setInterval(() => {
        const progress = Math.min(1, (Date.now() - started) / runtime);
        field.value = scrambled(key, progress);

        if (progress === 1) {
            clearInterval(timer);
            done();
        }
    }, 60);
}
