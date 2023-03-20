<?php

use App\Http\Controllers\Prometheus;
use App\Http\Controllers\SearchEngineList;
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
});
Route::get('impressum.html', function () {
    return redirect(url('impressum'));
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

Route::get('tor', function () {
    return view('tor')
        ->with('title', 'tor hidden service - MetaGer')
        ->with('navbarFocus', 'dienste');
});

Route::group(['prefix' => 'spende'], function () {
    Route::get(
        '/',
        function () {
            return view('spende.spende')
                ->with('title', trans('titles.spende'))
                ->with('js', [mix('/js/donation.js')])
                ->with('navbarFocus', 'foerdern');
        }
    )->name("spende");

    Route::post('/', 'MailController@donation');

    Route::get('paypal', 'MailController@donationPayPalCallback')->name('paypal-callback');

    Route::get(
        'danke/{data?}',
        function ($data) {
            return view('spende.danke')
                ->with('title', trans('titles.spende'))
                ->with('navbarFocus', 'foerdern')
                ->with('css', [mix('/css/spende/danke.css')])
                ->with('data', unserialize(base64_decode($data)));
        }
    )->name("danke");
});

Route::get('partnershops', function () {
    return view('spende.partnershops')
        ->with('title', trans('titles.partnershops'))
        ->with('navbarFocus', 'foerdern');
});

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
    return view('datenschutz/datenschutz')
        ->with('title', trans('titles.datenschutz'))
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
});

Route::get('settings', function () {
    return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/'));
});

Route::match(['get', 'post'], 'meta/meta.ger3', 'MetaGerSearch@search')->middleware("settings-migration", 'httpcache', 'spam', 'browserverification', 'humanverification', 'useragentmaster')->name("resultpage");

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
Route::get("lang", function () {
    // Check if a previous URL is given that we can offer a back button for
    $previous = request()->input("previous_url", URL::previous());

    $allowed_hosts = [
        "metager.de",
        "metager.org"
    ];

    $components = parse_url($previous);
    $previous_url = null; // URL for the back button
    if (is_array($components) && array_key_exists("host", $components)) {
        $host = $components["host"];
        $current_host = request()->getHost();

        $path = "/";
        if (array_key_exists("path", $components)) {
            $path = $components["path"];
        }
        if (array_key_exists("query", $components)) {
            $path .= "?" . $components["query"];
        }
        if (($host === $current_host || in_array($current_host, $allowed_hosts)) && preg_match("/^http(s)?:\/\//", $previous)) { // only if the host of that URL matches the current host
            $previous_url = LaravelLocalization::getLocalizedUrl(null, $path);
        }
    }

    return view('lang-selector')
        ->with("previous_url", $previous_url)
        ->with("title", trans("titles.lang-selector"))
        ->with('css', [mix('css/lang-selector.css')]);
})->name("lang-selector");
Route::get('languages', 'LanguageController@createOverview');
Route::get('synoptic/{exclude?}/{chosenFile?}', 'LanguageController@createSynopticEditPage');
Route::post('synoptic/{exclude?}/{chosenFile?}', 'LanguageController@processSynopticPageInput');
Route::get('languages/edit/{from}/{to}/{exclude?}/{email?}', 'LanguageController@createEditPage');
Route::post('languages/edit/{from}/{to}/{exclude?}/{email?}', 'MailController@sendLanguageFile');

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