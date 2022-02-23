<?php

use Illuminate\Support\Facades\Redis;
use Jenssegers\Agent\Agent;
use Prometheus\RenderTextFormat;
use Illuminate\Http\Request;
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

Route::get('/', 'StartpageController@loadStartPage')->name("startpage")->middleware("removekey");

Route::get('asso', function () {
    return view('assoziator.asso')
        ->with('title', trans('titles.asso'))
        ->with('navbarFocus', 'dienste')
        ->with('css', [mix('css/asso/style.css')])
        ->with('darkcss', [mix('css/asso/dark.css')]);
});

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
        ->with('url', $url);
})->name("contact");

Route::post('kontakt', 'MailController@contactMail');

Route::get('tor', function () {
    return view('tor')
        ->with('title', 'tor hidden service - MetaGer')
        ->with('navbarFocus', 'dienste');
});

Route::group(['prefix' => 'spende'], function(){
    Route::get('/', function () {
        return view('spende.spende')
            ->with('title', trans('titles.spende'))
            ->with('js', [mix('/js/donation.js')])
            ->with('navbarFocus', 'foerdern');
    })->name("spende");

    Route::post('/', 'MailController@donation');

    Route::get('paypal', 'MailController@donationPayPalCallback')->name('paypal-callback');

    Route::get('danke/{data?}', function ($data) {
        return view('spende.danke')
            ->with('title', trans('titles.spende'))
            ->with('navbarFocus', 'foerdern')
            ->with('css', [mix('/css/spende/danke.css')])
            ->with('data', unserialize(base64_decode($data)));
    })->name("danke");
});

Route::get('partnershops', function () {
    return view('spende.partnershops')
        ->with('title', trans('titles.partnershops'))
        ->with('navbarFocus', 'foerdern');
});

