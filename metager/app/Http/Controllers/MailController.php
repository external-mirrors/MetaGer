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
        ContactMail::dispatch($to_mail, $group, $name, $email, $subject, $message, $attachments, "text/plain")->onQueue("general");

        $returnMessage = trans('kontakt.success.1', ["email" => $email]);
        $messageType = "success";
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
}