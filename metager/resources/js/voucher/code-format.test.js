import { describe, expect, it } from "vitest";

import { caretForCleanCount, clean, format, group } from "./code-format";

/**
 * Die Schreibhilfe auf /c.
 *
 * Geprüft wird das, was ohne Test niemandem auffällt: **wo der Cursor
 * hinterher steht.** Ein Gutscheincode wird von einer Karte abgelesen; wer
 * sich beim vierten Zeichen vertippt, klickt hinein, verbessert — und wenn der
 * Cursor dabei ans Ende springt, steht der Rest des Codes plötzlich verkehrt
 * herum im Feld. Das meldet niemand, das tippt man noch einmal von vorn.
 *
 * Der Rest sind die Fälle, die aus der Zwischenablage kommen: mit
 * Bindestrichen, klein geschrieben, mit Leerzeichen, zu lang.
 */
const CODE_LENGTH = 10;

describe("clean", () => {
    it("schreibt groß und wirft weg, was kein Codezeichen ist", () => {
        expect(clean("abcd-efgh ij", CODE_LENGTH)).toBe("ABCDEFGHIJ");
    });

    it("schneidet ab, was über die Länge eines Codes hinausgeht", () => {
        // Eingefügt wird gern eine ganze Zeile, samt dem, was dahinter stand.
        expect(clean("ABCDEFGHIJKLMNOP", CODE_LENGTH)).toBe("ABCDEFGHIJ");
    });

    it("macht aus nichts nichts", () => {
        expect(clean("", CODE_LENGTH)).toBe("");
        expect(clean("---", CODE_LENGTH)).toBe("");
    });
});

describe("group", () => {
    it("setzt nach je vier Zeichen einen Bindestrich", () => {
        expect(group("ABCDEFGHIJ")).toBe("ABCD-EFGH-IJ");
    });

    it("lässt eine angefangene Gruppe stehen", () => {
        expect(group("AB")).toBe("AB");
        expect(group("ABCDE")).toBe("ABCD-E");
    });

    it("hängt keinen Bindestrich an eine volle Gruppe", () => {
        // Sonst stünde nach dem vierten Zeichen ein Strich, hinter dem noch
        // nichts kommt — und der Cursor davor.
        expect(group("ABCD")).toBe("ABCD");
    });

    it("liefert für nichts eine leere Zeichenkette und nicht null", () => {
        expect(group("")).toBe("");
    });
});

describe("caretForCleanCount", () => {
    it("bleibt am Anfang, wenn davor nichts stand", () => {
        expect(caretForCleanCount("ABCD-EFGH-IJ", 0)).toBe(0);
    });

    it("springt über einen Bindestrich, der gerade eingefügt wurde", () => {
        // Nach dem vierten Zeichen: der Strich steht dazwischen, und das
        // nächste getippte Zeichen gehört dahinter, nicht davor.
        expect(caretForCleanCount("ABCD-EFGH-IJ", 4)).toBe(5);
    });

    it("steht sonst direkt hinter dem gezählten Zeichen", () => {
        expect(caretForCleanCount("ABCD-EFGH-IJ", 2)).toBe(2);
        expect(caretForCleanCount("ABCD-EFGH-IJ", 6)).toBe(7);
    });

    it("geht ans Ende, wenn so viele Zeichen gar nicht dastehen", () => {
        expect(caretForCleanCount("ABCD-EFGH-IJ", 99)).toBe(12);
    });
});

describe("format", () => {
    it("formatiert und behält den Cursor, wo getippt wurde", () => {
        // Der Cursor stand hinter dem dritten Zeichen; dort steht er danach
        // wieder, obwohl das Feld neu beschriftet wurde.
        expect(format("abcdefgh", 3, CODE_LENGTH)).toEqual({
            value: "ABCD-EFGH",
            caret: 3,
        });
    });

    it("setzt den Cursor ans Ende, wenn dort getippt wurde", () => {
        expect(format("abcde", 5, CODE_LENGTH)).toEqual({
            value: "ABCD-E",
            caret: 6,
        });
    });

    it("nimmt einen eingefügten Code, wie er auf der Karte steht", () => {
        expect(format("ABCD-EFGH-IJ", 12, CODE_LENGTH)).toEqual({
            value: "ABCD-EFGH-IJ",
            caret: 12,
        });
    });

    it("zählt vor dem Cursor nur Codezeichen", () => {
        // Zwischen den beiden Gruppen steht ein Strich; hinter ihm sind vier
        // Codezeichen vorbei, nicht fünf.
        expect(format("ABCD-EFGH", 5, CODE_LENGTH).caret).toBe(5);
    });
});
