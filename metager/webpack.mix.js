let mix = require("laravel-mix");

require('laravel-mix-polyfill');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix

  // css
  .styles(["resources/css/noheader.css"], "public/css/noheader.css")
  .less("resources/less/metager/metager.less", "public/css/themes/metager.css")
  .less("resources/less/metager/startpage-only-light.less", "public/css/themes/startpage-only-light.css")
  .less("resources/less/metager/startpage-only-dark.less", "public/css/themes/startpage-only-dark.css")
  .less("resources/less/metager/pages/admin/spam/style.less", "public/css/admin/spam/style.css")
  .less("resources/less/metager/pages/admin/spam/dark.less", "public/css/admin/spam/dark.css")
  .less("resources/less/metager/metager-dark.less", "public/css/themes/metager-dark.css")
  .less("resources/less/metager/pages/key.less", "public/css/key.css")
  .less("resources/less/metager/pages/key-dark.less", "public/css/key-dark.css")
  .less("resources/less/utility.less", "public/css/utility.css")
  .less("resources/less/metager/pages/plugin-page.less", "public/css/plugin-page.css")
  .less("resources/less/metager/pages/count/style-dark.less", "public/css/count/dark.css")
  .less("resources/less/metager/pages/count/style.less", "public/css/count/style.css")
  .less("resources/less/metager/pages/admin/affilliates/index.less", "public/css/admin/affilliates/index.css")
  .less("resources/less/metager/pages/admin/affilliates/index-dark.less", "public/css/admin/affilliates/index-dark.css")
  .less("resources/less/metager/pages/asso/style-dark.less", "public/css/asso/dark.css")
  .less("resources/less/metager/pages/asso/style.less", "public/css/asso/style.css")
  .less("resources/less/metager/pages/spende/danke.less", "public/css/spende/danke.css")
  .less("resources/less/metager/pages/keychange/index.less", "public/css/keychange/index.css")
  .js(
    [
      "resources/js/scriptSettings.js"
    ],
    "public/js/scriptSettings.js"
  )
  .js(
    [
      //   'node_modules/chart.js/dist/chart.js',
      'resources/js/admin/count.js'
    ],
    'public/js/admin/count.js'
  )
  .js(
    [
      "resources/js/lib/md5.js",
      "resources/js/scriptResultPage.js",
      "resources/js/result-saver.js",
      "resources/js/translations.js",
      "resources/js/keyboardNavigation.js"
    ],
    "public/js/scriptResultPage.js"
  )
  .js("resources/js/editLanguage.js", "public/js/editLanguage.js")
  .js("resources/js/donation.js", "public/js/donation.js")
  // utility
  .js(
    ["resources/js/utility.js", "resources/js/translations.js"],
    "public/js/utility.js"
  )
  .js("resources/js/widgets.js", "public/js/widgets.js")
  .js("resources/js/scriptJoinPage.js", "public/js/scriptJoinPage.js")
  .js("resources/js/admin/affilliates/index.js", "public/js/admin/affilliates.js")
  .polyfill({
    enabled: true,
    useBuiltIns: "usage",
    targets: "firefox 50, IE 11"
  })
  // source maps
  .sourceMaps(false, "inline-source-map")
  // versioning
  .version();