import { describe, expect, it } from "vitest";

import { scrambled } from "./scramble";

/**
 * Der Trommelwirbel auf /schluessel-erstellen.
 *
 * Geprüft wird eine Sache, und die ist keine Geschmacksfrage: **bei 1 steht der
 * Schlüssel da.** Das Feld ist das, was jemand abliest und auf einen Zettel
 * schreibt; das versteckte Feld daneben schickt unabhängig davon den echten ab.
 * Wenn der Wirbel eine Ziffer stehen ließe, hätte der Besucher einen falschen
 * Schlüssel notiert und ein Konto, das er nie wieder findet — und nichts daran
 * sähe nach einem Fehler aus.
 */
const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

/** Ein „Zufall“, der immer dieselbe Ziffer liefert, damit man sie sieht. */
const always = (value) => () => value;

describe("scrambled", () => {
    it("gibt bei 1 den Schlüssel zurück, Zeichen für Zeichen", () => {
        expect(scrambled(A_KEY, 1, always(0))).toBe(A_KEY);
    });

    it("gibt bei mehr als 1 ebenfalls den Schlüssel zurück", () => {
        // Der Fortschritt wird aus einer Uhr gerechnet, und eine Uhr springt.
        expect(scrambled(A_KEY, 1.4, always(0))).toBe(A_KEY);
    });

    it("würfelt bei 0 alles und behält trotzdem die Form", () => {
        expect(scrambled(A_KEY, 0, always(0))).toBe("00000000-0000-0000-0000-000000000000");
    });

    it("lässt stehen, was schon feststeht", () => {
        // Die Hälfte von 32 Ziffern: die ersten 16 sind der Schlüssel, der Rest
        // ist gewürfelt. Von links nach rechts, damit sichtbar ist, dass etwas
        // fertig wird.
        expect(scrambled(A_KEY, 0.5, always(0))).toBe("5e9c1a2b-4f6d-4c3e-0000-000000000000");
    });

    it("hat immer die Länge eines Schlüssels", () => {
        for (const progress of [0, 0.1, 0.33, 0.5, 0.99, 1]) {
            expect(scrambled(A_KEY, progress, always(0))).toHaveLength(A_KEY.length);
        }
    });

    it("verträgt einen Fortschritt, der keine Zahl ist", () => {
        // Date.now() - started kann bei einer zurückgestellten Uhr negativ
        // werden, und eine Division kann NaN liefern. Beides darf nicht die
        // Form zerstören.
        expect(scrambled(A_KEY, -1, always(0))).toHaveLength(A_KEY.length);
        expect(scrambled(A_KEY, NaN, always(0))).toHaveLength(A_KEY.length);
    });

    it("würfelt nur aus den Zeichen, die ein Schlüssel enthalten kann", () => {
        // Sonst stünde für einen Wimpernschlag etwas im Feld, das kein
        // Schlüssel sein könnte — und genau das ist der Augenblick, in dem
        // jemand abschreibt.
        for (let i = 0; i < 50; i++) {
            expect(scrambled(A_KEY, 0.25)).toMatch(
                /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/
            );
        }
    });
});
