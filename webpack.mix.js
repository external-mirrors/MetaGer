let mix = require("laravel-mix");

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
  /*
  @import "./font-awesome/fontawesome.less";
  @import "./font-awesome/solid.less";
  @import "./bootstrap/bootstrap.less";
  */
  // css
  .styles(["resources/css/noheader.css"], "public/css/noheader.css")
  .less("resources/less/metager/metager.less", "public/css/themes/metager.css", {
    strictMath: true
  })
  .less("resources/less/metager/startpage-only-light.less", "public/css/themes/startpage-only-light.css", {
    strictMath: true
  })
  .less("resources/less/metager/startpage-only-dark.less", "public/css/themes/startpage-only-dark.css", {
    strictMath: true
  })
  .less("resources/less/metager/pages/spam.less", "public/css/spam.css", {
    strictMath: true
  })
  .less("resources/less/metager/metager-dark.less", "public/css/themes/metager-dark.css", {
    strictMath: true
  })
    strictMath: true
  })
  .less("resources/less/metager/pages/key.less", "public/css/key.css", {
    strictMath: true
  })
  .less("resources/less/metager/pages/key-dark.less", "public/css/key-dark.css", {
    strictMath: true
  })
  .less("resources/less/utility.less", "public/css/utility.css", {
    strictMath: true
  })
  .less("resources/less/metager/pages/count/style-dark.less", "public/css/count/dark.css", {
    strictMath: true
  })
  .less("resources/less/metager/pages/count/style.less", "public/css/count/style.css", {
    strictMath: true
  })
  .less("resources/less/metager/pages/spende/danke.less", "public/css/spende/danke.css", {
    strictMath: true
  })
  // js
  .babel(
    [
      "resources/js/lib/md5.js"
    ],
    "public/js/lib.js"
  )
  .babel(
    [
      "resources/js/scriptSettings.js"
    ],
    "public/js/scriptSettings.js"
  )
  .babel(
    [
      "resources/js/scriptResultPage.js",
      "resources/js/result-saver.js",
      "resources/js/translations.js",
      "resources/js/keyboardNavigation.js"
    ],
    "public/js/scriptResultPage.js"
  )
  .babel("resources/js/editLanguage.js", "public/js/editLanguage.js")
  .babel("resources/js/donation.js", "public/js/donation.js")
  // utility
  .babel(
    ["resources/js/utility.js", "resources/js/translations.js"],
    "public/js/utility.js"
  )
  .babel("resources/js/widgets.js", "public/js/widgets.js")
  .babel("resources/js/scriptJoinPage.js", "public/js/scriptJoinPage.js")
  // source maps
  .sourceMaps(false, "inline-source-map")
  // versioning
  .version();
