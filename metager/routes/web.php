<?php

use App\Http\Controllers\DonationController;
use App\Http\Controllers\LangSelector;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\Prometheus;
use App\Http\Controllers\SearchEngineList;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\TTSController;
use App\Localization;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get("robots.txt", function (Request $request) {
    $responseData = "";
    if (App::environment("production")) {
        $responseData = view("robots.production");
    } else {
        $responseData = view("robots.development");
    }
    return response($responseData, 200, ["Content-Type" => "text/plain"]);
});

/** ADD ALL LOCALIZED ROUTES INSIDE THIS GROUP **/

Route::get('/', 'StartpageController@loadStartPage')->name("startpage");

Route::get('asso', function () {
    return view('assoziator.asso')
        ->with('title', trans('titles.asso'))
        ->with('navbarFocus', 'dienste')
        ->with('css', [mix('css/asso/style.css')])
        ->with('darkcss', [mix('css/asso/dark.css')]);
})->name("asso");

Route::get('tts', [TTSController::class, 'tts'])->name("tts");

Route::get('asso/meta.ger3', 'Assoziator@asso')->middleware('browserverification:assoresults', 'humanverification')->name("assoresults");

Route::get('impressum', function () {
    return view('impressum')
        ->with('title', trans('titles.impressum'))
        ->with('navbarFocus', 'kontakt');
})->name('impress');
Route::get('impressum.html', function () {
    return redirect(url('impressum'));
});

Route::group(["prefix" => 'suggest'], function () {
    Route::get("partner", [SuggestionController::class, "partner"])->name("suggest_partner");
    Route::get("suggest", [SuggestionController::class, "suggest"])->name("suggest_suggest");
});

Route::get('about', function () {
    return view('about')
        ->with('title', trans('titles.about'))
        ->with('navbarFocus', 'info');
});
Route::get('team', function () {
    return view('team.team')
        ->with('title', trans('titles.team'))
        ->with('navbarFocus', 'kontakt');
});
Route::get('team/pubkey-wsb', function () {
    return view('team.pubkey-wsb')
        ->with('title', trans('titles.team'))
        ->with('navbarFocus', 'kontakt');
});

Route::get('kontakt/{url?}', function ($url = "") {
    return view('kontakt.kontakt')
        ->with('title', trans('titles.kontakt'))
        ->with('navbarFocus', 'kontakt')
        ->with('url', $url)
        ->with('js', [mix('js/contact.js')])
        ->with("css", [mix("css/contact.css")]);
})->name("contact");

Route::post('kontakt', 'MailController@contactMail');
Route::get('adblocker', function () {
    return response(view('adblocker', ["title" => __("titles.adblocker"), 'css' => [mix('/css/adblocker.css')]]));
})->name("adblocker");

Route::group(["prefix" => "membership"], function () {
    Route::get("/", [MembershipController::class, "contactData"])->name("membership_form");
    Route::post("/", [MembershipController::class, "submitMembershipForm"]);
    Route::get("/success", [MembershipController::class, "success"])->name("membership_success");
});

Route::get('tor', function () {
    return view('tor')
        ->with('title', 'tor hidden service - MetaGer')
        ->with('navbarFocus', 'dienste');
});

Route::group(['prefix' => 'spende'], function () {
    Route::get('/', [DonationController::class, "amount"])->name("spende");
    Route::get('/qr', [DonationController::class, "amountQr"]);
    Route::get('/{amount}', [DonationController::class, "interval"]);
    Route::get('/{amount}/{interval}', [DonationController::class, "paymentMethod"]);
    Route::get('/{amount}/{interval}/{funding_source}/{timestamp}/finished', [DonationController::class, "donationFinished"])->name("thankyou");
    Route::get('/{amount}/{interval}/banktransfer', [DonationController::class, 'banktransfer']);
    Route::get('/{amount}/{interval}/directdebit', [DonationController::class, 'directdebit']);
    Route::post('/{amount}/{interval}/directdebit', [DonationController::class, 'directdebitExecute']);
    Route::get('/{amount}/{interval}/banktransfer/qr', [DonationController::class, 'banktransferQr']);
    Route::get('/{amount}/{interval}/paypal/{funding_source}', [DonationController::class, 'paypalPayment']);
    Route::get('/{amount}/{interval}/paypal/{funding_source}/order', [DonationController::class, 'paypalCreateOrder']);
    Route::post('/{amount}/{interval}/paypal/{funding_source}/order', [DonationController::class, 'paypalCaptureOrder']);
});

