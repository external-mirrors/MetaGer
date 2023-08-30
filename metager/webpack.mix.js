let mix = require("laravel-mix");

require("laravel-mix-polyfill");

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
  .less(
    "resources/less/metager/pages/startpage/light.less",
    "public/css/themes/startpage/light.css"
  )
  .less(
    "resources/less/metager/pages/startpage/dark.less",
    "public/css/themes/startpage/dark.css"
  )
  .less(
    "resources/less/metager/pages/admin/spam/style.less",
    "public/css/admin/spam/style.css"
  )
  .less(
    "resources/less/metager/pages/admin/spam/dark.less",
    "public/css/admin/spam/dark.css"
  )
  .less(
    "resources/less/metager/metager-dark.less",
    "public/css/themes/metager-dark.css"
  )
  .less('resources/less/metager/pages/adblocker.less', 'public/css/adblocker.css')
  .less("resources/less/metager/pages/contact.less", "public/css/contact.css")
  .less("resources/less/utility.less", "public/css/utility.css")
  .less(
    "resources/less/metager/pages/lang-selector.less",
    "public/css/lang-selector.css"
  )
  .less(
    "resources/less/metager/pages/plugin-page.less",
    "public/css/plugin-page.css"
  )
  .less(
    "resources/less/metager/pages/count/style-dark.less",
    "public/css/count/dark.css"
  )
  .less(
    "resources/less/metager/pages/count/style.less",
    "public/css/count/style.css"
  )
  .less(
    "resources/less/metager/pages/verify/index.less",
    "public/css/verify/index.css"
  )
  .less(
    "resources/less/metager/pages/admin/affilliates/index.less",
    "public/css/admin/affilliates/index.css"
  )
  .less(
    "resources/less/metager/pages/admin/affilliates/index-dark.less",
    "public/css/admin/affilliates/index-dark.css"
  )
  .less(
    "resources/less/metager/pages/admin/bot/index.less",
    "public/css/admin/bot/index.css"
  )
  .less(
    "resources/less/metager/pages/asso/style-dark.less",
    "public/css/asso/dark.css"
  )
  .less(
    "resources/less/metager/pages/asso/style.less",
    "public/css/asso/style.css"
  )
  .less(
    "resources/less/metager/pages/spende/base.less",
    "public/css/spende.css"
  )
  .less(
    "resources/less/metager/pages/spende/base-dark.less",
    "public/css/spende-dark.css"
  )
  .less(
    "resources/less/metager/pages/membership/base.less",
    "public/css/membership.css"
  )
  .less(
    "resources/less/metager/pages/membership/base-dark.less",
    "public/css/membership-dark.css"
  )
  .less(
    "resources/less/metager/pages/prevention-information.less",
    "public/css/prevention-information.css"
  )
  .less(
    "resources/less/metager/pages/widget/widget-template.less",
    "public/css/widget/widget-template.css"
  )
  .less(
    "resources/less/metager/pages/widget/widget.less",
    "public/css/widget/widget.css"
  )
  .less(
    "resources/less/metager/pages/privacy.less",
    "public/css/privacy.css"
  )
  .js(["resources/js/suggest.js"], "public/js/suggest.js")
  .js(["resources/js/scriptSettings.js"], "public/js/scriptSettings.js")
  .js(
    [
      //   'node_modules/chart.js/dist/chart.js',
      "resources/js/admin/count.js",
    ],
    "public/js/admin/count.js"
  )
  .js(
    ["resources/js/scriptResultPage.js", "resources/js/keyboardNavigation.js"],
    "public/js/scriptResultPage.js"
  )
  .js("resources/js/aaresultpage.js", "public/js/aaresultpage.js")
  .js(["resources/js/contact.js"], "public/js/contact.js")
  .js("resources/js/editLanguage.js", "public/js/editLanguage.js")
  .js(["resources/js/donation/base.js", "resources/js/donation/paypal-options.js", "resources/js/donation/paypal-card.js", "resources/js/donation/paypal-subscription.js"], "public/js/donation.js")
  // utility
  .js(
    ["resources/js/utility.js", "resources/js/translations.js"],
    "public/js/utility.js"
  )
  .js("resources/js/widgets.js", "public/js/widgets.js")
  .js("resources/js/scriptJoinPage.js", "public/js/scriptJoinPage.js")
  .js(
    "resources/js/admin/affilliates/index.js",
    "public/js/admin/affilliates.js"
  )
  .js("resources/js/admin/spam.js", "public/js/admin/spam.js")
  .js("resources/js/admin/bot.js", "public/js/admin/bot.js")
  .js("resources/js/verify.js", "public/js/index.js")
  .js("resources/js/membership.js", "public/js/membership.js")
  .polyfill({
    enabled: true,
    useBuiltIns: "usage",
    targets: "firefox 50, IE 11",
  })
  // source maps
  .sourceMaps(false, "inline-source-map")
  // versioning
  .version();
