<?php

namespace App\Http\Controllers;

use App\Localization;
use App\Mail\Sprachdatei;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use LaravelLocalization;
use Mail;
use Log;
use Validator;
use \PHP_IBAN\IBAN;
use \PHP_IBAN\IBANCountry;

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
        $to_mail = Localization::getLanguage() === "de" ? config("metager.metager.ticketsystem.germanmail") : config("metager.metager.ticketsystem.englishmail");


        # Wir benötigen 3 Felder von dem Benutzer wenn diese nicht übermittelt wurden, oder nicht korrekt sind geben wir einen Error zurück
        $input_data = $request->all();

        $maxFileSize = 5 * 1024;
        $validator = Validator::make(
            $input_data,
            [
                'email' => 'required|email',
                'subject-2' => 'size:0',
                'pcsrf' => new \App\Rules\PCSRF,
                'attachments' => ['max:5'],
                'attachments.*' => ['file', 'max:' . $maxFileSize],
            ]
            ,
            ["size" => trans("validation.pcsrf")]
        );

        if ($validator->fails()) {
            return response(view('kontakt.kontakt')->with('formerrors', $validator)->with('title', trans('titles.kontakt'))->with('navbarFocus', 'kontakt')->with("css", ["css/contact.css"])->with("js", ["js/contact.js"]));
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
            $ip = $request->ip();
            $date = (new Carbon())->toRfc822String();

            $message_id = Str::uuid()->toString();
            $from_host = substr($replyTo, strpos($replyTo, "@") + 1);
            $boundary = md5($message);
            $boundary_inline = md5($message_id);
            $postdata = <<<POSTDATA
            MIME-Version: 1.0
            Received: by $ip with HTTP; $date
            Date: $date
            Delivered-To: $to_mail
            Message-ID: <$message_id@$from_host>
            Subject: $subject
            From: $name <$replyTo>
            To: $to_mail
            Content-Type: multipart/mixed; boundary=$boundary

            --$boundary
            Content-Type: multipart/alternative; boundary=$boundary_inline

            --$boundary_inline
            Content-Type: text/plain; charset=UTF-8

            $message

            --$boundary_inline--
            POSTDATA;

            if ($request->has("attachments") && is_array($request->file("attachments"))) {
                foreach ($request->file("attachments") as $attachment) {
                    $file_content = base64_encode(file_get_contents($attachment->getRealPath()));
                    $filename = $attachment->getClientOriginalName();
                    $file_mimetype = $attachment->getMimeType();
                    $postdata .= PHP_EOL . "--$boundary" . PHP_EOL;
                    $postdata .= <<<ATTACHMENT
                    Content-Type: $file_mimetype; charset=utf-8; name="$filename"
                    Content-Disposition: attachment; filename="$filename"
                    Content-Transfer-Encoding: base64

                    $file_content
                    ATTACHMENT;
                }
            }
            $postdata .= PHP_EOL . "--$boundary--" . PHP_EOL;

            $resulthash = md5($subject . $message);

            $mission = [
                "resulthash" => $resulthash,
                "url" => config("metager.metager.ticketsystem.url"),
                "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
                "username" => null,
                "password" => null,
                "headers" => [
                    "X-API-Key" => config("metager.metager.ticketsystem.apikey"),
                    "Content-Length" => strlen($postdata)
                ],
                "cacheDuration" => 0,
                "name" => "Ticket",
                "curlopts" => [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $postdata,
                    CURLOPT_LOW_SPEED_TIME => 60,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT => 60
                ]
            ];
            $mission = json_encode($mission);
            Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);

            // Fetch the result
            $answer = Redis::blpop($resulthash, 20);

            // Fehlerfall
            if (empty($answer) || (is_array($answer) && sizeof($answer) === 2 && $answer[1] === "no-result")) {
                $messageType = "error";
                $returnMessage = trans('kontakt.error.2', ["email" => $to_mail]);
            } else {
                $returnMessage = trans('kontakt.success.1', ["email" => $replyTo]);
                $messageType = "success";
            }
        }

        return response(view('kontakt.kontakt')
            ->with('title', 'Kontakt')
            ->with($messageType, $returnMessage)
            ->with("css", ["css/contact.css"])
            ->with("js", ["js/contact.js"]));
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