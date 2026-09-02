<?php

namespace App\Support;

use App\Localization;

/**
 * Ob dieser Besucher eine SUMA-EV-Mitgliedschaft angeboten bekommt.
 *
 * Eine Mitgliedschaft im Trägerverein ist nur auf Deutsch zu haben: das
 * Beitrittsformular ({@see \App\Http\Controllers\MembershipController}) ist in
 * keiner anderen Sprache übersetzt, die Satzung, die Beitragsordnung und die
 * Seiten auf suma-ev.de, auf die es verweist, ebenso wenig, und die
 * Mitgliederbetreuung läuft auf Deutsch. Ein englischer, spanischer oder
 * polnischer Besucher, der auf „Mitglied werden“ klickt, landet also in einem
 * Formular, das er nicht lesen kann — die Werbung dafür gehört auf die
 * deutsche Oberfläche und nur dorthin.
 *
 * Der Vergleich ist die *Sprache*, nicht die Region: `de`, `de-AT` und `de-CH`
 * lesen dasselbe Formular. Und er stand vorher an zwei Stellen wörtlich im
 * Blade ({@see resources/views/parts/sidebar.blade.php},
 * {@see resources/views/spende/amount.blade.php}), während vier weitere
 * Stellen ihn schlicht vergessen hatten. Genau so driftet eine Regel
 * auseinander, die an sechs Orten dasselbe bedeuten muss.
 *
 * Was hier *nicht* entschieden wird, ist die Erreichbarkeit des Formulars
 * selbst: die E-Mails an Mitglieder verlinken es mit einer Bearbeitungs-ID,
 * und eine Route, die je nach Oberflächensprache 404 antwortet, würde diese
 * Links brechen. Beworben wird auf Deutsch, erreichbar bleibt es überall.
 */
final class MembershipOffer
{
    /** Ob ein Hinweis auf die Mitgliedschaft gerendert werden darf. */
    public static function isAdvertised(): bool
    {
        return Localization::getLanguage() === "de";
    }
}
