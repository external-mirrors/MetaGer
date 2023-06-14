<?php

namespace App\Http\Controllers;

use App\Jobs\ContactMail;
use App\Localization;
use App\Mail\Sprachdatei;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LaravelLocalization;
use Mail;
use Validator;

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
        // Nachricht, die wir an den Nutzer weiterleiten:
        $messageType = ""; # [success|error]
        $returnMessage = '';


        // Wir benötigen 3 Felder von dem Benutzer wenn diese nicht übermittelt wurden, oder nicht korrekt sind geben wir einen Error zurück
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
                'message' => 'required',
                'subject' => 'required'
            ]
            ,
            ["size" => trans("validation.pcsrf")]
        );

        if ($validator->fails()) {
            return response(
                view('kontakt.kontakt')
                    ->with('formerrors', $validator)
                    ->with('title', trans('titles.kontakt'))
                    ->with('navbarFocus', 'kontakt')
                    ->with("css", [mix("css/contact.css")])
                    ->with("js", [mix("js/contact.js")])
            );
        }

        $to_mail = Localization::getLanguage() === "de" ? config("metager.metager.ticketsystem.germanmail") : config("metager.metager.ticketsystem.englishmail");
        $group = Localization::getLanguage() === "de" ? "MetaGer (DE)" : "MetaGer (EN)";
        $name = $request->input('name', '');
        $email = $request->input('email', 'noreply@metager.de');
        $message = $request->input('message');
        $subject = $request->input('subject');

        $attachments = [];
        if ($request->has("attachments") && is_array($request->file("attachments"))) {
            foreach ($request->file("attachments") as $attachment) {
                $file_content = base64_encode(file_get_contents($attachment->getRealPath()));
                $filename = $attachment->getClientOriginalName();
                $file_mimetype = $attachment->getMimeType();
                $attachments[] = [
                    "filename" => $filename,
                    "data" => $file_content,
                    "mime-type" => $file_mimetype
                ];
            }
        }
        ContactMail::dispatch($to_mail, $group, $name, $email, $subject, $message, $attachments)->onQueue("contact");

        $returnMessage = trans('kontakt.success.1', ["email" => $email]);
        $messageType = "success";
        $test = LaravelLocalization::getNonLocalizedURL(mix("/css/contact.css"));
        return response(view('kontakt.kontakt')
            ->with('title', 'Kontakt')
            ->with($messageType, $returnMessage)
            ->with("css", [mix("/css/contact.css")])
            ->with("js", [mix('/js/contact.js')]));
    }

    // Ueberprueft ob ein bereits vorhandener Eintrag bearbeitet worden ist
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