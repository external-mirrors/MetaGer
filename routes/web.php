<?php

use Illuminate\Support\Facades\Redis;
use Jenssegers\Agent\Agent;
use Prometheus\RenderTextFormat;

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
Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(), /*,
    'middleware' => [ 'localeSessionRedirect', 'localizationRedirect' ]*/
    ],
    function () {
        /** ADD ALL LOCALIZED ROUTES INSIDE THIS GROUP **/

        Route::get('/', 'StartpageController@loadStartPage')->name("startpage");

        Route::get('asso', function () {
            return view('assoziator.asso')
                ->with('title', trans('titles.asso'))
                ->with('navbarFocus', 'dienste');
        });
        Route::post('asso', 'Assoziator@asso');

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
                ->with('navbarFocus', 'kontakt');
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
        });

        Route::post('kontakt', 'MailController@contactMail');

        Route::get('tor', function () {
            return view('tor')
                ->with('title', 'tor hidden service - MetaGer')
                ->with('navbarFocus', 'dienste');
        });
        Route::get('spende', function () {
            return view('spende.spende')
                ->with('title', trans('titles.spende'))
                ->with('navbarFocus', 'foerdern');
        });
        Route::get('spende/danke/{data}', ['as' => 'danke', function ($data) {
            return view('spende.danke')
                ->with('title', trans('titles.spende'))
                ->with('navbarFocus', 'foerdern')
                ->with('data', unserialize(base64_decode($data)));
        }]);
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
        });

        Route::get('bform1.htm', function () {
            return redirect('beitritt');
        });

        Route::post('spende', 'MailController@donation');

        Route::get('datenschutz', function () {
            return view('datenschutz/datenschutz')
                ->with('title', trans('titles.datenschutz'))
                ->with('navbarFocus', 'datenschutz');
        });

        Route::get('hilfe', function () {
            return view('hilfe')
                ->with('title', trans('titles.hilfe'))
                ->with('navbarFocus', 'hilfe');
        });

        Route::get('hilfe/faktencheck', function () {
            return view('faktencheck')
                ->with('title', trans('titles.faktencheck'))
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

        Route::get('plugin', function () {
            return view('plugin-page')
                ->with('title', trans('titles.plugin'))
                ->with('navbarFocus', 'dienste')
                ->with('agent', new Agent())
                ->with('browser', (new Agent())->browser())
                ->with('request', $this->input('request', 'GET'));
        });

        Route::group(['middleware' => ['auth.basic'], 'prefix' => 'admin'], function () {
            Route::get('/', 'AdminInterface@index');
            Route::match(['get', 'post'], 'count', 'AdminInterface@count');
            Route::get('timings', 'MetaGerSearch@searchTimings');
            Route::get('count/graphtoday.svg', 'AdminInterface@countGraphToday');
            Route::get('engine/stats.json', 'AdminInterface@engineStats');
            Route::get('check', 'AdminInterface@check');
            Route::get('engines', 'AdminInterface@engines');
            Route::get('ip', function () {
                dd(Request::ip(), $_SERVER["AGENT"]);
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
            Route::post('service-desk', 'ServiceDesk@webhook');
            Route::get('stress', 'Stresstest@index');
            Route::get('stress/search', 'Stresstest@search');
        });

        Route::get('settings', function () {
            return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/'));
        });

        Route::match(['get', 'post'], 'meta/meta.ger3', 'MetaGerSearch@search')->middleware('browserverification', 'humanverification', 'useragentmaster')->name("resultpage");

        Route::get('meta/loadMore', 'MetaGerSearch@loadMore');
        Route::post('img/cat.jpg', 'HumanVerification@remove');
        Route::get('verify/metager/{id}/{uid}', ['as' => 'captcha', 'uses' => 'HumanVerification@captcha', 'middleware' => 'throttle:12,1']);
        Route::get('r/metager/{mm}/{pw}/{url}', ['as' => 'humanverification', 'uses' => 'HumanVerification@removeGet']);
        Route::post('img/dog.jpg', 'HumanVerification@whitelist');
        Route::get('index.css', 'HumanVerification@browserVerification');
        Route::get('index.js', function () {
            $key = Request::input("id", "");

            // Verify that key is a md5 checksum
            if (!preg_match("/^[a-f0-9]{32}$/", $key)) {
                abort(404);
            }

            Redis::connection("cache")->pipeline(function ($redis) use ($key) {
                $redis->rpush("js" . $key, true);
                $redis->expire($key, 30);
            });

            return response("", 200)->header("Content-Type", "application/js");
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
                            echo(fread($fh, 1024));
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
                            echo(fread($fh, 1024));
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
                $filePath = env('maps_version');
                $fileContents = file_get_contents($filePath);
                return response($fileContents, 200)
                    ->header('Content-Type', 'text/plain');
            });
        });

        Route::get('metrics', function () {
            $registry = \Prometheus\CollectorRegistry::getDefault();

            $renderer = new RenderTextFormat();
            $result = $renderer->render($registry->getMetricFamilySamples());

            return response($result, 200)
                ->header('Content-Type', RenderTextFormat::MIME_TYPE);
        });
    }
);
