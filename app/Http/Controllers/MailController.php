<?php

namespace App\Http\Controllers;

use App\Mail\Sprachdatei;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Response;
use LaravelLocalization;
use Mail;
use Log;
use Validator;
use \IBAN;
use \IBANCountry;

class MailController extends Controller
{
    /**
     * Load Startpage accordingly to the given URL-Parameter and Mobile
     *
     * @param  int  $id
     * @return Response
     */
    public function contactMail(Request $request)
    {
        # Nachricht, die wir an den Nutzer weiterleiten:
        $messageType = ""; # [success|error]
        $returnMessage = '';

        # Wir benötigen 3 Felder von dem Benutzer wenn diese nicht übermittelt wurden, oder nicht korrekt sind geben wir einen Error zurück
        $input_data = $request->all();

        $maxFileSize = 5 * 1024;
        $validator = Validator::make(
            $input_data,
            [
                'email' => 'required|email',
                'pcsrf' => ['required', 'string', new \App\Rules\PCSRF],
                'attachments' => ['max:5'],
                'attachments.*' => ['file', 'max:' . $maxFileSize],
            ]
        );

        if ($validator->fails()) {
            return view('kontakt.kontakt')->with('formerrors', $validator)->with('title', trans('titles.kontakt'))->with('navbarFocus', 'kontakt');
        }

        $name = $request->input('name', '');

        $replyTo = $request->input('email', 'noreply@metager.de');
        if ($replyTo === "") {
            $replyTo = "noreply@metager.de";
        } else {
            $replyTo = $request->input('email');
        }

        if (!$request->filled('message') || !$request->filled('subject')) {
            $messageType = "error";
            $returnMessage = trans('kontakt.error.1');
        } else {
            $message = $request->input('message');
            $subject = $request->input('subject');

            # Wir versenden die Mail des Benutzers an uns:
            $postdata = [
                "alert" => true,
                "autorespond" => true,
                "source" => "API",
                "name" => $name,
                "email" => $replyTo,
                "subject" => $subject,
                "ip" => $request->ip(),
                "deptId" => 5,
                "message" => "data:text/plain;charset=utf-8, $message",
                "attachments" => []
            ];

            if($request->has("attachments") && is_array($request->file("attachments"))){
                foreach($request->file("attachments") as $attachment){
                    $postdata["attachments"][] = [
                        $attachment->getClientOriginalName() => "data:" . $attachment->getMimeType() . ";base64," . base64_encode(file_get_contents($attachment->getRealPath()))
                    ];
                }
            }  

            if (LaravelLocalization::getCurrentLocale() === "de") {
                $postdata["deptId"] = 1;
            }

            $postdata = json_encode($postdata);

            $resulthash = md5($subject . $message);

            $mission = [
                "resulthash" => $resulthash,
                "url" => env("TICKET_URL", "https://metager.de"),
                "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
                "username" => null,
                "password" => null,
                "headers" => [
                    "X-API-Key" => env("TICKET_APIKEY", ""),
                    "Content-Type" => "application/json",
                    "Content-Length" => strlen($postdata)
                ],
                "cacheDuration" => 0,
                "name" => "Ticket",
                "curlopts" => [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $postdata,
                    CURLOPT_LOW_SPEED_TIME => 20,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT => 20
                ]
            ];
            $mission = json_encode($mission);
            Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);

            // Fetch the result
            $answer = Redis::blpop($resulthash, 20);
            
            // Fehlerfall
            if(empty($answer) || (is_array($answer) && sizeof($answer) === 2 && $answer[1] === "no-result")){
                $messageType = "error";
                $returnMessage = trans('kontakt.error.2', ["email" => env("MAIL_USERNAME", "support+46521@metager.de")]);
            }else{
                $returnMessage = trans('kontakt.success.1', ["email" => $replyTo]);
                $messageType = "success";
            }
        }