Route::get('partnershops', function () {
    return view('spende.partnershops')
        ->with('title', trans('titles.partnershops'))
        ->with('navbarFocus', 'foerdern');
})->name("partnershops");

Route::get('beitritt', function () {
    if (Localization::getLanguage() === "de") {
        return response()->download(storage_path('app/public/aufnahmeantrag-de.pdf'), "SUMA-EV_Beitrittsformular_" . (new \DateTime())->format("Y_m_d") . ".pdf", ["Content-Type" => "application/pdf"]);
    } else {
        return response()->download(storage_path('app/public/aufnahmeantrag-en.pdf'), "SUMA-EV_Membershipform_" . (new \DateTime())->format("Y_m_d") . ".pdf", ["Content-Type" => "application/pdf"]);
    }
})->name("beitritt");

Route::get('bform1.htm', function () {
    return redirect('beitritt');
});



Route::get('datenschutz', function () {
    return view('privacy')
        ->with('css', [mix('/css/privacy.css')])
        ->with('navbarFocus', 'datenschutz');
});

Route::get('transparency', function () {
    return view('transparency')
        ->with('title', trans('titles.transparency'))
        ->with('navbarFocus', 'info');
});

Route::get('search-engine', [SearchEngineList::class, 'index']);
Route::get('hilfe', function () {
    return view('help/help')
        ->with('title', trans('titles.help'))
        ->with('navbarFocus', 'hilfe');
});

Route::get('hilfe/faktencheck', function () {
    return view('help/faktencheck')
        ->with('title', trans('titles.faktencheck'))
        ->with('navbarFocus', 'hilfe');
})->name('faktencheck');

Route::get('hilfe/hauptseiten', function () {
    return view('help/help-mainpages')
        ->with('title', trans('titles.help-mainpages'))
        ->with('navbarFocus', 'hilfe');
});

Route::get('hilfe/funktionen', function () {
    return view('help/help-functions')
        ->with('title', trans('titles.help-functions'))
        ->with('navbarFocus', 'hilfe');
});

Route::get('hilfe/dienste', function () {
    return view('help/help-services')
        ->with('title', trans('titles.help-services'))
        ->with('navbarFocus', 'hilfe');
});

Route::get('hilfe/datensicherheit', function () {
    return view('help/help-privacy-protection')
        ->with('title', trans('titles.help-privacy-protection'))
        ->with('navbarFocus', 'hilfe');
});

Route::get('faq', function () {
    return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/hilfe'));
});

Route::get('widget', function () {
    return view('widget.widget')
        ->with('title', trans('titles.widget'))
        ->with('navbarFocus', 'dienste');
});

Route::get('sitesearch', 'SitesearchController@loadPage');

Route::get('websearch', function () {
    $css = file_get_contents(public_path("css/widget/widget-template.css"));
    return view('widget.websearch')
        ->with('title', trans('titles.websearch'))
        ->with('navbarFocus', 'dienste')
        ->with('css', [mix('css/widget/widget.css'), mix('css/widget/widget-template.css')])
        ->with('template_preview', view('widget.websearch-template')->render())
        ->with('template_webpage', view('widget.websearch-template', ["css" => $css])->render());
});

Route::get('zitat-suche', 'ZitatController@zitatSuche');

Route::get('jugendschutz', function () {
    return view('jugendschutz')
        ->with('title', trans('titles.jugendschutz'));
});


Route::get('prevention', function () {
    return view('prevention-information')
        ->with('title', trans('titles.prevention'))
        ->with('css', [mix('/css/prevention-information.css')]);
});

Route::get('ad-info', function () {
    return view('ad-info')
        ->with('title', trans('titles.ad-info'));
});

