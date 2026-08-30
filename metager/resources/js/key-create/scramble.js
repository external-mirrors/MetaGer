/**
 * Der Trommelwirbel beim Aufdecken eines neuen Schlüssels.
 *
 * Eine reine Funktion, getrennt vom DOM-Kleber in ../key-create.js, wegen einer
 * einzigen Zusicherung: **am Ende steht der echte Schlüssel da.** Das Feld ist
 * das, was jemand abliest, abschreibt und kopiert, und das versteckte Feld
 * daneben schickt unabhängig davon den echten ab. Ein Wirbel, der bei 99 %
 * stehen bleibt, wäre deshalb nicht ein Schönheitsfehler, sondern ein Besucher
 * mit einem falschen Schlüssel auf dem Zettel und einem richtigen im Konto.
 *
 * Deshalb ist der Fortschritt ein Wert und keine Laufzeit: bei 1 kommt der
 * Schlüssel zurück, Zeichen für Zeichen, ohne dass die aufrufende Seite ihn am
 * Schluss noch einmal hinschreiben muss.
 */

/** Woraus ein Schlüssel besteht. */
const ALPHABET = "0123456789abcdef";

/** Wo in den 32 Ziffern die Bindestriche stehen — 8-4-4-4-12. */
const DASHES = new Set([8, 12, 16, 20]);

/**
 * Der Schlüssel, teils gewürfelt.
 *
 * Von links nach rechts: was schon feststeht, steht fest und wackelt nicht
 * mehr. Alles andere flackerte gleichzeitig, und dann sähe niemand, dass
 * überhaupt etwas fertig wird.
 *
 * @param {string} key Der echte Schlüssel, mit Bindestrichen.
 * @param {number} progress 0 bis 1. Alles außerhalb wird hineingezogen.
 * @param {() => number} [random] Nur für den Test; sonst Math.random.
 * @returns {string}
 */
export function scrambled(key, progress, random = Math.random) {
    const digits = key.replace(/-/g, "");
    const done = Math.max(0, Math.min(1, Number.isFinite(progress) ? progress : 0));
    const settled = Math.floor(done * digits.length);

    let out = "";
    for (let i = 0; i < digits.length; i++) {
        if (DASHES.has(i)) {
            out += "-";
        }
        out += i < settled
            ? digits.charAt(i)
            : ALPHABET.charAt(Math.floor(random() * ALPHABET.length));
    }

    return out;
}
