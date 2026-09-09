/**
 * Die Seite nach dem Aufnahmeantrag, angereichert.
 *
 * Sie zeigt den Schlüssel, den der erste Schritt des Formulars still erstellt
 * hat, und ist für die meisten Mitglieder die einzige Gelegenheit, ihn zu
 * sehen: die Willkommensmail nennt ihn erst, wenn der Antrag bearbeitet ist.
 *
 * Die Seite funktioniert ohne eine Zeile hiervon — der Schlüssel, der QR-Code
 * und der Lesezeichen-URL stehen im Markup. Was hinzukommt, ist dasselbe wie
 * auf /schluessel-erstellen und steht deshalb dort, wo es beide haben können.
 */
import { enhanceCopyButtons, warnAboutMissingCookies } from "./key-backup";

enhanceCopyButtons();
warnAboutMissingCookies();
