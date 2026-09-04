<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>{{ $sourceLabel }}</title>
</head>

<body>
    <div style="width: 100%; margin: 0;">
        <table style="width: 100%; margin: 0;">
            <tr>
                <td style="font-size: .8em;">SUMA-EV, Röselerstr. 3, D-30159 Hannover</td>
                <td style="text-align: right; font-size: .8em;">{{ $date }}</td>
            </tr>
        </table>
    </div>
    <div style="width: 100%; margin-top: 9.5%;">
        <table>
            <tr><td style="font-weight: bold; line-height: 1;">{{ $payerName }}</td></tr>
            <tr><td style="font-weight: bold; line-height: 1;">{{ $payerStreet }}</td></tr>
            <tr><td style="font-weight: bold; line-height: 1;">{{ $payerPostalCode }} {{ $payerCity }}</td></tr>
        </table>
    </div>
    <div style="margin-top: 9%;">
        <table>
            <tr>
                <td style="font-weight: bold;">Bestätigung über</td>
                <td><input type="checkbox" @if($isDonation) checked="checked" @endif> <span>Geldzuwendung</span></td>
                <td><input type="checkbox" @unless($isDonation) checked="checked" @endunless> <span>Mitgliedsbeitrag</span></td>
            </tr>
            <tr>
                <td colspan="3" style="padding-top: 8px; text-align: justify; font-size: .7em;">
                    im Sinne des §10b des Einkommensteuergesetzes an eine der in §5 Abs.1 Nr.9 des
                    Körperschaftssteuergesetzes bezeichneten Körperschaften, Personenvereinigungen oder
                    Vermögensmassen. Die elektronische Ausstellung von Zuwendungsbescheinigungen wurde dem
                    Finanzamt Hannover Nord am 23.02.2022 angezeigt.
                </td>
            </tr>
        </table>
        <table style="border: 1px solid black; width: 100%; margin-top: 8px;">
            <tr>
                <td style="border-bottom: 2px solid black; padding: 4px;">
                    <span style="font-weight: bold;">Aussteller: </span>SUMA-EV, Röselerstraße 3, 30159 Hannover
                </td>
            </tr>
            <tr>
                <td style="padding: 4px;">
                    <div>Name und Anschrift des Zuwendenden:</div>
                    <div>{{ $payerName }}</div>
                    <div>{{ $payerStreet }}</div>
                    <div>{{ $payerPostalCode }} {{ $payerCity }}</div>
                </td>
            </tr>
        </table>
        <style>
            #donations { border: 1px solid black; border-collapse: collapse; }
            #donations thead td { border-bottom: 1px solid black; }
            #donations tbody tr:nth-child(even) { background-color: #ccc; }
        </style>
        <table id="donations" style="width: 100%; margin-top: 16px;">
            <thead style="text-align: center; font-weight: bold;">
                <tr>
                    <td>Betrag in Ziffern</td>
                    <td>Betrag in Buchstaben</td>
                    <td>Tag der Zuwendung</td>
                </tr>
            </thead>
            <tbody>
                @foreach($lines as $line)
                    <tr>
                        <td style="text-align: center;">€ {{ $line['amount'] }}</td>
                        <td style="text-align: center; text-transform: capitalize">{{ $line['amountWords'] }}</td>
                        <td style="text-align: center;">{{ $line['date'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <table style="margin-top: 16px; font-size: .9em; width: 100%;">
            <tr>
                <td>Es handelt sich um den Verzicht auf Erstattung von Aufwendungen:</td>
                <td><input type="checkbox"> <span>Ja</span></td>
                <td><input type="checkbox" checked="checked"> <span>Nein</span></td>
            </tr>
        </table>
        <div style="page-break-inside: avoid;">
            <table style="margin-top: 16px; font-size: .7em; width: 100%;">
                <tr>
                    <td style="text-align:center;"><input type="checkbox" checked="checked" /></td>
                    <td>
                        Wir sind wegen Förderung der Suchmaschinen-Technologie und des freien Wissenszuganges
                        nach dem Freistellungsbescheid bzw. nach der Anlage zum Körperschaftssteuerbescheid
                        <span style="font-weight: bold;">des Finanzamtes Hannover-Nord, Steuernummer 25/206/47005
                            vom 09.04.2021</span> für den letzten Veranlagungszeitraum 2019 nach §5 Abs.1 Nr.9 des
                        Körperschaftssteuergesetzes von der Körperschaftssteuer und nach § 3 Nr. 6 des
                        Gewerbesteuergesetzes von der Gewerbesteuer befreit.
                    </td>
                </tr>
            </table>
            <p style="font-size: .7em; border: 1px solid black; padding: 4px; margin-top: 18px;">
                Es wird bestätigt, dass die Zuwendung nur zur<br>
                <span style="font-weight: bold;">Förderung der Suchmaschinen-Technologie und des freien
                    Wissenszuganges</span><br> verwendet wird.
            </p>
            <p style="font-size: .8em; margin-top: 32px;">Hannover, den <span style="font-weight: bold;">{{ $date }}</span></p>

            <div style="font-size: .8em;">
                @if($signatureDataUri)
                    <img src="{{ $signatureDataUri }}" alt="Unterschrift" height="4rem" style="margin-left: 50px;">
                @endif
                <div style="margin-bottom: 4px; width: 250px; border-top: 1px solid black;"></div>
                <span>{{ $signeeName ?? '—' }}, SUMA-EV Vorstand</span>
            </div>

            <div style="font-size: .7em;">
                <p>
                    Hinweis: Wer vorsätzlich oder grob fahrlässig eine unrichtige Zuwendungsbestätigung erstellt
                    oder wer veranlasst, dass Zuwendungen nicht zu den in der Zuwendungsbestätigung angegebenen
                    steuerbegünstigten Zwecken verwendet werden, haftet für die Steuer, die dem Fiskus durch
                    etwaigen Abzug der Zuwendungen beim Zuwendenden entgeht (§10b Abs. 4 EStG, §9 Abs. 3 KStG, § 9
                    Nr. 5 GewStG). Diese Bestätigung wird nicht als Nachweis für die steuerliche Berücksichtigung
                    der Zuwendungen anerkannt, wenn das Datum des Freistellungsbescheides länger als 5 Jahre bzw.
                    das Datum der vorläufigen Bescheinigung länger als 3 Jahre seit Ausstellung der Bestätigung
                    zurückliegt (BMF vom 15.12.1994 # BStBl I S.884).
                </p>
            </div>
        </div>
    </div>
</body>

</html>