Route::get('beitritt', function () {
    if (LaravelLocalization::getCurrentLocale() === "de") {
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

Route::get('search-engine', function () {
    return view('search-engine')
        ->with('title', trans('titles.search-engine'))
        ->with('navbarFocus', 'info');
});
Route::get('hilfe', function () {
    return view('help/help')
        ->with('title', trans('titles.help'))
        ->with('navbarFocus', 'hilfe');
});
Route::get('hilfe/easy-language', function () {
    return view('help/easy-language/help')
        ->with('title', trans('titles.help'))
        ->with('navbarFocus', 'hilfe')
        ->with('css', [
            mix('/css/help-easy-language.css'),
        ]);
});
Route::get('hilfe/faktencheck', function () {
    return view('help/faktencheck')
        ->with('title', trans('titles.faktencheck'))
        ->with('navbarFocus', 'hilfe');
});

Route::get('hilfe/hauptseiten', function () {
    return view('help/help-mainpages')
        ->with('title', trans('titles.help-mainpages'))
        ->with('navbarFocus', 'hilfe');
});
Route::get('hilfe/easy-language/hauptseiten', function () {
    return view('help/easy-language/help-mainpages')
        ->with('title', trans('titles.help-mainpages'))
        ->with('navbarFocus', 'hilfe')
        ->with('css', [
            mix('/css/help-easy-language.css'),
        ]);
});

Route::get('hilfe/funktionen', function () {
    return view('help/help-functions')
        ->with('title', trans('titles.help-functions'))
        ->with('navbarFocus', 'hilfe');
});

Route::get('hilfe/easy-language/funktionen', function () {
    return view('help/easy-language/help-functions')
        ->with('title', trans('titles.help-functions'))
        ->with('navbarFocus', 'hilfe')
        ->with('css', [
            mix('/css/help-easy-language.css'),
        ]);
});

Route::get('hilfe/dienste', function () {
    return view('help/help-services')
        ->with('title', trans('titles.help-services'))
        ->with('navbarFocus', 'hilfe');
});

Route::get('hilfe/easy-language/dienste', function () {
    return view('help/easy-language/help-services')
        ->with('title', trans('titles.help-services'))
        ->with('navbarFocus', 'hilfe')
        ->with('css', [
            mix('/css/help-easy-language.css'),
        ]);
});

Route::get('hilfe/datensicherheit', function () {
    return view('help/help-privacy-protection')
        ->with('title', trans('titles.help-privacy-protection'))
        ->with('navbarFocus', 'hilfe');
});

Route::get('hilfe/easy-language/datensicherheit', function () {
    return view('help/easy-language/help-privacy-protection')
        ->with('title', trans('titles.help-privacy-protection'))
        ->with('navbarFocus', 'hilfe')
        ->with('css', [
            mix('/css/help-easy-language.css'),
        ]);
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
    return view('widget.websearch')
        ->with('title', trans('titles.websearch'))
        ->with('navbarFocus', 'dienste')
        ->with('template', view('widget.websearch-template')->render());
});

Route::get('zitat-suche', 'ZitatController@zitatSuche');

Route::get('jugendschutz', function () {
    return view('jugendschutz')
        ->with('title', trans('titles.jugendschutz'));
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

Route::group(['middleware' => ['auth.basic'], 'prefix' => 'admin'], function () {
    Route::get('/', 'AdminInterface@index');
    Route::match(['get', 'post'], 'count', 'AdminInterface@count');
    Route::get('timings', 'MetaGerSearch@searchTimings');
    Route::get('count/graphtoday.svg', 'AdminInterface@countGraphToday');
    Route::get('engine/stats.json', 'AdminInterface@engineStats');
    Route::get('check', 'AdminInterface@check');
    Route::get('engines', 'AdminInterface@engines');
    Route::get('ip', function (Request $request) {
        dd($request->ip(), $_SERVER["AGENT"]);
    });
    Route::get('bot', 'HumanVerification@botOverview');
    Route::post('bot', 'HumanVerification@botOverviewChange');
    Route::group(['prefix' => 'spam'], function () {
        Route::get('/', 'AdminSpamController@index');
        Route::post('/', 'AdminSpamController@ban');
        Route::get('jsonQueries', 'AdminSpamController@jsonQueries');
        Route::post('queryregexp', 'AdminSpamController@queryregexp');
        Route::post('deleteRegexp', 'AdminSpamController@deleteRegexp');
    });
    Route::get('stress', 'Stresstest@index');
    Route::get('stress/verify', 'Stresstest@index')->middleware('browserverification', 'humanverification');
    Route::get('adgoal', 'AdgoalTestController@index')->name("adgoal-index");
    Route::post('adgoal', 'AdgoalTestController@post')->name("adgoal-generate");
    Route::post('adgoal/generate-urls', 'AdgoalTestController@generateUrls')->name("adgoal-urls");

    Route::group(['prefix' => 'affiliates'], function () {
        Route::get('/', 'AdgoalController@adminIndex');
        Route::get('/json/blacklist', 'AdgoalController@blacklistJson');
        Route::put('/json/blacklist', 'AdgoalController@addblacklistJson');
        Route::delete('/json/blacklist', 'AdgoalController@deleteblacklistJson');
        Route::get('/json/whitelist', 'AdgoalController@whitelistJson');
        Route::put('/json/whitelist', 'AdgoalController@addwhitelistJson');
        Route::delete('/json/whitelist', 'AdgoalController@deletewhitelistJson');
        Route::get('/json/hosts', 'AdgoalController@hostsJson');
        Route::get('/json/hosts/clicks', 'AdgoalController@hostClicksJson');
    });
});

Route::get('settings', function () {
    return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/'));
});

Route::match(['get', 'post'], 'meta/meta.ger3', 'MetaGerSearch@search')->middleware('removekey', 'browserverification', 'humanverification', 'useragentmaster')->name("resultpage");

Route::get('meta/loadMore', 'MetaGerSearch@loadMore');
Route::post('img/cat.png', 'HumanVerification@remove');
Route::get('verify/metager/{id}/{uid}', ['as' => 'captcha', 'uses' => 'HumanVerification@captcha']);
Route::get('r/metager/{mm}/{pw}/{url}', ['as' => 'humanverification', 'uses' => 'HumanVerification@removeGet']);
Route::post('img/dog.jpg', 'HumanVerification@whitelist');
Route::get('index.css', 'HumanVerification@browserVerification');
Route::get('index.js', function (Request $request) {
    $key = $request->input("id", "");

    // Verify that key is a md5 checksum
    if (!preg_match("/^[a-f0-9]{32}$/", $key)) {
        abort(404);
    }

    Redis::connection(config('cache.stores.redis.connection'))->rpush("js" . $key, true);
    Redis::connection(config('cache.stores.redis.connection'))->expire($key, 30);

    return response("", 200)->header("Content-Type", "application/javascript");
});

Route::get('meta/picture', 'Pictureproxy@get');
Route::get('clickstats', 'LogController@clicklog');
Route::get('pluginClose', 'LogController@pluginClose');
Route::get('pluginInstall', 'LogController@pluginInstall');

Route::get('qt', 'MetaGerSearch@quicktips');
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
Route::get('languages', 'LanguageController@createOverview');
Route::get('synoptic/{exclude?}/{chosenFile?}', 'LanguageController@createSynopticEditPage');
Route::post('synoptic/{exclude?}/{chosenFile?}', 'LanguageController@processSynopticPageInput');
Route::get('languages/edit/{from}/{to}/{exclude?}/{email?}', 'LanguageController@createEditPage');
Route::post('languages/edit/{from}/{to}/{exclude?}/{email?}', 'MailController@sendLanguageFile');

Route::group(['prefix' => 'app'], function () {
    Route::get('/', function () {
        return view('app')
            ->with('title', trans('titles.app'))
            ->with('navbarFocus', 'dienste');
    });
    Route::get('metager', function () {
        return response()->streamDownload(function () {
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
        }, 'MetaGerSearch.apk', ["Content-Type" => "application/vnd.android.package-archive"]);
    });
    Route::get('maps', function () {
        return response()->streamDownload(function () {
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
        }, 'MetaGerMaps.apk', ["Content-Type" => "application/vnd.android.package-archive"]);
    });

    Route::get('maps/version', function () {
        $filePath = config("metager.metager.maps.version");
        $fileContents = file_get_contents($filePath);
        return response($fileContents, 200)
            ->header('Content-Type', 'text/plain');
    });
});

Route::get('metrics', function (Request $request) {
    // Only allow access to metrics from within our network
    $ip = $request->ip();
    $allowedNetworks = [
        "10.",
        "172.",
        "192.",
        "127.0.0.1",
    ];

    $allowed = false;
    foreach ($allowedNetworks as $part) {
        if (stripos($ip, $part) === 0) {
            $allowed = true;
        }
    }

    if (!$allowed) {
        abort(401);
    }

    $registry = \Prometheus\CollectorRegistry::getDefault();

    $renderer = new RenderTextFormat();
    $result = $renderer->render($registry->getMetricFamilySamples());

    return response($result, 200)
        ->header('Content-Type', RenderTextFormat::MIME_TYPE);
});

Route::group(['prefix' => 'partner'], function () {
    Route::get('r', 'AdgoalController@forward')->name('adgoal-redirect');
});

Route::group(['prefix' => 'health-check'], function () {
    Route::get('liveness', 'HealthcheckController@liveness');
    Route::get('liveness-scheduler', 'HealthcheckController@livenessScheduler');
    Route::get('liveness-worker', 'HealthcheckController@livenessWorker');
});
