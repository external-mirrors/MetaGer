/**
 * Die Schreibhilfe für einen Gutscheincode.
 *
 * Reine Funktionen, getrennt vom DOM-Kleber in ../voucher.js — aus demselben
 * Grund wie key-create/scramble.js: hier steckt die einzige Rechnung dieser
 * Seite, die falsch sein kann, ohne dass es jemand sieht. Der Code wird von
 * einer Karte abgelesen und in Vierergruppen getippt; wer sich in der Mitte
 * vertippt und dort verbessert, dem darf der Cursor nicht ans Ende springen.
 * Das ist die Art Fehler, die niemand meldet — man tippt eben noch einmal von
 * vorn und hält die Seite für kaputt.
 *
 * Das Verhalten ist unverändert aus dem Keymanager übernommen
 * (pass/resources/js/campaign_code.js). Der Code selbst wird serverseitig
 * geprüft; was hier steht, ist ausschließlich Schreibkomfort.
 */

/** So viele Zeichen stehen zwischen zwei Bindestrichen. */
const GROUP_SIZE = 4;

/**
 * Was von einer Eingabe übrig bleibt.
 *
 * Großbuchstaben, nur Ziffern und Buchstaben, höchstens so lang wie ein Code.
 * Das gilt für Getipptes wie für Eingefügtes: aus der Zwischenablage kommt der
 * Code mit Bindestrichen, mit Leerzeichen, klein geschrieben oder mit einem
 * Zeilenumbruch am Ende, und all das ist derselbe Code.
 *
 * @param {string} value
 * @param {number} codeLength Wie viele Zeichen ein Code hat, ohne Bindestriche.
 * @returns {string}
 */
export function clean(value, codeLength) {
    return value.toUpperCase().replace(/[^0-9A-Z]/g, "").slice(0, codeLength);
}

/**
 * Dieselben Zeichen, in Vierergruppen.
 *
 * @param {string} value Bereits durch clean() gegangen.
 * @returns {string}
 */
export function group(value) {
    return value.match(new RegExp(`.{1,${GROUP_SIZE}}`, "g"))?.join("-") || "";
}

/**
 * Wo der Cursor nach dem Umschreiben stehen muss.
 *
 * Gezählt wird nicht in Zeichen der Anzeige, sondern in Zeichen des Codes: vor
 * dem Cursor standen n Codezeichen, und hinter dem n-ten Codezeichen soll er
 * wieder stehen. Nur so überlebt er einen Bindestrich, der gerade erst
 * eingefügt wurde — und über den springt er gleich mit, weil sonst das nächste
 * getippte Zeichen davor landen würde.
 *
 * @param {string} formatted Der Wert, wie er jetzt im Feld steht.
 * @param {number} cleanCount Wie viele Codezeichen vor dem Cursor standen.
 * @returns {number}
 */
export function caretForCleanCount(formatted, cleanCount) {
    if (cleanCount <= 0) {
        return 0;
    }

    let seen = 0;
    for (let i = 0; i < formatted.length; i++) {
        if (formatted[i] !== "-") {
            seen++;
            if (seen === cleanCount) {
                return formatted[i + 1] === "-" ? i + 2 : i + 1;
            }
        }
    }

    return formatted.length;
}

/**
 * Eine Eingabe, fertig formatiert, mit der Cursorposition dazu.
 *
 * Beides in einem Rückgabewert, weil beides zusammengehört: das Feld neu zu
 * beschriften setzt den Cursor ans Ende, und wer ihn danach nicht zurücksetzt,
 * hat die Schreibhilfe in eine Schikane verwandelt.
 *
 * @param {string} raw Was im Feld steht.
 * @param {number} caret Wo der Cursor steht.
 * @param {number} codeLength
 * @returns {{value: string, caret: number}}
 */
export function format(raw, caret, codeLength) {
    const cleanBeforeCaret = clean(raw.slice(0, caret), codeLength).length;
    const value = group(clean(raw, codeLength));

    return { value, caret: caretForCleanCount(value, cleanBeforeCaret) };
}