        return view('kontakt.kontakt')
            ->with('title', 'Kontakt')
            ->with('js', ['lib.js'])
            ->with($messageType, $returnMessage);
    
    }

    public function donation(Request $request)
    {
        $name = '';
        if($request->input('person') === 'private') {
            $firstname = $request->input('firstname');
            $lastname = $request->input('lastname');
            if($firstname !== '' || $lastname !== '') {
                $name = $firstname . ' ' . $lastname;
            }
        } elseif($request->input('person') === 'company') {
            $company = $request->input('companyname');
            $name = $company;
        }

        $name = trim($name);

        $data = [
            'name' => $name,
            'iban' => $request->input('iban', ''),
            'bic' => $request->input('bic', ''),
            'email' => $request->input('email', ''),
            'betrag' => $request->input('amount', ''),
            'frequency' => $request->input('frequency', ''),
            'nachricht' => $request->input('Nachricht', ''),
        ];
    
        $iban = $request->input('iban', '');
        $bic = $request->input('bic', '');
        $email = $request->input('email', '');
        $frequency = $request->input('frequency', '');
        $betrag = $request->input('amount', '');
        $nachricht = $request->input('Nachricht', '');

        # Allow custom amounts
        if ($betrag == "custom" && $request->filled('custom-amount')) {
            $betrag = $request->input('custom-amount', '');
            $data['betrag'] = $betrag;
        }

        # Check for valid frequency
        $validFrequencies = [
            "once",
            "monthly",
            "quarterly",
            "six-monthly",
            "annual",
        ];

        # Der enthaltene String wird dem Benutzer nach der Spende ausgegeben
        $messageToUser = "";
        $messageType = ""; # [success|error]

        # Check the IBAN
        $iban = new IBAN($iban);
        $bic = $request->input('Bankleitzahl', '');
        $country = new IBANCountry($iban->Country());
        $isSEPA = filter_var($country->IsSEPA(), FILTER_VALIDATE_BOOLEAN);

        # Check the amount
        $validBetrag = is_numeric($betrag) && $betrag > 0;

        # Validate Email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = "anonymous@suma-ev.de";
        }
        if($name === ''){
            $messageToUser = trans('spende.error.name');
            $messageType = "error";
        } elseif (!$iban->Verify()) {
            $messageToUser = trans('spende.error.iban');
            $messageType = "error";
        } elseif (!$isSEPA && $bic === '') {
            $messageToUser = trans('spende.error.bic');
            $messageType = "error";
        } elseif (!$validBetrag) {
            $messageToUser = trans('spende.error.amount');
            $messageType = "error";
        } elseif (!in_array($frequency, $validFrequencies)) {
            $messageToUser = trans('spende.error.frequency');
            $messageType = "error";
        } else {

            # The value has to have a maximum of 2 decimal digits
            $betrag = round($betrag, 2, PHP_ROUND_HALF_DOWN);

            # Generating personalised key for donor
            $key = app('App\Models\Key')->generateKey($betrag);

            # a complete set of data from the form consists of:
            # -- if person == private --
            # firstname
            # lastname
            # -- if person == company --
            # companyname
            # --
            # email
            # iban
            # bic
            # amount
            # frequency
            # message

            $message = "\r\nName: " . $name;
            $message .= "\r\nIBAN: " . $iban->HumanFormat();
            if ($bic !== "") {
                $message .= "\r\nBIC: " . $bic;
            }

            $message .= "\r\nBetrag: " . $betrag;
            $message .= "\r\nHäufigkeit: " . trans('spende.frequency.' . $frequency);
            $message .= "\r\nNachricht: " . $nachricht;

            if($key){
                $message .= "\r\nSchlüssel:" . $key;
            }

            try {
                $postdata = [
                    "entity" => "Contribution",
                    "action" => "mgcreate",
                    "api_key" => env("TICKET_API_KEY", ''),
                    "key" => env("TICKET_SITE_KEY", ''),
                    "json" => 1,
                    "iban" => $iban->MachineFormat(),
                    "bic" => $bic,
                    "amount" => $betrag,
                    "frequency" => $frequency,
                    "email" => $email,
                    "mgkey" => $key,
                    "message" => $nachricht
                ];

                if($request->input('person') === 'private') {
                    $postdata['first_name'] = $firstname;
                    $postdata['last_name'] = $lastname;
                } elseif($request->input('person') === 'company') {
                    $postdata['business_name'] = $company;
                }

                $postdata = http_build_query($postdata);
    
                $resulthash = md5($message);
    
                $mission = [
                    "resulthash" => $resulthash,
                    "url" => env("TICKET_URL", "https://metager.de"),
                    "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
                    "username" => null,
                    "password" => null,
                    "headers" => [
                        "Content-Type" => "application/x-www-form-urlencoded",
                    ],
                    "cacheDuration" => 0,
                    "name" => "Ticket",
                    "curlopts" => [
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => $postdata,
                        CURLOPT_LOW_SPEED_TIME => 20,
                        CURLOPT_CONNECTTIMEOUT => 10,
                        CURLOPT_TIMEOUT => 20
                    ]
                ];
                $mission = json_encode($mission);
                Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
    
                // Fetch the result
                $answer = Redis::blpop($resulthash, 20);
                
                // Fehlerfall
                if(empty($answer) || (is_array($answer) && sizeof($answer) === 2 && $answer[1] === "no-result")){
                    $messageType = "error";
                    $messageToUser = "Beim Senden Ihrer Spendenbenachrichtigung ist ein Fehler auf unserer Seite aufgetreten. Bitte schicken Sie eine E-Mail an: dominik@suma-ev.de, damit wir uns darum kümmern können.";
                }else{
                    $messageToUser = "Herzlichen Dank!! Wir haben Ihre Spendenbenachrichtigung erhalten.";
                    $messageType = "success";
                }
            } catch (\Swift_TransportException $e) {
                Log::error($e->getMessage());
                $messageType = "error";
                $messageToUser = 'Beim Senden Ihrer Spendenbenachrichtigung ist ein Fehler auf unserer Seite aufgetreten. Bitte schicken Sie eine E-Mail an: dominik@suma-ev.de, damit wir uns darum kümmern können.';
            }
        }

        if ($messageType === "error") {
            return view('spende.spende')
                ->with('title', 'Kontakt')
                ->with($messageType, $messageToUser)
                ->with('data', $data);
        } else {
            $data['iban'] = $iban->HumanFormat();
            $data['key'] = $key;
            $data = base64_encode(serialize($data));
            return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route("danke", ['data' => $data])));
        }
    }

    #Ueberprueft ob ein bereits vorhandener Eintrag bearbeitet worden ist
    public static function isEdited($k, $v, $filename)
    {
        try {
            $temp = include resource_path() . "/" . $filename;
            foreach ($temp as $key => $value) {
                if ($k === $key && $v !== $value) {
                    return true;
                }
            }
        } catch (\ErrorException $e) {
            #Datei existiert noch nicht
            return true;
        }
        return false;
    }

    public function sendLanguageFile(Request $request, $from, $to, $exclude = "", $email = "")
    {
        $filename = $request->input('filename');
        # Wir erstellen nun zunächst den Inhalt der Datei:
        $data = [];
        $new = 0;
        $emailAddress = "";
        $editedKeys = "";
        foreach ($request->all() as $key => $value) {
            if ($key === "filename" || $value === "") {
                continue;
            }
            if ($key === "email") {
                $emailAddress = $value;
                continue;
            }
            $key = base64_decode($key);
            if (strpos($key, "_new_") === 0 && $value !== "") {
                $new++;
                $key = substr($key, strpos($key, "_new_") + 5);
                $editedKeys = $editedKeys . "\n" . $key;
            } elseif ($this->isEdited($key, $value, $filename)) {
                $new++;
                $editedKeys = $editedKeys . "\n" . $key;
            }

            $key = trim($key);
            if (!strpos($key, "#")) {
                $data[$key] = $value;
            } else {
                $ref = &$data;
                do {
                    $ref = &$ref[substr($key, 0, strpos($key, "#"))];
                    $key = substr($key, strpos($key, "#") + 1);
                } while (strpos($key, "#"));
                $ref = &$ref[$key];
                $ref = $value;
            }
        }

        $output = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $output = preg_replace("/\{/si", "[", $output);
        $output = preg_replace("/\}/si", "]", $output);
        $output = preg_replace("/\": ([\"\[])/si", "\"\t=>\t$1", $output);

        $output = "<?php\n\nreturn $output;\n";

        $message = "Moin moin,\n\nein Benutzer hat eine Sprachdatei aktualisiert.\nBearbeitet wurden die Einträge: $editedKeys\n\nSollten die Texte so in Ordnung sein, ersetzt, oder erstellt die Datei aus dem Anhang in folgendem Pfad:\n$filename\n\nFolgend zusätzlich der Inhalt der Datei:\n\n$output";

        # Wir haben nun eine Mail an uns geschickt, welche die entsprechende Datei beinhaltet.
        # Nun müssen wir den Nutzer eigentlich nur noch zurück leiten und die Letzte bearbeitete Datei ausschließen:
        $ex = [];
        if ($exclude !== "") {
            try {
                $ex = unserialize(base64_decode($exclude));
            } catch (\ErrorException $e) {
                $ex = [];
            }

            if (!isset($ex["files"])) {
                $ex["files"] = [];
            }
        }
        if (!isset($ex["new"])) {
            $ex["new"] = 0;
        }
        $ex['files'][] = basename($filename);
        $ex["new"] += $new;

        if ($new > 0) {
            if ($emailAddress !== "") {
                Mail::to("dev@suma-ev.de")
                    ->send(new Sprachdatei($message, $output, basename($filename), $emailAddress));
            } else {
                Mail::to("dev@suma-ev.de")
                    ->send(new Sprachdatei($message, $output, basename($filename)));
            }
        }
        $ex = base64_encode(serialize($ex));

        return redirect(url('languages/edit', ['from' => $from, 'to' => $to, 'exclude' => $ex, 'email' => $emailAddress]));
    }
}