Route::get('age.xml', function () {
    $response = Response::make(file_get_contents(resource_path('age/age.xml')));
    $response->header('Content-Type', "application/xml");
    return $response;
});
Route::get('age-de.xml', function () {
    $response = Response::make(file_get_contents(resource_path('age/age-de.xml')));
    $response->header('Content-Type', "application/xml");
    return $response;
});

Route::get('plugin', function (Request $request) {
    return view('plugin-page')
        ->with('title', trans('titles.plugin'))
        ->with('navbarFocus', 'dienste')
        ->with('agent', new Agent())
        ->with('request', $request->input('request', 'GET'))
        ->with('browser', (new Agent())->browser())
        ->with('css', [
            mix('/css/plugin-page.css'),
        ]);
})->name("plugin");

Route::get('settings', function () {
    return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/'));
});

Route::match(['get', 'post'], 'meta/meta.ger3', 'MetaGerSearch@search')->middleware('httpcache', 'externalimagesearch', 'spam', 'browserverification', 'humanverification', 'useragentmaster')->name("resultpage");

Route::get('meta/loadMore', 'MetaGerSearch@loadMore');


Route::get('meta/picture', 'Pictureproxy@get')->name("imageproxy");
Route::get('clickstats', 'LogController@clicklog');
Route::get('pluginClose', 'LogController@pluginClose');
Route::get('pluginInstall', 'LogController@pluginInstall');

Route::get('tips', 'MetaGerSearch@tips');
Route::get('/plugins/opensearch.xml', 'StartpageController@loadPlugin');
Route::get('owi', function () {
    return redirect('https://metager.de/klassik/en/owi/');
});
Route::get('MG20', function () {
    return redirect('https://metager.de/klassik/MG20');
});
Route::get('databund', function () {
    return redirect('https://metager.de/klassik/databund');
});
Route::get("lang", [LangSelector::class, "index"])->name("lang-selector");

Route::group(['prefix' => 'app'], function () {
    Route::get(
        '/',
        function () {
            return view('app')
                ->with('title', trans('titles.app'))
                ->with('navbarFocus', 'dienste');
        }
    );
    Route::get(
        'metager',
        function () {
            return response()->streamDownload(
                function () {
                    $fh = null;
                    try {
                        $fh = fopen("https://gitlab.metager.de/open-source/app-en/-/raw/latest/app/release_manual/app-release_manual.apk", "r");
                        while (!feof($fh)) {
                            echo (fread($fh, 1024));
                        }
                    } catch (\Exception $e) {
                        abort(404);
                    } finally {
                        if ($fh != null) {
                            fclose($fh);
                        }
                    }
                }
                ,
                'MetaGerSearch.apk',
                ["Content-Type" => "application/vnd.android.package-archive"]
            );
        }
    );
    Route::get(
        'maps',
        function () {
            return response()->streamDownload(
                function () {
                    $fh = null;
                    try {
                        $fh = fopen("https://gitlab.metager.de/open-source/metager-maps-android/raw/latest/app/release/app-release.apk?inline=false", "r");
                        while (!feof($fh)) {
                            echo (fread($fh, 1024));
                        }
                    } catch (\Exception $e) {
                        abort(404);
                    } finally {
                        if ($fh != null) {
                            fclose($fh);
                        }
                    }
                }
                ,
                'MetaGerMaps.apk',
                ["Content-Type" => "application/vnd.android.package-archive"]
            );
        }
    );

    Route::get(
        'maps/version',
        function () {
            $filePath = config("metager.metager.maps.version");
            $fileContents = file_get_contents($filePath);
            return response($fileContents, 200)
                ->header('Content-Type', 'text/plain');
        }
    );
});

Route::group(["prefix" => "metrics", "middleware" => "allow-local-only"], function (Router $router) {
    $router->get('/', [Prometheus::class, "metrics"]);
});


Route::group(['prefix' => 'partner'], function () {
    Route::get('r', 'AdgoalController@forward')->name('adgoal-redirect');
});

Route::group(['prefix' => 'health-check'], function () {
    Route::get('liveness', 'HealthcheckController@liveness');
    Route::get('liveness-scheduler', 'HealthcheckController@livenessScheduler');
    Route::get('liveness-worker', 'HealthcheckController@livenessWorker');
});