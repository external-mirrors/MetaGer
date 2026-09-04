# Vorschlag: Beitragsordnung für juristische Personen

Entwurf zum Einarbeiten in <https://suma-ev.de/beitragsordnung/>. Die Seite liegt nicht in diesem
Repository — dieser Text ist die Vorlage dafür, und er ist zugleich die Begründung für die Zahlen,
die `App\Support\MembershipFee` durchsetzt. **Beide Stellen müssen dieselben Beträge nennen.**

## Was sich ändert

| Mitarbeitende | bisher | Vorschlag | empfohlen |
|---|---|---|---|
| 1 – 19 | 5 € / Monat | **25 € / Monat** | 50 € / Monat |
| 20 – 199 | 100 € / Monat | 100 € / Monat | 200 € / Monat |
| ab 200 | 200 € / Monat | 200 € / Monat | 400 € / Monat |

Geändert wird genau eine Zahl. Die beiden größeren Klassen bleiben, wie sie sind; neu ist neben den
25 € nur, dass es — wie bei natürlichen Personen, wo 5 € der Mindest- und 10 € der empfohlene
Beitrag ist — auch für Firmen einen empfohlenen Betrag gibt. Das Beitrittsformular schlägt ihn vor,
statt jede Firma auf den Mindestbeitrag zu setzen.

### Warum die 5 € nicht bleiben können

Sie sind der Mindestbeitrag einer Einzelperson, und sie galten für eine Organisation mit bis zu
neunzehn Menschen, die alle suchen. Der Aufwand, den der Verein trägt, wächst mit der Zahl der
Suchenden: neunzehn Menschen suchen mehr als einer, und der Beitrag war bisher derselbe.

25 € ist die untere Kante dessen, was die Größenklasse trägt, und sie ist mit Absicht nicht höher:
die kleinen Büros und Praxen, um die es hier geht, sollen weiter beitreten können, und ein Beitrag,
über den jemand nachdenken muss, ist einer, der nicht gezahlt wird.

### Bestandsschutz

Bestehende Mitgliedschaften behalten ihren Beitrag. Das ist keine Freundlichkeit, sondern eine
technische Notwendigkeit: unsere Zahlungserinnerungen verlinken den Beitragsschritt des Formulars,
und dieser Schritt validiert den Beitrag neu. Ohne Bestandsschutz käme ein Mitglied, das seit Jahren
5 € zahlt, aus dieser Ansicht nicht mehr heraus, ohne seinen Beitrag zu verfünffachen — beim Versuch,
eine neue IBAN zu hinterlegen. Der Code kennt die Regel als `COMPANY_MINIMUM_GRANDFATHERED`.

Zu entscheiden: ob der Bestandsschutz dauerhaft gilt oder mit einem Stichtag endet, zu dem die
betroffenen Mitglieder angeschrieben werden. Dauerhaft ist die stillere Variante; ein Stichtag holt
Geld, kostet aber Austritte, und wir wissen nicht, wie viele Mitgliedschaften in dieser Klasse
überhaupt betroffen sind. Das steht in CiviCRM (Mitgliedschaftstyp `company.1-19.*`) und sollte vor
dem Beschluss nachgesehen werden.

## Vorschlag für den Text

> **Beiträge juristischer Personen**
>
> Für juristische Personen und Personenvereinigungen richtet sich der monatliche Mindestbeitrag nach
> der Zahl der Mitarbeitenden:
>
> | Mitarbeitende | Mindestbeitrag | empfohlener Beitrag |
> |---|---|---|
> | bis 19 | 25 € | 50 € |
> | 20 bis 199 | 100 € | 200 € |
> | ab 200 | 200 € | 400 € |
>
> Maßgeblich ist die Zahl der Mitarbeitenden zum Zeitpunkt des Aufnahmeantrags. Ändert sie sich
> dauerhaft, passen wir den Beitrag auf Mitteilung des Mitglieds hin an.
>
> Für Mitgliedschaften, die vor dem *(Datum des Beschlusses)* begründet wurden, gilt der bis dahin
> vereinbarte Beitrag weiter.
>
> Wie natürliche Personen können juristische Personen zwischen monatlicher, dreimonatlicher,
> halbjährlicher und jährlicher Zahlung wählen; der Beitrag wird für das gewählte Intervall im
> Voraus gezahlt.

Der Absatz über die Ermäßigung bleibt unberührt — sie ist an einen Einkommensnachweis geknüpft und
damit ein Recht natürlicher Personen. Das Beitrittsformular zeigt Firmen den Nachweis-Upload
inzwischen gar nicht mehr an.

## Offene Punkte für den Vorstand

1. **Stichtag oder dauerhafter Bestandsschutz** (siehe oben). Vorher die Zahl der betroffenen
   Mitgliedschaften in CiviCRM ansehen.
2. **Schulen, Hochschulen und Behörden.** Sie sind der Anlass für das Ganze, und „Mitarbeitende“
   passt schlecht: eine Schule mit 200 Schülerinnen und 20 Lehrkräften fällt in die kleinste Klasse
   und sucht wie die mittlere. Zwei Wege stehen zur Wahl — eine eigene Klasse in der
   Beitragsordnung, oder ein Satz, der für Bildungseinrichtungen auf die Zahl der *Nutzenden*
   abstellt statt auf die der Mitarbeitenden. Der zweite Weg ist der kleinere Eingriff: die drei
   Größenklassen liegen als ENUM in der Datenbank und als Mitgliedschaftstypen in CiviCRM, eine
   vierte wäre eine Migration und neue Typen. Ein Satz, der die Zählweise ändert, ist keins von
   beidem.
3. **Ob die Beitragsordnung die Nutzung von MetaGer überhaupt erwähnt.** Der Vorschlag oben tut es
   nicht: er regelt Beiträge, wie eine Beitragsordnung es tun soll. Dass Mitglieder auf MetaGer
   werbefrei suchen, steht auf `/firmen` und auf der Startseite. Sollte der Vorstand das anders
   wollen, gehört der Satz vorher über den Tisch des Steuerberaters.

## Wenn eine Zahl sich ändert

Sie steht an genau einer Stelle im Code:

```
metager/app/Support/MembershipFee.php   COMPANY_MINIMUM, COMPANY_PRESETS
```

Von dort kommen der Mindestbeitrag, den die Validierung durchsetzt, die drei Vorschläge im
Beitrittsformular und die Tabelle auf `/firmen`. `tests/Unit/MembershipFeeTest.php` hält die Zahlen
fest, `tests/Feature/BusinessPageTest.php` prüft, dass die Seite dieselben nennt.
